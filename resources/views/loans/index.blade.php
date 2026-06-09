    @extends('layouts.app')

@section('title', 'Pinjaman')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pinjaman</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Daftar Pinjaman</h4>
        <p class="text-muted small mb-0">Kelola pinjaman anggota koperasi</p>
    </div>
    <a href="{{ route('loans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Buat Pinjaman
    </a>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari Anggota</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nama atau kode anggota…"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Aktif</option>
                    <option value="overdue"   {{ request('status') === 'overdue'   ? 'selected' : '' }}>Overdue</option>
                    <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-cash-stack me-2 text-primary"></i>Pinjaman
            <span class="badge bg-secondary ms-1">{{ $loans->total() }}</span>
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Anggota</th>
                    <th>Pokok</th>
                    <th>Total Kewajiban</th>
                    <th>Sisa</th>
                    <th>Durasi</th>
                    <th>Mulai</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                    <tr>
                        <td class="text-muted small">{{ $loan->id }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $loan->member->full_name }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $loan->member->member_code }}</div>
                        </td>
                        <td class="small">Rp {{ number_format($loan->principal_amount, 0, ',', '.') }}</td>
                        <td class="small">Rp {{ number_format($loan->total_due, 0, ',', '.') }}</td>
                        <td class="small fw-semibold
                            {{ $loan->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($loan->remaining_balance, 0, ',', '.') }}
                        </td>
                        <td class="small text-muted">{{ $loan->duration_months }} bln</td>
                        <td class="small text-muted">{{ $loan->start_date->format('d/m/Y') }}</td>
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
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Tidak ada pinjaman ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($loans->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Menampilkan {{ $loans->firstItem() }}–{{ $loans->lastItem() }}
                dari {{ $loans->total() }} pinjaman
            </small>
            {{ $loans->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection
