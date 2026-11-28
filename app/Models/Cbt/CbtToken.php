<?php

namespace App\Models\Cbt;

use App\Models\Ujian\UjianPeriode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtToken extends Model
{
    use HasFactory;

    protected $table = 'cbt_tokens';

    protected $fillable = [
        'ujian_periode_id',
        'token',
        'is_auto',
        'interval_minutes',
        'refreshed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_auto' => 'boolean',
            'interval_minutes' => 'integer',
            'refreshed_at' => 'datetime',
        ];
    }

    public function ujianPeriode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }
}
