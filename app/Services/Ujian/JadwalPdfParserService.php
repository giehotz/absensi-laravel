<?php

namespace App\Services\Ujian;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JadwalPdfParserService
{
    /**
     * Parsing file PDF matriks jadwal ujian & penugasan pengawas.
     *
     * @return array{imported_jadwal: int, imported_pengawas: int, skipped: array<int, string>}
     */
    public function parseAndImport(UploadedFile $pdfFile, int $periodeId): array
    {
        $periode = UjianPeriode::findOrFail($periodeId);
        $lines = $this->extractLayoutText($pdfFile->getRealPath());

        if (empty($lines)) {
            throw new \RuntimeException('Gagal mengekstrak teks dari file PDF. Pastikan file PDF valid.');
        }

        // 1. Ekstrak Legenda Pengawas di Kolom Kanan
        $legend = $this->parseLegend($lines);
        $teacherMap = $this->resolveTeachers($legend);

        // 2. Ekstrak Baris Sesi & Penugasan Ruang di Kolom Kiri
        $sessions = $this->parseSessions($lines, $periode);

        // 3. Muat Data Master Ruang & Mapel
        $ruangList = UjianRuang::all();
        $subjects = Subject::all();

        $importedJadwal = 0;
        $importedPengawas = 0;
        $skipped = [];

        DB::transaction(function () use (
            $sessions,
            $periode,
            $teacherMap,
            $ruangList,
            $subjects,
            &$importedJadwal,
            &$importedPengawas,
            &$skipped
        ) {
            foreach ($sessions as $sIndex => $s) {
                $tanggal = $s['tanggal'];
                $jamMulai = $s['jam_mulai'];
                $jamSelesai = $s['jam_selesai'];
                $sesiKe = $s['sesi_ke'];
                $mapel12 = $s['mapel_12'];
                $mapel36 = $s['mapel_36'];
                $assignments = $s['assignments']; // map: '1A' => 5, '1B' => 11, ...

                // Tentukan jadwal yang dibutuhkan:
                // Jika mapel 1-2 dan 3-6 sama, kita bisa buat satu jadwal untuk semua tingkat atau dua terpisah
                $createdSchedules = [];

                if (! empty($mapel12)) {
                    $jadwal12 = $this->findOrCreateJadwal(
                        $periode->id,
                        $mapel12,
                        '1, 2',
                        $tanggal,
                        $jamMulai,
                        $jamSelesai,
                        $sesiKe,
                        $subjects
                    );
                    $createdSchedules['1, 2'] = $jadwal12;
                    $importedJadwal++;
                }

                if (! empty($mapel36)) {
                    $jadwal36 = $this->findOrCreateJadwal(
                        $periode->id,
                        $mapel36,
                        '3, 4, 5, 6',
                        $tanggal,
                        $jamMulai,
                        $jamSelesai,
                        $sesiKe,
                        $subjects
                    );
                    $createdSchedules['3, 4, 5, 6'] = $jadwal36;
                    $importedJadwal++;
                }

                // Simpan penugasan pengawas untuk setiap ruang
                foreach ($assignments as $rCode => $legendNo) {
                    $ruang = $this->findRuangByCode($ruangList, $rCode);
                    if (! $ruang) {
                        $skipped[] = "Ruang '{$rCode}' pada tanggal {$tanggal} tidak ditemukan di master ruangan.";

                        continue;
                    }

                    // Tentukan jadwal yang cocok dengan tingkat ruang ini
                    $ruangLevel = (int) $ruang->getLevel();
                    $targetJadwal = null;
                    if ($ruangLevel <= 2 && isset($createdSchedules['1, 2'])) {
                        $targetJadwal = $createdSchedules['1, 2'];
                    } elseif ($ruangLevel >= 3 && isset($createdSchedules['3, 4, 5, 6'])) {
                        $targetJadwal = $createdSchedules['3, 4, 5, 6'];
                    }

                    if (! $targetJadwal) {
                        continue;
                    }

                    // Ambil Teacher ID dari legenda
                    $teacherData = $teacherMap[$legendNo] ?? null;
                    $teacherId = $teacherData['teacher_id'] ?? null;

                    if ($teacherId) {
                        UjianJadwalPengawas::updateOrCreate(
                            [
                                'ujian_jadwal_id' => $targetJadwal->id,
                                'ujian_ruang_id' => $ruang->id,
                            ],
                            [
                                'pengawas_1_id' => $teacherId,
                                'status_kehadiran' => 'Terjadwal',
                            ]
                        );
                        $importedPengawas++;
                    }
                }
            }
        });

        return [
            'imported_jadwal' => $importedJadwal,
            'imported_pengawas' => $importedPengawas,
            'skipped' => $skipped,
        ];
    }

