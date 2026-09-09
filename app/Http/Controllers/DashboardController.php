<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\Member;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Kas saat ini ──────────────────────────────────────────────────────
        $currentCash = CashFlow::currentBalance();

        // ── Total piutang aktif (remaining_balance dari pinjaman aktif/overdue) ─
        $totalReceivable = Loan::whereIn('status', ['active', 'overdue'])
            ->sum('remaining_balance');

        // ── Anggota aktif ─────────────────────────────────────────────────────
        $activeMembers = Member::where('status', 'active')->count();

        // ── Cicilan jatuh tempo bulan ini yang belum lunas ────────────────────
        $dueThisMonth = LoanInstallment::whereIn('status', ['unpaid', 'partial'])
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->count();

        // ── Cicilan terlambat ─────────────────────────────────────────────────
        $overdueInstallments = LoanInstallment::whereIn('status', ['unpaid', 'partial', 'late'])
            ->where('due_date', '<', now()->toDateString())
            ->count();

        // ── List cicilan jatuh tempo HARI INI ──────────────────────────────────
        $dueTodayList = LoanInstallment::with('loan.member')
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', now()->toDateString())
            ->orderBy('due_date')
            ->get();

        // ── List cicilan yang SUDAH LEWAT jatuh tempo ─────────────────────────
        $overdueList = LoanInstallment::with('loan.member')
            ->whereIn('status', ['unpaid', 'partial', 'late'])
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->get();

        // ── Riwayat transaksi terbaru ─────────────────────────────────────────
        $recentTransactions = CashFlow::with('member')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Grafik kas 6 bulan terakhir ───────────────────────────────────────
        $cashChart = CashFlow::select(
            DB::raw("TO_CHAR(transaction_date, 'YYYY-MM') as month"),
            'flow_type',
            DB::raw('SUM(amount) as total')
        )
            ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month', 'flow_type')
            ->orderBy('month')
            ->get();

        // ── Pencairan pinjaman bulan ini ("omset penyaluran") ───────────────────
        $monthlyDisbursement = CashFlow::where('category', 'loan_disbursement')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // ── Cicilan masuk bulan ini ("omset penerimaan") ─────────────────────────
        $monthlyRepaymentIn = CashFlow::where('category', 'repayment')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // ── Laba (bunga terealisasi) bulan ini ───────────────────────────────────
        // ASUMSI: reference_type pada baris cash_flows berkategori 'repayment'
        // diisi 'loans' dan reference_id = loans.id (mengikuti pola Loan::cashFlows()).
        // Kalau ternyata reference_type = 'loan_installments', join ini harus
        // diubah lewat tabel loan_installments dulu. Cek di kode yang membuat
        // CashFlow untuk pembayaran cicilan sebelum pakai angka ini di produksi.
        $monthlyProfit = DB::table('cash_flows')
            ->join('repayments', function ($join) {
                $join->on('cash_flows.reference_id', '=', 'repayments.id')
                    ->where('cash_flows.reference_type', '=', 'repayments');
            })
            ->join('loan_installments', 'repayments.installment_id', '=', 'loan_installments.id')
            ->join('loans', 'loan_installments.loan_id', '=', 'loans.id')
            ->where('cash_flows.category', 'repayment')
            ->whereMonth('cash_flows.transaction_date', now()->month)
            ->whereYear('cash_flows.transaction_date', now()->year)
            ->selectRaw('
                COALESCE(SUM(
                    repayments.amount
                    * (loans.total_due - loans.principal_amount)
                    / NULLIF(loans.total_due, 0)
                ), 0) as profit
            ')
            ->value('profit');

        return view('dashboard.index', compact(
            'currentCash', 'totalReceivable', 'activeMembers',
            'dueThisMonth', 'overdueInstallments', 'recentTransactions',
            'cashChart', 'dueTodayList', 'overdueList',
            'monthlyDisbursement', 'monthlyRepaymentIn', 'monthlyProfit'
        ));
    }
}