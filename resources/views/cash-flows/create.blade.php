@extends('layouts.app')

@section('title', 'Tambah Transaksi Kas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cash-flows.index') }}">Kas & Arus Dana</a></li>
    <li class="breadcrumb-item active">Tambah Transaksi</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('cash-flows.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Tambah Transaksi Kas</h4>
        <p class="text-muted small mb-0">Catat transaksi manual (iuran, biaya operasional, penyesuaian, dll)</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-journal-plus me-2 text-primary"></i>Detail Transaksi
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('cash-flows.store') }}" data-loading-text="Menyimpan transaksi...">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="transaction_date"
                               class="form-control @error('transaction_date') is-invalid @enderror"
                               value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="flow_type" id="flowType"
                                    class="form-select @error('flow_type') is-invalid @enderror" required>
                                <option value="">— Pilih —</option>
                                <option value="in"  {{ old('flow_type') === 'in'  ? 'selected' : '' }}>
                                    ↑ Uang Masuk
                                </option>
                                <option value="out" {{ old('flow_type') === 'out' ? 'selected' : '' }}>
                                    ↓ Uang Keluar
                                </option>
                            </select>
                            @error('flow_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">— Pilih —</option>
                                <option value="contribution"      {{ old('category') === 'contribution'      ? 'selected' : '' }}>Iuran / Kontribusi</option>
                                <option value="loan_disbursement" {{ old('category') === 'loan_disbursement' ? 'selected' : '' }}>Pencairan Pinjaman</option>
                                <option value="repayment"         {{ old('category') === 'repayment'         ? 'selected' : '' }}>Pembayaran Cicilan</option>
                                <option value="expense"           {{ old('category') === 'expense'           ? 'selected' : '' }}>Biaya Operasional</option>
                                <option value="adjustment"        {{ old('category') === 'adjustment'        ? 'selected' : '' }}>Penyesuaian</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount"
                               class="form-control form-control-lg @error('amount') is-invalid @enderror"
                               value="{{ old('amount') }}"
                               min="0.01" step="0.01" placeholder="Masukkan jumlah" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Anggota Terkait</label>
                        <select name="member_id"
                                class="form-select @error('member_id') is-invalid @enderror">
                            <option value="">— Tidak terkait anggota —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->full_name }} ({{ $m->member_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="2" placeholder="Keterangan transaksi (opsional)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Transaksi
                        </button>
                        <a href="{{ route('cash-flows.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
