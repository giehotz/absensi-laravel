<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    public function index(): View
    {
        $supervisors = SupervisiSupervisor::with(['teacher.user'])
            ->withCount('jadwals')
            ->latest('id')
            ->get();

        $assignedIds = $supervisors->pluck('teacher_id');
        $availableTeachers = Teacher::with('user')
            ->excludeKepala()
            ->whereNotIn('id', $assignedIds)
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn (Teacher $t) => $t->user?->name ?? '')
            ->values();

        return view('supervisi.admin.supervisors.index', compact('supervisors', 'availableTeachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id', 'unique:supervisi_supervisors,teacher_id'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'tanggal_penugasan' => ['nullable', 'date'],
        ]);

        SupervisiSupervisor::create([
            'teacher_id' => $validated['teacher_id'],
            'jabatan' => $validated['jabatan'] ?? 'Tim Supervisor',
            'tanggal_penugasan' => $validated['tanggal_penugasan'] ?? now()->toDateString(),
            'is_active' => true,
        ]);

        return redirect()->route('supervisi.admin.supervisors.index')
            ->with('success', 'Guru berhasil ditambahkan ke Tim Supervisor.');
    }

    public function toggle(SupervisiSupervisor $supervisor): RedirectResponse
    {
        $supervisor->update(['is_active' => ! $supervisor->is_active]);

        return redirect()->route('supervisi.admin.supervisors.index')
            ->with('success', 'Status keaktifan supervisor berhasil diperbarui.');
    }

    public function destroy(SupervisiSupervisor $supervisor): RedirectResponse
    {
        if ($supervisor->jadwals()->exists()) {
            return redirect()->route('supervisi.admin.supervisors.index')
                ->with('error', 'Supervisor sudah memiliki riwayat jadwal supervisi; nonaktifkan saja statusnya.');
        }

        $supervisor->delete();

        return redirect()->route('supervisi.admin.supervisors.index')
            ->with('success', 'Supervisor berhasil dihapus dari daftar.');
    }
}
