<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashFlow;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\Repayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepaymentController extends Controller
{
    public function index(Request $request): View
    {
        $repayments = Repayment::with(['installment.loan.member'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());

        return view('repayments.index', compact('repayments'));
    }

    public function create(LoanInstallment $installment): View
    {
        $installment->load('loan.member');
        return view('repayments.create', compact('installment'));
    }

    public function store(Request $request, LoanInstallment $installment): RedirectResponse
    {
        $remaining = $installment->remainingDue();

        $data = $request->validate([
            'amount'       => "required|numeric|min:0.01|max:{$remaining}",
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $installment) {
            $this->recordRepayment($installment, $data['amount'], $data['payment_date'], $data['notes'] ?? null);
        });

        return redirect()->route('loans.show', $installment->loan_id)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function bulkStore(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->status === 'cancelled') {
            return response()->json([
                'message' => 'Pinjaman yang dibatalkan tidak bisa menerima pembayaran.',
            ], 422);
        }

        $data = $request->validate([
            'payment_date'           => 'required|date',
            'notes'                  => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.installment_id' => 'required|integer|distinct|exists:loan_installments,id',
            'items.*.amount'         => 'required|numeric|min:0.01',
        ]);

        $installments = LoanInstallment::whereIn('id', collect($data['items'])->pluck('installment_id'))
            ->where('loan_id', $loan->id)
            ->get()
            ->keyBy('id');

        $errors = [];

        foreach ($data['items'] as $item) {
            $installment = $installments->get($item['installment_id']);

            if (!$installment) {
                $errors[] = "Cicilan #{$item['installment_id']} tidak ditemukan pada pinjaman ini.";
                continue;
            }
            if ($installment->status === 'paid') {
                $errors[] = "Cicilan #{$installment->installment_number} sudah lunas.";
                continue;
            }
            if ($item['amount'] > $installment->remainingDue()) {
                $errors[] = "Jumlah bayar cicilan #{$installment->installment_number} melebihi sisa tagihan.";
            }
        }

        if (!empty($errors)) {
            return response()->json(['message' => implode(' ', $errors)], 422);
        }

        DB::transaction(function () use ($data, $installments) {
            foreach ($data['items'] as $item) {
                $installment = $installments->get($item['installment_id']);
                $this->recordRepayment($installment, $item['amount'], $data['payment_date'], $data['notes'] ?? null);
            }
        });

        return response()->json([
            'message' => 'Pembayaran bulk untuk ' . count($data['items']) . ' cicilan berhasil dicatat.',
        ]);
    }

    private function recordRepayment(LoanInstallment $installment, float $amount, string $paymentDate, ?string $notes): Repayment
    {
        $repayment = Repayment::create([
            'installment_id' => $installment->id,
            'amount'         => $amount,
            'payment_date'   => $paymentDate,
            'notes'          => $notes,
        ]);

        $installment->syncStatus();

        $loan = $installment->loan;
        $loan->syncBalance();

        CashFlow::create([
            'transaction_date' => $paymentDate,
            'flow_type'        => 'in',
            'category'         => 'repayment',
            'amount'           => $amount,
            'member_id'        => $loan->member_id,
            'reference_type'   => 'repayments',
            'reference_id'     => $repayment->id,
            'description'      => "Pembayaran cicilan #{$installment->installment_number} – Pinjaman #{$loan->id}",
        ]);

        AuditLog::record('repayments', $repayment->id, 'created', null, $repayment->toArray());

        return $repayment;
    }
}