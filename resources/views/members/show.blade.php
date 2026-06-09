@extends('layouts.app')

@section('title', $member->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Anggota</a></li>
    <li class="breadcrumb-item active">{{ $member->full_name }}</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h4 class="fw-bold mb-0">{{ $member->full_name }}</h4>
        <p class="text-muted small mb-0">{{ $member->member_code }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('members.edit', $member) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('loans.create') }}?member_id={{ $member->id }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Buat Pinjaman
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="mb-3" style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;margin:auto;">
                    <span class="text-white fw-bold fs-3">
                        {{ strtoupper(substr($member->full_name, 0, 1)) }}
                    </span>
                </div>
                <h5 class="fw-bold mb-1">{{ $member->full_name }}</h5>
                <p class="text-muted small mb-3">{{ $member->member_code }}</p>
                <span class="badge rounded-pill badge-status-{{ $member->status }} px-3 py-2">
                    {{ $member->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="card-footer p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-start px-4 py-3">
                        <div class="text-muted small"><i class="bi bi-telephone me-2"></i>No. HP</div>
                        <span class="small fw-semibold">{{ $member->phone_number ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start px-4 py-3">
                        <div class="text-muted small"><i class="bi bi-geo-alt me-2"></i>Alamat</div>
                        <span class="small fw-semibold text-end" style="max-width:180px;">
                            {{ $member->address ?? '—' }}
                        </span>
                    </li>
                    @if($member->notes)
                    <li class="list-group-item px-4 py-3">
                        <div class="text-muted small mb-1"><i class="bi bi-sticky me-2"></i>Catatan</div>
                        <p class="small mb-0">{{ $member->notes }}</p>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <div class="text-muted small"><i class="bi bi-calendar-plus me-2"></i>Bergabung</div>
                        <span class="small fw-semibold">{{ $member->created_at->format('d/m/Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Loans Table --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-cash-stack me-2 text-primary"></i>Riwayat Pinjaman</span>
                <span class="badge bg-secondary">{{ $member->loans->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pokok</th>
                            <th>Total Kewajiban</th>
                            <th>Sisa</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($member->loans as $loan)
                            <tr>
                                <td class="text-muted small">{{ $loan->id }}</td>
                                <td class="small">Rp {{ number_format($loan->principal_amount, 0, ',', '.') }}</td>
                                <td class="small">Rp {{ number_format($loan->total_due, 0, ',', '.') }}</td>
                                <td class="small fw-semibold
                                    {{ $loan->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($loan->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="small">{{ $loan->duration_months }} bln</td>
                                <td>
                                    <span class="badge rounded-pill badge-status-{{ $loan->status }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('loans.show', $loan) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-cash-stack fs-3 d-block mb-2"></i>
                                    Belum ada pinjaman
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary Stats --}}
        @if($member->loans->count() > 0)
        <div class="row g-3 mt-0">
            <div class="col-sm-4">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-primary">{{ $member->loans->count() }}</div>
                    <div class="text-muted small">Total Pinjaman</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-success">
                        {{ $member->loans->where('status', 'paid')->count() }}
                    </div>
                    <div class="text-muted small">Lunas</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-danger">
                        {{ $member->loans->whereIn('status', ['active','overdue'])->count() }}
                    </div>
                    <div class="text-muted small">Aktif / Overdue</div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection
