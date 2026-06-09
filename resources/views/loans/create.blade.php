@extends('layouts.app')

@section('title', 'Buat Pinjaman')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Pinjaman</a></li>
    <li class="breadcrumb-item active">Buat Baru</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('loans.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0">Buat Pinjaman Baru</h4>
        <p class="text-muted small mb-0">Isi detail pinjaman anggota</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cash-stack me-2 text-primary"></i>Detail Pinjaman
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('loans.store') }}" id="loanForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Anggota <span class="text-danger">*</span></label>
                        <select name="member_id" id="memberId"
                                class="form-select @error('member_id') is-invalid @enderror" required>
                            <option value="">— Pilih Anggota —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}"
                                    {{ old('member_id', request('member_id')) == $m->id ? 'selected' : '' }}>
                                    {{ $m->full_name }} ({{ $m->member_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pokok Pinjaman (Rp) <span class="text-danger">*</span></label>
                            <div style="position: relative;">
                                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); 
                                            color:#6c757d; font-size:14px; pointer-events:none;">Rp</span>
                                <input type="text" id="principalDisplay"
                                    class="form-control ps-5 @error('principal_amount') is-invalid @enderror"
                                    inputmode="numeric"
                                    placeholder="0"
                                    value="{{ old('principal_amount') ? number_format(old('principal_amount'), 0, ',', '.') : '' }}"
                                    autocomplete="off">
                                <input type="hidden" name="principal_amount" id="principal"
                                    value="{{ old('principal_amount', 0) }}">
                            </div>
                            @error('principal_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bunga (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="interest_percent" id="interest"
                                       class="form-control @error('interest_percent') is-invalid @enderror"
                                       value="{{ old('interest_percent', 0) }}"
                                       min="0" max="100" step="0.01" placeholder="Contoh: 5" required>
                                <span class="input-group-text">%</span>
                            </div>
                            @error('interest_percent')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Durasi (Bulan) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_months" id="duration"
                                   class="form-control @error('duration_months') is-invalid @enderror"
                                   value="{{ old('duration_months') }}"
                                   min="1" max="360" placeholder="Contoh: 12" required>
                            @error('duration_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
                            <i class="bi bi-check-lg me-1"></i> Buat Pinjaman
                        </button>
                        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="col-lg-5">
        <div class="card" id="previewCard" style="display:none!important;">
            <div class="card-header">
                <i class="bi bi-calculator me-2 text-primary"></i>Preview Perhitungan
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted small">Pokok Pinjaman</td>
                            <td class="text-end fw-semibold small" id="prevPrincipal">—</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Bunga</td>
                            <td class="text-end fw-semibold small" id="prevInterest">—</td>
                        </tr>
                        <tr class="table-primary">
                            <td class="fw-bold small">Total Kewajiban</td>
                            <td class="text-end fw-bold small" id="prevTotal">—</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Durasi</td>
                            <td class="text-end small" id="prevDuration">—</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Cicilan / Bulan</td>
                            <td class="text-end fw-semibold small text-primary" id="prevMonthly">—</td>
                        </tr>
                    </tbody>
                </table>
                <div class="alert alert-info mt-3 mb-0 small py-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Jadwal cicilan akan dibuat otomatis setelah pinjaman disimpan.
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body py-3">
                <p class="text-muted small mb-0">
                    <i class="bi bi-lightbulb me-1 text-warning"></i>
                    Sistem akan otomatis membuat jadwal cicilan bulanan dan mencatat pencairan ke arus kas.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // --- Format Rupiah ---
    const displayInput = document.getElementById('principalDisplay');
    const hiddenInput  = document.getElementById('principal');

    function fmtNum(n) {
        return new Intl.NumberFormat('id-ID').format(Math.round(n));
    }

    function syncPrincipal(rawVal) {
        const digits = rawVal.replace(/\D/g, '');
        const num = parseInt(digits || '0', 10);
        hiddenInput.value = num;                              
        displayInput.value = digits ? fmtNum(num) : '';      
    }

    displayInput.addEventListener('input', function () {
        const cursor = this.selectionStart;
        const before = this.value.length;
        syncPrincipal(this.value);
        const after = this.value.length;
        try { this.setSelectionRange(cursor + (after - before), cursor + (after - before)); } catch (_) {}
    });

    displayInput.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && this.value[this.selectionStart - 1] === '.') {
            e.preventDefault();
            const p = this.selectionStart - 1;
            this.setSelectionRange(p, p);
        }
    });

    const fmt = n => 'Rp ' + new Intl.NumberFormat('id').format(Math.round(n));

    function recalc() {
        const p = parseInt(document.getElementById('principal').value || '0', 10);
        const i = parseFloat(document.getElementById('interest').value) || 0;
        const d = parseInt(document.getElementById('duration').value) || 0;

        if (p <= 0 || d <= 0) {
            document.getElementById('previewCard').style.display = 'none';
            return;
        }

        const interest = p * (i / 100);
        const total    = p + interest;
        const monthly  = total / d;

        document.getElementById('prevPrincipal').textContent = fmt(p);
        document.getElementById('prevInterest').textContent  = fmt(interest) + ' (' + i + '%)';
        document.getElementById('prevTotal').textContent     = fmt(total);
        document.getElementById('prevDuration').textContent  = d + ' bulan';
        document.getElementById('prevMonthly').textContent   = fmt(monthly) + ' / bln';

        document.getElementById('previewCard').style.removeProperty('display');
    }

    ['principal','interest','duration'].forEach(id => {
        document.getElementById(id).addEventListener('input', recalc);
    });

    recalc();
</script>
@endpush
