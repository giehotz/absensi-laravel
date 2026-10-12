<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SisEmisSyncService
{
    /**
     * Tarik siswa dari SIS-EMIS API dan sinkronkan ke database Absensi.
     *
     * @return array{success: bool, imported: int, updated: int, total_remote: int, message: string}
     */
    public function sync(?string $rombel = null, ?int $level = null, ?int $targetClassId = null, ?string $status = null, ?callable $onProgress = null): array
    {
        $apiUrl = config('services.sis_emis.url', 'http://127.0.0.1:8000/api/v1');
        $token = config('services.sis_emis.token');

        if (blank($token)) {
            $msg = 'Token SIS-EMIS belum dikonfigurasi di file .env (SIS_EMIS_API_TOKEN).';
            if ($onProgress) {
                $onProgress([
                    'event' => 'error',
                    'message' => $msg,
                ]);
            }

            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total_remote' => 0,
                'message' => $msg,
            ];
        }

        if ($onProgress) {
            $filterText = filled($rombel) ? " (filter rombel: {$rombel})" : ' (seluruh rombel)';
            $onProgress([
                'event' => 'log',
                'message' => "Menghubungi endpoint API SIS-EMIS di {$apiUrl}/students/export-json{$filterText}...",
                'type' => 'info',
            ]);
        }

        $params = [
            'limit' => 2000,
        ];

        if (filled($status)) {
            $params['filter[status]'] = $status;
        }

        if (filled($rombel)) {
            $params['filter[rombel]'] = $rombel;
        }

        if (filled($level)) {
            $params['filter[level]'] = $level;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(60)
                ->get("{$apiUrl}/students/export-json", $params);
        } catch (\Throwable $e) {
            Log::error('Koneksi SIS-EMIS gagal: '.$e->getMessage());

            $msg = 'Tidak dapat terhubung ke server SIS-EMIS: '.$e->getMessage();
            if ($onProgress) {
                $onProgress([
                    'event' => 'error',
                    'message' => $msg,
                ]);
            }

            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total_remote' => 0,
                'message' => $msg,
            ];
        }

        if (! $response->successful()) {
            $msg = 'Gagal menghubungi SIS-EMIS (HTTP '.$response->status().'): '.($response->json('message') ?? $response->body());
            if ($onProgress) {
                $onProgress([
                    'event' => 'error',
                    'message' => $msg,
                ]);
            }

            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total_remote' => 0,
                'message' => $msg,
            ];
        }

        $students = $response->json('students', []);
        $institutionName = $response->json('institution.name', 'Madrasah');
        $importedCount = 0;
        $updatedCount = 0;
        $totalRemote = count($students);

        if ($onProgress) {
            $onProgress([
                'event' => 'init',
                'total' => $totalRemote,
                'institution' => $institutionName,
                'message' => "Terhubung dengan {$institutionName}. Ditemukan {$totalRemote} siswa.",
            ]);
        }

        if ($totalRemote === 0) {
            $filterInfo = filled($rombel) ? " untuk filter rombel '{$rombel}'" : '';
            $emptyMsg = "Tidak ada data siswa ditemukan dari SIS-EMIS ({$institutionName}){$filterInfo}. Pastikan nama rombel sesuai atau pilih Tarik Seluruh Siswa.";

            if ($onProgress) {
                $onProgress([
                    'event' => 'complete',
                    'imported' => 0,
                    'updated' => 0,
                    'total' => 0,
                    'message' => $emptyMsg,
                ]);
            }

            return [
                'success' => true,
                'imported' => 0,
                'updated' => 0,
                'total_remote' => 0,
                'message' => $emptyMsg,
            ];
        }

        set_time_limit(300);
        $activeYearId = AcademicYear::activeSemester()?->id ?? AcademicYear::latest('id')->value('id');

        try {
            DB::transaction(function () use ($students, $targetClassId, $activeYearId, $onProgress, &$importedCount, &$updatedCount) {
                $classesCache = SchoolClass::all();
                $passwordCache = [];
                $total = count($students);
                $processed = 0;

                foreach ($students as $row) {
                    $processed++;
                    $nisn = trim((string) ($row['nisn'] ?? ''));
                    $nis = trim((string) ($row['local_nis'] ?? '')) ?: $nisn;
                    $name = trim((string) ($row['full_name'] ?? ''));
                    $rombelName = trim((string) ($row['rombel_name'] ?? ''));

                    if (blank($name) || (blank($nis) && blank($nisn))) {
                        if ($onProgress) {
                            $onProgress([
                                'event' => 'progress',
                                'current' => $processed,
                                'total' => $total,
                                'percent' => (int) round(($processed / $total) * 100),
                                'student_name' => $name ?: 'Tanpa Nama',
                                'rombel' => $rombelName ?: '-',
                                'action' => 'skipped',
                                'imported_count' => $importedCount,
                                'updated_count' => $updatedCount,
                                'message' => "Dilewati: NIS/Nama kosong ({$name})",
                            ]);
                        }

                        continue;
                    }

                    // Tentukan kelas tujuan (toleran terhadap spasi / huruf kapital seperti 'Kelas 3A' vs 'Kelas 3 a')
                    $classId = $targetClassId;
                    if (! $classId && filled($rombelName)) {
                        $normRombel = str_replace(' ', '', strtolower($rombelName));
                        $matchedClass = $classesCache->first(function ($cls) use ($rombelName, $normRombel) {
                            return $cls->name === $rombelName || str_replace(' ', '', strtolower($cls->name)) === $normRombel;
                        });

                        if (! $matchedClass) {
                            $matchedClass = SchoolClass::create([
                                'academic_year_id' => $activeYearId,
                                'name' => $rombelName,
                                'level' => (int) ($row['level'] ?? 1),
                            ]);
                            $classesCache->push($matchedClass);
                        }
                        $classId = $matchedClass->id;
                    }

                    $genderRaw = strtoupper(trim((string) ($row['gender'] ?? 'L')));
                    $gender = in_array($genderRaw, ['L', 'P']) ? $genderRaw : 'L';
                    $birthDate = ! empty($row['birth_date']) ? date('Y-m-d', strtotime($row['birth_date'])) : null;

                    // Cari apakah siswa sudah ada berdasarkan NISN atau NIS
                    $existingStudent = Student::query()
                        ->when($nisn, fn ($q) => $q->where('nisn', $nisn))
                        ->when(! $nisn && $nis, fn ($q) => $q->where('nis', $nis))
                        ->first();

                    if (! $existingStudent && $nis) {
                        $existingStudent = Student::where('nis', $nis)->first();
                    }

                    if ($existingStudent) {
                        // Update siswa yang sudah ada
                        $existingStudent->user->update([
                            'name' => $name,
                        ]);

                        $existingStudent->update([
                            'school_class_id' => $classId ?? $existingStudent->school_class_id,
                            'gender' => $gender,
                            'birth_place' => ! empty($row['birth_place']) ? $row['birth_place'] : $existingStudent->birth_place,
                            'birth_date' => $birthDate ?? $existingStudent->birth_date,
                            'phone' => ! empty($row['phone_number']) ? $row['phone_number'] : $existingStudent->phone,
                            'address' => ! empty($row['full_address']) ? $row['full_address'] : $existingStudent->address,
                            'father_name' => ! empty($row['father_name']) ? $row['father_name'] : $existingStudent->father_name,
                            'mother_name' => ! empty($row['mother_name']) ? $row['mother_name'] : $existingStudent->mother_name,
                            'guardian_name' => ! empty($row['guardian_name']) ? $row['guardian_name'] : $existingStudent->guardian_name,
                        ]);

                        $updatedCount++;
                        $action = 'updated';
                    } else {
                        // Buat akun User dan Student baru
                        $email = Student::generateDefaultEmail($nis);
                        $plainPassword = Student::getDefaultPassword($nisn, $nis);

                        $qrCodeIdentifier = 'QR-'.$nis.'-'.strtoupper(bin2hex(random_bytes(3)));
                        $hashedPassword = $passwordCache[$plainPassword] ??= Hash::make($plainPassword);

                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => $hashedPassword,
                            'role' => 'siswa',
                            'is_active' => true,
                        ]);

                        Student::create([
                            'user_id' => $user->id,
                            'school_class_id' => $classId,
                            'nis' => $nis,
                            'nisn' => ! empty($nisn) ? $nisn : null,
                            'qr_code_identifier' => $qrCodeIdentifier,
                            'gender' => $gender,
                            'birth_place' => ! empty($row['birth_place']) ? $row['birth_place'] : null,
                            'birth_date' => $birthDate,
                            'phone' => ! empty($row['phone_number']) ? $row['phone_number'] : null,
                            'address' => ! empty($row['full_address']) ? $row['full_address'] : null,
                            'father_name' => ! empty($row['father_name']) ? $row['father_name'] : null,
                            'mother_name' => ! empty($row['mother_name']) ? $row['mother_name'] : null,
                            'guardian_name' => ! empty($row['guardian_name']) ? $row['guardian_name'] : null,
                        ]);

                        $importedCount++;
                        $action = 'imported';
                    }

                    if ($onProgress) {
                        $onProgress([
                            'event' => 'progress',
                            'current' => $processed,
                            'total' => $total,
                            'percent' => (int) round(($processed / $total) * 100),
                            'student_name' => $name,
                            'rombel' => $rombelName ?: '-',
                            'action' => $action,
                            'imported_count' => $importedCount,
                            'updated_count' => $updatedCount,
                            'message' => ($action === 'imported' ? '[+] Ditambahkan: ' : '[~] Diperbarui: ').$name.($rombelName ? " ({$rombelName})" : ''),
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Sinkronisasi SIS-EMIS error saat transaksi: '.$e->getMessage());

            $errorMsg = 'Gagal memproses data siswa ke database: '.$e->getMessage();
            if ($onProgress) {
                $onProgress([
                    'event' => 'error',
                    'message' => $errorMsg,
                ]);
            }

            return [
                'success' => false,
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'total_remote' => $totalRemote,
                'message' => $errorMsg,
            ];
        }

        $completeMsg = "Sinkronisasi dari SIS-EMIS ({$institutionName}) berhasil: {$importedCount} siswa baru ditambahkan, {$updatedCount} siswa diperbarui.";

        if ($onProgress) {
            $onProgress([
                'event' => 'complete',
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'total' => $totalRemote,
                'message' => $completeMsg,
            ]);
        }

        return [
            'success' => true,
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'total_remote' => $totalRemote,
            'message' => $completeMsg,
        ];
    }

    /**
     * Ambil daftar rombel resmi dari SIS-EMIS API.
     *
     * @return array{success: bool, rombels: array, message: string}
     */
    public function getAvailableRombels(): array
    {
        $apiUrl = config('services.sis_emis.url', 'http://127.0.0.1:8000/api/v1');
        $token = config('services.sis_emis.token');

        if (blank($token)) {
            return [
                'success' => false,
                'rombels' => [],
                'message' => 'Token SIS-EMIS belum dikonfigurasi di file .env (SIS_EMIS_API_TOKEN).',
            ];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->get("{$apiUrl}/students/rombels");

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'rombels' => [],
                    'message' => 'Gagal mengambil rombel dari SIS-EMIS (HTTP '.$response->status().').',
                ];
            }

            return [
                'success' => true,
                'rombels' => $response->json('rombels', []),
                'message' => 'Berhasil mengambil daftar rombel dari SIS-EMIS.',
            ];
        } catch (\Throwable $e) {
            Log::error('Koneksi SIS-EMIS rombels gagal: '.$e->getMessage());

            return [
                'success' => false,
                'rombels' => [],
                'message' => 'Tidak dapat terhubung ke server SIS-EMIS: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Tarik dan sinkronkan rombel resmi dari SIS-EMIS ke tabel school_classes di database Absensi.
     *
     * @return array{success: bool, imported: int, updated: int, total_remote: int, message: string}
     */
    public function syncClasses(?int $targetAcademicYearId = null): array
    {
        $result = $this->getAvailableRombels();
        if (! $result['success']) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'total_remote' => 0,
                'message' => $result['message'],
            ];
        }

        $rombels = $result['rombels'];
        if (empty($rombels)) {
            return [
                'success' => true,
                'imported' => 0,
                'updated' => 0,
                'total_remote' => 0,
                'message' => 'Tidak ada rombel yang ditemukan di SIS-EMIS.',
            ];
        }

        $yearId = $targetAcademicYearId
            ?: (AcademicYear::activeSemester()?->id ?? AcademicYear::latest('id')->value('id'));

        $imported = 0;
        $updated = 0;

        DB::transaction(function () use ($rombels, $yearId, &$imported, &$updated) {
            $existingClasses = SchoolClass::where('academic_year_id', $yearId)->get();

            foreach ($rombels as $rombel) {
                $rawName = trim((string) ($rombel['rombel_name'] ?? ''));
                $level = (int) ($rombel['level'] ?? 1);

                if (blank($rawName)) {
                    continue;
                }

                // Normalisasi nama kelas: pastikan diawali huruf besar (contoh: 'Kelas 1 c', 'Kelas 6 c')
                $name = preg_match('/^kelas/i', $rawName) ? ucfirst($rawName) : 'Kelas '.$rawName;
                $normName = str_replace(' ', '', strtolower($name));

                // Cari apakah kelas sudah ada di tahun ajaran ini
                $existing = $existingClasses->first(function ($cls) use ($name, $normName) {
                    return $cls->name === $name || str_replace(' ', '', strtolower($cls->name)) === $normName;
                });

                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'level' => $level,
                    ]);
                    $updated++;
                } else {
                    $newClass = SchoolClass::create([
                        'academic_year_id' => $yearId,
                        'name' => $name,
                        'level' => $level,
                    ]);
                    $existingClasses->push($newClass);
                    $imported++;
                }
            }
        });

        $academicYear = AcademicYear::find($yearId);
        $yearLabel = $academicYear ? "Tahun Ajaran {$academicYear->name}" : '';

        return [
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'total_remote' => count($rombels),
            'message' => "Sinkronisasi Rombel SIS-EMIS berhasil ({$yearLabel}): {$imported} kelas baru ditambahkan, {$updated} kelas diperbarui dari total ".count($rombels).' rombel resmi.',
        ];
    }
}
