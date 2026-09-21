<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Perbarui kontak, email, dan foto profil siswa.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            return back()->with('error', 'Data profil siswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photo_cropped' => ['nullable', 'string'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'photo.image' => 'Berkas harus berupa gambar.',
            'photo.mimes' => 'Format foto yang didukung: JPG, JPEG, PNG, dan WEBP.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        if (! empty($validated['email'])) {
            $user->update([
                'email' => $validated['email'],
            ]);
        }

        // Penanganan Foto Siswa
        if ($request->boolean('remove_photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $student->photo = null;
        } elseif ($request->filled('photo_cropped') && str_starts_with($request->input('photo_cropped'), 'data:image/')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $base64String = $request->input('photo_cropped');
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $decoded = base64_decode($data);
            $fileName = 'students/crop_'.uniqid().'.jpg';
            Storage::disk('public')->put($fileName, $decoded);
            $student->photo = $fileName;
        } elseif ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $student->photo = $request->file('photo')->store('students', 'public');
        }

        $student->phone = $validated['phone'] ?? null;
        $student->save();

        return redirect()->to(route('siswa.dashboard').'#tab=profil')->with('success', 'Profil siswa berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun siswa.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->to(route('siswa.dashboard').'#tab=profil')->with('success', 'Kata sandi akun siswa berhasil diperbarui.');
    }
}
