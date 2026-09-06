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
                        <div class="position-relative">
                            <input type="text" id="payment_date_input"
                                   class="form-control @error('payment_date') is-invalid @enderror"
                                   readonly autocomplete="off" placeholder="Pilih tanggal"
                                   style="cursor: pointer; background: #fff;">
                            <i class="bi bi-calendar3" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#6c757d; pointer-events:none;"></i>
                        </div>
                        <input type="hidden" name="payment_date" id="payment_date"
                               value="{{ old('payment_date', now()->format('Y-m-d')) }}">
                        @error('payment_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
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
{{-- Hapus baris ini kalau SweetAlert2 sudah dimuat global di layout --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const dayNamesShort = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const dayNamesFull = { 0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu' };

    const hiddenInput = document.getElementById('payment_date');
    const displayInput = document.getElementById('payment_date_input');

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

    // Bangun struktur modal kalender sekali, di-append ke body
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
        const startWeekday = firstDayOfMonth.getDay(); // 0 = Minggu
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const selNorm = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());

        let cells = [];

        // hari-hari akhir bulan sebelumnya (pengisi baris pertama)
        for (let i = startWeekday - 1; i >= 0; i--) {
            const d = daysInPrevMonth - i;
            cells.push({ date: new Date(viewYear, viewMonth - 1, d), inMonth: false });
        }

        // hari-hari bulan berjalan
        for (let d = 1; d <= daysInMonth; d++) {
            cells.push({ date: new Date(viewYear, viewMonth, d), inMonth: true });
        }

        // hari-hari awal bulan berikutnya (pengisi baris terakhir)
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

    // Tap di backdrop sengaja tidak menutup modal -- user harus pilih
    // tanggal atau pencet tombol tutup (X), biar fokus gak keganggu.

    const form = document.getElementById('paymentForm');
    const amountInput = document.getElementById('amount');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const amount = parseFloat(amountInput.value || 0);
        const amountFormatted = 'Rp ' + amount.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
        const displayDate = formatIndo(selectedDate);

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