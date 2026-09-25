<?php

namespace App\Services\Holiday;

use App\Models\Holiday;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class NationalHolidayService
{
    public const DEFAULT_KEMENDESA_URL = 'https://api.kemendesa.link/libur-nasional';

    public const DEFAULT_UPSET_DEV_URL = 'https://tanggalmerah.upset.dev';

    protected string $kemendesaUrl;

    protected string $upsetDevUrl;

    public function __construct(?string $kemendesaUrl = null, ?string $upsetDevUrl = null)
    {
        $this->kemendesaUrl = $kemendesaUrl ?? config('services.kemendesa_holiday.url', self::DEFAULT_KEMENDESA_URL);
        $this->upsetDevUrl = $upsetDevUrl ?? config('services.upset_dev_holiday.url', self::DEFAULT_UPSET_DEV_URL);
    }

    /**
     * Ambil data libur nasional dari API Kemendesa.
     *
     * @return array<int, array{date: string, name: string, is_cuti_bersama: bool}>
     *
     * @throws RequestException|ConnectionException|Throwable
     */
    public function fetchFromKemendesa(int $year): array
    {
        $response = Http::baseUrl($this->kemendesaUrl)
            ->connectTimeout(3)
            ->timeout(6)
            ->retry([200, 500, 1000], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->get("/api/holidays/{$year}.json");

        if ($response->failed()) {
            $response->throw();
        }

        $json = $response->json();
        $raw = is_array($json) && isset($json['data']) && is_array($json['data'])
            ? $json['data']
            : [];

        return $this->normalizeHolidayItems($raw, 'kemendesa_api');
    }

    /**
     * Ambil data libur nasional dari API TanggalMerah (upset.dev).
     *
     * @return array<int, array{date: string, name: string, is_cuti_bersama: bool}>
     *
     * @throws RequestException|ConnectionException|Throwable
     */
    public function fetchFromUpsetDev(int $year): array
    {
        $response = Http::baseUrl($this->upsetDevUrl)
            ->connectTimeout(3)
            ->timeout(6)
            ->retry([200, 500, 1000], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->get('/api/holidays', ['year' => $year]);

        if ($response->failed()) {
            $response->throw();
        }

        $json = $response->json();
        $raw = is_array($json) && isset($json['data']) && is_array($json['data'])
            ? $json['data']
            : [];

        return $this->normalizeHolidayItems($raw, 'upset_dev');
    }

    /**
     * Ambil data libur nasional dari URL Kustom manual.
     *
     * @return array<int, array{date: string, name: string, is_cuti_bersama: bool}>
     *
     * @throws RequestException|ConnectionException|Throwable
     */
    public function fetchFromCustomUrl(string $url, int $year): array
    {
        $targetUrl = trim($url);

        // Subtitusi {year} jika ada di template URL
        if (str_contains($targetUrl, '{year}')) {
            $targetUrl = str_replace('{year}', (string) $year, $targetUrl);
            $queryParams = [];
        } else {
            // Jika tidak ada {year}, tambahkan query param year jika belum ada
            $queryParams = ! str_contains($targetUrl, 'year=') ? ['year' => $year] : [];
        }

        $response = Http::connectTimeout(3)
            ->timeout(8)
            ->retry([200, 500, 1000], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->get($targetUrl, $queryParams);

        if ($response->failed()) {
            $response->throw();
        }

        $json = $response->json();

        $raw = [];
        if (is_array($json)) {
            if (isset($json['data']) && is_array($json['data'])) {
                $raw = $json['data'];
            } elseif (array_is_list($json)) {
                $raw = $json;
            }
        }

        if (empty($raw)) {
            throw new RuntimeException('Respon dari URL kustom tidak memuat data array hari libur yang valid.');
        }

        return $this->normalizeHolidayItems($raw, 'custom_url');
    }

    /**
     * Normalisasi format beragam API ke struktur standar [date, name, is_cuti_bersama].
     *
     * @param  array<int, mixed>  $rawItems
     * @return array<int, array{date: string, name: string, is_cuti_bersama: bool}>
     */
    protected function normalizeHolidayItems(array $rawItems, string $source): array
    {
        $normalized = [];

        foreach ($rawItems as $item) {
            if (! is_array($item)) {
                continue;
            }

            $date = $item['date'] ?? $item['holiday_date'] ?? null;
            $name = $item['name'] ?? $item['holiday_name'] ?? $item['description'] ?? null;

            if (! $date || ! $name) {
                continue;
            }

            // Normalisasi boolean is_cuti_bersama
            $isCutiBersama = false;
            if (isset($item['is_cuti_bersama'])) {
                $isCutiBersama = (bool) $item['is_cuti_bersama'];
            } elseif (isset($item['type'])) {
                $isCutiBersama = strtolower((string) $item['type']) === 'leave';
            }

            $normalized[] = [
                'date' => (string) $date,
                'name' => (string) $name,
                'is_cuti_bersama' => $isCutiBersama,
            ];
        }

        return $normalized;
    }

    /**
     * Ambil data libur sesuai mode (auto, kemendesa, upset_dev, custom).
     *
     * @return array{data: array<int, array{date: string, name: string, is_cuti_bersama: bool}>, provider: string, provider_name: string, is_fallback: bool}
     */
    public function fetchHolidays(int $year, string $source = 'auto', ?string $customUrl = null): array
    {
        if ($source === 'custom') {
            if (empty($customUrl)) {
                throw new RuntimeException('URL kustom tidak boleh kosong.');
            }

            $data = $this->fetchFromCustomUrl($customUrl, $year);

            return [
                'data' => $data,
                'provider' => 'custom_url',
                'provider_name' => 'URL Kustom Manual',
                'is_fallback' => false,
            ];
        }

        if ($source === 'upset_dev') {
            $data = $this->fetchFromUpsetDev($year);

            return [
                'data' => $data,
                'provider' => 'upset_dev',
                'provider_name' => 'TanggalMerah (upset.dev)',
                'is_fallback' => false,
            ];
        }

        if ($source === 'kemendesa') {
            $data = $this->fetchFromKemendesa($year);

            return [
                'data' => $data,
                'provider' => 'kemendesa_api',
                'provider_name' => 'API Kemendesa',
                'is_fallback' => false,
            ];
        }

        // Mode AUTO: Coba Kemendesa terlebih dahulu, jika gagal otomatis beralih ke TanggalMerah upset.dev
        try {
            $data = $this->fetchFromKemendesa($year);

            return [
                'data' => $data,
                'provider' => 'kemendesa_api',
                'provider_name' => 'API Kemendesa',
                'is_fallback' => false,
            ];
        } catch (Throwable $e) {
            Log::warning('Sumber utama API Kemendesa gagal diakses, mencoba sumber cadangan TanggalMerah upset.dev...', [
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            try {
                $data = $this->fetchFromUpsetDev($year);

                return [
                    'data' => $data,
                    'provider' => 'upset_dev',
                    'provider_name' => 'TanggalMerah (upset.dev)',
                    'is_fallback' => true,
                ];
            } catch (Throwable $fallbackError) {
                Log::error('Seluruh sumber API hari libur (Kemendesa & upset.dev) gagal diakses', [
                    'year' => $year,
                    'kemendesa_error' => $e->getMessage(),
                    'upset_dev_error' => $fallbackError->getMessage(),
                ]);

                throw new RuntimeException("Gagal menyinkronkan hari libur: Sumber utama (Kemendesa) dan cadangan (upset.dev) tidak dapat dihubungi. ({$e->getMessage()})");
            }
        }
    }

    /**
     * Sinkronkan data hari libur nasional ke database lokal.
     *
     * @return array{year: int, total: int, inserted: int, updated: int, provider: string, provider_name: string, is_fallback: bool}
     */
    public function syncHolidays(?int $year = null, string $source = 'auto', ?string $customUrl = null): array
    {
        $targetYear = $year ?? (int) now()->format('Y');
        $fetchResult = $this->fetchHolidays($targetYear, $source, $customUrl);

        $holidaysData = $fetchResult['data'];
        $provider = $fetchResult['provider'];
        $providerName = $fetchResult['provider_name'];
        $isFallback = $fetchResult['is_fallback'];

        $inserted = 0;
        $updated = 0;

        foreach ($holidaysData as $item) {
            $date = $item['date'] ?? null;
            $name = $item['name'] ?? null;

            if (! $date || ! $name) {
                continue;
            }

            $existing = Holiday::where('holiday_date', $date)->first();

            if ($existing) {
                $existing->update([
                    'name' => $name,
                    'is_national' => true,
                    'is_cuti_bersama' => (bool) ($item['is_cuti_bersama'] ?? false),
                    'source' => $provider,
                ]);
                $updated++;
            } else {
                Holiday::create([
                    'holiday_date' => $date,
                    'name' => $name,
                    'is_national' => true,
                    'is_cuti_bersama' => (bool) ($item['is_cuti_bersama'] ?? false),
                    'is_active' => true,
                    'source' => $provider,
                ]);
                $inserted++;
            }
        }

        return [
            'year' => $targetYear,
            'total' => count($holidaysData),
            'inserted' => $inserted,
            'updated' => $updated,
            'provider' => $provider,
            'provider_name' => $providerName,
            'is_fallback' => $isFallback,
        ];
    }
}
