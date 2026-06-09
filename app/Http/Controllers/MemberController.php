<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::withCount(['loans' => fn ($q) => $q->whereIn('status', ['active', 'overdue'])]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%$search%")
                  ->orWhere('member_code', 'ilike', "%$search%")
                  ->orWhere('phone_number', 'ilike', "%$search%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $members = $query->orderBy('full_name')->paginate(20)->appends($request->query());

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        $code = Member::generateCode();
        return view('members.create', compact('code'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_code'  => 'required|string|max:20|unique:members',
            'full_name'    => 'required|string|max:150',
            'phone_number' => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'status'       => 'required|in:active,nonactive',
            'notes'        => 'nullable|string',
        ]);

        $member = Member::create($data);

        AuditLog::record('members', $member->id, 'created', null, $member->toArray());

        return redirect()->route('members.show', $member)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function show(Member $member): View
    {
        $member->load(['loans' => fn ($q) => $q->orderByDesc('created_at')]);
        return view('members.show', compact('member'));
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'member_code'  => 'required|string|max:20|unique:members,member_code,' . $member->id,
            'full_name'    => 'required|string|max:150',
            'phone_number' => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'status'       => 'required|in:active,nonactive',
            'notes'        => 'nullable|string',
        ]);

        $old = $member->toArray();
        $member->update($data);
        AuditLog::record('members', $member->id, 'updated', $old, $member->fresh()->toArray());

        return redirect()->route('members.show', $member)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->loans()->whereIn('status', ['active', 'overdue'])->exists()) {
            return back()->with('error', 'Tidak dapat menghapus anggota yang masih memiliki pinjaman aktif.');
        }

        AuditLog::record('members', $member->id, 'deleted', $member->toArray(), null);
        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }
}