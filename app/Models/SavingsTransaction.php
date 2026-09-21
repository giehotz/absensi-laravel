<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_account_id',
        'transaction_code',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'handled_by',
        'is_corrected',
        'original_amount',
        'correction_reason',
        'corrected_by',
        'corrected_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'is_corrected' => 'boolean',
            'original_amount' => 'decimal:2',
            'corrected_at' => 'datetime',
        ];
    }

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function getFormattedOriginalAmountAttribute(): ?string
    {
        return $this->original_amount !== null
            ? 'Rp '.number_format((float) $this->original_amount, 0, ',', '.')
            : null;
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }

    public function getFormattedBalanceAfterAttribute(): string
    {
        return 'Rp '.number_format((float) $this->balance_after, 0, ',', '.');
    }

    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    public function isWithdrawal(): bool
    {
        return $this->type === 'withdrawal';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->isDeposit() ? 'Setoran Tunai' : 'Penarikan Tunai';
    }
}
