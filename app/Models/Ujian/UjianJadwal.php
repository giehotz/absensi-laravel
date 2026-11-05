<?php

namespace App\Models\Ujian;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UjianJadwal extends Model
{
    use HasFactory;

    protected $table = 'ujian_jadwal';

    protected $fillable = [
        'ujian_periode_id',
        'subject_id',
        'nama_mapel_kustom',
        'school_class_id',
        'tingkat',
        'tanggal_ujian',
        'jam_mulai',
        'jam_selesai',
        'sesi_ke',
        'durasi_menit',
        'jenis_soal',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_ujian' => 'date',
            'sesi_ke' => 'integer',
            'durasi_menit' => 'integer',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function pengawas(): HasMany
    {
        return $this->hasMany(UjianJadwalPengawas::class, 'ujian_jadwal_id');
    }

    public function getNamaMapelAttribute(): string
    {
        return $this->subject?->name ?? $this->nama_mapel_kustom ?? '-';
    }

    public function getTargetKelasAttribute(): string
    {
        if ($this->schoolClass) {
            return $this->schoolClass->name;
        }

        if ($this->tingkat) {
            return 'Tingkat '.$this->tingkat;
        }

        return 'Semua Kelas';
    }
}
