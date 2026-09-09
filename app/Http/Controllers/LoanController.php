<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashFlow;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\LoanInstallment;
use Illuminate\Http\JsonResponse;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Loan::with('member');

        if ($search = $request->input('search')) {
            $query->whereHas('member', fn ($q) => $q->where('full_name', 'ilike', "%$search%")
                ->orWhere('member_code', 'ilike', "%$search%"));
        }

        $status = $request->query('status');

        if ($status === null) {
            $query->where('status', 'active');
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $frequency = $request->query('frequency');

        if ($frequency && $frequency !== 'all') {
            $query->where('installment_frequency', $frequency);
        }

        $loans = $query->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $selectedStatus    = $status ?? 'active';
        $selectedFrequency = $frequency ?? 'all';

        return view('loans.index', compact('loans', 'selectedStatus', 'selectedFrequency'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('full_name')->get();
        return view('loans.create', compact('members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id'              => 'required|exists:members,id',
            'principal_amount'       => 'required|numeric|min:1',
            'interest_percent'       => 'required|numeric|min:0|max:100',
            'installment_frequency'  => 'required|in:daily,weekly,monthly',
            'duration_months'        => 'required|integer|min:1|max:360',
            'start_date'             => 'required|date',
            'notes'                  => 'nullable|string',
        ]);

        $lock = \Illuminate\Support\Facades\Cache::lock('loan-create-' . auth()->id(), 5);
        
        if (! $lock->get()) {
            return back()->with('error', 'Permintaan sedang diproses, coba lagi sebentar.');
        }

        DB::transaction(function () use ($data) {
            $principal = $data['principal_amount'];
            $interest  = $data['interest_percent'];
            $totalDue  = bcadd(
                $principal,
                bcmul($principal, bcdiv($interest, '100', 6), 2),
                2
            );

            $loan = Loan::create([
                'member_id'              => $data['member_id'],
                'principal_amount'       => $principal,
                'interest_percent'       => $interest,
                'total_due'              => $totalDue,
                'remaining_balance'      => $totalDue,
                'installment_frequency'  => $data['installment_frequency'],
                'duration_months'        => $data['duration_months'],
                'start_date'             => $data['start_date'],
                'status'                 => 'active',
                'notes'                  => $data['notes'] ?? null,
            ]);

            $loan->generateInstallments();

            // Catat ke cash_flows sebagai uang keluar
            CashFlow::create([
                'transaction_date' => $data['start_date'],
                'flow_type'        => 'out',
                'category'         => 'loan_disbursement',
                'amount'           => $principal,
                'member_id'        => $data['member_id'],
                'reference_type'   => 'loans',
                'reference_id'     => $loan->id,
                'description'      => "Pencairan pinjaman #{$loan->id} – {$loan->member->full_name}",
            ]);

            AuditLog::record('loans', $loan->id, 'created', null, $loan->toArray());
        });

        return redirect()->route('loans.index')
            ->with('success', 'Pinjaman berhasil dibuat dan jadwal cicilan telah dibuat otomatis.');
    }

    public function show(Loan $loan): View
    {
        $loan->load(['member', 'installments.repayments']);
        return view('loans.show', compact('loan'));
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        if (!in_array($loan->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Hanya pinjaman dengan status Lunas atau Dibatalkan yang bisa dihapus.');
        }

        DB::transaction(function () use ($loan) {
            $old = $loan->toArray();

            $loan->installments()->each(function ($installment) {
                $installment->repayments()->delete();
            });
            $loan->installments()->delete();

            AuditLog::record('loans', $loan->id, 'deleted', $old, null);

            $loan->delete();
        });

        return redirect()->route('loans.index')->with('success', 'Pinjaman berhasil dihapus.');
    }

    public function updateStatus(Request $request, Loan $loan): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:active,overdue,cancelled']);

        $old = $loan->toArray();
        $loan->update($data);
        AuditLog::record('loans', $loan->id, 'updated', $old, $loan->fresh()->toArray());

        return back()->with('success', 'Status pinjaman diperbarui.');
    }

    public function updateInstallmentDueDate(Request $request, LoanInstallment $installment): JsonResponse
    {
        if ($installment->status === 'paid') {
            return response()->json([
                'message' => 'Cicilan yang sudah lunas tidak bisa diubah jatuh temponya.',
            ], 422);
        }

        $data = $request->validate([
            'due_date' => 'required|date',
            'cascade'  => 'required|boolean',
        ]);

        $loan    = $installment->loan;
        $oldDate = $installment->due_date->copy();
        $newDate = \Carbon\Carbon::parse($data['due_date']);

        $updatedInstallments = DB::transaction(function () use ($installment, $loan, $oldDate, $newDate, $data) {
            $installment->due_date = $newDate;
            $installment->save();

            if (!$data['cascade']) {
                return collect([$installment]);
            }

            // Hitung pergeseran: pakai business-day steps kalau daily (biar Sabtu→Senin dihitung 1 langkah, bukan 2),
            // selain itu (weekly/monthly) pakai selisih kalender biasa karena gak ada aturan skip Minggu di sana.
            if ($loan->installment_frequency === 'daily') {
                $steps = Loan::businessDayDiff($oldDate, $newDate);
            } else {
                $steps = $oldDate->diffInDays($newDate, false);
            }

            if ($steps !== 0) {
                $installment->loan->installments()
                    ->where('installment_number', '>', $installment->installment_number)
                    ->where('status', '!=', 'paid')
                    ->each(function (LoanInstallment $inst) use ($steps, $loan) {
                        $inst->due_date = $loan->installment_frequency === 'daily'
                            ? Loan::addBusinessDays($inst->due_date, $steps)
                            : $inst->due_date->copy()->addDays($steps);
                        $inst->save();
                    });
            }

            return $installment->loan->installments()
                ->where('installment_number', '>=', $installment->installment_number)
                ->get();
        });

        $dayNameMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];

        return response()->json([
            'message' => $data['cascade']
                ? 'Jatuh tempo cicilan #' . $installment->installment_number . ' diubah, cicilan berikutnya ikut menyesuaikan.'
                : 'Jatuh tempo cicilan #' . $installment->installment_number . ' diubah tanpa mempengaruhi cicilan lain.',
            'installments' => $updatedInstallments->map(fn ($i) => [
                'id'           => $i->id,
                'due_date'     => $i->due_date->format('Y-m-d'),
                'due_date_fmt' => $i->due_date->format('d/m/Y'),
                'day_name'     => $dayNameMap[$i->due_date->format('l')],
            ]),
        ]);
    }

}