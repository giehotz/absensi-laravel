<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::withCount('schedules')->latest()->paginate(15);
        $existingCodes = Subject::pluck('code')->map(fn ($c) => strtoupper(trim($c)))->all();
        $schoolLevel = AttendanceSetting::first()->level ?? 'SMP';

        return view('admin.subjects.index', compact('subjects', 'existingCodes', 'schoolLevel'));
    }

    public function sync(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.code' => ['required', 'string', 'max:20'],
            'subjects.*.name' => ['required', 'string', 'max:100'],
        ], [
            'subjects.required' => 'Pilih setidaknya satu mata pelajaran untuk disinkronkan.',
            'subjects.min' => 'Pilih setidaknya satu mata pelajaran untuk disinkronkan.',
        ]);

        $existingCodes = Subject::pluck('code')->map(fn ($c) => strtoupper(trim($c)))->all();
        $syncedCount = 0;

        foreach ($validated['subjects'] as $item) {
            $code = strtoupper(trim($item['code']));
            $name = trim($item['name']);

            if (in_array($code, $existingCodes, true)) {
                continue;
            }

            Subject::create([
                'code' => $code,
                'name' => $name,
            ]);

            $existingCodes[] = $code;
            $syncedCount++;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $syncedCount > 0
                    ? "Berhasil mensinkronkan {$syncedCount} mata pelajaran ke database."
                    : 'Tidak ada mata pelajaran baru yang ditambahkan karena semua mapel terpilih sudah ada.',
                'synced_count' => $syncedCount,
            ]);
        }

        if ($syncedCount === 0) {
            return redirect()->route('admin.subjects.index')
                ->with('info', 'Tidak ada mata pelajaran baru yang ditambahkan karena semua mapel terpilih sudah ada di database.');
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', "Berhasil mensinkronkan {$syncedCount} mata pelajaran dari API ke database.");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique' => 'Kode mata pelajaran sudah digunakan.',
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,'.$subject->id],
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
