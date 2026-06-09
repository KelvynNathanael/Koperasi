@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Saldo Kas</div>
                    <div class="stat-value">Rp {{ number_format($currentCash, 0, ',', '.') }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card" style="background: linear-gradient(135deg, #0891b2, #0e7490);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Piutang Aktif</div>
                    <div class="stat-value">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card" style="background: linear-gradient(135deg, #059669, #047857);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Anggota Aktif</div>
                    <div class="stat-value">{{ number_format($activeMembers) }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Cicilan Terlambat</div>
                    <div class="stat-value">{{ $overdueInstallments }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
        </div>
    </div>

</div>

{{-- ── Second Row ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Cash Chart --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bar-chart-line me-2 text-primary"></i>Arus Kas 6 Bulan Terakhir</span>
            </div>
            <div class="card-body">
                <canvas id="cashChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-info-circle me-2 text-primary"></i>Ringkasan Bulan Ini
            </div>
            <div class="card-body d-flex flex-column gap-3">

                <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                     style="background:#f0fdf4;">
                    <div>
                        <div class="fw-600 small text-success">Cicilan Jatuh Tempo</div>
                        <div class="text-muted small">Bulan {{ now()->translatedFormat('F Y') }}</div>
                    </div>
                    <span class="fs-4 fw-bold text-success">{{ $dueThisMonth }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                     style="background:#fef2f2;">
                    <div>
                        <div class="fw-600 small text-danger">Cicilan Belum Lunas Lewat Jatuh Tempo</div>
                        <div class="text-muted small">Akumulatif</div>
                    </div>
                    <span class="fs-4 fw-bold text-danger">{{ $overdueInstallments }}</span>
                </div>

                <div class="mt-auto">
                    <a href="{{ route('loans.create') }}" class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="bi bi-plus-lg me-1"></i> Buat Pinjaman Baru
                    </a>
                    <a href="{{ route('members.create') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-person-plus me-1"></i> Tambah Anggota
                    </a>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ── Recent Transactions ─────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Transaksi Terbaru</span>
        <a href="{{ route('cash-flows.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Anggota</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                    <tr>
                        <td class="text-muted small">
                            {{ $tx->transaction_date->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="badge rounded-pill badge-flow-{{ $tx->flow_type }}">
                                {{ $tx->flow_type === 'in' ? '↑ Masuk' : '↓ Keluar' }}
                            </span>
                        </td>
                        <td class="small">
                            {{ \App\Models\CashFlow::categoryLabel($tx->category) }}
                        </td>
                        <td class="small">{{ $tx->member?->full_name ?? '—' }}</td>
                        <td class="small text-muted">{{ Str::limit($tx->description, 45) }}</td>
                        <td class="text-end fw-semibold small
                            {{ $tx->flow_type === 'in' ? 'text-success' : 'text-danger' }}">
                            {{ $tx->flow_type === 'in' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            Belum ada transaksi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const raw = @json($cashChart);

    // Build month labels & data maps
    const months = [...new Set(raw.map(r => r.month))].sort();
    const inMap  = {}, outMap = {};
    raw.forEach(r => {
        if (r.flow_type === 'in')  inMap[r.month]  = parseFloat(r.total);
        if (r.flow_type === 'out') outMap[r.month] = parseFloat(r.total);
    });

    // Format month labels (YYYY-MM → Mon 'YY)
    const labels = months.map(m => {
        const [y, mo] = m.split('-');
        const names = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return names[parseInt(mo) - 1] + " '" + y.slice(2);
    });

    new Chart(document.getElementById('cashChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: months.map(m => inMap[m] || 0),
                    backgroundColor: 'rgba(5,150,105,.75)',
                    borderRadius: 4,
                },
                {
                    label: 'Pengeluaran',
                    data: months.map(m => outMap[m] || 0),
                    backgroundColor: 'rgba(220,38,38,.65)',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + new Intl.NumberFormat('id').format(ctx.parsed.y),
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v),
                        font: { size: 11 },
                    },
                },
                x: { ticks: { font: { size: 11 } } },
            },
        },
    });
})();
</script>
@endpush
