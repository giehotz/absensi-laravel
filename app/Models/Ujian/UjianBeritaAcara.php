<?php

namespace App\Models\Ujian;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianBeritaAcara extends Model
{
    use HasFactory;

    protected $table = 'ujian_berita_acara';

    protected $fillable = [
        'ujian_periode_id',
        'ujian_jadwal_id',
        'ujian_ruang_id',
        'pengawas_pengisi_id',
        'peserta_seharusnya',
        'peserta_hadir',
        'peserta_tidak_hadir',
        'naskah_soal_diterima',
        'naskah_soal_digunakan',
        'naskah_soal_sisa',
        'naskah_soal_rusak',
        'catatan_kejadian',
        'daftar_absen_siswa',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'peserta_seharusnya' => 'integer',
            'peserta_hadir' => 'integer',
            'peserta_tidak_hadir' => 'integer',
            'naskah_soal_diterima' => 'integer',
            'naskah_soal_digunakan' => 'integer',
            'naskah_soal_sisa' => 'integer',
            'naskah_soal_rusak' => 'integer',
            'daftar_absen_siswa' => 'array',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(UjianJadwal::class, 'ujian_jadwal_id');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(UjianRuang::class, 'ujian_ruang_id');
    }

    public function pengawasPengisi(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'pengawas_pengisi_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }
}
