<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $selectedClassId = $request->query('class_id');

        $query = Student::with(['user', 'schoolClass'])->latest();

        if (! empty($selectedClassId)) {
            $query->where('school_class_id', $selectedClassId);
        }

        $students = $query->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        $setting = AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'SMP Negeri 1 Garuda',
            'npsn' => '20102030',
            'level' => 'SMP',
        ]);

        return view('admin.students.index', compact('students', 'classes', 'selectedClassId', 'setting'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'school_class_id.required' => 'Pilih kelas siswa.',
            'gender.required' => 'Pilih jenis kelamin.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        // Email default otomatis dari NIS jika tidak diinput
        $email = $validated['email'] ?? ($validated['nis'].'@siswa.sekolah.sch.id');

        // Pastikan email unik jika fallback otomatis
        if (User::where('email', $email)->exists()) {
            $email = $validated['nis'].'.'.rand(100, 999).'@siswa.sekolah.sch.id';
        }

        // Generate QR code identifier permanen
        $qrCodeIdentifier = 'QR-'.$validated['nis'].'-'.strtoupper(bin2hex(random_bytes(3)));

        // Handle upload foto jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('students/photos', 'public');
        }

        DB::transaction(function () use ($validated, $email, $qrCodeIdentifier, $photoPath) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'school_class_id' => $validated['school_class_id'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'qr_code_identifier' => $qrCodeIdentifier,
                'gender' => $validated['gender'],
                'photo' => $photoPath,
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Data siswa berhasil ditambahkan ke kelas.');
        }

        return redirect()->route('admin.students.index')->with('success', 'Data siswa dan kartu QR Code berhasil dibuat.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis,'.$student->id],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn,'.$student->id],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$student->user_id],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'school_class_id.required' => 'Pilih kelas siswa.',
            'gender.required' => 'Pilih jenis kelamin.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        $photoPath = $student->photo;

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $photoPath = $request->file('photo')->store('students/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $photoPath = null;
        }

        DB::transaction(function () use ($validated, $student, $photoPath) {
            $userUpdate = ['name' => $validated['name']];
            if (! empty($validated['email'])) {
                $userUpdate['email'] = $validated['email'];
            }
            $student->user()->update($userUpdate);

            $student->update([
                'school_class_id' => $validated['school_class_id'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'gender' => $validated['gender'],
                'photo' => $photoPath,
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            Storage::disk('public')->delete($student->photo);
        }

        $user = $student->user;
        $student->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}
