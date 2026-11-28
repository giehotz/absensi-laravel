<?php

namespace App\Models\Supervisi;

use App\Enums\SupervisiStatus;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupervisiJadwal extends Model
{
    public const PENDEKATAN_OPTIONS = [
        'direktif' => 'Direktif (Pembinaan Langsung)',
        'non_direktif' => 'Non-Direktif (Mandiri Reflektif)',
        'kolaboratif' => 'Kolaboratif (Kemitrakerjaan)',
    ];

    public const TEKNIK_OPTIONS = [
        'kunjungan_kelas' => 'Kunjungan Kelas',
        'observasi_kelas' => 'Observasi Kelas',
        'pertemuan_individual' => 'Pertemuan Individual',
        'kkg' => 'KKG / Forum Sejawat',
    ];

    protected $table = 'supervisi_jadwal';

    protected $fillable = [
        'academic_year_id',
        'teacher_id',
        'supervisor_teacher_id',
        'supervisor_user_id',
        'school_class_id',
        'subject_id',
        'indikator_pmm_id',
        'tanggal',
        'hari',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'materi',
        'pendekatan',
        'teknik_supervisi',
        'sumber_slot',
        'status',
        'nilai_akhir',
        'ketercapaian',
        'tanggal_pra_observasi',
        'tanggal_pasca_observasi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nilai_akhir' => 'decimal:2',
            'tanggal_pra_observasi' => 'datetime',
            'tanggal_pasca_observasi' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function supervisorTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'supervisor_teacher_id');
    }

    public function supervisorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function indikatorPmm(): BelongsTo
    {
        return $this->belongsTo(SupervisiJenisPenilaian::class, 'indikator_pmm_id');
    }

    public function praObservasi(): HasOne
    {
        return $this->hasOne(SupervisiPraObservasi::class, 'jadwal_id');
    }

    public function dokumenAjars(): HasMany
    {
        return $this->hasMany(SupervisiDokumenAjar::class, 'jadwal_id');
    }

    public function hasils(): HasMany
    {
        return $this->hasMany(SupervisiHasil::class, 'jadwal_id');
    }

    public function fotoBuktis(): HasMany
    {
        return $this->hasMany(SupervisiFotoBukti::class, 'jadwal_id');
    }

    public function tindakLanjut(): HasOne
    {
        return $this->hasOne(SupervisiTindakLanjut::class, 'jadwal_id');
    }

    public function coachingTirtas(): HasMany
    {
        return $this->hasMany(SupervisiCoachingTirta::class, 'jadwal_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(SupervisiDokumenSignature::class, 'jadwal_id');
    }

    public function statusEnum(): SupervisiStatus
    {
        return SupervisiStatus::tryFrom((string) $this->status) ?? SupervisiStatus::Terjadwal;
    }

    public function isKurmer(): bool
    {
        return $this->indikator_pmm_id !== null;
    }

    public function isLocked(): bool
    {
        return $this->signatures()->exists();
    }

    public function getSupervisorDisplayNameAttribute(): string
    {
        return $this->supervisorTeacher?->user?->name
            ?? $this->supervisorUser?->name
            ?? 'Belum Ditentukan';
    }

    public function getSupervisorNipAttribute(): ?string
    {
        return $this->supervisorTeacher?->nip ?? $this->supervisorUser?->nip;
    }

    public function scopeForAcademicYear(Builder $query, ?int $academicYearId): Builder
    {
        return $query->when($academicYearId, fn (Builder $q) => $q->where('academic_year_id', $academicYearId));
    }
}
