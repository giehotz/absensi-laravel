<?php

namespace App\Models\Ujian;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UjianPeserta extends Model
{
    use HasFactory;

    protected $table = 'ujian_peserta';

    protected $fillable = [
        'ujian_periode_id',
        'student_id',
        'school_class_id',
        'ujian_ruang_id',
        'nomor_kursi',
        'sesi',
        'nis_lokal',
        'nisn',
        'nama_peserta',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_kelas',
        'nomor_peserta',
        'foto_peserta_path',
        'is_eligible',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'nomor_kursi' => 'integer',
            'sesi' => 'integer',
            'is_eligible' => 'boolean',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(UjianRuang::class, 'ujian_ruang_id');
    }

    public function getFormattedBirthDateAttribute(): string
    {
        if (! $this->tanggal_lahir) {
            return '-';
        }

        try {
            return Carbon::parse($this->tanggal_lahir)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return $this->tanggal_lahir->format('d/m/Y');
        }
    }

    public function getTempatTanggalLahirAttribute(): string
    {
        $tempat = $this->tempat_lahir ?: '-';
        $tgl = $this->formatted_birth_date;

        if ($tempat === '-' && $tgl === '-') {
            return '-';
        }

        return $tempat !== '-' ? "{$tempat}, {$tgl}" : $tgl;
    }

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto_peserta_path && Storage::disk('public')->exists($this->foto_peserta_path)) {
            return asset('storage/'.$this->foto_peserta_path);
        }

        return $this->student?->photo_url;
    }

    public function getQrDataUriAttribute(): string
    {
        $payload = implode('|', [
            'UJIAN',
            $this->nomor_peserta,
            $this->nisn ?? $this->nis_lokal,
            $this->nama_peserta,
            $this->nama_kelas,
        ]);

        return app(QrCodeService::class)->generateDataUri($payload, 150, 1);
    }
}
