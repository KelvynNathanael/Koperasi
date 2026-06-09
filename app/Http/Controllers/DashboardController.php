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

        return view('dashboard.index', compact(
            'currentCash', 'totalReceivable', 'activeMembers',
            'dueThisMonth', 'overdueInstallments', 'recentTransactions',
            'cashChart'
        ));
    }
}