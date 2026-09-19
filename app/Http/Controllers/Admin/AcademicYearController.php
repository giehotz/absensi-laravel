<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'in:ganjil,genap'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $isActive = $request->boolean('is_active');

        if ($isActive) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        AcademicYear::create([
            'name' => $validated['name'],
            'semester' => $validated['semester'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.settings.index')->with('success', "Tahun ajaran {$validated['name']} ({$validated['semester']}) berhasil ditambahkan.");
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'in:ganjil,genap'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $isActive = $request->boolean('is_active');

        if ($isActive) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update([
            'name' => $validated['name'],
            'semester' => $validated['semester'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.settings.index')->with('success', "Data tahun ajaran {$academicYear->name} berhasil diperbarui.");
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $classesCount = $academicYear->schoolClasses()->count();

        if ($classesCount > 0) {
            return redirect()->route('admin.settings.index')->with('error', "Tahun ajaran {$academicYear->name} tidak dapat dihapus karena masih digunakan oleh {$classesCount} rombel/kelas.");
        }

        $name = $academicYear->name;
        $academicYear->delete();

        return redirect()->route('admin.settings.index')->with('success', "Tahun ajaran {$name} berhasil dihapus.");
    }

    public function toggleStatus(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            $academicYear->update(['is_active' => false]);
            $message = "Tahun ajaran {$academicYear->name} ({$academicYear->semester}) telah dinonaktifkan.";
        } else {
            AcademicYear::query()->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
            $message = "Tahun ajaran {$academicYear->name} (Semester ".ucfirst($academicYear->semester).') berhasil diaktifkan sebagai periode aktif saat ini.';
        }

        return redirect()->route('admin.settings.index')->with('success', $message);
    }
}
