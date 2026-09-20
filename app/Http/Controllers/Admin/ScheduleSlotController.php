<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SlotTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleSlotController extends Controller
{
    public const DAYS_MAP = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    /**
     * Tampilkan matriks grid interaktif Hari x Jam 1-12 ala EMIS GTK.
     */
    public function index(Request $request): View
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $academicYear?->id;

        $existingSlots = SlotTemplate::where('academic_year_id', $academicYearId)
            ->orderBy('day_of_week')
            ->orderBy('jam_ke')
            ->get();

        // Jika belum ada template sama sekali, siapkan data default
        $slotsByDay = [];
        $defaultPresets = SlotTemplate::getDefaultMadrasahSlots();

        foreach (self::DAYS_MAP as $dayNum => $dayName) {
            $daySlots = $existingSlots->where('day_of_week', $dayNum)->values();

            if ($daySlots->isEmpty() && isset($defaultPresets[$dayNum])) {
                // Tampilkan preview default jika database masih kosong
                $slotsByDay[$dayNum] = array_map(function ($s) use ($dayNum) {
                    return [
                        'id' => null,
                        'day_of_week' => $dayNum,
                        'jam_ke' => $s['jam_ke'],
                        'k_jadwal' => $s['k_jadwal'],
                        'name' => $s['name'],
                        'start_time' => $s['start'],
                        'end_time' => $s['end'],
                    ];
                }, $defaultPresets[$dayNum]);
            } else {
                $slotsByDay[$dayNum] = $daySlots->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'day_of_week' => $s->day_of_week,
                        'jam_ke' => $s->jam_ke,
                        'k_jadwal' => $s->k_jadwal,
                        'name' => $s->name,
                        'start_time' => $s->getShortStartTime(),
                        'end_time' => $s->getShortEndTime(),
                    ];
                })->all();
            }
        }

        $kegiatanLabels = SlotTemplate::KEGIATAN_LABELS;
        $kegiatanColors = SlotTemplate::KEGIATAN_COLORS;

        return view('admin.schedules.slots', compact(
            'academicYear',
            'slotsByDay',
            'kegiatanLabels',
            'kegiatanColors'
        ));
    }

    /**
     * Simpan pembaruan slot template via JSON dengan Conflict Detection & Auto-Sync.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'slots.*.jam_ke' => ['required', 'integer', 'between:1,12'],
            'slots.*.k_jadwal' => ['required', 'integer', 'in:0,3,4,5,6,7'],
            'slots.*.start_time' => ['required', 'string'],
            'slots.*.end_time' => ['required', 'string'],
            'slots.*.name' => ['nullable', 'string', 'max:100'],
            'force_sync' => ['nullable', 'boolean'],
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $academicYear?->id;
        $forceSync = $request->boolean('force_sync', false);

        $slots = $validated['slots'];

        // --- CONFLICT DETECTION ---
        // Deteksi jika ada jadwal kelas aktif di tabel `schedules` yang bentrok dengan perubahan ini
        $conflicts = [];

        // Ambil semua jadwal kelas yang ada
        $existingSchedules = Schedule::with(['schoolClass', 'subject', 'teacher.user'])->get();

        foreach ($slots as $slot) {
            $day = (int) $slot['day_of_week'];
            $newKategori = (int) $slot['k_jadwal'];
            $slotStart = substr($slot['start_time'], 0, 5);
            $slotEnd = substr($slot['end_time'], 0, 5);
            $slotName = $slot['name'] ?: (SlotTemplate::KEGIATAN_LABELS[$newKategori] ?? 'Slot');

            // Cari jadwal pada hari yang sama yang beririsan waktu
            $overlappingSchedules = $existingSchedules->where('day_of_week', $day)->filter(function ($sched) use ($slotStart, $slotEnd) {
                $schedStart = substr((string) $sched->start_time, 0, 5);
                $schedEnd = substr((string) $sched->end_time, 0, 5);

                return ($schedStart < $slotEnd) && ($schedEnd > $slotStart);
            });

            foreach ($overlappingSchedules as $sched) {
                $schedStart = substr((string) $sched->start_time, 0, 5);
                $schedEnd = substr((string) $sched->end_time, 0, 5);

                // Konflik 1: Slot ini sekarang bertipe Non-KBM (Upacara, Istirahat, dll), tapi ada pelajaran aktif
                if ($newKategori !== SlotTemplate::K_KBM) {
                    $conflicts[] = [
                        'schedule_id' => $sched->id,
                        'class_name' => $sched->schoolClass?->name ?? 'Kelas',
                        'subject_name' => $sched->subject?->name ?? 'Mapel',
                        'teacher_name' => $sched->teacher?->user?->name ?? 'Guru',
                        'day' => self::DAYS_MAP[$day] ?? 'Hari',
                        'current_time' => "{$schedStart} - {$schedEnd}",
                        'slot_time' => "{$slotStart} - {$slotEnd}",
                        'type' => 'non_kbm_clash',
                        'description' => "Jadwal bertabrakan dengan kegiatan [{$slotName}] ({$slotStart} - {$slotEnd}).",
                    ];
                }
            }
        }

        // Jika ditemukan konflik dan operator belum menyetujui force_sync
        if (! empty($conflicts) && ! $forceSync) {
            return response()->json([
                'status' => 'conflict',
                'action_required' => 'resolve_conflicts',
                'conflict_count' => count($conflicts),
                'conflicts' => array_values($conflicts),
                'message' => 'Ditemukan '.count($conflicts).' bentrok antara template slot baru dengan jadwal pelajaran yang sudah ada.',
            ], 422);
        }

        // --- SIMPAN TEMPLATE & SINKRONISASI ---
        DB::transaction(function () use ($slots, $academicYearId, $forceSync, $conflicts) {
            // Hapus slot lama untuk tahun ajaran ini lalu re-insert data baru yang bersih
            SlotTemplate::where('academic_year_id', $academicYearId)->delete();

            foreach ($slots as $slot) {
                SlotTemplate::create([
                    'academic_year_id' => $academicYearId,
                    'day_of_week' => (int) $slot['day_of_week'],
                    'jam_ke' => (int) $slot['jam_ke'],
                    'k_jadwal' => (int) $slot['k_jadwal'],
                    'name' => $slot['name'] ?? null,
                    'start_time' => substr($slot['start_time'], 0, 5).':00',
                    'end_time' => substr($slot['end_time'], 0, 5).':00',
                ]);
            }

            // Jika forceSync aktif dan terdapat jadwal yang bentrok dengan jam non-KBM,
            // kita hapus jadwal yang menimpa upacara/istirahat
            if ($forceSync && ! empty($conflicts)) {
                $conflictIds = collect($conflicts)->pluck('schedule_id')->unique()->filter();
                if ($conflictIds->isNotEmpty()) {
                    Schedule::whereIn('id', $conflictIds)->delete();
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Template slot jadwal KBM berhasil disimpan!',
        ]);
    }

    /**
     * Muat ulang template default bawaan madrasah (1-click reset).
     */
    public function resetDefault(Request $request): RedirectResponse
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $academicYear?->id;

        $defaultSchedule = SlotTemplate::getDefaultMadrasahSlots();

        DB::transaction(function () use ($defaultSchedule, $academicYearId) {
            SlotTemplate::where('academic_year_id', $academicYearId)->delete();

            foreach ($defaultSchedule as $day => $slots) {
                foreach ($slots as $slot) {
                    SlotTemplate::create([
                        'academic_year_id' => $academicYearId,
                        'day_of_week' => $day,
                        'jam_ke' => $slot['jam_ke'],
                        'k_jadwal' => $slot['k_jadwal'],
                        'name' => $slot['name'],
                        'start_time' => $slot['start'].':00',
                        'end_time' => $slot['end'].':00',
                    ]);
                }
            }
        });

        return redirect()->route('admin.schedules.slots.index')
            ->with('success', 'Template slot jam KBM berhasil direset ke standar Madrasah/Kemenag.');
    }
}
