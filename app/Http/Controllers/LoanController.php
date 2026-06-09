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

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Loan::with('member');

        if ($search = $request->input('search')) {
            $query->whereHas('member', fn ($q) => $q->where('full_name', 'ilike', "%$search%")
                ->orWhere('member_code', 'ilike', "%$search%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $loans = $query->orderByDesc('created_at')
        ->paginate(20)
        ->appends($request->query());

        return view('loans.index', compact('loans'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('full_name')->get();
        return view('loans.create', compact('members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id'       => 'required|exists:members,id',
            'principal_amount'=> 'required|numeric|min:1',
            'interest_percent'=> 'required|numeric|min:0|max:100',
            'duration_months' => 'required|integer|min:1|max:360',
            'start_date'      => 'required|date',
            'notes'           => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $principal = $data['principal_amount'];
            $interest  = $data['interest_percent'];
            $totalDue  = bcadd(
                $principal,
                bcmul($principal, bcdiv($interest, '100', 6), 2),
                2
            );

            $loan = Loan::create([
                'member_id'         => $data['member_id'],
                'principal_amount'  => $principal,
                'interest_percent'  => $interest,
                'total_due'         => $totalDue,
                'remaining_balance' => $totalDue,
                'duration_months'   => $data['duration_months'],
                'start_date'        => $data['start_date'],
                'status'            => 'active',
                'notes'             => $data['notes'] ?? null,
            ]);

            // Generate jadwal cicilan
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

    public function updateStatus(Request $request, Loan $loan): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:active,overdue,cancelled']);

        $old = $loan->toArray();
        $loan->update($data);
        AuditLog::record('loans', $loan->id, 'updated', $old, $loan->fresh()->toArray());

        return back()->with('success', 'Status pinjaman diperbarui.');
    }
}