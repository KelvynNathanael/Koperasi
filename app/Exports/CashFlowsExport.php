<?php

namespace App\Exports;

use App\Models\CashFlow;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export "Arus Kas" ke Excel.
 * Menghormati filter yang sama dengan CashFlowController::index().
 */
class CashFlowsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $flowType = null,
        protected ?string $category = null,
        protected ?string $from = null,
        protected ?string $to = null,
    ) {}

    public function collection()
    {
        $query = CashFlow::with('member');

        if ($this->flowType) {
            $query->where('flow_type', $this->flowType);
        }
        if ($this->category) {
            $query->where('category', $this->category);
        }
        if ($this->from) {
            $query->where('transaction_date', '>=', $this->from);
        }
        if ($this->to) {
            $query->where('transaction_date', '<=', $this->to);
        }

        return $query->orderByDesc('transaction_date')->orderByDesc('id')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Tanggal', 'Jenis', 'Kategori', 'Jumlah', 'Anggota', 'Deskripsi'];
    }

    public function map($cf): array
    {
        return [
            $cf->id,
            optional($cf->transaction_date)->format('d/m/Y'),
            $cf->flow_type === 'in' ? 'Masuk' : 'Keluar',
            CashFlow::categoryLabel($cf->category),
            (float) $cf->amount,
            $cf->member?->full_name ?? '-',
            $cf->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Arus Kas';
    }
}
