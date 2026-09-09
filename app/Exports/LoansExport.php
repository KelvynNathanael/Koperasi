<?php

namespace App\Exports;

use App\Models\Loan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export "Daftar Pinjaman" ke Excel.
 * Menghormati filter search & status yang sama dengan LoanController::index().
 */
class LoansExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $search = null,
        protected ?string $status = null,
        protected ?string $frequency = null,
    ) {}

    public function collection()
    {
        $query = Loan::with('member');

        if ($this->search) {
            $query->whereHas('member', fn ($q) => $q->where('full_name', 'ilike', "%{$this->search}%")
                ->orWhere('member_code', 'ilike', "%{$this->search}%"));
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->frequency && $this->frequency !== 'all') {
            $query->where('installment_frequency', $this->frequency);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Kode Anggota', 'Nama Anggota', 'Pokok Pinjaman', 'Bunga (%)',
            'Total Tagihan', 'Sisa Saldo', 'Frekuensi Cicilan', 'Durasi (bulan)',
            'Tanggal Mulai', 'Status', 'Catatan',
        ];
    }

    public function map($loan): array
    {
        return [
            $loan->id,
            $loan->member->member_code,
            $loan->member->full_name,
            (float) $loan->principal_amount,
            (float) $loan->interest_percent,
            (float) $loan->total_due,
            (float) $loan->remaining_balance,
            ucfirst($loan->installment_frequency),
            $loan->duration_months,
            optional($loan->start_date)->format('d/m/Y'),
            ucfirst($loan->status),
            $loan->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Daftar Pinjaman';
    }
}
