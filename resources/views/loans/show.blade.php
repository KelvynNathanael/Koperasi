@extends('layouts.app')

@section('title', 'Detail Pinjaman #' . $loan->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Pinjaman</a></li>
    <li class="breadcrumb-item active">Pinjaman #{{ $loan->id }}</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('loans.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h4 class="fw-bold mb-0">Pinjaman #{{ $loan->id }}</h4>
        <p class="text-muted small mb-0">
            {{ $loan->member->full_name }} · {{ $loan->member->member_code }}
        </p>
    </div>

    {{-- Status Update --}}
    @if($loan->status !== 'paid')
    <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-pencil me-1"></i> Ubah Status
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @foreach(['active' => 'Aktif', 'overdue' => 'Overdue', 'cancelled' => 'Dibatalkan'] as $val => $label)
                @if($loan->status !== $val)
                    <li>
                        <form method="POST" action="{{ route('loans.update-status', $loan) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $val }}">
                            <button type="submit" class="dropdown-item">{{ $label }}</button>
                        </form>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
    @endif
</div>

<div class="row g-4">

    {{-- Loan Summary --}}
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Pinjaman
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Status</span>
                    <span class="badge rounded-pill badge-status-{{ $loan->status }}">
                        {{ ucfirst($loan->status) }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Pokok</span>
                    <span class="small fw-semibold">Rp {{ number_format($loan->principal_amount, 0, ',', '.') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Bunga</span>
                    <span class="small fw-semibold">{{ $loan->interest_percent }}%</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Total Kewajiban</span>
                    <span class="small fw-bold text-primary">Rp {{ number_format($loan->total_due, 0, ',', '.') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Sisa Hutang</span>
                    <span class="small fw-bold {{ $loan->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($loan->remaining_balance, 0, ',', '.') }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Durasi</span>
                    <span class="small fw-semibold">
                        {{ $loan->duration_months }}
                        {{ match($loan->installment_frequency) {
                            'daily'   => 'hari',
                            'weekly'  => 'minggu',
                            default   => 'bulan',
                        } }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between py-3 px-4">
                    <span class="text-muted small">Tanggal Mulai</span>
                    <span class="small fw-semibold">{{ $loan->start_date->format('d/m/Y') }}</span>
                </li>
            </ul>
        </div>

        {{-- Progress --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small text-muted">Progress Pelunasan</span>
                    <span class="small fw-bold">{{ $loan->progressPercent() }}%</span>
                </div>
                <div class="progress" style="height: 10px; border-radius: 999px;">
                    <div class="progress-bar bg-success" style="width: {{ $loan->progressPercent() }}%; border-radius: 999px;"></div>
                </div>
                <div class="mt-2 text-muted small text-center">
                    Terbayar Rp {{ number_format($loan->total_due - $loan->remaining_balance, 0, ',', '.') }}
                    dari Rp {{ number_format($loan->total_due, 0, ',', '.') }}
                </div>
            </div>
        </div>

        @if($loan->notes)
        <div class="card mt-3">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-sticky me-1"></i>Catatan</div>
                <p class="small mb-0">{{ $loan->notes }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- Installments --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-list-check me-2 text-primary"></i>Jadwal Cicilan</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button"
                            id="toggleDueDateCol"
                            class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye-slash me-1"></i>
                        <span id="toggleDueDateColLabel">Sembunyikan Jatuh Tempo</span>
                    </button>
                    <span class="text-muted small">{{ $loan->installments->count() }} cicilan</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th class="col-due-date">Jatuh Tempo</th>
                            <th>Tagihan</th>
                            <th>Terbayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th class="text-center">Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dayNames = [
                                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
                            ];
                        @endphp
                        @foreach($loan->installments as $inst)
                            <tr class="{{ $inst->isLate() ? 'table-danger bg-opacity-25' : '' }}">
                                <td class="text-muted small">{{ $inst->installment_number }}</td>
                                <td class="small col-due-date" id="due-date-cell-{{ $inst->id }}">
                                    @php
                                        $dayName = $inst->due_date ? $dayNames[$inst->due_date->format('l')] : null;
                                    @endphp
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="due-date-text-{{ $inst->id }}">
                                            {{ $inst->due_date ? "$dayName, " . $inst->due_date->format('d/m/Y') : '—' }}
                                        </span>
                                        <i class="bi bi-exclamation-circle-fill text-danger {{ $inst->isLate() ? '' : 'd-none' }}"
                                           id="due-date-late-{{ $inst->id }}" title="Terlambat"></i>
                                        @if($inst->status !== 'paid')
                                            <button type="button"
                                                    class="btn btn-sm btn-link p-0 ms-1 btn-edit-due-date"
                                                    data-id="{{ $inst->id }}"
                                                    data-number="{{ $inst->installment_number }}"
                                                    data-due-date="{{ $inst->due_date?->format('Y-m-d') }}"
                                                    data-url="{{ route('loans.installments.update-due-date', $inst) }}"
                                                    title="Ubah jatuh tempo">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="small">Rp {{ number_format($inst->scheduled_amount, 0, ',', '.') }}</td>
                                <td class="small text-success">Rp {{ number_format($inst->paid_amount, 0, ',', '.') }}</td>
                                <td class="small fw-semibold
                                    {{ $inst->remainingDue() > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($inst->remainingDue(), 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge rounded-pill badge-status-{{ $inst->status }}">
                                        {{ match($inst->status) {
                                            'paid'    => 'Lunas',
                                            'partial' => 'Sebagian',
                                            'late'    => 'Terlambat',
                                            default   => 'Belum Bayar',
                                        } }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($inst->status !== 'paid' && $loan->status !== 'cancelled')
                                        <a href="{{ route('repayments.create', $inst) }}"
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-cash me-1"></i> Bayar
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Repayment sub-rows --}}
                            @if($inst->repayments->isNotEmpty())
                                @foreach($inst->repayments as $rep)
                                    <tr class="bg-light">
                                        <td colspan="1"></td>
                                        <td class="text-muted col-due-date" style="font-size:.75rem; padding-left:1.5rem;">
                                            <i class="bi bi-arrow-return-right me-1"></i>
                                            {{ $rep->payment_date->format('d/m/Y') }}
                                        </td>
                                        <td colspan="2" style="font-size:.75rem;" class="text-muted">
                                            {{ $rep->notes ?? 'Pembayaran' }}
                                        </td>
                                        <td class="text-success fw-semibold" style="font-size:.75rem;">
                                            +Rp {{ number_format($rep->amount, 0, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Edit Due Date Modal --}}
<div class="modal fade" id="editDueDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editDueDateForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Ubah Jatuh Tempo Cicilan #<span id="editDueDateNumber"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small">Tanggal Jatuh Tempo Baru</label>
                    <input type="date" name="due_date" id="editDueDateInput" class="form-control" required>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="editDueDateCascade" checked>
                        <label class="form-check-label small" for="editDueDateCascade">
                            Geser cicilan berikutnya juga?
                        </label>
                    </div>

                    <div class="text-danger small mt-2 d-none" id="editDueDateError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="editDueDateSubmit">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        // ── Toggle kolom Jatuh Tempo ──────────────────────────────
        const STORAGE_KEY = 'loanShowDueDateCol';
        const toggleBtn = document.getElementById('toggleDueDateCol');
        const toggleLabel = document.getElementById('toggleDueDateColLabel');
        const toggleIcon = toggleBtn.querySelector('i');
        const cols = document.querySelectorAll('.col-due-date');

        function applyColState(visible) {
            cols.forEach(el => el.classList.toggle('d-none', !visible));
            toggleLabel.textContent = visible ? 'Sembunyikan Jatuh Tempo' : 'Tampilkan Jatuh Tempo';
            toggleIcon.className = visible ? 'bi bi-eye-slash me-1' : 'bi bi-eye me-1';
        }

        const savedVisible = localStorage.getItem(STORAGE_KEY);
        let colVisible = savedVisible === null ? true : savedVisible === 'true';
        applyColState(colVisible);

        toggleBtn.addEventListener('click', function () {
            colVisible = !colVisible;
            localStorage.setItem(STORAGE_KEY, colVisible);
            applyColState(colVisible);
        });

        const editModalEl = document.getElementById('editDueDateModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editDueDateForm');
        const numberEl = document.getElementById('editDueDateNumber');
        const dateInput = document.getElementById('editDueDateInput');
        const errorEl = document.getElementById('editDueDateError');
        const submitBtn = document.getElementById('editDueDateSubmit');
        let currentInstallmentId = null;
        let currentUrl = null;

        document.querySelectorAll('.btn-edit-due-date').forEach(btn => {
            btn.addEventListener('click', function () {
                currentInstallmentId = this.dataset.id;
                currentUrl = this.dataset.url;
                numberEl.textContent = this.dataset.number;
                dateInput.value = this.dataset.dueDate;
                errorEl.classList.add('d-none');
                editModal.show();
            });
        });

        const cascadeCheckbox = document.getElementById('editDueDateCascade');

        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            errorEl.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            try {
                const res = await fetch(currentUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        due_date: dateInput.value,
                        cascade: cascadeCheckbox.checked,
                    }),
                });

                const json = await res.json();

                if (!res.ok) {
                    errorEl.textContent = json.message || 'Terjadi kesalahan.';
                    errorEl.classList.remove('d-none');
                    return;
                }

                json.installments.forEach(inst => {
                    const textEl = document.getElementById(`due-date-text-${inst.id}`);
                    if (textEl) textEl.textContent = `${inst.day_name}, ${inst.due_date_fmt}`;

                    const btnEl = document.querySelector(`.btn-edit-due-date[data-id="${inst.id}"]`);
                    if (btnEl) btnEl.dataset.dueDate = inst.due_date;
                });

                editModal.hide();
            } catch (err) {
                errorEl.textContent = 'Gagal terhubung ke server.';
                errorEl.classList.remove('d-none');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan';
            }
        });
    })();
</script>
@endpush