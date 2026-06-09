@extends('layouts.app')

@section('title', 'Bayar Cicilan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Pinjaman</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('loans.show', $installment->loan) }}">Pinjaman #{{ $installment->loan->id }}</a>
    </li>
    <li class="breadcrumb-item active">Bayar Cicilan #{{ $installment->installment_number }}</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('loans.show', $installment->loan) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Pembayaran Cicilan</h4>
        <p class="text-muted small mb-0">
            Cicilan #{{ $installment->installment_number }} –
            {{ $installment->loan->member->full_name }}
        </p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Installment Info --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3 text-center">
                        <div class="text-muted small mb-1">Cicilan ke-</div>
                        <div class="fw-bold fs-4 text-primary">{{ $installment->installment_number }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3 text-center">
                        <div class="text-muted small mb-1">Jatuh Tempo</div>
                        <div class="fw-bold small
                            {{ $installment->isLate() ? 'text-danger' : '' }}">
                            {{ $installment->due_date ? $installment->due_date->format('d/m/Y') : '—' }}
                        </div>
                        @if($installment->isLate())
                            <span class="badge bg-danger mt-1" style="font-size:.65rem;">Terlambat</span>
                        @endif
                    </div>
                    <div class="col-sm-6 col-md-3 text-center">
                        <div class="text-muted small mb-1">Tagihan</div>
                        <div class="fw-bold small">
                            Rp {{ number_format($installment->scheduled_amount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3 text-center">
                        <div class="text-muted small mb-1">Sisa Tagihan</div>
                        <div class="fw-bold text-danger small">
                            Rp {{ number_format($installment->remainingDue(), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Form --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cash me-2 text-primary"></i>Form Pembayaran
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('repayments.store', $installment) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount"
                               class="form-control form-control-lg @error('amount') is-invalid @enderror"
                               value="{{ old('amount', $installment->remainingDue()) }}"
                               min="0.01"
                               max="{{ $installment->remainingDue() }}"
                               step="0.01" required>
                        <div class="form-text text-muted">
                            Maksimal: Rp {{ number_format($installment->remainingDue(), 0, ',', '.') }}
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                        @error('payment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="Catatan pembayaran (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info small py-2 mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Pembayaran akan otomatis dicatat ke arus kas sebagai pemasukan.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Pembayaran
                        </button>
                        <a href="{{ route('loans.show', $installment->loan) }}"
                           class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Previous repayments --}}
        @if($installment->repayments->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-clock-history me-2 text-secondary"></i>Riwayat Pembayaran Cicilan Ini
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-end">Jumlah</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($installment->repayments as $rep)
                            <tr>
                                <td class="small">{{ $rep->payment_date->format('d/m/Y') }}</td>
                                <td class="text-end small fw-semibold text-success">
                                    Rp {{ number_format($rep->amount, 0, ',', '.') }}
                                </td>
                                <td class="small text-muted">{{ $rep->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
