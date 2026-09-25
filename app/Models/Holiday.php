<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'holiday_date',
        'name',
        'is_national',
        'is_cuti_bersama',
        'is_active',
        'source',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'holiday_date' => 'date:Y-m-d',
            'is_national' => 'boolean',
            'is_cuti_bersama' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope query untuk libur yang aktif (dianggap libur sekolah).
     *
     * @param  Builder<Holiday>  $query
     * @return Builder<Holiday>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk libur dalam tahun tertentu.
     *
     * @param  Builder<Holiday>  $query
     * @return Builder<Holiday>
     */
    public function scopeInYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('holiday_date', $year);
    }

    /**
     * Scope query untuk libur dalam bulan dan tahun tertentu.
     *
     * @param  Builder<Holiday>  $query
     * @return Builder<Holiday>
     */
    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month);
    }

    /**
     * Cek apakah suatu tanggal merupakan hari libur aktif.
     */
    public static function isHoliday(string|Carbon $date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return static::active()->where('holiday_date', $dateStr)->exists();
    }

    /**
     * Ambil record Holiday pada tanggal tertentu jika ada.
     */
    public static function getHolidayFor(string|Carbon $date): ?self
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return static::active()->where('holiday_date', $dateStr)->first();
    }

    /**
     * Ambil seluruh hari libur aktif dalam rentang bulan tertentu.
     *
     * @return Collection<int, Holiday>
     */
    public static function getHolidaysInMonth(int $year, int $month): Collection
    {
        return static::active()->inMonth($year, $month)->orderBy('holiday_date')->get();
    }
}
