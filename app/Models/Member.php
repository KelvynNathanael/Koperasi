<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'member_code', 'full_name', 'phone_number',
        'address', 'status', 'notes',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function cashFlows(): HasMany
    {
        return $this->hasMany(CashFlow::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activeLoansCount(): int
    {
        return $this->loans()->where('status', 'active')->count();
    }

    public function totalRemainingBalance(): string
    {
        return $this->loans()
            ->whereIn('status', ['active', 'overdue'])
            ->sum('remaining_balance');
    }

    public static function generateCode(): string
    {
        $seq = static::count() + 1;

        return str_pad($seq, 5, '0', STR_PAD_LEFT)
            . '-' .
            now()->format('d-m-Y');
    }
}