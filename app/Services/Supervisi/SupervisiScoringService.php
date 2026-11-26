<?php

namespace App\Services\Supervisi;

use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiDetailHasil;
use App\Models\Supervisi\SupervisiHasil;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use Illuminate\Support\Facades\DB;

class SupervisiScoringService
{
    public function aspectMaxScore(SupervisiAspekPenilaian $aspek, ?SupervisiJenisPenilaian $jenis = null): int
    {
        if ($aspek->tipe_skala === SupervisiAspekPenilaian::SKALA_PMM) {
            return 3;
        }

        if ($aspek->tipe_skala === SupervisiAspekPenilaian::SKALA_REGULER) {
            return 4;
        }

        return ($jenis?->skor_maksimal ?? 4) === 3 ? 3 : 4;
    }

    public function predikat(float $nilaiAkhir): string
    {
        if ($nilaiAkhir >= 86) {
            return 'Baik Sekali';
        }

        if ($nilaiAkhir >= 70) {
            return 'Baik';
        }

        if ($nilaiAkhir >= 55) {
            return 'Cukup';
        }

        return 'Kurang';
    }

    /**
     * Simpan skor dan catatan untuk satu komponen jenis penilaian lalu hitung ulang agregasi.
     *
     * @param  array<int|string, int|string|null>  $scores
     * @param  array<int|string, string|null>  $notes
     */
    public function saveKomponen(
        SupervisiJadwal $jadwal,
        SupervisiJenisPenilaian $jenis,
        array $scores,
        array $notes = [],
        ?string $rekomendasi = null
    ): SupervisiHasil {
        return DB::transaction(function () use ($jadwal, $jenis, $scores, $notes, $rekomendasi): SupervisiHasil {
            $aspeks = $jenis->aspekPenilaians()->get();

            $hasil = SupervisiHasil::firstOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'jenis_penilaian_id' => $jenis->id,
                ],
                [
                    'total_skor' => 0,
                    'skor_maksimal' => 0,
                    'nilai_akhir' => 0,
                    'ketercapaian' => null,
                    'rekomendasi' => $rekomendasi,
                ]
            );

            $totalSkor = 0;
            $skorMaksimal = 0;

            SupervisiDetailHasil::where('hasil_id', $hasil->id)->delete();

            $rows = [];
            $now = now();

            foreach ($aspeks as $aspek) {
                $rawSkor = $scores[$aspek->id] ?? null;
                if ($rawSkor === null || $rawSkor === '') {
                    continue;
                }

                $maxAspect = $this->aspectMaxScore($aspek, $jenis);
                $skor = max(1, min($maxAspect, (int) $rawSkor));
                $totalSkor += $skor;
                $skorMaksimal += $maxAspect;

                $rows[] = [
                    'hasil_id' => $hasil->id,
                    'aspek_id' => $aspek->id,
                    'skor' => $skor,
                    'catatan' => $notes[$aspek->id] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($rows)) {
                SupervisiDetailHasil::insert($rows);
            }

            $nilaiAkhir = $skorMaksimal > 0 ? round(($totalSkor / $skorMaksimal) * 100, 2) : 0.0;

            $hasil->update([
                'total_skor' => $totalSkor,
                'skor_maksimal' => $skorMaksimal,
                'nilai_akhir' => $nilaiAkhir,
                'ketercapaian' => $skorMaksimal > 0 ? $this->predikat($nilaiAkhir) : null,
                'rekomendasi' => $rekomendasi ?? $hasil->rekomendasi,
            ]);

            $this->recalculateJadwalSummary($jadwal);

            return $hasil->fresh(['detailHasils.aspek', 'jenisPenilaian']);
        });
    }

    /**
     * Hitung ulang ringkasan nilai akhir & ketercapaian pada jadwal dari seluruh komponen hasil.
     *
     * @return array{total_skor: int, skor_maksimal: int, nilai_akhir: float, ketercapaian: string}
     */
    public function recalculateJadwalSummary(SupervisiJadwal $jadwal): array
    {
        $hasils = $jadwal->hasils()->with(['detailHasils.aspek', 'jenisPenilaian'])->get();

        $grandTotalSkor = 0;
        $grandSkorMaks = 0;

        foreach ($hasils as $hasil) {
            if ($hasil->detailHasils->isEmpty()) {
                continue;
            }

            $jenisTotal = 0;
            $jenisMax = 0;

            foreach ($hasil->detailHasils as $detail) {
                $jenisTotal += (int) $detail->skor;
                $jenisMax += $detail->aspek
                    ? $this->aspectMaxScore($detail->aspek, $hasil->jenisPenilaian)
                    : (($hasil->jenisPenilaian?->skor_maksimal ?? 4) === 3 ? 3 : 4);
            }

            $jenisNilai = $jenisMax > 0 ? round(($jenisTotal / $jenisMax) * 100, 2) : 0.0;

            if ((int) $hasil->total_skor !== $jenisTotal || (int) $hasil->skor_maksimal !== $jenisMax) {
                $hasil->update([
                    'total_skor' => $jenisTotal,
                    'skor_maksimal' => $jenisMax,
                    'nilai_akhir' => $jenisNilai,
                    'ketercapaian' => $this->predikat($jenisNilai),
                ]);
            }

            $grandTotalSkor += $jenisTotal;
            $grandSkorMaks += $jenisMax;
        }

        $nilaiAkhir = $grandSkorMaks > 0 ? round(($grandTotalSkor / $grandSkorMaks) * 100, 2) : 0.0;
        $ketercapaian = $grandSkorMaks > 0 ? $this->predikat($nilaiAkhir) : 'Kurang';

        $jadwal->update([
            'nilai_akhir' => $grandSkorMaks > 0 ? $nilaiAkhir : null,
            'ketercapaian' => $grandSkorMaks > 0 ? $ketercapaian : null,
        ]);

        return [
            'total_skor' => $grandTotalSkor,
            'skor_maksimal' => $grandSkorMaks,
            'nilai_akhir' => $nilaiAkhir,
            'ketercapaian' => $ketercapaian,
        ];
    }
}
