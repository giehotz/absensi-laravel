<?php

namespace App\Services\Ujian;

use App\Models\Ujian\UjianPeriodeKelompok;
use App\Models\Ujian\UjianSesi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class UjianKalenderService
{
    /**
     * Menghitung tanggal-tanggal efektif pelaksanaan ujian berdasarkan konfigurasi.
     *
     * @return Collection<int, Carbon>
     */
    public function getEffectiveDates(UjianPeriodeKelompok $config): Collection
    {
        if (! $config->tanggal_mulai || ! $config->tanggal_selesai) {
            return collect();
        }

        if ($config->tanggal_selesai->lt($config->tanggal_mulai)) {
            return collect();
        }

        $period = CarbonPeriod::create($config->tanggal_mulai, $config->tanggal_selesai);
        $liburList = collect($config->tanggal_libur ?? [])
            ->filter()
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->flip()
            ->all();

        $dates = collect();

        foreach ($period as $date) {
            $carbon = Carbon::instance($date);

            // 1. Cek pengecualian hari Minggu
            if ($config->kecualikan_minggu && $carbon->isSunday()) {
                continue;
            }

            // 2. Cek tanggal libur
            if (isset($liburList[$carbon->format('Y-m-d')])) {
                continue;
            }

            $dates->push($carbon);
        }

        return $dates->values();
    }

    /**
     * Membangun daftar slot ujian per hari dan sesi.
     *
     * @return array<int, array{
     *   hari_ke: int,
     *   nama_hari: string,
     *   tanggal: string,
     *   tanggal_label: string,
     *   sesi_ke: int,
     *   jam_mulai: string,
     *   jam_selesai: string,
     *   durasi_menit: int
     * }>
     */
    public function buildSlots(UjianPeriodeKelompok $config): array
    {
        $dates = $this->getEffectiveDates($config);
        if ($dates->isEmpty()) {
            return [];
        }

        $lastIndex = $dates->count() - 1;
        $activeSesiModels = null;
        $slots = [];

        // Pre-parse jam_sesi dari konfigurasi
        $configuredJam = [];
        if (! empty($config->jam_sesi) && is_array($config->jam_sesi)) {
            foreach ($config->jam_sesi as $item) {
                if (isset($item['sesi'], $item['mulai'], $item['selesai'])) {
                    $configuredJam[(int) $item['sesi']] = [
                        'mulai' => $item['mulai'],
                        'selesai' => $item['selesai'],
                    ];
                }
            }
        }

        foreach ($dates as $index => $date) {
            $sessionCount = ($index === $lastIndex)
                ? (int) $config->sesi_hari_terakhir
                : (int) $config->jumlah_sesi_per_hari;

            $namaHari = $date->locale('id')->isoFormat('dddd');

            for ($sesi = 1; $sesi <= $sessionCount; $sesi++) {
                $jamMulai = null;
                $jamSelesai = null;

                // 1. Ambil dari konfigurasi kartu
                if (isset($configuredJam[$sesi])) {
                    $jamMulai = $configuredJam[$sesi]['mulai'];
                    $jamSelesai = $configuredJam[$sesi]['selesai'];
                }

                // 2. Fallback ke database master UjianSesi
                if (! $jamMulai || ! $jamSelesai) {
                    if ($activeSesiModels === null) {
                        try {
                            $activeSesiModels = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get()->keyBy('sesi_ke');
                        } catch (\Throwable) {
                            $activeSesiModels = collect();
                        }
                    }
                    if (isset($activeSesiModels[$sesi])) {
                        $jamMulai = Carbon::parse($activeSesiModels[$sesi]->jam_mulai)->format('H:i');
                        $jamSelesai = Carbon::parse($activeSesiModels[$sesi]->jam_selesai)->format('H:i');
                    }
                }

                // 3. Fallback konstanta jika master belum ada
                if (! $jamMulai || ! $jamSelesai) {
                    [$jamMulai, $jamSelesai] = match ($sesi) {
                        1 => ['07:30', '09:30'],
                        2 => ['10:00', '12:00'],
                        3 => ['13:00', '14:30'],
                        4 => ['15:00', '16:30'],
                        default => ['08:00', '09:30'],
                    };
                }

                // Hitung durasi menit
                $durasiMenit = 90;
                try {
                    $tMulai = Carbon::createFromFormat('H:i', $jamMulai);
                    $tSelesai = Carbon::createFromFormat('H:i', $jamSelesai);
                    if ($tSelesai->gt($tMulai)) {
                        $durasiMenit = $tMulai->diffInMinutes($tSelesai);
                    }
                } catch (\Throwable) {
                    $durasiMenit = 90;
                }

                $slots[] = [
                    'hari_ke' => $index + 1,
                    'nama_hari' => ucfirst($namaHari),
                    'tanggal' => $date->format('Y-m-d'),
                    'tanggal_label' => $date->format('d-m-Y'),
                    'sesi_ke' => $sesi,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'durasi_menit' => $durasiMenit,
                ];
            }
        }

        return $slots;
    }

    /**
     * Menghasilkan struktur preview hari dan slot untuk antarmuka pengguna.
     *
     * @return array{
     *   hari_efektif: int,
     *   total_slot: int,
     *   tanggal: array<int, array{tanggal: string, label: string, nama_hari: string}>,
     *   slots: array<int, array<string, mixed>>
     * }
     */
    public function getPreview(UjianPeriodeKelompok $config): array
    {
        $dates = $this->getEffectiveDates($config);
        $slots = $this->buildSlots($config);

        $tanggalList = $dates->map(function (Carbon $date) {
            return [
                'tanggal' => $date->format('Y-m-d'),
                'label' => $date->format('d-m-Y'),
                'nama_hari' => ucfirst($date->locale('id')->isoFormat('dddd')),
            ];
        })->all();

        return [
            'hari_efektif' => $dates->count(),
            'total_slot' => count($slots),
            'tanggal' => $tanggalList,
            'slots' => $slots,
        ];
    }
}
