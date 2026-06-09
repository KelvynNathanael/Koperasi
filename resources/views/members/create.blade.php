@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Anggota</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Tambah Anggota Baru</h4>
        <p class="text-muted small mb-0">Isi data anggota koperasi</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus me-2 text-primary"></i>Data Anggota
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('members.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Kode Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="member_code" class="form-control @error('member_code') is-invalid @enderror"
                               value="{{ old('member_code', $code) }}" required>
                        <div class="form-text text-muted">Kode otomatis, bisa diubah jika perlu. Format: 00000-DD-MM-YYYY</div>
                        @error('member_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name') }}" placeholder="Masukkan nama lengkap" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                               value="{{ old('phone_number') }}" placeholder="Contoh: 081234567890">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="3" placeholder="Alamat lengkap anggota">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active"    {{ old('status', 'active') === 'active'    ? 'selected' : '' }}>Aktif</option>
                            <option value="nonactive" {{ old('status') === 'nonactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Anggota
                        </button>
                        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
