<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MbgUserManagementController extends Controller
{
    /**
     * Daftar akun Petugas MBG.
     */
    public function index(): View
    {
        $officers = User::where('role', 'petugas_mbg')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.petugas-mbg.index', compact('officers'));
    }

    /**
     * Tambah akun Petugas MBG baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'petugas_mbg',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.petugas-mbg.index')->with('success', 'Akun Petugas MBG baru berhasil dibuat.');
    }

    /**
     * Perbarui data akun Petugas MBG.
     */
    public function update(Request $request, User $petugas_mbg): RedirectResponse
    {
        if ($petugas_mbg->role !== 'petugas_mbg') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($petugas_mbg->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $petugas_mbg->name = $validated['name'];
        $petugas_mbg->email = $validated['email'];
        $petugas_mbg->is_active = $request->boolean('is_active', true);

        if (! empty($validated['password'])) {
            $petugas_mbg->password = Hash::make($validated['password']);
        }

        $petugas_mbg->save();

        return redirect()->route('admin.petugas-mbg.index')->with('success', 'Data akun Petugas MBG berhasil diperbarui.');
    }

    /**
     * Hapus akun Petugas MBG.
     */
    public function destroy(User $petugas_mbg): RedirectResponse
    {
        if ($petugas_mbg->role !== 'petugas_mbg') {
            abort(404);
        }

        $petugas_mbg->delete();

        return redirect()->route('admin.petugas-mbg.index')->with('success', 'Akun Petugas MBG berhasil dihapus.');
    }
}
