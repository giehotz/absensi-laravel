<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectReferenceController extends Controller
{
    /**
     * Master referensi kurikulum mata pelajaran standar nasional.
     *
     * @var array<int, array{code: string, name: string, category: string, levels: array<string>}>
     */
    protected array $subjects = [
        // Kelompok Mata Pelajaran Ciri Khas Madrasah (KMA No. 1503 Tahun 2025)
        ['code' => 'ALQ', 'name' => 'Al-Qur\'an Hadis', 'category' => 'PAI Madrasah (KMA 1503)', 'levels' => ['MI', 'MTs', 'MA']],
        ['code' => 'AA', 'name' => 'Akidah Akhlak', 'category' => 'PAI Madrasah (KMA 1503)', 'levels' => ['MI', 'MTs', 'MA']],
        ['code' => 'FIQ', 'name' => 'Fikih', 'category' => 'PAI Madrasah (KMA 1503)', 'levels' => ['MI', 'MTs', 'MA']],
        ['code' => 'SKI', 'name' => 'Sejarah Kebudayaan Islam (SKI)', 'category' => 'PAI Madrasah (KMA 1503)', 'levels' => ['MI', 'MTs', 'MA']],
        ['code' => 'ARB', 'name' => 'Bahasa Arab', 'category' => 'Bahasa Madrasah (KMA 1503)', 'levels' => ['MI', 'MTs', 'MA']],

        // Kelompok Umum / Wajib Sekolah Umum (Kemendikbud)
        ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam dan Budi Pekerti', 'category' => 'Wajib Umum', 'levels' => ['SD', 'SMP', 'SMA', 'SMK']],
        ['code' => 'PAK', 'name' => 'Pendidikan Agama Kristen dan Budi Pekerti', 'category' => 'Wajib Umum', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'PAKT', 'name' => 'Pendidikan Agama Katolik dan Budi Pekerti', 'category' => 'Wajib Umum', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'PAH', 'name' => 'Pendidikan Agama Hindu dan Budi Pekerti', 'category' => 'Wajib Umum', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'PAB', 'name' => 'Pendidikan Agama Buddha dan Budi Pekerti', 'category' => 'Wajib Umum', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'PPKN', 'name' => 'Pendidikan Pancasila', 'category' => 'Wajib', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'IND', 'name' => 'Bahasa Indonesia', 'category' => 'Wajib', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'MTK', 'name' => 'Matematika', 'category' => 'Wajib', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'ING', 'name' => 'Bahasa Inggris', 'category' => 'Wajib', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)', 'category' => 'Wajib', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'SENI', 'name' => 'Seni dan Budaya (Umum)', 'category' => 'Seni Budaya', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'SRUP', 'name' => 'Seni Rupa', 'category' => 'Seni Budaya', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'SMUS', 'name' => 'Seni Musik', 'category' => 'Seni Budaya', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'STAR', 'name' => 'Seni Tari', 'category' => 'Seni Budaya', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'STEA', 'name' => 'Seni Teater', 'category' => 'Seni Budaya', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],

        // Mata Pelajaran Pilihan Baru (KMA 1503 Tahun 2025)
        ['code' => 'KKA', 'name' => 'Koding dan Kecerdasan Artifisial', 'category' => 'Pilihan (KMA 1503)', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],

        // Khusus SD / MI
        ['code' => 'IPAS', 'name' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'category' => 'Wajib SD/MI', 'levels' => ['SD', 'MI']],

        // Khusus SMP / MTs
        ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam (IPA)', 'category' => 'Wajib SMP/MTs', 'levels' => ['SMP', 'MTs']],
        ['code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial (IPS)', 'category' => 'Wajib SMP/MTs', 'levels' => ['SMP', 'MTs']],
        ['code' => 'INF', 'name' => 'Informatika', 'category' => 'Wajib SMP/MTs', 'levels' => ['SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'PRAK', 'name' => 'Prakarya dan Kewirausahaan', 'category' => 'Wajib', 'levels' => ['SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'BK', 'name' => 'Bimbingan dan Konseling (BK)', 'category' => 'Layanan', 'levels' => ['SMP', 'MTs', 'SMA', 'MA', 'SMK']],

        // Menengah Atas (SMA / MA) & Peminatan
        ['code' => 'SEJ', 'name' => 'Sejarah', 'category' => 'Wajib SMA/MA', 'levels' => ['SMA', 'MA', 'SMK']],
        ['code' => 'FIS', 'name' => 'Fisika', 'category' => 'MIPA', 'levels' => ['SMA', 'MA']],
        ['code' => 'KIM', 'name' => 'Kimia', 'category' => 'MIPA', 'levels' => ['SMA', 'MA']],
        ['code' => 'BIO', 'name' => 'Biologi', 'category' => 'MIPA', 'levels' => ['SMA', 'MA']],
        ['code' => 'MTKL', 'name' => 'Matematika Tingkat Lanjut', 'category' => 'MIPA', 'levels' => ['SMA', 'MA']],
        ['code' => 'EKO', 'name' => 'Ekonomi', 'category' => 'IPS', 'levels' => ['SMA', 'MA']],
        ['code' => 'GEO', 'name' => 'Geografi', 'category' => 'IPS', 'levels' => ['SMA', 'MA']],
        ['code' => 'SOS', 'name' => 'Sosiologi', 'category' => 'IPS', 'levels' => ['SMA', 'MA']],
        ['code' => 'ANT', 'name' => 'Antropologi', 'category' => 'Bahasa & Humaniora', 'levels' => ['SMA', 'MA']],
        ['code' => 'INGL', 'name' => 'Bahasa Inggris Tingkat Lanjut', 'category' => 'Bahasa & Humaniora', 'levels' => ['SMA', 'MA']],

        // Peminatan Keagamaan MA (Madrasah Aliyah)
        ['code' => 'ITAF', 'name' => 'Ilmu Tafsir', 'category' => 'Keagamaan MA', 'levels' => ['MA']],
        ['code' => 'IHAD', 'name' => 'Ilmu Hadis', 'category' => 'Keagamaan MA', 'levels' => ['MA']],
        ['code' => 'USFIQ', 'name' => 'Ushul Fikih', 'category' => 'Keagamaan MA', 'levels' => ['MA']],
        ['code' => 'ARBL', 'name' => 'Bahasa Arab Peminatan', 'category' => 'Keagamaan MA', 'levels' => ['MA']],

        // Khusus SMK
        ['code' => 'DPK', 'name' => 'Dasar-dasar Program Keahlian', 'category' => 'Kejuruan SMK', 'levels' => ['SMK']],
        ['code' => 'KKK', 'name' => 'Konsentrasi Keahlian Kejuruan', 'category' => 'Kejuruan SMK', 'levels' => ['SMK']],
        ['code' => 'PKK', 'name' => 'Projek Kreatif dan Kewirausahaan (PKK)', 'category' => 'Kejuruan SMK', 'levels' => ['SMK']],
        ['code' => 'SIMDIG', 'name' => 'Simulasi dan Komunikasi Digital', 'category' => 'Kejuruan SMK', 'levels' => ['SMK']],

        // Muatan Lokal
        ['code' => 'ML-BD', 'name' => 'Muatan Lokal: Bahasa Daerah', 'category' => 'Muatan Lokal', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'ML-BTQ', 'name' => 'Muatan Lokal: Baca Tulis Al-Qur\'an (BTQ)', 'category' => 'Muatan Lokal', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'ML-KDA', 'name' => 'Muatan Lokal: Kesenian Daerah', 'category' => 'Muatan Lokal', 'levels' => ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK']],
        ['code' => 'ML-PBA', 'name' => 'Muatan Lokal: Pendalaman Bahasa Arab', 'category' => 'Muatan Lokal', 'levels' => ['MI', 'MTs', 'MA']],
    ];

    /**
     * Menampilkan daftar referensi mata pelajaran via REST API.
     */
    public function index(Request $request): JsonResponse
    {
        $level = $request->query('level');
        $search = $request->query('search');

        $results = collect($this->subjects);

        if (! empty($level) && $level !== 'SEMUA') {
            $results = $results->filter(function ($item) use ($level) {
                return in_array(strtoupper($level), $item['levels'], true);
            });
        }

        if (! empty($search)) {
            $searchLower = strtolower($search);
            $results = $results->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['code']), $searchLower)
                    || str_contains(strtolower($item['name']), $searchLower)
                    || str_contains(strtolower($item['category']), $searchLower);
            });
        }

        $formatted = $results->values()->map(function ($item) {
            return [
                'code' => $item['code'],
                'name' => $item['name'],
                'category' => $item['category'],
                'levels' => $item['levels'],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar referensi mata pelajaran berhasil diambil.',
            'level' => $level ?: 'SEMUA',
            'total' => $formatted->count(),
            'data' => $formatted,
        ]);
    }
}
