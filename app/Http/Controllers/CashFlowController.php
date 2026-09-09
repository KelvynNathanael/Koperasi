<?php

namespace App\Http\Controllers;

use App\Exports\CashFlowsExport;
use App\Models\AuditLog;
use App\Models\CashFlow;
use App\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class CashFlowController extends Controller
{
    public function index(Request $request): View
    {
        $query = CashFlow::with('member');

        if ($type = $request->input('flow_type')) {
            $query->where('flow_type', $type);
        }

        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }

        if ($from = $request->input('from')) {
            $query->where('transaction_date', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('transaction_date', '<=', $to);
        }

        $cashFlows = $query->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());

        $balance  = CashFlow::currentBalance();
        $totalIn  = CashFlow::where('flow_type', 'in')->sum('amount');
        $totalOut = CashFlow::where('flow_type', 'out')->sum('amount');

        return view('cash-flows.index', compact('cashFlows', 'balance', 'totalIn', 'totalOut'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('full_name')->get();
        return view('cash-flows.create', compact('members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'flow_type'        => 'required|in:in,out',
            'category'         => 'required|in:contribution,loan_disbursement,repayment,expense,adjustment',
            'amount'           => 'required|numeric|min:0.01',
            'member_id'        => 'nullable|exists:members,id',
            'description'      => 'nullable|string',
        ]);

        $cf = CashFlow::create($data);
        AuditLog::record('cash_flows', $cf->id, 'created', null, $cf->toArray());

        return redirect()->route('cash-flows.index')
            ->with('success', 'Transaksi kas berhasil dicatat.');
    }

    /**
     * Export daftar arus kas (menghormati filter aktif di halaman index) ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $export = new CashFlowsExport(
            $request->input('flow_type'),
            $request->input('category'),
            $request->input('from'),
            $request->input('to'),
        );

        return Excel::download($export, 'arus-kas-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Rekap arus kas bulanan dalam bentuk PDF (untuk laporan ke pengurus/RAT).
     */
    public function exportRecapPdf(Request $request): Response
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $flows = CashFlow::with('member')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->orderBy('transaction_date')
            ->get();

        $totalIn  = (float) $flows->where('flow_type', 'in')->sum('amount');
        $totalOut = (float) $flows->where('flow_type', 'out')->sum('amount');

        $byCategory = $flows->groupBy('category')->map(fn ($rows, $cat) => [
            'label' => CashFlow::categoryLabel($cat),
            'in'    => (float) $rows->where('flow_type', 'in')->sum('amount'),
            'out'   => (float) $rows->where('flow_type', 'out')->sum('amount'),
        ]);

        $periodStart = sprintf('%04d-%02d-01', $year, $month);

        $inBefore  = (float) CashFlow::where('flow_type', 'in')->where('transaction_date', '<', $periodStart)->sum('amount');
        $outBefore = (float) CashFlow::where('flow_type', 'out')->where('transaction_date', '<', $periodStart)->sum('amount');

        $openingBalance = bcsub((string) $inBefore, (string) $outBefore, 2);
        $closingBalance = bcadd($openingBalance, bcsub((string) $totalIn, (string) $totalOut, 2), 2);

        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->locale('id')->translatedFormat('F');

        $pdf = Pdf::loadView('cash-flows.pdf-recap', [
            'flows'          => $flows,
            'totalIn'        => $totalIn,
            'totalOut'       => $totalOut,
            'byCategory'     => $byCategory,
            'openingBalance' => (float) $openingBalance,
            'closingBalance' => (float) $closingBalance,
            'month'          => $month,
            'year'           => $year,
            'monthName'      => $monthName,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("rekap-bulanan-{$year}-{$month}.pdf");
    }
}