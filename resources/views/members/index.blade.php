@extends('layouts.app')
@stack('scripts')

@section('title', 'Anggota')

@section('breadcrumb')
    <li class="breadcrumb-item active">Anggota</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Daftar Anggota</h4>
        <p class="text-muted small mb-0">Kelola data anggota koperasi</p>
    </div>
    <a href="{{ route('members.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah Anggota
    </a>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nama, kode, atau nomor HP…"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all"         {{ $selectedStatus === 'all'       ? 'selected' : '' }}>Semua Status</option>
                    <option value="active"      {{ $selectedStatus === 'active'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonactive"   {{ $selectedStatus === 'nonactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-people me-2 text-primary"></i>Anggota
            <span class="badge bg-secondary ms-1">{{ $members->total() }}</span>
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Lengkap</th>
                    <th>No. HP</th>
                    <th>Status</th>
                    <th>Pinjaman Aktif</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr>
                        <td>
                            <a href="{{ route('members.show', $member) }} " class="text-decoration-none">
                                <code class="text-primary">{{ $member->member_code }}</code>
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $member->full_name }}</td>
                        <td class="text-muted small">{{ $member->phone_number ?? '—' }}</td>
                        <td>
                            <span class="badge rounded-pill badge-status-{{ $member->status }}">
                                {{ $member->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($member->loans_count > 0)
                                <span class="badge bg-primary rounded-pill">{{ $member->loans_count }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('members.show', $member) }}"
                                   class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('members.edit', $member) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('members.destroy', $member) }}"
                                    class="form-delete-member" data-name="{{ $member->full_name }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-member" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-person-slash fs-3 d-block mb-2"></i>
                            Tidak ada anggota ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($members->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Menampilkan {{ $members->firstItem() }}–{{ $members->lastItem() }}
                dari {{ $members->total() }} anggota
            </small>
            {{ $members->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menghapus',
            text: @json(session('error')),
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#dc3545',
        });
    @endif

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success')),
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false,
        });
    @endif

    document.querySelectorAll('.btn-delete-member').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = btn.closest('.form-delete-member');
            const name = form.dataset.name;

            Swal.fire({
                icon: 'warning',
                title: 'Hapus Anggota?',
                text: `Anggota "${name}" akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
