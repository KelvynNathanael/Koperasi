<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'member_id', 'principal_amount', 'interest_percent',
        'total_due', 'remaining_balance', 'duration_months',
        'start_date', 'status', 'notes', 'installment_frequency',
    ];

    protected $casts = [
        'principal_amount'  => 'decimal:2',
        'interest_percent'  => 'decimal:2',
        'total_due'         => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'start_date'        => 'date:Y-m-d',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class)->orderBy('installment_number');
    }

    public function cashFlows(): HasMany
    {
        return $this->hasMany(CashFlow::class, 'reference_id')
            ->where('reference_type', 'loans');
    }

    // ── Business logic ─────────────────────────────────────────────────────────

    /**
     * Recalculate remaining_balance from actual repayments and update status.
     * Must be called inside a DB transaction.
     */
    public function syncBalance(): void
    {
        $totalPaid = (string) $this->installments()->sum('paid_amount');
        $totalDue  = (string) $this->total_due;

        $remaining = bcsub($totalDue, $totalPaid, 2);

        // Pastikan tidak negatif
        $this->remaining_balance = bccomp($remaining, '0', 2) < 0 ? '0.00' : $remaining;
        $this->status            = $remaining == 0 ? 'paid' : $this->status;
        $this->saveQuietly();
    }

    /**
     * Generate equal monthly installments for this loan.
     * Call after creating the loan record (within a transaction).
     */
    public function generateInstallments(): void
    {
        $per = bcdiv((string) $this->total_due, (string) $this->duration_months, 2);

        $totalScheduled = bcmul($per, (string) ($this->duration_months - 1), 2);
        $lastAmount     = bcsub((string) $this->total_due, $totalScheduled, 2);

        $dueDate = \Carbon\Carbon::parse($this->start_date);

        for ($i = 1; $i <= $this->duration_months; $i++) {
            $dueDate = $this->nextDueDate($dueDate);

            LoanInstallment::create([
                'loan_id'            => $this->id,
                'installment_number' => $i,
                'due_date'           => $dueDate->toDateString(),
                'scheduled_amount'   => (float) ($i === $this->duration_months ? $lastAmount : $per),
                'paid_amount'        => 0,
                'status'             => 'unpaid',
            ]);
        }
    }

    private function nextDueDate(\Carbon\Carbon $date): \Carbon\Carbon
    {
        return match ($this->installment_frequency) {
            'daily'   => $this->nextDailyDueDate($date),
            'weekly'  => $date->addWeek(),
            'monthly' => $date->addMonth(),
            default   => $date->addMonth(),
        };
    }

    private function nextDailyDueDate(\Carbon\Carbon $date): \Carbon\Carbon
    {   
        $date->addDay();

        while ($date->isSunday()) {
            $date->addDay();
        }

        return $date;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function progressPercent(): float
    {
        if ($this->total_due == 0) return 100;
        $paid = $this->total_due - $this->remaining_balance;
        return round(($paid / $this->total_due) * 100, 1);
    }

    public static function businessDayDiff(\Carbon\Carbon $from, \Carbon\Carbon $to): int
    {
        $cursor = $from->copy();
        $direction = $cursor->lt($to) ? 1 : -1;
        $steps = 0;

        while (!$cursor->isSameDay($to)) {
            $cursor->addDays($direction);
            if ($cursor->isSunday()) {
                continue; // Minggu dilewati, gak dihitung sebagai langkah
            }
            $steps += $direction;
        }

        return $steps;
    }

    public static function addBusinessDays(\Carbon\Carbon $date, int $steps): \Carbon\Carbon
    {
        $result = $date->copy();
        $direction = $steps > 0 ? 1 : -1;
        $remaining = abs($steps);

        while ($remaining > 0) {
            $result->addDays($direction);
            if ($result->isSunday()) {
                continue;
            }
            $remaining--;
        }

        return $result;
    }
}