<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\Holiday\NationalHolidayService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class HolidayController extends Controller
{
    /**
     * Tampilkan daftar hari libur.
     */
    public function index(Request $request): View
    {
        $currentYear = (int) now()->format('Y');
        $selectedYear = (int) $request->input('year', $currentYear);

        // Ambil daftar tahun dari data yang ada di database secara database-agnostic
        $recordedYears = Holiday::select('holiday_date')
            ->get()
            ->pluck('holiday_date')
            ->map(fn ($d) => (int) Carbon::parse($d)->format('Y'))
            ->unique()
            ->values()
            ->all();

        $availableYears = array_unique(array_merge(
            [$currentYear - 1, $currentYear, $currentYear + 1],
            $recordedYears
        ));
        rsort($availableYears);

        $holidays = Holiday::inYear($selectedYear)
            ->orderBy('holiday_date')
            ->paginate(35)
            ->withQueryString();

        $stats = [
            'total' => Holiday::inYear($selectedYear)->count(),
            'national' => Holiday::inYear($selectedYear)->where('is_national', true)->where('is_cuti_bersama', false)->count(),
            'cuti_bersama' => Holiday::inYear($selectedYear)->where('is_cuti_bersama', true)->count(),
            'school_specific' => Holiday::inYear($selectedYear)->where('is_national', false)->count(),
            'active_holidays' => Holiday::inYear($selectedYear)->active()->count(),
        ];

        return view('admin.holidays.index', compact('holidays', 'selectedYear', 'availableYears', 'stats'));
    }

    /**
     * Sinkronkan data hari libur dari API (Auto Failover, Upset.dev, atau Custom URL).
     */
    public function sync(Request $request, NationalHolidayService $service): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2020,2099'],
            'source' => ['nullable', 'in:auto,kemendesa,upset_dev,custom'],
            'custom_url' => ['nullable', 'url', 'required_if:source,custom'],
        ]);

        $year = (int) ($validated['year'] ?? now()->format('Y'));
        $source = $validated['source'] ?? 'auto';
        $customUrl = $validated['custom_url'] ?? null;

        try {
            $result = $service->syncHolidays($year, $source, $customUrl);

            $sourceBadge = $result['is_fallback']
                ? "{$result['provider_name']} (Fallback Otomatis)"
                : $result['provider_name'];

            $message = "Berhasil menyinkronkan {$result['total']} hari libur tahun {$result['year']} dari [{$sourceBadge}] ({$result['inserted']} baru, {$result['updated']} diperbarui).";

            return redirect()->route('admin.holidays.index', ['year' => $year])
                ->with('success', $message);
        } catch (Throwable $e) {
            return redirect()->route('admin.holidays.index', ['year' => $year])
                ->with('error', 'Gagal menyinkronkan data hari libur: '.$e->getMessage());
        }
    }

    /**
     * Simpan hari libur khusus internal sekolah secara manual.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date', 'unique:holidays,holiday_date'],
            'name' => ['required', 'string', 'max:255'],
            'is_cuti_bersama' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $holiday = Holiday::create([
            'holiday_date' => $validated['holiday_date'],
            'name' => $validated['name'],
            'is_national' => false,
            'is_cuti_bersama' => $request->boolean('is_cuti_bersama'),
            'is_active' => true,
            'source' => 'manual',
            'description' => $validated['description'] ?? null,
        ]);

        $year = (int) date('Y', strtotime($holiday->holiday_date));

        return redirect()->route('admin.holidays.index', ['year' => $year])
            ->with('success', "Hari libur sekolah '{$holiday->name}' berhasil ditambahkan.");
    }

    /**
     * Toggle status aktif/nonaktif libur.
     */
    public function toggleActive(Holiday $holiday): RedirectResponse
    {
        $holiday->update([
            'is_active' => ! $holiday->is_active,
        ]);

        $statusText = $holiday->is_active ? 'diaktifkan sebagai hari libur' : 'dinonaktifkan (sekolah tetap masuk)';

        return back()->with('success', "Hari '{$holiday->name}' berhasil {$statusText}.");
    }

    /**
     * Hapus hari libur.
     */
    public function destroy(Holiday $holiday): RedirectResponse
    {
        $name = $holiday->name;
        $holiday->delete();

        return back()->with('success', "Hari libur '{$name}' berhasil dihapus.");
    }
}
