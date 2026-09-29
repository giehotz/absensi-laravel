<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendar extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_name',
        'semester',
        'start_date',
        'end_date',
        'day_name',
        'description',
        'category',
        'color',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Tampilan tanggal terformat (dd/mm/yyyy atau rentang dd/mm/yyyy - dd/mm/yyyy).
     */
    public function getFormattedDateAttribute(): string
    {
        $start = Carbon::parse($this->start_date)->format('d/m/Y');

        if ($this->end_date && $this->end_date->format('Y-m-d') !== $this->start_date->format('Y-m-d')) {
            $end = Carbon::parse($this->end_date)->format('d/m/Y');

            return "{$start} - {$end}";
        }

        return $start;
    }

    /**
     * Scope untuk memfilter berdasarkan nama tahun ajaran (contoh: '2026/2027').
     *
     * @param  Builder<AcademicCalendar>  $query
     * @return Builder<AcademicCalendar>
     */
    public function scopeForYear(Builder $query, string $yearName): Builder
    {
        return $query->where('academic_year_name', $yearName);
    }

    /**
     * Scope untuk memfilter semester ('ganjil' atau 'genap').
     *
     * @param  Builder<AcademicCalendar>  $query
     * @return Builder<AcademicCalendar>
     */
    public function scopeSemester(Builder $query, string $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope urutan tanggal kegiatan.
     *
     * @param  Builder<AcademicCalendar>  $query
     * @return Builder<AcademicCalendar>
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('start_date', 'asc')->orderBy('id', 'asc');
    }
}
