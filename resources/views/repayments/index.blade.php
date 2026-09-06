@extends('layouts.app')

@section('title', 'Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pembayaran</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Riwayat Pembayaran</h4>
        <p class="text-muted small mb-0">Semua catatan pembayaran cicilan</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-arrow-return-left me-2 text-primary"></i>Pembayaran</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Anggota</th>
                    <th>Pinjaman</th>
                    <th>Cicilan ke-</th>
                    <th class="text-end">Jumlah</th>
                    <th>Catatan</th>
                    <th class="text-center">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repayments as $rep)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td class="small">{{ $rep->payment_date->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold small">
                                {{ $rep->installment->loan->member->full_name }}
                            </div>
                            <div class="text-muted" style="font-size:.7rem;">
                                {{ $rep->installment->loan->member->member_code }}
                            </div>
                        </td>
                        <td class="small text-muted">
                            #{{ $rep->installment->loan->id }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">
                                {{ $rep->installment->installment_number }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold small text-success">
                            +Rp {{ number_format($rep->amount, 0, ',', '.') }}
                        </td>
                        <td class="small text-muted">
                            {{ $rep->notes ? \Str::limit($rep->notes, 35) : '—' }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('loans.show', $rep->installment->loan_id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Belum ada pembayaran tercatat
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($repayments->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Menampilkan {{ $repayments->firstItem() }}–{{ $repayments->lastItem() }}
                dari {{ $repayments->total() }} pembayaran
            </small>
            {{ $repayments->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection
