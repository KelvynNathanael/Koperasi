@extends('layouts.app')

@section('title', 'Edit Anggota')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Anggota</a></li>
    <li class="breadcrumb-item"><a href="{{ route('members.show', $member) }}">{{ $member->full_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Edit Anggota</h4>
        <p class="text-muted small mb-0">{{ $member->member_code }} – {{ $member->full_name }}</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-primary"></i>Edit Data Anggota
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('members.update', $member) }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Kode Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="member_code" class="form-control @error('member_code') is-invalid @enderror"
                               value="{{ old('member_code', $member->member_code) }}" required>
                        @error('member_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name', $member->full_name) }}" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                               value="{{ old('phone_number', $member->phone_number) }}">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="3">{{ old('address', $member->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active"    {{ old('status', $member->status) === 'active'    ? 'selected' : '' }}>Aktif</option>
                            <option value="nonactive" {{ old('status', $member->status) === 'nonactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2">{{ old('notes', $member->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
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
