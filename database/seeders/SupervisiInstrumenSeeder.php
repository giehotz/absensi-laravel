<?php

namespace Database\Seeders;

use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use Illuminate\Database\Seeder;

class SupervisiInstrumenSeeder extends Seeder
{
    public function run(): void
    {
        $skalaReguler = [
            '86-100' => 'Baik Sekali',
            '70-85' => 'Baik',
            '55-69' => 'Cukup',
            '0-54' => 'Kurang',
        ];

        $skalaPmm = [
            '1' => 'Belum Dilakukan',
            '2' => 'Dilakukan tapi Belum Efektif',
            '3' => 'Dilakukan dan Efektif',
        ];

        foreach ($this->regulerInstruments() as $item) {
            $this->seedInstrument($item, SupervisiJenisPenilaian::KATEGORI_REGULER, 4, $skalaReguler, SupervisiAspekPenilaian::SKALA_REGULER);
        }

        foreach ($this->pmmInstruments() as $item) {
            $this->seedInstrument($item, SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM, 3, $skalaPmm, SupervisiAspekPenilaian::SKALA_PMM);
        }

        foreach ($this->perangkatAjarInstruments() as $item) {
            $this->seedInstrument($item, SupervisiJenisPenilaian::KATEGORI_PERANGKAT_AJAR, 4, $skalaReguler, SupervisiAspekPenilaian::SKALA_REGULER);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $kategoriSkor
     */
    private function seedInstrument(
        array $item,
        string $kategoriSupervisi,
        int $skorMaksimal,
        array $kategoriSkor,
        string $tipeSkala
    ): void {
        $jenis = SupervisiJenisPenilaian::updateOrCreate(
            ['kode' => $item['kode']],
            [
                'nama' => $item['nama'],
                'kategori_supervisi' => $kategoriSupervisi,
                'dimensi_pmm' => $item['dimensi_pmm'] ?? null,
                'skor_maksimal' => $skorMaksimal,
                'kategori_skor' => $kategoriSkor,
                'deskripsi' => $item['deskripsi'] ?? null,
                'is_active' => true,
            ]
        );

        foreach ($item['aspek'] as $index => $aspek) {
            $urutan = $index + 1;
            $payload = is_array($aspek) ? $aspek : ['nama_aspek' => $aspek];

            SupervisiAspekPenilaian::updateOrCreate(
                [
                    'jenis_penilaian_id' => $jenis->id,
                    'urutan' => $urutan,
                ],
                [
                    'nama_aspek' => $payload['nama_aspek'],
                    'fokus_perilaku' => $payload['fokus_perilaku'] ?? null,
                    'perilaku_dianjurkan' => $payload['perilaku_dianjurkan'] ?? null,
                    'perilaku_dihindari' => $payload['perilaku_dihindari'] ?? null,
                    'tipe_skala' => $tipeSkala,
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function regulerInstruments(): array
    {
        return [
            [
                'kode' => 'REG-ADM',
                'nama' => 'SUPERVISI ADMINISTRASI GURU (PERENCANAAN PEMBELAJARAN)',
                'deskripsi' => 'Instrumen penilaian kelengkapan administrasi dan perencanaan pembelajaran guru.',
                'aspek' => [
                    'Kalender Pendidikan',
                    'Program Tahunan',
                    'Program Semester',
                    'Silabus',
                    'Modul Ajar',
                    'Jadwal Tatap Muka',
                    'Agenda Harian',
                    'Daftar Nilai',
                    'KKM',
                    'Absensi Peserta Didik',
                    'Buku Pegangan Guru',
                    'Buku Teks Siswa',
                ],
            ],
            [
                'kode' => 'REG-PROSES',
                'nama' => 'SUPERVISI PROSES PEMBELAJARAN (PELAKSANAAN PEMBELAJARAN)',
                'deskripsi' => 'Instrumen observasi pelaksanaan kegiatan belajar mengajar di kelas.',
                'aspek' => [
                    'Pendahuluan',
                    'Kegiatan Inti Eksplorasi',
                    'Kegiatan Inti Elaborasi',
                    'Kegiatan Inti Konfirmasi',
                    'Penutup',
                    'Metode Pembelajaran',
                    'Media Pembelajaran',
                    'Sumber Belajar',
                    'Evaluasi',
                    'Karakteristik Siswa',
                    'Interaksi Guru-Murid',
                    'Penggunaan Waktu',
                ],
            ],
            [
                'kode' => 'REG-EVAL',
                'nama' => 'SUPERVISI EVALUASI PEMBELAJARAN (PENILAIAN PEMBELAJARAN)',
                'deskripsi' => 'Instrumen supervisi teknik, butir soal, analisis, dan tindak lanjut evaluasi.',
                'aspek' => [
                    'Prinsip Penilaian',
                    'Teknik Penilaian',
                    'Bentuk Instrumen',
                    'Butir Soal',
                    'Analisis Butir Soal',
                    'Analisis Hasil Penilaian',
                    'Tindak Lanjut Hasil Penilaian',
                    'Pelaporan Hasil Penilaian',
                    'Manfaat Hasil Penilaian',
                    'Keterlibatan Peserta Didik',
                    'Portofolio',
                    'Karya Siswa',
                ],
            ],
            [
                'kode' => 'REG-PENGEMBANGAN',
                'nama' => 'SUPERVISI PENGEMBANGAN DIRI GURU',
                'deskripsi' => 'Instrumen supervisi pengembangan keprofesian berkelanjutan dan karya guru.',
                'aspek' => [
                    'Mengikuti Pelatihan',
                    'Mengikuti Workshop',
                    'Melaksanakan Penelitian Tindakan Kelas',
                    'Menyusun Karya Tulis Ilmiah',
                    'Melaksanakan Pengabdian Kepada Masyarakat',
                    'Mengikuti Organisasi Profesi',
                    'Mengembangkan Bahan Ajar',
                    'Melaksanakan Bimbingan Konseling',
                    'Melaksanakan Bimbingan Penelitian',
                    'Melaksanakan Bimbingan Akademik',
                    'Melaksanakan Tugas Tambahan',
                    'Meningkatkan Kualifikasi Pendidikan',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pmmInstruments(): array
    {
        return [
            [
                'kode' => 'PMM-DISIPLIN',
                'nama' => 'PENERAPAN DISIPLIN POSITIF',
                'dimensi_pmm' => 'manajemen_kelas',
                'deskripsi' => 'Upaya menerapkan prinsip disiplin positif untuk mengelola perilaku dan kebiasaan kelas yang disepakati bersama.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Refleksi Dinamika Kelas',
                        'fokus_perilaku' => 'Guru melakukan refleksi dinamika kelas untuk menerapkan kesepakatan kelas',
                        'perilaku_dianjurkan' => "• Guru mengajak peserta didik melakukan refleksi dinamika kelas mengacu kesepakatan kelas\n• Guru menunjukkan kesediaan mendengarkan pandangan peserta didik tentang dinamika kelas\n• Guru bersikap adaptif sehingga bersedia mengubah kesepakatan kelas bila diperlukan",
                        'perilaku_dihindari' => "• Guru mengabaikan pendapat peserta didik tentang apa yang terjadi di kelas\n• Guru bersikap defensif dalam menyikapi umpan balik dari peserta didik terkait kedisiplinan\n• Guru menerapkan hukuman fisik atau intimidasi verbal",
                    ],
                    [
                        'nama_aspek' => 'Penguatan Positif Perilaku',
                        'fokus_perilaku' => 'Guru melakukan penguatan positif terhadap perilaku yang sesuai atau mendukung kesepakatan kelas',
                        'perilaku_dianjurkan' => "• Guru memberi pujian terhadap perilaku peserta didik yang sesuai kesepakatan kelas\n• Guru memberi penguatan positif dengan beragam cara yang bermakna\n• Guru mengakui suatu perilaku positif secara spesifik dan menjelaskan alasannya",
                        'perilaku_dihindari' => "• Guru tidak konsisten dalam memberikan penguatan positif\n• Guru memberikan reward berlebihan yang mengurangi motivasi intrinsik\n• Guru membanding-bandingkan pencapaian antar murid secara tidak adil",
                    ],
                    [
                        'nama_aspek' => 'Fasilitasi Restitusi Pelanggaran',
                        'fokus_perilaku' => 'Guru memfasilitasi peserta didik menyadari konsekuensi dan memperbaiki perilaku melanggarnya (restitusi)',
                        'perilaku_dianjurkan' => "• Guru membantu peserta didik menyadari konsekuensi dari perilaku melanggarnya\n• Guru mendengarkan sudut pandang peserta didik terhadap perilaku melanggarnya\n• Guru memberikan dukungan pada peserta didik dalam melakukan perbaikan perilakunya",
                        'perilaku_dihindari' => "• Guru langsung memberikan hukuman tanpa menanyakan alasan\n• Guru mempermalukan peserta didik di depan teman-temannya\n• Guru bersikap acuh tak acuh terhadap pelanggaran berulang",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-SUASANA',
                'nama' => 'KETERATURAN SUASANA KELAS',
                'dimensi_pmm' => 'manajemen_kelas',
                'deskripsi' => 'Upaya membangun suasana kelas yang kondusif untuk proses belajar, dengan minimal gangguan yang mengalihkan perhatian.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Komunikasi Positif Kondusif',
                        'fokus_perilaku' => 'Guru melakukan komunikasi positif untuk membangun suasana kelas yang kondusif',
                        'perilaku_dianjurkan' => "• Guru memanggil murid dengan menyebut namanya secara hangat\n• Guru menyampaikan harapan positif terhadap iklim belajar di kelas\n• Guru melakukan aktivitas pencair suasana (ice breaking) yang relevan",
                        'perilaku_dihindari' => "• Guru memanggil murid dengan sebutan bernada merendahkan/label negatif\n• Guru menggunakan kata-kata bernada sarkasme atau ancaman\n• Guru mengabaikan ketegangan suasana kelas",
                    ],
                    [
                        'nama_aspek' => 'Strategi Pengelompokan Efektif',
                        'fokus_perilaku' => 'Guru melakukan strategi pengelompokan untuk mengaktifkan keterlibatan peserta didik',
                        'perilaku_dianjurkan' => "• Guru mengelompokkan murid dengan menyampaikan tujuannya secara transparan\n• Guru menyediakan beragam peran dalam kelompok agar semua anggota aktif\n• Guru mengajak murid berinteraksi dan berkolaborasi dalam kelompok",
                        'perilaku_dihindari' => "• Guru membiarkan kelompok didominasi hanya oleh murid tertentu\n• Guru membagi kelompok tanpa mempertimbangkan kebutuhan dan dinamika belajar\n• Guru tidak mendampingi proses interaksi antarkelompok",
                    ],
                    [
                        'nama_aspek' => 'Mengingatkan Aturan/Kesepakatan',
                        'fokus_perilaku' => 'Guru membuat dan mengingatkan aturan/kesepakatan kelas',
                        'perilaku_dianjurkan' => "• Guru membuat aturan/kesepakatan kelas bersama peserta didik\n• Guru mengajak peserta didik mengingat aturan/kesepakatan yang telah disepakati\n• Guru mengajak peserta didik menilai seberapa efektif pelaksanaan aturan kelas",
                        'perilaku_dihindari' => "• Guru menetapkan aturan sepihak tanpa melibatkan murid\n• Guru mengabaikan pelanggaran kesepakatan kelas\n• Guru membuat aturan yang tidak realistis untuk dijalankan",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-EKSPEKTASI',
                'nama' => 'EKSPEKTASI PADA PESERTA DIDIK',
                'dimensi_pmm' => 'dukungan_psikologis',
                'deskripsi' => 'Upaya mengomunikasikan ekspektasi tinggi terhadap kemampuan dan potensi seluruh peserta didik.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Komunikasi Harapan Masa Depan',
                        'fokus_perilaku' => 'Guru mengkomunikasikan harapannya yang tinggi terhadap masa depan seluruh peserta didiknya',
                        'perilaku_dianjurkan' => "• Guru mengenal dan menyebutkan potensi unik peserta didik\n• Guru memberikan contoh tokoh inspiratif yang relevan dengan kehidupan murid\n• Guru menyampaikan keyakinan bahwa semua murid mampu berkembang mencapai potensinya",
                        'perilaku_dihindari' => "• Guru meremehkan potensi atau cita-cita peserta didik\n• Guru bersikap apatis terhadap masa depan murid\n• Guru melabeli murid dengan predikat yang membatasi perkembangannya",
                    ],
                    [
                        'nama_aspek' => 'Harapan Positif yang Setara',
                        'fokus_perilaku' => 'Guru mengkomunikasikan harapan positif terhadap semua peserta didik secara setara dan tanpa diskriminasi',
                        'perilaku_dianjurkan' => "• Guru memberikan perhatian dan kesempatan setara kepada seluruh murid\n• Guru memfasilitasi murid yang butuh bimbingan lebih tanpa merendahkan martabatnya\n• Guru mengapresiasi keberagaman latar belakang dan kemampuan murid",
                        'perilaku_dihindari' => "• Guru hanya memberi panggung pada murid berprestasi tinggi\n• Guru mendiskriminasi murid berdasarkan latar belakang, gender, atau status ekonomi\n• Guru bersikap pilih kasih dalam proses pembelajaran",
                    ],
                    [
                        'nama_aspek' => 'Tantangan Belajar Bermakna',
                        'fokus_perilaku' => 'Guru memberikan tantangan yang bermakna disertai motivasi untuk mencapainya',
                        'perilaku_dianjurkan' => "• Guru memberi tugas yang menantang namun terjangkau dengan kemampuan murid\n• Guru memandu proses penyelesaian tantangan secara bertahap (scaffolding)\n• Guru memotivasi murid agar tekun dan pantang menyerah saat menghadapi kendala",
                        'perilaku_dihindari' => "• Guru memberikan tugas yang terlalu mudah sehingga tidak menantang, atau terlalu sulit tanpa bimbingan\n• Guru membiarkan murid menyerah tanpa ada upaya dukungan\n• Guru membebani murid dengan tugas hafalan mekanis yang tidak bermakna",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-KEPEDULIAN',
                'nama' => 'PERHATIAN DAN KEPEDULIAN',
                'dimensi_pmm' => 'dukungan_psikologis',
                'deskripsi' => 'Upaya menunjukkan empati, penghargaan, dan kepedulian terhadap kebutuhan belajar dan kondisi emosional murid.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Empati dan Pemahaman Murid',
                        'fokus_perilaku' => 'Guru menunjukkan empati untuk mendapatkan pemahaman utuh tentang peserta didik',
                        'perilaku_dianjurkan' => "• Guru mendengarkan aktif saat murid menyampaikan pendapat atau keluh kesah\n• Guru peka terhadap perubahan emosi atau perilaku murid di kelas\n• Guru mencari tahu akar masalah murid saat mengalami kesulitan belajar",
                        'perilaku_dihindari' => "• Guru mengabaikan keluhan, kesedihan, atau kekhawatiran murid\n• Guru langsung menghakimi perasaan atau perilaku murid\n• Guru bersikap dingin, sinis, dan menjaga jarak emosional",
                    ],
                    [
                        'nama_aspek' => 'Responsif Kebutuhan Belajar',
                        'fokus_perilaku' => 'Guru menunjukkan pemahaman terhadap kebutuhan, kondisi, dan karakteristik peserta didik',
                        'perilaku_dianjurkan' => "• Guru memfasilitasi gaya belajar, minat, dan kecepatan belajar yang beragam\n• Guru memberikan fleksibilitas tugas sesuai kondisi spesifik murid\n• Guru merancang aktivitas pembelajaran yang aman, inklusif, dan ramah anak",
                        'perilaku_dihindari' => "• Guru menyamaratakan seluruh murid tanpa diferensiasi metode\n• Guru memaksakan satu cara belajar yang tidak cocok bagi semua anak\n• Guru tidak memedulikan kebutuhan khusus peserta didik",
                    ],
                    [
                        'nama_aspek' => 'Apresiasi Usaha dan Proses',
                        'fokus_perilaku' => 'Guru mengakui dan menghargai usaha yang ditunjukkan peserta didik',
                        'perilaku_dianjurkan' => "• Guru mengapresiasi proses belajar dan daya juang murid, bukan hanya nilai akhir\n• Guru memberi penguatan atas kemajuan sekecil apapun yang dicapai murid\n• Guru merayakan keberanian murid mencoba hal baru atau menjawab tantangan",
                        'perilaku_dihindari' => "• Guru hanya memuji murid yang mendapatkan nilai sempurna\n• Guru mengabaikan murid yang telah bekerja keras namun belum meraih hasil maksimal\n• Guru mencela atau menertawakan kesalahan murid",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-UMPAN-BALIK',
                'nama' => 'UMPAN BALIK KONSTRUKTIF',
                'dimensi_pmm' => 'dukungan_psikologis',
                'deskripsi' => 'Upaya memberikan informasi umpan balik yang spesifik, tepat waktu, dan berorientasi pada proses perbaikan belajar.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Umpan Balik Spesifik & Terarah',
                        'fokus_perilaku' => 'Guru memberikan umpan balik spesifik dan berorientasi tujuan',
                        'perilaku_dianjurkan' => "• Guru menjelaskan letak kekuatan dan kekurangan karya murid secara rinci\n• Guru mengaitkan umpan balik dengan kriteria ketercapaian tujuan pembelajaran\n• Guru memberikan langkah konkret yang dapat diambil murid untuk perbaikan",
                        'perilaku_dihindari' => "• Guru memberi umpan balik terlalu umum seperti 'Bagus' atau 'Salah' tanpa penjelasan\n• Guru tidak menjelaskan cara memperbaiki kesalahan yang dibuat\n• Guru mengomentari pribadi murid, bukan hasil karyanya",
                    ],
                    [
                        'nama_aspek' => 'Fokus pada Proses dan Growth Mindset',
                        'fokus_perilaku' => 'Guru memberikan umpan balik yang fokus pada proses atau usaha peserta didik',
                        'perilaku_dianjurkan' => "• Guru mengapresiasi strategi berpikir yang digunakan murid dalam menyelesaikan masalah\n• Guru mendiskusikan alternatif cara penyelesaian masalah bersama murid\n• Guru mendorong cara pandang bertumbuh (growth mindset) bahwa kesalahan adalah bagian dari belajar",
                        'perilaku_dihindari' => "• Guru hanya membandingkan hasil kerja antar murid\n• Guru mengabaikan proses atau strategi yang sudah diupayakan murid\n• Guru fokus pada detail minor yang tidak esensial",
                    ],
                    [
                        'nama_aspek' => 'Diskusi Reflektif Hasil Belajar',
                        'fokus_perilaku' => 'Guru menunjukkan kesediaan mendiskusikan umpan balik dengan peserta didik',
                        'perilaku_dianjurkan' => "• Guru membuka sesi dialog terkait hasil asesmen dan respon umpan balik\n• Guru mengajak murid merefleksikan catatan perbaikan yang diterimanya\n• Guru mendengarkan penjelasan murid atas hasil pekerjaannya secara adil",
                        'perilaku_dihindari' => "• Guru bersikap otoriter atas koreksi yang diberikan tanpa memberi ruang klarifikasi\n• Guru menolak mendengarkan penjelasan murid\n• Guru terburu-buru memberikan feedback tanpa memastikan murid memahaminya",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-INTERAKTIF',
                'nama' => 'AKTIVITAS INTERAKTIF',
                'dimensi_pmm' => 'metode_pembelajaran',
                'deskripsi' => 'Upaya memfasilitasi keterlibatan aktif peserta didik melalui dialog, kolaborasi, dan eksplorasi gagasan.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Peran Aktif Semua Murid',
                        'fokus_perilaku' => 'Guru memfasilitasi kegiatan pembelajaran yang memberi peran pada semua peserta didik',
                        'perilaku_dianjurkan' => "• Guru merancang aktivitas yang membagi peran secara adil dan terukur\n• Guru memastikan setiap murid memiliki tanggung jawab konkret dalam tugas\n• Guru memotivasi murid yang pendiam untuk berani mengambil peran aktif",
                        'perilaku_dihindari' => "• Guru membiarkan murid tertentu pasif atau asyik sendiri\n• Guru hanya mengandalkan murid tertentu yang vokal\n• Guru mendominasi seluruh jalannya pembelajaran (teacher-centered murni)",
                    ],
                    [
                        'nama_aspek' => 'Pertanyaan Pemantik Berpikir Kritis',
                        'fokus_perilaku' => 'Guru mengajukan pertanyaan yang menstimulasi proses diskusi dan berpikir kritis',
                        'perilaku_dianjurkan' => "• Guru memberikan pertanyaan pemantik terbuka tingkat tinggi (HOTS)\n• Guru memberi jeda waktu tunggu (wait time) yang memadai agar murid berpikir\n• Guru merespon jawaban murid dengan pertanyaan lanjutan yang mendalam",
                        'perilaku_dihindari' => "• Guru hanya mengajukan pertanyaan ingatan tertutup (Ya/Tidak)\n• Guru langsung menjawab pertanyaannya sendiri tanpa memberi kesempatan murid berpikir\n• Guru memotong pembicaraan murid saat sedang merumuskan ide",
                    ],
                    [
                        'nama_aspek' => 'Diskusi Kelompok Kritis & Inklusif',
                        'fokus_perilaku' => 'Guru memfasilitasi terjadinya diskusi kelompok yang interaktif, kritis, dan inklusif',
                        'perilaku_dianjurkan' => "• Guru memandu jalannya dialog antarmurid agar saling menghargai pendapat\n• Guru mendorong murid saling menanggapi gagasan rekan secara santun\n• Guru berkeliling mendampingi dinamika diskusi setiap kelompok",
                        'perilaku_dihindari' => "• Guru duduk diam di meja guru selama murid berdiskusi kelompok\n• Guru membiarkan perdebatan menyimpang dari topik atau menjadi tidak santun\n• Guru membiarkan diskusi berjalan tanpa tujuan atau arahan hasil yang jelas",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-INSTRUKSI',
                'nama' => 'INSTRUKSI PEMBELAJARAN',
                'dimensi_pmm' => 'metode_pembelajaran',
                'deskripsi' => 'Upaya menyampaikan penjelasan konsep secara terstruktur, kontekstual, dan mudah dipahami sesuai kemampuan awal murid.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Penjelasan Sesuai Pemahaman Awal',
                        'fokus_perilaku' => 'Guru memberikan penjelasan yang mudah sesuai pemahaman awal peserta didik',
                        'perilaku_dianjurkan' => "• Guru menggunakan bahasa yang jelas, lugas, dan mudah dicerna murid\n• Guru mengaitkan konsep baru dengan pengetahuan yang sudah dimiliki murid\n• Guru melakukan pengecekan pemahaman konsep (concept check) secara berkala",
                        'perilaku_dihindari' => "• Guru menggunakan istilah teknis/jargon rumit tanpa penjelasan memadai\n• Guru berbicara terlalu cepat atau dengan intonasi monoton\n• Guru mengasumsikan seluruh murid paham tanpa melakukan asesmen awal",
                    ],
                    [
                        'nama_aspek' => 'Contoh Kontekstual Nyata',
                        'fokus_perilaku' => 'Guru memberikan contoh yang kontekstual dan relevan dengan keseharian peserta didik',
                        'perilaku_dianjurkan' => "• Guru mengambil contoh atau studi kasus dari lingkungan nyata sekitar murid\n• Guru menggunakan analogi visual yang dekat dengan pengalaman murid\n• Guru mengajak murid menceritakan pengalaman pribadi yang relevan dengan topik",
                        'perilaku_dihindari' => "• Guru memberikan contoh yang abstrak dan asing bagi dunia murid\n• Guru menggunakan ilustrasi usang yang membingungkan pemahaman\n• Guru mengabaikan kaitan materi dengan aplikasi di dunia nyata",
                    ],
                    [
                        'nama_aspek' => 'Struktur Penjelasan Logis',
                        'fokus_perilaku' => 'Guru menyampaikan penjelasan secara terstruktur dan logis',
                        'perilaku_dianjurkan' => "• Guru mengurutkan penyampaian materi secara sistematis dari konkret ke abstrak\n• Guru menyajikan kerangka alur belajar atau peta konsep di awal sesi\n• Guru merangkum poin-poin kunci di setiap akhir sub-penjelasan",
                        'perilaku_dihindari' => "• Guru melompat-lompat antar bahasan tanpa alur transisi yang runtut\n• Guru menyampaikan penjelasan tanpa tujuan atau benang merah yang jelas\n• Guru berpanjang lebar pada rincian yang tidak esensial",
                    ],
                ],
            ],
            [
                'kode' => 'PMM-ADAPTIF',
                'nama' => 'INSTRUKSI YANG ADAPTIF',
                'dimensi_pmm' => 'metode_pembelajaran',
                'deskripsi' => 'Upaya menyesuaikan praktik pembelajaran berdasarkan respon, kebutuhan, dan perkembangan belajar murid.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Responsif Dinamika Pembelajaran',
                        'fokus_perilaku' => 'Guru melakukan strategi pembelajaran yang responsif terhadap peserta didik',
                        'perilaku_dianjurkan' => "• Guru menyesuaikan tempo pembelajaran saat melihat respon bingung pada murid\n• Guru menyiapkan cara alternatif menerangkan materi bila cara awal belum efektif\n• Guru fleksibel dalam penataan waktu kegiatan pembelajaran demi pemahaman murid",
                        'perilaku_dihindari' => "• Guru kaku terpaku pada urutan RPP/modul tanpa memedulikan pemahaman murid\n• Guru terus melanjutkan topik baru saat mayoritas murid masih tertinggal\n• Guru menolak menyesuaikan metode di tengah situasi kelas",
                    ],
                    [
                        'nama_aspek' => 'Pembelajaran Berdiferensiasi',
                        'fokus_perilaku' => 'Guru melakukan penyesuaian pembelajaran mengacu kondisi, kebutuhan, dan karakteristik peserta didik',
                        'perilaku_dianjurkan' => "• Guru menyediakan materi dengan keragaman tingkat kesulitan (diferensiasi konten)\n• Guru memvariasikan bentuk kegiatan mandiri, berpasangan, atau kelompok (diferensiasi proses)\n• Guru mengizinkan murid menunjukkan pemahaman melalui beragam bentuk karya (diferensiasi produk)",
                        'perilaku_dihindari' => "• Guru menyamaratakan materi, proses, dan tugas bagi seluruh murid tanpa pilihan\n• Guru mengabaikan hasil asesmen diagnostik awal murid\n• Guru melabeli murid cerdas vs murid lambat secara terbuka",
                    ],
                    [
                        'nama_aspek' => 'Refleksi Praktik Bersama Murid',
                        'fokus_perilaku' => 'Guru merefleksikan praktik pembelajaran yang melibatkan peserta didik',
                        'perilaku_dianjurkan' => "• Guru mengajak murid menyampaikan hal yang paling dipahami dan yang masih menantang\n• Guru mencatat masukan murid sebagai bahan perbaikan pertemuan berikutnya\n• Guru mengajak murid menyimpulkan pembelajaran bersama-sama secara reflektif",
                        'perilaku_dihindari' => "• Guru langsung mengakhiri sesi pembelajaran tanpa waktu refleksi\n• Guru mengabaikan kesan atau masukan dari peserta didik\n• Guru tidak menindaklanjuti kendala belajar yang dirasakan murid",
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function perangkatAjarInstruments(): array
    {
        return [
            [
                'kode' => 'KURMER-ATP',
                'nama' => 'TELAAH ALUR TUJUAN PEMBELAJARAN (ATP)',
                'deskripsi' => 'Instrumen verifikasi dan telaah kesesuaian Alur Tujuan Pembelajaran (ATP) terhadap Capaian Pembelajaran Fase Kurikulum Merdeka.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Kesesuaian CP dan Penurunan ke TP',
                        'fokus_perilaku' => 'Capaian Pembelajaran (CP) diturunkan secara tepat ke dalam Tujuan Pembelajaran (TP) yang memuat Kompetensi dan Lingkup Materi',
                        'perilaku_dianjurkan' => 'TP memuat kompetensi operasional terukur dan lingkup materi yang esensial sesuai Fase.',
                        'perilaku_dihindari' => 'TP hanya mengulang CP tanpa perincian kompetensi dan materi.',
                    ],
                    [
                        'nama_aspek' => 'Kelogisan Alur dan Tahapan Belajar',
                        'fokus_perilaku' => 'Urutan Tujuan Pembelajaran dalam ATP disusun secara logis dan runtut (konkret ke abstrak, mudah ke kompleks)',
                        'perilaku_dianjurkan' => 'Pengurutan TP memperhatikan prasyarat kompetensi dan kesinambungan materi.',
                        'perilaku_dihindari' => 'Urutan TP melompat tanpa mempertimbangkan pemahaman awal.',
                    ],
                    [
                        'nama_aspek' => 'Alokasi Waktu dan Integrasi Karakter',
                        'fokus_perilaku' => 'ATP mencantumkan alokasi Jam Pelajaran (JP) yang realistis serta penguatan Profil Pelajar Pancasila',
                        'perilaku_dianjurkan' => 'Perkiraan JP proporsional dengan kedalaman materi dan dimensi profil lulusan terintegrasi.',
                        'perilaku_dihindari' => 'Alokasi waktu tidak realistis dan tidak ada integrasi nilai karakter.',
                    ],
                ],
            ],
            [
                'kode' => 'KURMER-MODUL',
                'nama' => 'TELAAH MODUL AJAR / RPP+ KURIKULUM MERDEKA',
                'deskripsi' => 'Instrumen telaah kelengkapan dan kualitas Modul Ajar / RPP+ berbasis pembelajaran mendalam dan berdiferensiasi.',
                'aspek' => [
                    [
                        'nama_aspek' => 'Informasi Umum & Identitas Modul',
                        'fokus_perilaku' => 'Memuat identitas modul, Fase/Kelas, Kompetensi Awal, Profil Pelajar Pancasila, Sarpras, dan Target Peserta Didik',
                        'perilaku_dianjurkan' => 'Komponen informasi umum lengkap dan relevan dengan karakteristik satuan pendidikan.',
                        'perilaku_dihindari' => 'Informasi umum tidak lengkap atau hanya copy-paste tanpa adaptasi.',
                    ],
                    [
                        'nama_aspek' => 'Tujuan Pembelajaran & KKTP',
                        'fokus_perilaku' => 'Tujuan Pembelajaran jelas serta dilengkapi Kriteria Ketercapaian Tujuan Pembelajaran (KKTP) yang terukur',
                        'perilaku_dianjurkan' => 'KKTP menggunakan rubrik atau interval nilai yang mendeskripsikan capaian kompetensi.',
                        'perilaku_dihindari' => 'KKTP hanya berupa angka KKM tunggal tanpa deskripsi ketercapaian.',
                    ],
                    [
                        'nama_aspek' => 'Pemahaman Bermakna & Pertanyaan Pemantik',
                        'fokus_perilaku' => 'Memuat pemahaman bermakna dan pertanyaan pemantik yang merangsang rasa ingin tahu serta relevan dengan kehidupan nyata',
                        'perilaku_dianjurkan' => 'Pertanyaan pemantik bersifat terbuka dan memicu eksplorasi konsep.',
                        'perilaku_dihindari' => 'Pertanyaan pemantik bersifat tertutup atau tes hafalan langsung.',
                    ],
                    [
                        'nama_aspek' => 'Kegiatan Pembelajaran Berdiferensiasi',
                        'fokus_perilaku' => 'Langkah kegiatan memfasilitasi pembelajaran berdiferensiasi (konten, proses, atau produk) dan berpusat pada murid',
                        'perilaku_dianjurkan' => 'Terdapat diferensiasi kegiatan/tugas untuk mengakomodasi keberagaman murid.',
                        'perilaku_dihindari' => 'Kegiatan belajar seragam dan didominasi ceramah satu arah.',
                    ],
                    [
                        'nama_aspek' => 'Rancangan Asesmen Awal, Formatif, & Sumatif',
                        'fokus_perilaku' => 'Memuat rencana asesmen awal diagnostik, asesmen formatif selama proses, dan asesmen sumatif dengan instrumen yang jelas',
                        'perilaku_dianjurkan' => 'Asesmen formatif digunakan untuk umpan balik pembelajaran berkelanjutan.',
                        'perilaku_dihindari' => 'Hanya ada asesmen sumatif di akhir tanpa asesmen formatif berkala.',
                    ],
                    [
                        'nama_aspek' => 'Lampiran LKPD dan Bahan Ajar',
                        'fokus_perilaku' => 'Menyertakan Lembar Kerja Peserta Didik (LKPD), bahan bacaan guru/murid, glosarium, dan daftar pustaka',
                        'perilaku_dianjurkan' => 'LKPD interaktif, memandu penyelidikan, dan ramah anak.',
                        'perilaku_dihindari' => 'Lampiran kosong atau hanya berupa bank soal pilihan ganda.',
                    ],
                ],
            ],
        ];
    }
}
