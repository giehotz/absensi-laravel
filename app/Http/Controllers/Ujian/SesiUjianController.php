<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Ujian\UjianSesi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SesiUjianController extends Controller
{
    /**
     * Tambah master sesi ujian baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sesi_ke' => ['required', 'integer', 'min:1', 'unique:ujian_sesi,sesi_ke'],
            'nama_sesi' => ['required', 'string', 'max:100'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_menit' => ['nullable', 'integer', 'min:15'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'sesi_ke.unique' => 'Nomor urut sesi ini sudah ada.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ]);

        if (empty($validated['durasi_menit'])) {
            $start = Carbon::createFromFormat('H:i', $validated['jam_mulai']);
            $end = Carbon::createFromFormat('H:i', $validated['jam_selesai']);
            $validated['durasi_menit'] = (int) $start->diffInMinutes($end);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        UjianSesi::create($validated);

        return back()->with('success', "Master {$validated['nama_sesi']} berhasil ditambahkan.");
    }

    /**
     * Perbarui data master sesi ujian.
     */
    public function update(Request $request, UjianSesi $sesi): RedirectResponse
    {
        $validated = $request->validate([
            'sesi_ke' => ['required', 'integer', 'min:1', Rule::unique('ujian_sesi', 'sesi_ke')->ignore($sesi->id)],
            'nama_sesi' => ['required', 'string', 'max:100'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_menit' => ['nullable', 'integer', 'min:15'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'sesi_ke.unique' => 'Nomor urut sesi ini sudah ada.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ]);

        if (empty($validated['durasi_menit'])) {
            $start = Carbon::createFromFormat('H:i', $validated['jam_mulai']);
            $end = Carbon::createFromFormat('H:i', $validated['jam_selesai']);
            $validated['durasi_menit'] = (int) $start->diffInMinutes($end);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $sesi->update($validated);

        return back()->with('success', "Data {$sesi->nama_sesi} berhasil diperbarui.");
    }

    /**
     * Hapus master sesi ujian.
     */
    public function destroy(UjianSesi $sesi): RedirectResponse
    {
        $nama = $sesi->nama_sesi;
        $sesi->delete();

        return back()->with('success', "Master {$nama} berhasil dihapus.");
    }

    /**
     * Reset sesi ke pengaturan bawaan (Sesi 1: 07:30 - 09:30, Sesi 2: 10:00 - 12:00).
     */
    public function resetDefault(): RedirectResponse
    {
        UjianSesi::truncate();

        UjianSesi::insert([
            [
                'sesi_ke' => 1,
                'nama_sesi' => 'Sesi 1',
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
                'durasi_menit' => 120,
                'keterangan' => 'Sesi Pagi Utama',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sesi_ke' => 2,
                'nama_sesi' => 'Sesi 2',
                'jam_mulai' => '10:00:00',
                'jam_selesai' => '12:00:00',
                'durasi_menit' => 120,
                'keterangan' => 'Sesi Siang',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return back()->with('success', 'Pengaturan sesi berhasil direset ke standar bawaan (Sesi 1 & Sesi 2).');
    }
}
