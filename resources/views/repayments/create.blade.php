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
            @php
                $dayNames = [
                    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
                ];
            @endphp
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3 text-center">
                        <div class="text-muted small mb-1">Cicilan ke-</div>
                        <div class="fw-bold fs-4 text-primary">{{ $installment->installment_number }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3 text-center">
                        <div class="text-muted small mb-1">Jatuh Tempo</div>
                        <div class="fw-bold small
                            @php
                                $dayName = $installment->due_date ? $dayNames[$installment->due_date->format('l')] : null;
                            @endphp
                            {{ $installment->isLate() ? 'text-danger' : '' }}">
                            {{ $installment->due_date ? "$dayName, " . $installment->due_date->format('d/m/Y') : '—' }}
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
                <form method="POST" action="{{ route('repayments.store', $installment) }}" id="paymentForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount"
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
                        <input type="text" name="payment_date" id="payment_date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                               autocomplete="off" required>
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

@push('styles')
{{-- Hapus baris ini kalau flatpickr sudah dimuat global di layout --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
{{-- Hapus dua baris ini kalau flatpickr sudah dimuat global di layout --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

{{-- Hapus baris ini kalau SweetAlert2 sudah dimuat global di layout --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tanggal: tampil "Hari, dd/mm/yyyy" (bahasa Indonesia),
    // tapi value asli yang dikirim ke server tetap format Y-m-d
    const fp = flatpickr('#payment_date', {
        locale: 'id',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'l, d/m/Y',
        maxDate: 'today',
        allowInput: true,
    });

    const form = document.getElementById('paymentForm');
    const amountInput = document.getElementById('amount');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const amount = parseFloat(amountInput.value || 0);
        const amountFormatted = 'Rp ' + amount.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
        const displayDate = fp.altInput ? fp.altInput.value : document.getElementById('payment_date').value;

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `Anda akan mencatat pembayaran untuk cicilan <b>#{{ $installment->installment_number }}</b><br>
                   sebesar <b>${amountFormatted}</b><br>
                   pada tanggal <b>${displayDate}</b>.<br><br>
                   Lanjutkan simpan pembayaran?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush