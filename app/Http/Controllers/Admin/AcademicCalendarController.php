<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicCalendarDocument;
use App\Models\AcademicYear;
use App\Services\AcademicCalendar\AcademicCalendarImportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AcademicCalendarController extends Controller
{
    /**
     * Tampilkan halaman utama Kalender Pendidikan.
     */
    public function index(Request $request): View
    {
        // Ambil daftar Tahun Ajaran dari tabel academic_years
        $recordedYears = AcademicYear::select('name')
            ->distinct()
            ->pluck('name')
            ->all();

        // Default list tahun jika tabel kosong
        $availableYears = array_values(array_unique(array_merge(
            ['2026/2027', '2025/2026'],
            $recordedYears
        )));
        rsort($availableYears);

        // Cari tahun ajaran aktif saat ini
        $activeYear = AcademicYear::where('is_active', true)->value('name');
        $selectedYear = $request->input('academic_year_name', $activeYear ?? $availableYears[0]);

        $activeTab = $request->input('tab', 'ganjil');
        if (! in_array($activeTab, ['ganjil', 'genap'], true)) {
            $activeTab = 'ganjil';
        }

        // Ambil data kegiatan kalender
        $calendarsGanjil = AcademicCalendar::forYear($selectedYear)
            ->semester('ganjil')
            ->chronological()
            ->get();

        $calendarsGenap = AcademicCalendar::forYear($selectedYear)
            ->semester('genap')
            ->chronological()
            ->get();

        // Dokumen SK PDF dasar penetapan
        $document = AcademicCalendarDocument::where('academic_year_name', $selectedYear)->first();

        // Statistik
        $stats = [
            'total' => $calendarsGanjil->count() + $calendarsGenap->count(),
            'ganjil' => $calendarsGanjil->count(),
            'genap' => $calendarsGenap->count(),
            'libur' => AcademicCalendar::forYear($selectedYear)->where('category', 'libur')->count(),
            'ujian' => AcademicCalendar::forYear($selectedYear)->where('category', 'ujian')->count(),
        ];

        return view('admin.academic-calendar.index', compact(
            'availableYears',
            'selectedYear',
            'activeTab',
            'calendarsGanjil',
            'calendarsGenap',
            'document',
            'stats'
        ));
    }

    /**
     * Unduh template file Excel kalender pendidikan.
     */
    public function downloadTemplate(Request $request, AcademicCalendarImportService $service): StreamedResponse
    {
        $yearName = $request->input('academic_year_name', '2026/2027');

        return $service->downloadTemplate($yearName);
    }

    /**
     * Impor data kegiatan dari berkas Excel (.xlsx / .xls / .csv).
     */
    public function importExcel(Request $request, AcademicCalendarImportService $service): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_name' => ['required', 'string', 'max:50'],
            'mode' => ['required', 'in:append,replace'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Silakan pilih berkas Excel kalender pendidikan.',
            'file.mimes' => 'Format berkas harus berupa Excel (.xlsx, .xls) atau CSV.',
            'file.max' => 'Ukuran berkas Excel maksimal 10 MB.',
        ]);

        try {
            $result = $service->import(
                $request->file('file'),
                $validated['academic_year_name'],
                $validated['mode']
            );

            $modeText = $result['mode'] === 'replace' ? 'menggantikan seluruh data lama' : 'menambahkan ke data yang ada';
            $message = "Berhasil mengimpor {$result['inserted']} agenda kalender pendidikan tahun ajaran {$validated['academic_year_name']} ({$modeText}).";

            return redirect()->route('admin.academic-calendar.index', ['academic_year_name' => $validated['academic_year_name']])
                ->with('success', $message);
        } catch (Throwable $e) {
            return redirect()->route('admin.academic-calendar.index', ['academic_year_name' => $validated['academic_year_name']])
                ->with('error', 'Gagal memproses berkas Excel: '.$e->getMessage());
        }
    }

    /**
     * Unggah berkas SK PDF dasar penetapan kalender pendidikan & hari libur.
     */
    public function uploadPdf(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_name' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'pdf_file.required' => 'Silakan pilih berkas PDF yang ingin diunggah.',
            'pdf_file.mimes' => 'Format berkas harus berupa PDF.',
            'pdf_file.max' => 'Ukuran berkas PDF maksimal 20 MB.',
        ]);

        $file = $request->file('pdf_file');
        $yearName = $validated['academic_year_name'];
        $title = $validated['title'] ?: 'Dasar Penetapan Kalender Pendidikan & Hari Libur TA '.$yearName;

        // Cari dokumen lama jika ada
        $existing = AcademicCalendarDocument::where('academic_year_name', $yearName)->first();
        if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $safeYear = str_replace(['/', '\\'], '-', $yearName);
        $fileName = 'sk_kalender_'.$safeYear.'_'.time().'.pdf';
        $storedPath = $file->storeAs('academic_calendars', $fileName, 'public');

        AcademicCalendarDocument::updateOrCreate(
            ['academic_year_name' => $yearName],
            [
                'title' => $title,
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]
        );

        return redirect()->route('admin.academic-calendar.index', ['academic_year_name' => $yearName])
            ->with('success', "Berkas SK PDF dasar penetapan untuk Tahun Ajaran {$yearName} berhasil disimpan.");
    }

    /**
     * Hapus berkas SK PDF dasar penetapan.
     */
    public function deletePdf(Request $request): RedirectResponse
    {
        $yearName = $request->input('academic_year_name');
        $document = AcademicCalendarDocument::where('academic_year_name', $yearName)->first();

        if ($document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        return redirect()->route('admin.academic-calendar.index', ['academic_year_name' => $yearName])
            ->with('success', 'Berkas SK PDF dasar penetapan berhasil dihapus.');
    }

    /**
     * Tambah kegiatan kalender manual.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'in:ganjil,genap'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['required', 'string', 'max:500'],
            'category' => ['required', 'in:kegiatan,libur,ujian,rapat,umum'],
        ], [
            'start_date.required' => 'Tanggal kegiatan wajib diisi.',
            'description.required' => 'Keterangan kegiatan wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = ! empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;

        $startDay = $startDate->locale('id')->isoFormat('dddd');
        if ($endDate && $endDate->format('Y-m-d') !== $startDate->format('Y-m-d')) {
            $endDay = $endDate->locale('id')->isoFormat('dddd');
            $dayName = "{$startDay} - {$endDay}";
        } else {
            $dayName = $startDay;
        }

        AcademicCalendar::create([
            'academic_year_name' => $validated['academic_year_name'],
            'semester' => $validated['semester'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
            'day_name' => $dayName,
            'description' => $validated['description'],
            'category' => $validated['category'],
        ]);

        return redirect()->route('admin.academic-calendar.index', [
            'academic_year_name' => $validated['academic_year_name'],
            'tab' => $validated['semester'],
        ])->with('success', 'Agenda kegiatan berhasil ditambahkan ke Kalender Pendidikan.');
    }

    /**
     * Perbarui agenda kegiatan kalender manual.
     */
    public function update(Request $request, AcademicCalendar $calendar): RedirectResponse
    {
        $validated = $request->validate([
            'semester' => ['required', 'in:ganjil,genap'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['required', 'string', 'max:500'],
            'category' => ['required', 'in:kegiatan,libur,ujian,rapat,umum'],
        ], [
            'start_date.required' => 'Tanggal kegiatan wajib diisi.',
            'description.required' => 'Keterangan kegiatan wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = ! empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;

        $startDay = $startDate->locale('id')->isoFormat('dddd');
        if ($endDate && $endDate->format('Y-m-d') !== $startDate->format('Y-m-d')) {
            $endDay = $endDate->locale('id')->isoFormat('dddd');
            $dayName = "{$startDay} - {$endDay}";
        } else {
            $dayName = $startDay;
        }

        $calendar->update([
            'semester' => $validated['semester'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
            'day_name' => $dayName,
            'description' => $validated['description'],
            'category' => $validated['category'],
        ]);

        return redirect()->route('admin.academic-calendar.index', [
            'academic_year_name' => $calendar->academic_year_name,
            'tab' => $calendar->semester,
        ])->with('success', 'Agenda kegiatan berhasil diperbarui.');
    }

    /**
     * Hapus agenda kegiatan kalender.
     */
    public function destroy(AcademicCalendar $calendar): RedirectResponse
    {
        $yearName = $calendar->academic_year_name;
        $semester = $calendar->semester;
        $desc = $calendar->description;

        $calendar->delete();

        return redirect()->route('admin.academic-calendar.index', [
            'academic_year_name' => $yearName,
            'tab' => $semester,
        ])->with('success', "Agenda '{$desc}' berhasil dihapus.");
    }
}
