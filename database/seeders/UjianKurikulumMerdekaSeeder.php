<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\Teacher;
use App\Models\Ujian\UjianJenis;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use Illuminate\Database\Seeder;

class UjianKurikulumMerdekaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Jenis Asesmen Standar Kurikulum Merdeka
        $jenisList = [
            [
                'kode_jenis' => 'SLM',
                'nama_jenis' => 'Asesmen Sumatif Lingkup Materi',
                'kategori_kurikulum' => 'Sumatif Lingkup Materi',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'ASESMEN SUMATIF LINGKUP MATERI',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'STS-1',
                'nama_jenis' => 'Asesmen Sumatif Tengah Semester (STS) Ganjil',
                'kategori_kurikulum' => 'Sumatif Tengah Semester',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'ASESMEN SUMATIF TENGAH SEMESTER GANJIL',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'SAS-1',
                'nama_jenis' => 'Asesmen Sumatif Akhir Semester (SAS) Ganjil',
                'kategori_kurikulum' => 'Sumatif Akhir Semester (SAS)',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'ASESMEN SUMATIF AKHIR SEMESTER GANJIL',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'STS-2',
                'nama_jenis' => 'Asesmen Sumatif Tengah Semester (STS) Genap',
                'kategori_kurikulum' => 'Sumatif Tengah Semester',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'ASESMEN SUMATIF TENGAH SEMESTER GENAP',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'SAT-2',
                'nama_jenis' => 'Asesmen Sumatif Akhir Tahun (SAT) Genap',
                'kategori_kurikulum' => 'Sumatif Akhir Tahun (SAT)',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'ASESMEN SUMATIF AKHIR TAHUN',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'SAJ-UM',
                'nama_jenis' => 'Asesmen Sumatif Akhir Jenjang / Ujian Madrasah',
                'kategori_kurikulum' => 'Sumatif Akhir Jenjang',
                'fase_sasaran' => 'Fase C',
                'judul_kop_kartu' => 'ASESMEN SUMATIF AKHIR JENJANG (UM)',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'PRAKTIK',
                'nama_jenis' => 'Ujian Praktik & Kinerja Portofolio',
                'kategori_kurikulum' => 'Ujian Praktik / Kinerja',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'UJIAN PRAKTIK & KINERJA',
                'status_aktif' => true,
            ],
            [
                'kode_jenis' => 'P5-PPRA',
                'nama_jenis' => 'Asesmen Projek Penguatan Profil Pelajar Pancasila & Rahmatan Lil Alamin',
                'kategori_kurikulum' => 'P5-PPRA',
                'fase_sasaran' => 'Semua Fase',
                'judul_kop_kartu' => 'ASESMEN PROJEK P5-PPRA',
                'status_aktif' => true,
            ],
        ];

        foreach ($jenisList as $item) {
            UjianJenis::updateOrCreate(
                ['kode_jenis' => $item['kode_jenis']],
                $item
            );
        }

        // 2. Ruang Ujian Awal
        $ruangList = [
            ['kode_ruang' => 'R-01', 'nama_ruang' => 'Ruang 01', 'kapasitas_maksimal' => 24, 'lokasi_gedung' => 'Gedung Utama Lantai 1'],
            ['kode_ruang' => 'R-02', 'nama_ruang' => 'Ruang 02', 'kapasitas_maksimal' => 24, 'lokasi_gedung' => 'Gedung Utama Lantai 1'],
            ['kode_ruang' => 'R-03', 'nama_ruang' => 'Ruang 03', 'kapasitas_maksimal' => 24, 'lokasi_gedung' => 'Gedung Utama Lantai 2'],
            ['kode_ruang' => 'R-04', 'nama_ruang' => 'Ruang 04', 'kapasitas_maksimal' => 24, 'lokasi_gedung' => 'Gedung Utama Lantai 2'],
            ['kode_ruang' => 'R-05', 'nama_ruang' => 'Ruang 05', 'kapasitas_maksimal' => 24, 'lokasi_gedung' => 'Gedung Timur'],
            ['kode_ruang' => 'R-06', 'nama_ruang' => 'Ruang 06', 'kapasitas_maksimal' => 24, 'lokasi_gedung' => 'Gedung Timur'],
        ];

        foreach ($ruangList as $ruang) {
            UjianRuang::updateOrCreate(
                ['kode_ruang' => $ruang['kode_ruang']],
                $ruang
            );
        }

        // 3. Periode Ujian Aktif Default (SAS Ganjil)
        $activeYear = AcademicYear::activeSemester() ?? AcademicYear::first();
        $sasGanjil = UjianJenis::where('kode_jenis', 'SAS-1')->first();

        $yearName = $activeYear?->name ?? '2026/2027';
        $semesterName = ucfirst($activeYear?->semester ?? 'Ganjil');

        $periode = UjianPeriode::firstOrCreate(
            [
                'ujian_jenis_id' => $sasGanjil?->id ?? 1,
                'tahun_pelajaran' => $yearName,
                'semester' => $semesterName,
            ],
            [
                'academic_year_id' => $activeYear?->id,
                'nama_periode' => "Asesmen Sumatif Akhir Semester (SAS) {$semesterName} {$yearName}",
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
                'tempat_cetak' => 'Tanggamus',
                'tanggal_cetak_kartu' => now()->toDateString(),
                'is_active' => true,
            ]
        );

        // 4. Default Pengaturan Tanda Tangan & Kop Mewarisi dari AttendanceSetting
        $setting = AttendanceSetting::first();
        $schoolName = $setting?->school_name ?: 'MIN 2 TANGGAMUS';
        $principalName = $setting?->card_principal_name ?: ($setting?->headmasterTeacher?->user?->name ?: 'Sipulloh, M.Pd');
        $principalNip = $setting?->card_principal_nip ?: ($setting?->headmasterTeacher?->nip ?: '197005272007011022');

        UjianPengaturanTtd::firstOrCreate(
            ['ujian_periode_id' => $periode->id],
            [
                'nama_instansi' => $schoolName,
                'kop_baris_1' => 'KARTU PESERTA',
                'kop_baris_2' => 'ASESMEN SUMATIF AKHIR SEMESTER GANJIL',
                'kop_baris_3' => $schoolName,
                'kop_baris_4' => "TAHUN PELAJARAN {$yearName}",
                'jabatan_penandatangan' => 'Kepala Madrasah',
                'nama_kepala' => $principalName,
                'nip_kepala' => $principalNip,
                'tempat_penetapan' => 'Tanggamus',
                'tanggal_penetapan' => now()->toDateString(),
                'path_logo_kiri' => $setting?->logo,
                'path_watermark' => $setting?->logo,
                'opacity_watermark' => 0.35,
                'path_gambar_ttd' => $setting?->card_signature_image,
                'lebar_ttd_cm' => 3.00,
                'tinggi_ttd_cm' => 3.00,
                'offset_atas_ttd_cm' => 0.00,
                'offset_kanan_ttd_cm' => 0.00,
                'tampilkan_stempel' => true,
                'tampilkan_qrcode_validasi' => true,
                'is_active' => true,
            ]
        );

        // 5. Contoh Penunjukan Awal Panitia Ujian (jika ada guru)
        $teachers = Teacher::with('user')->take(3)->get();
        if ($teachers->count() >= 3) {
            $roles = ['ketua', 'sekretaris', 'bendahara'];
            foreach ($teachers as $idx => $teacher) {
                UjianPanitia::firstOrCreate(
                    [
                        'ujian_periode_id' => $periode->id,
                        'teacher_id' => $teacher->id,
                    ],
                    [
                        'jabatan' => $roles[$idx] ?? 'anggota',
                        'sk_nomor' => 'MIN.02/PP.00.4/01/2026',
                        'keterangan' => 'Panitia Inti SAS',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
