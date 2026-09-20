<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'account_number',
        'balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class)->orderByDesc('id');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->balance, 0, ',', '.');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClosed(): bool
    {
        return $this->status === 'inactive';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->isActive() ? 'Aktif' : 'Tutup Buku';
    }
}
