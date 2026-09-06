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
                                    <option value="{{ $m->id }}" {{ old('member_id', request('member_id')) == $m->id ? 'selected' : '' }}>
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
                                        inputmode="numeric" placeholder="0"
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
                                        value="{{ old('interest_percent', 0) }}" min="0" max="100" step="0.01"
                                        placeholder="Contoh: 5" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('interest_percent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Frekuensi Cicilan <span class="text-danger">*</span></label>
                                <select name="installment_frequency" id="frequency"
                                        class="form-select @error('installment_frequency') is-invalid @enderror" required>
                                    <option value="">— Pilih —</option>
                                    <option value="daily"   {{ old('installment_frequency') === 'daily'   ? 'selected' : '' }}>Harian</option>
                                    <option value="weekly"  {{ old('installment_frequency') === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                                    <option value="monthly" {{ old('installment_frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                </select>
                                @error('installment_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" id="durationLabel">Durasi (Bulan) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_months" id="duration"
                                    class="form-control @error('duration_months') is-invalid @enderror"
                                    value="{{ old('duration_months') }}"
                                    min="1" max="360" placeholder="Contoh: 12" required>
                                @error('duration_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" id="start_date_input"
                                        class="form-control @error('start_date') is-invalid @enderror"
                                        readonly autocomplete="off" placeholder="Pilih tanggal"
                                        style="cursor: pointer; background: #fff;">
                                    <i class="bi bi-calendar3" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#6c757d; pointer-events:none;"></i>
                                </div>
                                <input type="hidden" name="start_date" id="start_date"
                                    value="{{ old('start_date', now()->format('Y-m-d')) }}">
                                @error('start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
                                placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
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

@push('styles')
<style>
/* ===== Kalender custom (bukan native, bukan flatpickr) ===== */
#custom-cal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .55);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

#custom-cal-overlay.show { display: flex; }

#custom-cal-modal {
    background: #fff;
    width: 100%;
    max-width: 340px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, .35);
    animation: ccalIn .15s ease-out;
}

@keyframes ccalIn {
    from { opacity: 0; transform: scale(.94); }
    to   { opacity: 1; transform: scale(1); }
}

.ccal-header {
    display: flex;
    align-items: center;
    background: #2563eb;
    padding: 14px 44px;
    position: relative;
}

.ccal-title {
    flex: 1;
    text-align: center;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
}

.ccal-nav {
    background: transparent;
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ccal-nav:hover { background: rgba(255, 255, 255, .15); }

.ccal-close {
    background: transparent;
    border: none;
    color: #fff;
    position: absolute;
    right: 8px;
    top: 12px;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
}

.ccal-close:hover { background: rgba(255, 255, 255, .15); }

.ccal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    padding: 10px 10px 0;
    text-align: center;
}

.ccal-weekdays div {
    color: #64748b;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.ccal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    padding: 8px 10px 6px;
}

.ccal-day {
    height: 40px;
    border: none;
    background: transparent;
    border-radius: 10px;
    font-size: .9rem;
    font-weight: 500;
    color: #1e293b;
}

.ccal-day:hover { background: #eff6ff; }
.ccal-day-muted { color: #cbd5e1; }
.ccal-day-today { border: 2px solid #16a34a; font-weight: 700; }

.ccal-day-selected {
    background: #2563eb !important;
    color: #fff;
    font-weight: 700;
}

.ccal-footer {
    padding: 4px 12px 14px;
    display: flex;
    justify-content: center;
}
</style>
@endpush

@push('scripts')
    <script>
        // ===== Kalender custom untuk field Tanggal Mulai =====
        (function () {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayNamesShort = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const dayNamesFull = { 0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu' };

            const hiddenInput = document.getElementById('start_date');
            const displayInput = document.getElementById('start_date_input');

            function parseISODate(str) {
                if (!str) return null;
                const [y, m, d] = str.split('-').map(Number);
                if (!y || !m || !d) return null;
                return new Date(y, m - 1, d);
            }

            function toISODate(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function formatIndo(date) {
                const day = dayNamesFull[date.getDay()];
                const d = String(date.getDate()).padStart(2, '0');
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const y = date.getFullYear();
                return `${day}, ${d}/${m}/${y}`;
            }

            let selectedDate = parseISODate(hiddenInput.value) || new Date();
            let viewYear = selectedDate.getFullYear();
            let viewMonth = selectedDate.getMonth();

            function updateFields() {
                displayInput.value = formatIndo(selectedDate);
                hiddenInput.value = toISODate(selectedDate);
            }

            updateFields();

            const overlay = document.createElement('div');
            overlay.id = 'custom-cal-overlay';
            overlay.innerHTML = `
                <div id="custom-cal-modal">
                    <div class="ccal-header">
                        <button type="button" class="ccal-nav" id="ccal-prev"><i class="bi bi-chevron-left"></i></button>
                        <div class="ccal-title" id="ccal-title"></div>
                        <button type="button" class="ccal-nav" id="ccal-next"><i class="bi bi-chevron-right"></i></button>
                        <button type="button" class="ccal-close" id="ccal-close"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="ccal-weekdays">
                        ${dayNamesShort.map(d => `<div>${d}</div>`).join('')}
                    </div>
                    <div class="ccal-grid" id="ccal-grid"></div>
                    <div class="ccal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="ccal-today">Hari Ini</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            const titleEl = overlay.querySelector('#ccal-title');
            const gridEl = overlay.querySelector('#ccal-grid');

            function renderCalendar() {
                titleEl.textContent = `${monthNames[viewMonth]} ${viewYear}`;

                const firstDayOfMonth = new Date(viewYear, viewMonth, 1);
                const startWeekday = firstDayOfMonth.getDay();
                const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
                const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const selNorm = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());

                let cells = [];

                for (let i = startWeekday - 1; i >= 0; i--) {
                    const d = daysInPrevMonth - i;
                    cells.push({ date: new Date(viewYear, viewMonth - 1, d), inMonth: false });
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    cells.push({ date: new Date(viewYear, viewMonth, d), inMonth: true });
                }

                while (cells.length % 7 !== 0) {
                    const lastDate = cells[cells.length - 1].date;
                    const next = new Date(lastDate);
                    next.setDate(next.getDate() + 1);
                    cells.push({ date: next, inMonth: false });
                }

                gridEl.innerHTML = cells.map(cell => {
                    const isToday = cell.date.getTime() === today.getTime();
                    const isSelected = cell.date.getTime() === selNorm.getTime();
                    let classes = 'ccal-day';
                    if (!cell.inMonth) classes += ' ccal-day-muted';
                    if (isToday) classes += ' ccal-day-today';
                    if (isSelected) classes += ' ccal-day-selected';
                    return `<button type="button" class="${classes}" data-date="${toISODate(cell.date)}">${cell.date.getDate()}</button>`;
                }).join('');
            }

            function openCalendar() {
                viewYear = selectedDate.getFullYear();
                viewMonth = selectedDate.getMonth();
                renderCalendar();
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeCalendar() {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }

            displayInput.addEventListener('click', openCalendar);
            overlay.querySelector('#ccal-close').addEventListener('click', closeCalendar);

            overlay.querySelector('#ccal-prev').addEventListener('click', function () {
                viewMonth--;
                if (viewMonth < 0) { viewMonth = 11; viewYear--; }
                renderCalendar();
            });

            overlay.querySelector('#ccal-next').addEventListener('click', function () {
                viewMonth++;
                if (viewMonth > 11) { viewMonth = 0; viewYear++; }
                renderCalendar();
            });

            overlay.querySelector('#ccal-today').addEventListener('click', function () {
                selectedDate = new Date();
                updateFields();
                closeCalendar();
            });

            gridEl.addEventListener('click', function (e) {
                const btn = e.target.closest('.ccal-day');
                if (!btn) return;
                const [y, m, d] = btn.dataset.date.split('-').map(Number);
                selectedDate = new Date(y, m - 1, d);
                updateFields();
                closeCalendar();
            });

            // Tap di backdrop sengaja tidak menutup modal
        })();

        // --- Format Rupiah ---
        const displayInput = document.getElementById('principalDisplay');
        const hiddenInput = document.getElementById('principal');

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
            try { this.setSelectionRange(cursor + (after - before), cursor + (after - before)); } catch (_) { }
        });

        displayInput.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && this.value[this.selectionStart - 1] === '.') {
                e.preventDefault();
                const p = this.selectionStart - 1;
                this.setSelectionRange(p, p);
            }
        });

        const fmt = n => 'Rp ' + new Intl.NumberFormat('id').format(Math.round(n));

        const freqLabel = { daily: 'hari', weekly: 'minggu', monthly: 'bulan' };
        const freqNoun  = { daily: 'Hari', weekly: 'Minggu', monthly: 'Bulan' };

        function recalc() {
            const p = parseInt(document.getElementById('principal').value || '0', 10);
            const i = parseFloat(document.getElementById('interest').value) || 0;
            const d = parseInt(document.getElementById('duration').value) || 0;
            const f = document.getElementById('frequency').value || 'monthly';

            document.getElementById('durationLabel').textContent = `Durasi (${freqNoun[f]}) `;

            if (p <= 0 || d <= 0) {
                document.getElementById('previewCard').style.display = 'none';
                return;
            }

            const interest = p * (i / 100);
            const total    = p + interest;
            const perPeriod = total / d;

            document.getElementById('prevPrincipal').textContent = fmt(p);
            document.getElementById('prevInterest').textContent  = fmt(interest) + ' (' + i + '%)';
            document.getElementById('prevTotal').textContent     = fmt(total);
            document.getElementById('prevDuration').textContent  = d + ' ' + freqLabel[f];
            document.getElementById('prevMonthly').textContent   = fmt(perPeriod) + ' / ' + freqLabel[f];

            document.getElementById('previewCard').style.removeProperty('display');
        }

        ['principal','interest','duration','frequency'].forEach(id => {
            document.getElementById(id).addEventListener('input', recalc);
        });

        recalc();
    </script>
@endpush