    /**
     * Ekstrak teks layout dari PDF menggunakan pdftotext atau fallback.
     *
     * @return array<int, string>
     */
    protected function extractLayoutText(string $filePath): array
    {
        $pdftotext = 'C:\\laragon\\bin\\git\\mingw64\\bin\\pdftotext.exe';
        if (! file_exists($pdftotext)) {
            $pdftotext = 'pdftotext';
        }

        $cmd = escapeshellcmd($pdftotext).' -layout '.escapeshellarg($filePath).' -';
        $output = [];
        $returnCode = 0;
        @exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && ! empty($output)) {
            return $output;
        }

        // Fallback: baca konten teks langsung jika memungkinkan
        $content = @file_get_contents($filePath);
        if ($content && preg_match_all('/BT[\s\S]*?ET/s', $content, $matches)) {
            $lines = [];
            foreach ($matches[0] as $block) {
                if (preg_match_all('/\((.*?)\)\s*Tj/s', $block, $tjs)) {
                    $lines[] = implode(' ', $tjs[1]);
                }
            }
            if (! empty($lines)) {
                return $lines;
            }
        }

        return $output;
    }

    /**
     * Parsing tabel legenda nama pengawas di sisi kanan PDF.
     *
     * @param  array<int, string>  $lines
     * @return array<int, array{no: int, nama: string, jumlah: int}>
     */
    protected function parseLegend(array $lines): array
    {
        $legend = [];
        foreach ($lines as $line) {
            if (strlen($line) > 95) {
                $rightPart = substr($line, 95);
                if (preg_match('/(\d{1,2})\s+([A-Za-z\s,\.`\']+?)(?:\s+(\d{1,2}))?\s*$/u', $rightPart, $matches)) {
                    $no = (int) $matches[1];
                    $nama = trim($matches[2]);
                    $jumlah = isset($matches[3]) ? (int) $matches[3] : 0;
                    if ($no >= 1 && $no <= 40 && strlen($nama) >= 3 && ! preg_match('/^(kelas|mapel|pukul|jumlah)/i', $nama)) {
                        $legend[$no] = [
                            'no' => $no,
                            'nama' => $nama,
                            'jumlah' => $jumlah,
                        ];
                    }
                }
            }
        }
        ksort($legend);

        return $legend;
    }

    /**
     * Resolusi nama pengawas legenda ke User dan Teacher di database.
     *
     * @param  array<int, array{no: int, nama: string, jumlah: int}>  $legend
     * @return array<int, array{user_id: int, teacher_id: int, name: string}>
     */
    protected function resolveTeachers(array $legend): array
    {
        $allUsers = User::with('teacher')->whereIn('role', ['guru', 'pengawas', 'admin'])->get();
        $resolved = [];

        foreach ($legend as $no => $item) {
            $rawName = $item['nama'];
            $cleanName = $this->cleanHonorifics($rawName);

            $matchedUser = null;
            $bestScore = 0;

            foreach ($allUsers as $u) {
                $cleanU = $this->cleanHonorifics($u->name);
                if ($cleanName === $cleanU) {
                    $matchedUser = $u;
                    break;
                }
                if (str_contains($cleanU, $cleanName) || str_contains($cleanName, $cleanU)) {
                    $matchedUser = $u;
                    break;
                }
                similar_text($cleanName, $cleanU, $percent);
                if ($percent > 70 && $percent > $bestScore) {
                    $bestScore = $percent;
                    $matchedUser = $u;
                }
            }

            // Jika belum ada di DB, buatkan akun Guru & Teacher secara otomatis
            if (! $matchedUser) {
                $matchedUser = User::create([
                    'name' => $rawName,
                    'email' => Str::slug($cleanName).'.'.$no.'@sekolah.sch.id',
                    'role' => 'guru',
                    'password' => Hash::make('password123'),
                ]);
            }

            $teacher = $matchedUser->teacher ?? Teacher::firstOrCreate(
                ['user_id' => $matchedUser->id],
                ['nip' => null]
            );

            $resolved[$no] = [
                'user_id' => $matchedUser->id,
                'teacher_id' => $teacher->id,
                'name' => $matchedUser->name,
            ];
        }

        return $resolved;
    }

    /**
     * Membersihkan gelar akademis untuk mempermudah pencocokan nama.
     */
    protected function cleanHonorifics(string $name): string
    {
        $n = strtolower($name);
        $n = preg_replace('/\b(s\.pd\.i|s\.pd\.sd|s\.pd|s\.s\.i|m\.pd|s\.ag|h\.|dra|drs)\b/i', '', $n);
        $n = preg_replace('/[,\.`\']/', '', $n);
        $n = preg_replace('/\s+/', ' ', $n);

        return trim($n);
    }

    /**
     * Parsing baris-baris sesi ujian dan angka ruangan dari kolom kiri PDF.
     *
     * @param  array<int, string>  $lines
     * @return array<int, array{tanggal: string, sesi_ke: int, jam_mulai: string, jam_selesai: string, mapel_12: ?string, mapel_36: ?string, assignments: array<string, int>}>
     */
    protected function parseSessions(array $lines, UjianPeriode $periode): array
    {
        $months = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $roomCodes = [
            '1A', '1B', '1C',
            '2A', '2B', '2C',
            '3A', '3B',
            '4A', '4B', '4C',
            '5A', '5B', '5C',
            '6A', '6B',
        ];

        // 1. Ekstrak seluruh tanggal yang ada beserta barisnya
        $dateAtLines = [];
        for ($i = 0; $i < count($lines); $i++) {
            $sub = substr($lines[$i], 0, 40);
            if (preg_match('/(\d{1,2})\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+(\d{4})/i', $sub, $m)) {
                $d = (int) $m[1];
                $mon = $months[strtolower($m[2])] ?? 1;
                $y = (int) $m[3];
                // Sesuaikan tahun dengan tahun periode jika periode aktif berbeda
                $periodeYear = $periode->tanggal_mulai ? (int) $periode->tanggal_mulai->format('Y') : $y;
                $dateAtLines[$i] = sprintf('%04d-%02d-%02d', $periodeYear, $mon, $d);
            }
        }

        $sessions = [];
        $currentDate = ! empty($dateAtLines) ? reset($dateAtLines) : now()->format('Y-m-d');

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Update tanggal jika ada di baris ini atau baris tepat di sekitarnya
            if (isset($dateAtLines[$i])) {
                $currentDate = $dateAtLines[$i];
            } elseif (isset($dateAtLines[$i + 1])) {
                $currentDate = $dateAtLines[$i + 1];
            }

            // Cari pola waktu "07.30 - 09.30" atau "10.00 - 12.00"
            if (preg_match('/(\d{2}\.\d{2})\s*-\s*(\d{2}\.\d{2})/', substr($line, 0, 85), $timeMatch)) {
                $jamMulai = str_replace('.', ':', $timeMatch[1]);
                $jamSelesai = str_replace('.', ':', $timeMatch[2]);
                $sesiKe = ($jamMulai === '07:30') ? 1 : 2;

                $timePos = strpos($line, $timeMatch[0]);

                // Ekstrak nama mata pelajaran di sebelah kiri waktu
                $leftSection = substr($line, 0, $timePos);
                $mapels = $this->extractSubjectsFromLine($leftSection, $lines, $i);

                // Ekstrak angka kode pengawas pada kolom ruangan
                $numbersSlice = substr($line, $timePos + strlen($timeMatch[0]), 55);
                preg_match_all('/\b\d{1,2}\b/', $numbersSlice, $numMatches);
                $assignedNums = $numMatches[0] ?? [];

                $assignments = [];
                if (count($assignedNums) === 16) {
                    foreach ($roomCodes as $idx => $rCode) {
                        $assignments[$rCode] = (int) $assignedNums[$idx];
                    }
                } elseif (count($assignedNums) === 10) {
                    // Hanya kelas 3A s/d 6B (index 6 s/d 15)
                    $upperRooms = array_slice($roomCodes, 6);
                    foreach ($upperRooms as $idx => $rCode) {
                        $assignments[$rCode] = (int) $assignedNums[$idx];
                    }
                }

                $sessions[] = [
                    'tanggal' => $currentDate,
                    'sesi_ke' => $sesiKe,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'mapel_12' => $mapels['mapel_12'],
                    'mapel_36' => $mapels['mapel_36'],
                    'assignments' => $assignments,
                ];
            }
        }

        return $sessions;
    }

    /**
     * Memisahkan mata pelajaran kelas 1-2 dan kelas 3-6 dari teks baris.
     *
     * @param  array<int, string>  $allLines
     * @return array{mapel_12: ?string, mapel_36: ?string}
     */
    protected function extractSubjectsFromLine(string $leftText, array $allLines, int $lineIndex): array
    {
        // Bersihkan hari dan nomor di depan
        $cleaned = preg_replace('/^(senin|selasa|rabu|kamis|jum\'?at|sabtu|minggu),?\s*/i', '', trim($leftText));
        $cleaned = preg_replace('/^\d+\s+/', '', $cleaned); // buang no urut
        $cleaned = preg_replace('/^\d+\s+/', '', $cleaned); // buang sesi_ke

        $tokens = preg_split('/\s{2,}/', trim($cleaned));
        $tokens = array_values(array_filter($tokens, fn ($t) => strlen(trim($t)) > 1));

        $mapel12 = null;
        $mapel36 = null;

        if (count($tokens) >= 2) {
            $mapel12 = trim($tokens[0]);
            $mapel36 = trim($tokens[1]);
        } elseif (count($tokens) === 1) {
            // Bisa jadi hanya kelas 3-6 (misal Selasa sesi 1: Aqidah Akhlak / Ipas)
            $mapel36 = trim($tokens[0]);
        }

        // Cek baris berikutnya jika Sabtu sesi 2 yang bergeser ke baris bawah
        if (empty($mapel12) && empty($mapel36) && isset($allLines[$lineIndex - 1])) {
            $prevLine = $allLines[$lineIndex - 1];
            if (str_contains($prevLine, 'Bahasa Inggris') && str_contains($prevLine, 'Bahasa Lampung')) {
                $mapel12 = 'Bahasa Inggris';
                $mapel36 = 'Bahasa Lampung';
            }
        }

        return [
            'mapel_12' => $mapel12 ?: null,
            'mapel_36' => $mapel36 ?: ($mapel12 ?: 'Ujian Terjadwal'),
        ];
    }

    /**
     * Cari atau buat jadwal ujian di database.
     *
     * @param  Collection<int, Subject>  $subjects
     */
    protected function findOrCreateJadwal(
        int $periodeId,
        string $mapelName,
        string $tingkat,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        int $sesiKe,
        $subjects
    ): UjianJadwal {
        $matched = $subjects->first(function ($s) use ($mapelName) {
            return strcasecmp($s->name, $mapelName) === 0 || strcasecmp($s->code ?? '', $mapelName) === 0;
        });

        $subjectId = $matched?->id;
        $namaMapelKustom = $matched ? null : $mapelName;

        return UjianJadwal::firstOrCreate(
            [
                'ujian_periode_id' => $periodeId,
                'tanggal_ujian' => $tanggal,
                'jam_mulai' => $jamMulai,
                'tingkat' => $tingkat,
            ],
            [
                'subject_id' => $subjectId,
                'nama_mapel_kustom' => $namaMapelKustom,
                'jam_selesai' => $jamSelesai,
                'sesi_ke' => $sesiKe,
                'durasi_menit' => 120,
                'jenis_soal' => 'Tertulis',
                'catatan' => 'Import dari PDF Matriks',
            ]
        );
    }

    /**
     * Cari objek ruang ujian berdasarkan kode ruang (1A, 2B, dll).
     *
     * @param  Collection<int, UjianRuang>  $ruangList
     */
    protected function findRuangByCode($ruangList, string $code): ?UjianRuang
    {
        $level = substr($code, 0, 1);
        $char = strtolower(substr($code, 1, 1));

        return $ruangList->first(function ($r) use ($level, $char) {
            $name = strtolower($r->nama_ruang);
            $cCode = strtolower($r->kode_ruang);

            return (str_contains($name, $level) && str_contains($name, $char)) ||
                   (str_contains($cCode, $level) && str_contains($cCode, $char));
        });
    }
}
