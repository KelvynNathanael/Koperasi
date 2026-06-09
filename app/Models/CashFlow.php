<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFlow extends Model
{
    public $timestamps = false;   // only created_at

    protected $fillable = [
        'transaction_date', 'flow_type', 'category',
        'amount', 'member_id', 'reference_type',
        'reference_id', 'description',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date',
        'created_at'       => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────
    public function scopeIn($query)
    {
        return $query->where('flow_type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('flow_type', 'out');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    /**
     * Current cash balance: total-in minus total-out.
     */
    public static function currentBalance(): string
    {
        $in  = static::where('flow_type', 'in')->sum('amount');
        $out = static::where('flow_type', 'out')->sum('amount');
        return bcsub($in, $out, 2);
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'contribution'      => 'Iuran / Kontribusi',
            'loan_disbursement' => 'Pencairan Pinjaman',
            'repayment'         => 'Pembayaran Cicilan',
            'expense'           => 'Biaya Operasional',
            'adjustment'        => 'Penyesuaian',
            default             => ucfirst($category),
        };
    }
}