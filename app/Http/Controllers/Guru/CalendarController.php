<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicCalendarDocument;
use App\Models\AcademicYear;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Tampilkan halaman Kalender Pendidikan dan Hari Libur untuk Guru (Read-Only).
     */
    public function index(Request $request): View
    {
        $recordedYears = AcademicYear::select('name')
            ->distinct()
            ->pluck('name')
            ->all();

        $availableYears = array_values(array_unique(array_merge(
            ['2026/2027', '2025/2026'],
            $recordedYears
        )));
        rsort($availableYears);

        $activeYear = AcademicYear::where('is_active', true)->value('name');
        $selectedYear = $request->input('academic_year_name', $activeYear ?? $availableYears[0]);

        $activeTab = $request->input('tab', 'ganjil');
        if (! in_array($activeTab, ['ganjil', 'genap', 'libur'], true)) {
            $activeTab = 'ganjil';
        }

        $calendarsGanjil = AcademicCalendar::forYear($selectedYear)
            ->semester('ganjil')
            ->chronological()
            ->get();

        $calendarsGenap = AcademicCalendar::forYear($selectedYear)
            ->semester('genap')
            ->chronological()
            ->get();

        // Hari Libur untuk rentang tahun ajaran
        $years = explode('/', $selectedYear);
        $startYear = (int) ($years[0] ?? now()->format('Y'));
        $endYear = (int) ($years[1] ?? ($startYear + 1));

        $holidays = Holiday::active()
            ->where(function ($q) use ($startYear, $endYear) {
                $q->where(function ($sub) use ($startYear) {
                    $sub->whereYear('holiday_date', $startYear)->whereMonth('holiday_date', '>=', 7);
                })->orWhere(function ($sub) use ($endYear) {
                    $sub->whereYear('holiday_date', $endYear)->whereMonth('holiday_date', '<=', 7);
                });
            })
            ->orderBy('holiday_date')
            ->get();

        if ($holidays->isEmpty()) {
            $holidays = Holiday::active()->inYear($startYear)->orderBy('holiday_date')->get();
        }

        $document = AcademicCalendarDocument::where('academic_year_name', $selectedYear)->first();

        $stats = [
            'total' => $calendarsGanjil->count() + $calendarsGenap->count(),
            'ganjil' => $calendarsGanjil->count(),
            'genap' => $calendarsGenap->count(),
            'libur_nasional' => $holidays->count(),
        ];

        return view('guru.calendar.index', compact(
            'availableYears',
            'selectedYear',
            'activeTab',
            'calendarsGanjil',
            'calendarsGenap',
            'holidays',
            'document',
            'stats'
        ));
    }
}
