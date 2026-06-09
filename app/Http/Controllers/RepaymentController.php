<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashFlow;
use App\Models\LoanInstallment;
use App\Models\Repayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepaymentController extends Controller
{
    public function index(Request $request): View
    {
        $repayments = Repayment::with([
            'installment.loan.member',
        ])->orderByDesc('payment_date')
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
            // 1. Simpan pembayaran
            $repayment = Repayment::create([
                'installment_id' => $installment->id,
                'amount'         => $data['amount'],
                'payment_date'   => $data['payment_date'],
                'notes'          => $data['notes'] ?? null,
            ]);

            // 2. Update status cicilan
            $installment->syncStatus();

            // 3. Sinkronisasi sisa hutang pinjaman
            $loan = $installment->loan;
            $loan->syncBalance();

            // 4. Catat ke cash_flows sebagai uang masuk
            CashFlow::create([
                'transaction_date' => $data['payment_date'],
                'flow_type'        => 'in',
                'category'         => 'repayment',
                'amount'           => $data['amount'],
                'member_id'        => $loan->member_id,
                'reference_type'   => 'repayments',
                'reference_id'     => $repayment->id,
                'description'      => "Pembayaran cicilan #{$installment->installment_number} – Pinjaman #{$loan->id}",
            ]);

            AuditLog::record('repayments', $repayment->id, 'created', null, $repayment->toArray());
        });

        return redirect()->route('loans.show', $installment->loan_id)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}