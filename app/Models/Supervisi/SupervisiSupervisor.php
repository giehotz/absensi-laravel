<?php

namespace App\Models\Supervisi;

use App\Models\AttendanceSetting;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupervisiSupervisor extends Model
{
    protected $table = 'supervisi_supervisors';

    protected $fillable = [
        'teacher_id',
        'jabatan',
        'nomor_sk',
        'tanggal_penugasan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penugasan' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public static function syncKepala(Teacher $teacher): self
    {
        $supervisor = self::firstOrNew(['teacher_id' => $teacher->id]);
        if (blank($supervisor->jabatan)) {
            $supervisor->jabatan = 'Kepala Madrasah';
        }
        if ($supervisor->tanggal_penugasan === null) {
            $supervisor->tanggal_penugasan = now()->toDateString();
        }
        $supervisor->is_active = true;
        $supervisor->save();

        return $supervisor;
    }

    public static function deactivateExKepala(int $teacherId): void
    {
        self::query()
            ->where('teacher_id', $teacherId)
            ->update(['is_active' => false]);
    }

    public static function ensureHeadmastersSynced(): void
    {
        $kepalaIds = Teacher::query()
            ->where('staff_type', Teacher::STAFF_TYPE_KEPALA)
            ->whereHas('user', fn (Builder $q) => $q->where('is_active', true))
            ->pluck('id')
            ->all();

        $headmasterSettingId = AttendanceSetting::query()->value('headmaster_teacher_id');
        if ($headmasterSettingId && ! in_array((int) $headmasterSettingId, $kepalaIds, true)) {
            $kepalaIds[] = (int) $headmasterSettingId;
        }

        if (empty($kepalaIds)) {
            return;
        }

        $existingIds = self::query()
            ->whereIn('teacher_id', $kepalaIds)
            ->pluck('teacher_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $missingIds = array_diff(array_unique(array_map('intval', $kepalaIds)), $existingIds);
        if (empty($missingIds)) {
            return;
        }

        $today = now()->toDateString();
        foreach ($missingIds as $teacherId) {
            self::create([
                'teacher_id' => $teacherId,
                'jabatan' => 'Kepala Madrasah',
                'tanggal_penugasan' => $today,
                'is_active' => true,
            ]);
        }
    }

    public function isHeadmaster(?int $headmasterTeacherId = null): bool
    {
        $resolvedSettingId = $headmasterTeacherId ?? AttendanceSetting::query()->value('headmaster_teacher_id');

        return ($this->teacher?->isKepala() ?? false)
            || ($resolvedSettingId !== null && (int) $this->teacher_id === (int) $resolvedSettingId);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(SupervisiJadwal::class, 'supervisor_teacher_id', 'teacher_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
