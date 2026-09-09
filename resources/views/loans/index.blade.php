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
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all"       {{ $selectedStatus === 'all'       ? 'selected' : '' }}>Semua Status</option>
                    <option value="active"    {{ $selectedStatus === 'active'    ? 'selected' : '' }}>Aktif</option>
                    <option value="paid"      {{ $selectedStatus === 'paid'      ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Frekuensi Cicilan</label>
                <select name="frequency" class="form-select" onchange="this.form.submit()">
                    <option value="all"     {{ $selectedFrequency === 'all'     ? 'selected' : '' }}>Semua Frekuensi</option>
                    <option value="daily"   {{ $selectedFrequency === 'daily'   ? 'selected' : '' }}>Harian</option>
                    <option value="weekly"  {{ $selectedFrequency === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ $selectedFrequency === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="tempo"   {{ $selectedFrequency === 'tempo'   ? 'selected' : '' }}>Tempo</option>
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
                    <th>No.</th>
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
                        <td class="text-muted small">{{ $loop->iteration }}</td>
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
                        <td class="small text-muted">
                            {{ $loan->duration_months }}
                            {{ match($loan->installment_frequency) {
                                'daily'   => 'hari',
                                'weekly'  => 'minggu',
                                'tempo'    => 'tempo',
                                default   => 'bln',
                            } }}
                        </td>
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

                            @if(in_array($loan->status, ['paid', 'cancelled']))
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger ms-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteLoanModal"
                                        data-delete-url="{{ route('loans.destroy', $loan) }}"
                                        data-loan-info="#{{ $loan->id }} – {{ $loan->member->full_name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
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

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteLoanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                    Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Yakin ingin menghapus pinjaman berikut?</p>
                <p class="fw-semibold" id="deleteLoanInfo"></p>
                <p class="text-muted small mb-0">
                    Tindakan ini tidak bisa dibatalkan. Seluruh jadwal cicilan terkait juga akan ikut terhapus.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteLoanForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('deleteLoanModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const url = button.getAttribute('data-delete-url');
        const info = button.getAttribute('data-loan-info');

        document.getElementById('deleteLoanForm').setAttribute('action', url);
        document.getElementById('deleteLoanInfo').textContent = info;
    });
</script>
@endpush

@endsection
