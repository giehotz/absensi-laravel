<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Tampilkan halaman Profil Guru beserta penugasan & jadwal mengajar.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        $homeroomClasses = $teacher->homeroomClasses()->with('academicYear')->get();

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $daysMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        // Kelompokkan jadwal berdasarkan hari
        $schedulesByDay = [];
        foreach ($schedules as $sched) {
            $dayName = $daysMap[$sched->day_of_week] ?? 'Hari '.$sched->day_of_week;
            $schedulesByDay[$dayName][] = $sched;
        }

        return view('guru.profile.index', compact(
            'user',
            'teacher',
            'homeroomClasses',
            'schedules',
            'schedulesByDay',
            'daysMap'
        ));
    }

    /**
     * Perbarui biodata guru (Nama, Email, No. HP, Foto Profil).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photo_cropped' => ['nullable', 'string'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format foto yang didukung: JPG, JPEG, PNG, dan WEBP.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->boolean('remove_photo')) {
            $this->imageUploadService->deleteOldFile($teacher->photo);
            $teacher->photo = null;
        } elseif ($request->filled('photo_cropped') && str_starts_with($request->input('photo_cropped'), 'data:image/')) {
            $this->imageUploadService->deleteOldFile($teacher->photo);
            $teacher->photo = $this->imageUploadService->uploadBase64AsWebp(
                $request->input('photo_cropped'),
                'teachers',
                82,
                800,
                1000
            );
        } elseif ($request->hasFile('photo')) {
            $this->imageUploadService->deleteOldFile($teacher->photo);
            $teacher->photo = $this->imageUploadService->uploadAsWebp(
                $request->file('photo'),
                'teachers',
                82,
                800,
                1000
            );
        }

        $teacher->phone = $validated['phone'];
        $teacher->save();

        return back()->with('success', 'Biodata profil berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun guru.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
