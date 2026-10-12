<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\SisEmisSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiClientController extends Controller
{
    /**
     * Tampilkan Pusat Konfigurasi & Integrasi SIS-EMIS
     */
    public function index(): View
    {
        $apiUrl = (string) config('services.sis_emis.url', env('SIS_EMIS_API_URL', 'http://127.0.0.1:8000/api/v1'));
        $apiToken = (string) config('services.sis_emis.token', env('SIS_EMIS_API_TOKEN', ''));

        $connectionStatus = [
            'connected' => false,
            'configured' => filled($apiToken),
            'status_code' => null,
            'message' => '',
            'institution' => null,
            'emis_students_count' => 0,
            'emis_rombels_count' => 0,
            'checked_at' => now()->format('d M Y, H:i:s'),
        ];

        if (filled($apiToken)) {
            try {
                $studentRes = Http::withToken($apiToken)
                    ->acceptJson()
                    ->timeout(6)
                    ->get("{$apiUrl}/students", ['per_page' => 1]);

                if ($studentRes->successful()) {
                    $connectionStatus['connected'] = true;
                    $connectionStatus['status_code'] = 200;
                    $connectionStatus['emis_students_count'] = (int) $studentRes->json('meta.total', 0);
                    $connectionStatus['message'] = 'Terhubung aktif dengan server API SIS-EMIS.';
                } else {
                    $connectionStatus['status_code'] = $studentRes->status();
                    $connectionStatus['message'] = 'Koneksi ditolak (HTTP '.$studentRes->status().'): '.($studentRes->json('message') ?? 'Token tidak valid.');
                }

                $rombelsRes = Http::withToken($apiToken)
                    ->acceptJson()
                    ->timeout(6)
                    ->get("{$apiUrl}/students/rombels");

                if ($rombelsRes->successful()) {
                    $connectionStatus['emis_rombels_count'] = (int) $rombelsRes->json('total', 0);
                    if ($connectionStatus['emis_students_count'] === 0) {
                        $connectionStatus['emis_students_count'] = (int) collect($rombelsRes->json('rombels', []))->sum('student_count');
                    }
                }

                $exportHeaderRes = Http::withToken($apiToken)
                    ->acceptJson()
                    ->timeout(6)
                    ->get("{$apiUrl}/students/export-json", ['limit' => 1]);

                if ($exportHeaderRes->successful()) {
                    $connectionStatus['institution'] = $exportHeaderRes->json('institution');
                }
            } catch (\Throwable $e) {
                $connectionStatus['message'] = 'Gagal terhubung: '.$e->getMessage().'. Pastikan server SIS-EMIS aktif di '.$apiUrl;
            }
        } else {
            $connectionStatus['message'] = 'Token API belum diatur. Silakan masukkan token Sanctum dari SIS-EMIS di bawah ini.';
        }

        $localStats = [
            'local_students_count' => Student::count(),
            'local_classes_count' => SchoolClass::count(),
        ];

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $academicYears = AcademicYear::all();

        return view('admin.api-clients.index', compact(
            'apiUrl',
            'apiToken',
            'connectionStatus',
            'localStats',
            'classes',
            'academicYears'
        ));
    }

    /**
     * Simpan konfigurasi URL dan Token SIS-EMIS ke file .env
     */
    public function saveConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sis_emis_api_url' => ['required', 'url'],
            'sis_emis_api_token' => ['required', 'string'],
        ], [
            'sis_emis_api_url.required' => 'URL Server SIS-EMIS wajib diisi.',
            'sis_emis_api_url.url' => 'Format URL Server SIS-EMIS tidak valid.',
            'sis_emis_api_token.required' => 'Token Bearer SIS-EMIS wajib diisi.',
        ]);

        $url = rtrim(trim($validated['sis_emis_api_url']), '/');
        $token = trim($validated['sis_emis_api_token']);

        $this->updateEnv([
            'SIS_EMIS_API_URL' => $url,
            'SIS_EMIS_API_TOKEN' => $token,
        ]);

        return redirect()->route('admin.api-clients.index')
            ->with('success', 'Konfigurasi integrasi SIS-EMIS berhasil disimpan ke .env.');
    }

    /**
     * Tes handshake koneksi ke server SIS-EMIS
     */
    public function testConnection(SisEmisSyncService $syncService): RedirectResponse
    {
        $result = $syncService->getAvailableRombels();

        if ($result['success']) {
            $count = count($result['rombels']);

            return redirect()->route('admin.api-clients.index')
                ->with('success', "Tes koneksi BERHASIL! Terhubung ke SIS-EMIS dan mendeteksi {$count} rombel resmi.");
        }

        return redirect()->route('admin.api-clients.index')
            ->with('error', 'Tes koneksi GAGAL: '.$result['message']);
    }

    /**
     * Aksi cepat tarik seluruh rombel dari SIS-EMIS
     */
    public function syncClasses(Request $request, SisEmisSyncService $syncService): RedirectResponse
    {
        $targetYearId = $request->integer('academic_year_id') ?: null;
        $result = $syncService->syncClasses($targetYearId);

        if (! $result['success']) {
            return redirect()->route('admin.api-clients.index')->with('error', $result['message']);
        }

        return redirect()->route('admin.api-clients.index')->with('success', $result['message']);
    }

    /**
     * Aksi cepat tarik siswa dari SIS-EMIS
     */
    public function syncStudents(Request $request, SisEmisSyncService $syncService): RedirectResponse
    {
        $rombel = $request->input('rombel') ?: null;
        $result = $syncService->sync($rombel);

        if (! $result['success']) {
            return redirect()->route('admin.api-clients.index')->with('error', $result['message']);
        }

        return redirect()->route('admin.api-clients.index')->with('success', $result['message']);
    }

    /**
     * Endpoint Server-Sent Events (SSE) untuk streaming progres sinkronisasi siswa secara real-time.
     */
    public function syncStudentsStream(Request $request, SisEmisSyncService $syncService): StreamedResponse
    {
        $rombel = $request->query('rombel') ?: null;

        return response()->stream(function () use ($rombel, $syncService) {
            // Tutup session agar tidak mengunci request lain di browser
            if (session()->isStarted()) {
                session()->save();
            }

            // Nonaktifkan buffering output agar data langsung terkirim
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $sendEvent = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: '.json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            // Kirim event pembuka
            $sendEvent('log', [
                'message' => 'Koneksi streaming aktif. Mempersiapkan proses sinkronisasi...',
                'type' => 'info',
            ]);

            try {
                $syncService->sync(
                    rombel: $rombel,
                    onProgress: function (array $payload) use ($sendEvent) {
                        $event = $payload['event'] ?? 'progress';
                        $sendEvent($event, $payload);
                    }
                );
            } catch (\Throwable $e) {
                $sendEvent('error', [
                    'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Helper simpel untuk memperbarui key-value pada file .env
     */
    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $escaped = preg_quote($key, '/');
            if (preg_match("/^{$escaped}=.*/m", $content)) {
                $content = preg_replace("/^{$escaped}=.*/m", "{$key}={$value}", $content);
            } else {
                $content = rtrim($content)."\n{$key}={$value}\n";
            }
        }

        file_put_contents($envPath, $content);
    }
}
