<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public static function activeSemester(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public static function activeYearName(): ?string
    {
        return static::activeSemester()?->name ?? static::orderByDesc('start_date')->value('name');
    }

    /**
     * @return array<int, string>
     */
    public static function distinctYearNames(): array
    {
        return static::orderByDesc('start_date')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }
}
