<?php

namespace App\Console\Commands;

use App\Services\Holiday\NationalHolidayService;
use Illuminate\Console\Command;
use Throwable;

class SyncNationalHolidaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync 
                            {year? : Tahun kalender yang ingin disinkronkan (default: tahun berjalan)} 
                            {--source=auto : Sumber provider API (auto, kemendesa, upset_dev, custom)} 
                            {--url= : URL kustom jika menggunakan source custom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data hari libur nasional dari API resmi (Kemendesa / upset.dev / custom URL) ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle(NationalHolidayService $service): int
    {
        $yearInput = $this->argument('year');
        $year = $yearInput ? (int) $yearInput : (int) now()->format('Y');
        $source = (string) ($this->option('source') ?: 'auto');
        $customUrl = $this->option('url');

        $this->components->info("Memulai sinkronisasi data hari libur nasional untuk tahun {$year} (Sumber: {$source})...");

        try {
            $result = $service->syncHolidays($year, $source, $customUrl);

            $sourceLabel = $result['is_fallback']
                ? "{$result['provider_name']} [Fallback Otomatis]"
                : $result['provider_name'];

            $this->components->info(
                "Sinkronisasi berhasil via {$sourceLabel}: Total {$result['total']} hari libur ({$result['inserted']} baru, {$result['updated']} diperbarui)."
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->components->error("Gagal menyinkronkan hari libur: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
