<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan - {{ $monthName }} {{ $year }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 0; }
        p.subtitle { color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .summary-table td { border: none; padding: 3px 7px; }
        .summary-table .label { color: #555; }
        .in  { color: #157347; }
        .out { color: #b02a37; }
        .section-title { margin-top: 18px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Rekap Arus Kas Bulanan</h1>
    <p class="subtitle">Periode: {{ $monthName }} {{ $year }}</p>

    <table class="summary-table">
        <tr>
            <td class="label">Saldo Awal</td>
            <td>: Rp {{ number_format($openingBalance, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Kas Masuk</td>
            <td class="in">: Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Kas Keluar</td>
            <td class="out">: Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label"><strong>Saldo Akhir</strong></td>
            <td><strong>: Rp {{ number_format($closingBalance, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <p class="section-title">Rekap per Kategori</p>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-right">Masuk</th>
                <th class="text-right">Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCategory as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="text-right in">Rp {{ number_format($row['in'], 0, ',', '.') }}</td>
                    <td class="text-right out">Rp {{ number_format($row['out'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="section-title">Rincian Transaksi</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Anggota</th>
                <th class="text-right">Jumlah</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($flows as $flow)
                <tr>
                    <td>{{ $flow->transaction_date->format('d/m/Y') }}</td>
                    <td class="{{ $flow->flow_type === 'in' ? 'in' : 'out' }}">
                        {{ $flow->flow_type === 'in' ? 'Masuk' : 'Keluar' }}
                    </td>
                    <td>{{ \App\Models\CashFlow::categoryLabel($flow->category) }}</td>
                    <td>{{ $flow->member->full_name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($flow->amount, 0, ',', '.') }}</td>
                    <td>{{ $flow->description }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
