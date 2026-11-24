<?php

namespace App\Models;

use App\Enums\StudentDataSource;
use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'nis',
        'nisn',
        'nik',
        'qr_code_identifier',
        'gender',
        'birth_place',
        'birth_date',
        'photo',
        'phone',
        'address',
        'religion',
        'family_status',
        'child_number',
        'previous_school',
        'admission_date',
        'entry_grade',
        'father_name',
        'mother_name',
        'father_job',
        'mother_job',
        'parent_address',
        'guardian_name',
        'guardian_job',
        'guardian_address',
        'data_source',
        'api_external_id',
        'last_synced_at',
        'created_by',
        'audit_status',
        'audit_note',
        'audited_by',
        'audited_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admission_date' => 'date',
            'data_source' => StudentDataSource::class,
            'last_synced_at' => 'datetime',
            'audited_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function getQrDataUriAttribute(): string
    {
        return app(QrCodeService::class)->generateDataUri($this->qr_code_identifier, 180, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Parents::class, 'student_parent', 'student_id', 'parent_id');
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function studentNotes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function savingsAccount(): HasOne
    {
        return $this->hasOne(SavingsAccount::class);
    }

    public function assessmentScores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function duplicateConflicts(): HasMany
    {
        return $this->hasMany(StudentDuplicateConflict::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    /**
     * Scope query siswa berdasarkan sumber data.
     */
    public function scopeFromSource(Builder $query, string|StudentDataSource $source): Builder
    {
        $val = $source instanceof StudentDataSource ? $source->value : $source;

        return $query->where('data_source', $val);
    }

    /**
     * Scope query siswa input manual yang belum pernah sinkron dengan API.
     */
    public function scopeManualUnsynced(Builder $query): Builder
    {
        return $query->where('data_source', StudentDataSource::Manual->value)
            ->whereNull('last_synced_at');
    }

    /**
     * Scope query siswa berdasarkan nama tahun ajaran induk rombelnya (contoh: '2026/2027').
     */
    public function scopeForAcademicYearName(Builder $query, ?string $yearName): Builder
    {
        if (blank($yearName)) {
            return $query;
        }

        return $query->whereHas('schoolClass', function (Builder $q) use ($yearName) {
            $q->forAcademicYearName($yearName);
        });
    }

    /**
     * Scope query siswa berdasarkan tahun ajaran induk yang sedang aktif.
     */
    public function scopeCurrentAcademicYear(Builder $query): Builder
    {
        return $query->whereHas('schoolClass', function (Builder $q) {
            $q->currentAcademicYear();
        });
    }

    /**
     * Dapatkan domain email resmi untuk akun siswa.
     */
    public static function getEmailDomain(): string
    {
        $configured = config('services.student.email_domain');
        if (filled($configured)) {
            return trim((string) $configured, '@ ');
        }

        $host = app()->runningInConsole()
            ? (parse_url((string) config('app.url'), PHP_URL_HOST) ?: '127.0.0.1')
            : request()->getHost();

        $host = strtolower(trim((string) $host));

        if (str_contains($host, 'min2tanggamus.sch.id')) {
            return 'min2tanggamus.sch.id';
        }

        if ($host === '127.0.0.1' || $host === 'localhost' || blank($host)) {
            return 'siswa.sekolah.sch.id';
        }

        return $host;
    }

    /**
     * Generate format email unik untuk siswa berdasarkan NIS.
     */
    public static function generateDefaultEmail(string $nis): string
    {
        $domain = static::getEmailDomain();
        $cleanNis = strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', trim($nis)) ?: 'siswa');
        $candidate = "{$cleanNis}@{$domain}";

        while (User::where('email', $candidate)->exists()) {
            $candidate = "{$cleanNis}.".random_int(100, 9999)."@{$domain}";
        }

        return $candidate;
    }

    /**
     * Generate identifier unik untuk QR Code siswa.
     */
    public static function generateUniqueQrIdentifier(string $nis): string
    {
        $cleanNis = strtoupper(preg_replace('/[^a-zA-Z0-9-]/', '', trim($nis)) ?: 'SISWA');

        do {
            $identifier = 'QR-'.$cleanNis.'-'.strtoupper(bin2hex(random_bytes(3)));
        } while (static::where('qr_code_identifier', $identifier)->exists());

        return $identifier;
    }

    /**
     * Tentukan kata sandi default siswa (prioritas: NISN, fallback: NIS, fallback: NIK).
     */
    public static function getDefaultPassword(?string $nisn = null, ?string $nis = null, ?string $nik = null): string
    {
        $cleanNisn = trim((string) $nisn);
        if ($cleanNisn !== '' && $cleanNisn !== '-') {
            return $cleanNisn;
        }

        $cleanNis = trim((string) $nis);
        if ($cleanNis !== '' && $cleanNis !== '-') {
            return $cleanNis;
        }

        $cleanNik = trim((string) $nik);
        if ($cleanNik !== '' && $cleanNik !== '-') {
            return $cleanNik;
        }

        return '123456';
    }
}
