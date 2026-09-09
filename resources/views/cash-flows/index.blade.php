@extends('layouts.app')

@section('title', 'Kas & Arus Dana')

@section('breadcrumb')
    <li class="breadcrumb-item active">Kas & Arus Dana</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Kas & Arus Dana</h4>
        <p class="text-muted small mb-0">Catatan seluruh transaksi keuangan koperasi</p>
    </div>
    <a href="{{ route('cash-flows.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Transaksi
    </a>
</div>

{{-- Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-wallet2 me-1 text-primary"></i>Saldo Kas</div>
            <div class="fw-bold fs-5 {{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">
                Rp {{ number_format($balance, 0, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-arrow-up-circle me-1 text-success"></i>Total Masuk</div>
            <div class="fw-bold fs-5 text-success">
                Rp {{ number_format($totalIn, 0, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-center py-3">
            <div class="text-muted small mb-1"><i class="bi bi-arrow-down-circle me-1 text-danger"></i>Total Keluar</div>
            <div class="fw-bold fs-5 text-danger">
                Rp {{ number_format($totalOut, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Tipe</label>
                <select name="flow_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="in"  {{ request('flow_type') === 'in'  ? 'selected' : '' }}>Masuk</option>
                    <option value="out" {{ request('flow_type') === 'out' ? 'selected' : '' }}>Keluar</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="contribution"      {{ request('category') === 'contribution'      ? 'selected' : '' }}>Iuran / Kontribusi</option>
                    <option value="loan_disbursement" {{ request('category') === 'loan_disbursement' ? 'selected' : '' }}>Pencairan Pinjaman</option>
                    <option value="repayment"         {{ request('category') === 'repayment'         ? 'selected' : '' }}>Pembayaran Cicilan</option>
                    <option value="expense"           {{ request('category') === 'expense'           ? 'selected' : '' }}>Biaya Operasional</option>
                    <option value="adjustment"        {{ request('category') === 'adjustment'        ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('cash-flows.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-journal-text me-2 text-primary"></i>Transaksi</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Anggota</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashFlows as $cf)
                    <tr>
                        <td class="small text-muted">{{ $cf->transaction_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge rounded-pill badge-flow-{{ $cf->flow_type }}">
                                {{ $cf->flow_type === 'in' ? '↑ Masuk' : '↓ Keluar' }}
                            </span>
                        </td>
                        <td class="small">
                            {{ \App\Models\CashFlow::categoryLabel($cf->category) }}
                        </td>
                        <td class="small">{{ $cf->member?->full_name ?? '—' }}</td>
                        <td class="small text-muted">
                            {{ $cf->description ? \Str::limit($cf->description, 50) : '—' }}
                        </td>
                        <td class="text-end fw-semibold small
                            {{ $cf->flow_type === 'in' ? 'text-success' : 'text-danger' }}">
                            {{ $cf->flow_type === 'in' ? '+' : '-' }}Rp {{ number_format($cf->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Tidak ada transaksi ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cashFlows->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Menampilkan {{ $cashFlows->firstItem() }}–{{ $cashFlows->lastItem() }}
                dari {{ $cashFlows->total() }} transaksi
            </small>
            {{ $cashFlows->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection
