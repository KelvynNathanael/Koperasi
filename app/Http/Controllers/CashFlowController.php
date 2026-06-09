<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashFlow;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

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
}