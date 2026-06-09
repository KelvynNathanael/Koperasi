<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LoanInstallment extends Model
{
    protected $fillable = [
        'loan_id', 'installment_number', 'due_date',
        'scheduled_amount', 'paid_amount', 'status',
    ];

    protected $casts = [
        'scheduled_amount' => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'due_date'         => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class, 'installment_id');
    }

    // ── Business logic ─────────────────────────────────────────────────────────

    /**
     * Recompute paid_amount + status from child repayments.
     * Must run inside a DB transaction.
     */
    public function syncStatus(): void
    {
        $this->paid_amount = $this->repayments()->sum('amount');

        $today   = now()->toDateString();
        $dueDate = $this->due_date
            ? Carbon::parse($this->due_date)->toDateString()
            : null;

        if ($this->paid_amount <= 0) {
            $this->status = ($dueDate && $today > $dueDate) ? 'late' : 'unpaid';
        } elseif ($this->paid_amount >= $this->scheduled_amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->saveQuietly();
    }

    public function isLate(): bool
    {
        if (!$this->due_date) return false;

        return in_array($this->status, ['late', 'unpaid'])
            && now()->toDateString() > Carbon::parse($this->due_date)->toDateString();
    }

    public function remainingDue(): string
    {
        return max(0, $this->scheduled_amount - $this->paid_amount);
    }
}