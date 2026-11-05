<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianPeriode;
use App\Services\Ujian\JadwalConflictService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalUjianController extends Controller
{
    public function __construct(
        protected JadwalConflictService $conflictService
    ) {}

    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $jadwals = collect();
        if ($activePeriode) {
            $jadwals = UjianJadwal::with(['subject', 'schoolClass', 'pengawas.ruang'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();
        }

        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();

        return view('ujian.jadwal.index', compact('periodes', 'activePeriode', 'jadwals', 'subjects', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'nama_mapel_kustom' => ['nullable', 'string', 'max:150'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'tingkat' => ['nullable', 'string', 'max:30'],
            'tanggal_ujian' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'sesi_ke' => ['required', 'integer', 'min:1'],
            'durasi_menit' => ['required', 'integer', 'min:15'],
            'jenis_soal' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['subject_id']) && empty($validated['nama_mapel_kustom'])) {
            return back()->withInput()->with('error', 'Pilih mata pelajaran atau isi nama mata pelajaran kustom.');
        }

        // Cek Bentrok Jadwal Kelas
        $conflict = $this->conflictService->checkClassScheduleConflict(
            (int) $validated['ujian_periode_id'],
            $validated['school_class_id'] ? (int) $validated['school_class_id'] : null,
            $validated['tingkat'] ?? null,
            $validated['tanggal_ujian'],
            $validated['jam_mulai'],
            $validated['jam_selesai']
        );

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Bentrok Jadwal: Rombel/Tingkat tersebut sudah memiliki jadwal '{$conflict->nama_mapel}' pada tanggal {$conflict->tanggal_ujian->format('d/m/Y')} pukul {$conflict->jam_mulai} - {$conflict->jam_selesai}."
            );
        }

        UjianJadwal::create($validated);

        return back()->with('success', 'Jadwal Ujian berhasil ditambahkan.');
    }

    public function update(Request $request, UjianJadwal $jadwal): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'nama_mapel_kustom' => ['nullable', 'string', 'max:150'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'tingkat' => ['nullable', 'string', 'max:30'],
            'tanggal_ujian' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'sesi_ke' => ['required', 'integer', 'min:1'],
            'durasi_menit' => ['required', 'integer', 'min:15'],
            'jenis_soal' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $conflict = $this->conflictService->checkClassScheduleConflict(
            $jadwal->ujian_periode_id,
            $validated['school_class_id'] ? (int) $validated['school_class_id'] : null,
            $validated['tingkat'] ?? null,
            $validated['tanggal_ujian'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $jadwal->id
        );

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Bentrok Jadwal: Rombel/Tingkat tersebut sudah memiliki jadwal '{$conflict->nama_mapel}' pada tanggal {$conflict->tanggal_ujian->format('d/m/Y')} pukul {$conflict->jam_mulai} - {$conflict->jam_selesai}."
            );
        }

        $jadwal->update($validated);

        return back()->with('success', 'Jadwal Ujian berhasil diperbarui.');
    }

    public function copySchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'source_class_id' => ['required', 'exists:school_classes,id'],
            'target_class_ids' => ['required', 'array'],
            'target_class_ids.*' => ['exists:school_classes,id'],
        ]);

        $sourceSchedules = UjianJadwal::where('ujian_periode_id', $validated['ujian_periode_id'])
            ->where('school_class_id', $validated['source_class_id'])
            ->get();

        if ($sourceSchedules->isEmpty()) {
            return back()->with('error', 'Tidak ada jadwal pada rombel sumber yang dipilih.');
        }

        $copiedCount = 0;
        foreach ($validated['target_class_ids'] as $targetClassId) {
            if ($targetClassId == $validated['source_class_id']) {
                continue;
            }

            foreach ($sourceSchedules as $source) {
                // Periksa apakah jadwal sudah ada
                $exists = UjianJadwal::where('ujian_periode_id', $source->ujian_periode_id)
                    ->where('school_class_id', $targetClassId)
                    ->where('subject_id', $source->subject_id)
                    ->where('tanggal_ujian', $source->tanggal_ujian)
                    ->exists();

                if (! $exists) {
                    $newSchedule = $source->replicate();
                    $newSchedule->school_class_id = $targetClassId;
                    $newSchedule->save();
                    $copiedCount++;
                }
            }
        }

        return back()->with('success', "Berhasil menduplikasi {$copiedCount} slot jadwal ke rombel tujuan.");
    }

    public function destroy(UjianJadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal Ujian berhasil dihapus.');
    }

    public function print(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $jadwals = collect();
        if ($activePeriode) {
            $jadwals = UjianJadwal::with(['subject', 'schoolClass'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();
        }

        $setting = AttendanceSetting::first();

        return view('ujian.jadwal.print', compact('activePeriode', 'jadwals', 'setting'));
    }
}
