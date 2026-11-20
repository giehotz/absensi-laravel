<?php

namespace App\Models\Ujian;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UjianRuang extends Model
{
    use HasFactory;

    protected $table = 'ujian_ruang';

    protected $fillable = [
        'kode_ruang',
        'nama_ruang',
        'kapasitas_maksimal',
        'tipe_ruang',
        'school_class_id',
        'lokasi_gedung',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas_maksimal' => 'integer',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function pesertas(): HasMany
    {
        return $this->hasMany(UjianPeserta::class, 'ujian_ruang_id');
    }

    public function jadwalPengawas(): HasMany
    {
        return $this->hasMany(UjianJadwalPengawas::class, 'ujian_ruang_id');
    }

    public function isKelas(): bool
    {
        return $this->tipe_ruang === 'kelas';
    }

    public function isManual(): bool
    {
        return $this->tipe_ruang === 'manual';
    }

    public function scopeKelas(Builder $query): Builder
    {
        return $query->where('tipe_ruang', 'kelas');
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where('tipe_ruang', 'manual');
    }

    /**
     * Mengambil angka tingkat kelas ruang (1..6) dari relasi schoolClass, nama_ruang, atau kode_ruang.
     */
    public function getLevel(): ?string
    {
        $sources = [
            $this->schoolClass?->name,
            $this->nama_ruang,
            $this->kode_ruang,
        ];

        foreach ($sources as $text) {
            if ($text && preg_match('/\d+/', $text, $matches)) {
                $num = (int) $matches[0];
                if ($num >= 1 && $num <= 6) {
                    return (string) $num;
                }
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah ruang ini merupakan target yang valid untuk jadwal ujian tertentu.
     */
    public function matchesJadwal(UjianJadwal $jadwal): bool
    {
        // Jika jadwal ditujukan untuk 1 kelas spesifik
        if ($jadwal->school_class_id && $this->school_class_id) {
            return (int) $this->school_class_id === (int) $jadwal->school_class_id;
        }

        $targetLevels = $jadwal->getTargetLevels();
        $ruangLevel = $this->getLevel();

        // Jika ruang terikat ke tingkat kelas tertentu (mis. Kelas 1 A -> '1')
        if ($ruangLevel !== null) {
            return in_array($ruangLevel, $targetLevels, true);
        }

        // Untuk ruang tipe 'manual' tanpa angka tingkat, hanya cocok jika jadwal untuk Semua Tingkat
        return count($targetLevels) >= 6;
    }
}
