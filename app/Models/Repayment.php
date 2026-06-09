<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repayment extends Model
{
    public $timestamps = false;   // only created_at, no updated_at

    protected $fillable = [
        'installment_id', 'amount', 'payment_date', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'created_at'   => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────
    public function installment(): BelongsTo
    {
        return $this->belongsTo(LoanInstallment::class);
    }
}