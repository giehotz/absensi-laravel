<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'date',
        'category',
        'title',
        'content',
        'follow_up',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Label kategori yang ramah dibaca.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'kedisiplinan' => 'Kedisiplinan',
            'prestasi' => 'Prestasi',
            'kesehatan' => 'Kesehatan',
            'pembinaan' => 'Pembinaan / BK',
            'umum' => 'Catatan Umum',
            default => ucfirst($this->category),
        };
    }

    /**
     * Kelas badge Neobrutalism untuk kategori.
     */
    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'kedisiplinan' => 'bg-[#FFE3E3] text-rose-950 border-rose-900',
            'prestasi' => 'bg-[#D3F9D8] text-emerald-950 border-emerald-900',
            'kesehatan' => 'bg-[#FFF3BF] text-amber-950 border-amber-900',
            'pembinaan' => 'bg-[#D0EBFF] text-blue-950 border-blue-900',
            default => 'bg-[#E9ECEF] text-slate-800 border-slate-700',
        };
    }
}
