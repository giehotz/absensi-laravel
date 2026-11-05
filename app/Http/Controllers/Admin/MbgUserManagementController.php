<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MbgUserManagementController extends Controller
{
    /**
     * Daftar akun & guru yang bertugas sebagai Petugas MBG.
     */
    public function index(): View
    {
        $officers = User::with('teacher')
            ->where(function ($query) {
                $query->where('role', 'petugas_mbg')
                    ->orWhereHas('teacher', function ($tQuery) {
                        $tQuery->where('is_petugas_mbg', true);
                    });
            })
            ->orderBy('name')
            ->paginate(15);

        $availableTeachers = Teacher::with('user')
            ->where('is_petugas_mbg', false)
            ->get()
            ->sortBy(fn ($t) => $t->user?->name ?? '')
            ->values();

        return view('admin.petugas-mbg.index', compact('officers', 'availableTeachers'));
    }

    /**
     * Tambah penugasan Guru atau buat akun Petugas MBG mandiri baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $isGuru = ($request->input('account_type') === 'guru' && ! $request->filled('name')) || $request->filled('teacher_id');

        if ($isGuru) {
            $validated = $request->validate([
                'teacher_id' => ['required', 'exists:teachers,id'],
            ], [
                'teacher_id.required' => 'Pilih guru yang akan ditunjuk sebagai Petugas MBG.',
                'teacher_id.exists' => 'Data guru tidak ditemukan.',
            ]);

            $teacher = Teacher::with('user')->findOrFail($validated['teacher_id']);
            $teacher->update(['is_petugas_mbg' => true]);

            $teacherName = $teacher->user?->name ?? 'Guru';

            return redirect()->route('admin.petugas-mbg.index')
                ->with('success', "Guru {$teacherName} berhasil ditunjuk sebagai Petugas MBG.");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Email login petugas wajib diisi.',
            'email.unique' => 'Email sudah terdaftar pada sistem.',
            'password.required' => 'Password wajib diisi minimal 6 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'nip' => ! empty($validated['nip']) ? $validated['nip'] : null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'petugas_mbg',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.petugas-mbg.index')
            ->with('success', 'Akun Petugas MBG mandiri baru berhasil dibuat.');
    }

    /**
     * Perbarui data akun atau penugasan Petugas MBG.
     */
    public function update(Request $request, User $petugas_mbg): RedirectResponse
    {
        if ($petugas_mbg->isGuru()) {
            if (! $petugas_mbg->teacher || ! $petugas_mbg->teacher->is_petugas_mbg) {
                abort(404);
            }

            $validated = $request->validate([
                'nip' => ['nullable', 'string', 'max:50'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            if (array_key_exists('nip', $validated)) {
                $petugas_mbg->teacher->update(['nip' => $validated['nip']]);
            }

            $petugas_mbg->is_active = $request->boolean('is_active', true);
            $petugas_mbg->save();

            return redirect()->route('admin.petugas-mbg.index')
                ->with('success', "Data penugasan guru {$petugas_mbg->name} berhasil diperbarui.");
        }

        if ($petugas_mbg->role === 'petugas_mbg') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'nip' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($petugas_mbg->id)],
                'password' => ['nullable', 'string', 'min:6'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            $petugas_mbg->name = $validated['name'];
            $petugas_mbg->nip = ! empty($validated['nip']) ? $validated['nip'] : null;
            $petugas_mbg->email = $validated['email'];
            $petugas_mbg->is_active = $request->boolean('is_active', true);

            if (! empty($validated['password'])) {
                $petugas_mbg->password = Hash::make($validated['password']);
            }

            $petugas_mbg->save();

            return redirect()->route('admin.petugas-mbg.index')
                ->with('success', 'Data akun Petugas MBG berhasil diperbarui.');
        }

        abort(404);
    }

    /**
     * Cabut penugasan Guru atau hapus akun Petugas MBG mandiri.
     */
    public function destroy(User $petugas_mbg): RedirectResponse
    {
        if ($petugas_mbg->isGuru()) {
            if (! $petugas_mbg->teacher || ! $petugas_mbg->teacher->is_petugas_mbg) {
                abort(404);
            }

            $petugas_mbg->teacher->update(['is_petugas_mbg' => false]);

            return redirect()->route('admin.petugas-mbg.index')
                ->with('success', "Penugasan Petugas MBG untuk guru {$petugas_mbg->name} berhasil dicabut. Akun guru tetap aman.");
        }

        if ($petugas_mbg->role === 'petugas_mbg') {
            $petugas_mbg->delete();

            return redirect()->route('admin.petugas-mbg.index')
                ->with('success', 'Akun Petugas MBG mandiri berhasil dihapus.');
        }

        abort(404);
    }
}
