<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceArchive;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $setting = AttendanceSetting::first() ?? AttendanceSetting::create([
            'mode' => 'daily',
            'tolerance_minutes' => 15,
            'school_name' => 'SMP Negeri 1 Garuda',
            'npsn' => '20102030',
            'level' => 'SMP',
            'school_address' => 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri',
        ]);

        $academicYears = AcademicYear::withCount('schoolClasses')
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $activeAttendanceCount = Attendance::count();
        $archivedAttendanceCount = AttendanceArchive::count();

        foreach ($academicYears as $ay) {
            $ay->active_records_count = Attendance::whereDate('date', '>=', $ay->start_date)
                ->whereDate('date', '<=', $ay->end_date)
                ->count();

            $ay->archived_records_count = AttendanceArchive::where('academic_year_id', $ay->id)
                ->orWhere(function ($q) use ($ay) {
                    $q->whereNull('academic_year_id')
                        ->whereDate('date', '>=', $ay->start_date)
                        ->whereDate('date', '<=', $ay->end_date);
                })
                ->count();
        }

        return view('admin.settings.index', compact('setting', 'academicYears', 'activeAttendanceCount', 'archivedAttendanceCount'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:daily,per_lesson'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'active_academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', 'in:SD,MI,SMP,MTs,SMA,MA,SMK'],
            'school_address' => ['nullable', 'string', 'max:500'],
        ], [
            'mode.required' => 'Mode absensi wajib dipilih.',
            'tolerance_minutes.required' => 'Toleransi keterlambatan wajib diisi.',
            'tolerance_minutes.integer' => 'Toleransi keterlambatan harus berupa angka menit.',
        ]);

        $setting = AttendanceSetting::first() ?? new AttendanceSetting;
        $setting->mode = $validated['mode'];
        $setting->tolerance_minutes = $validated['tolerance_minutes'];
        if (isset($validated['school_name'])) {
            $setting->school_name = $validated['school_name'];
        }
        if (array_key_exists('npsn', $validated)) {
            $setting->npsn = $validated['npsn'];
        }
        if (isset($validated['level'])) {
            $setting->level = $validated['level'];
        }
        if (array_key_exists('school_address', $validated)) {
            $setting->school_address = $validated['school_address'];
        }
        $setting->save();

        if (! empty($validated['level'])) {
            // Sinkronisasi jenjang ke seluruh rombel/kelas sebagai sumber data utama
            SchoolClass::query()->update(['level' => $validated['level']]);
        }

        if (! empty($validated['active_academic_year_id'])) {
            AcademicYear::query()->update(['is_active' => false]);
            AcademicYear::where('id', $validated['active_academic_year_id'])->update(['is_active' => true]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan sistem dan profil lembaga berhasil disimpan.');
    }
}
