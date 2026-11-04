<?php

namespace App\Http\Controllers\Mbg;

use App\Http\Controllers\Controller;
use App\Models\MbgDistribution;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\MbgReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MbgDistributionController extends Controller
{
    public function __construct(
        protected MbgReportService $reportService
    ) {}

    /**
     * Dashboard Petugas MBG & Daftar Distribusi Terkini.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $todayDistribution = MbgDistribution::with('classDistributions.schoolClass')
            ->whereDate('date', $today)
            ->first();

        $now = Carbon::now();
        $monthStats = $this->reportService->getSummaryStats($now->copy()->startOfMonth(), $now->copy()->endOfMonth());

        $recentDistributions = MbgDistribution::with(['recordedByUser', 'classDistributions.schoolClass'])
            ->orderByDesc('date')
            ->paginate(15);

        return view('mbg.dashboard', compact(
            'todayDistribution',
            'monthStats',
            'recentDistributions'
        ));
    }

    /**
     * Form Catat Distribusi MBG Baru Berdasarkan Daftar Kelas.
     */
    public function create(): View
    {
        $classes = SchoolClass::withCount(['students' => function ($q) {
            $q->whereHas('user', function ($uq) {
                $uq->where('role', 'siswa');
            });
        }])->orderBy('name')->get();

        $defaultTargetTeachers = Teacher::count();
        $defaultDate = Carbon::today()->format('Y-m-d');

        return view('mbg.form', [
            'distribution' => new MbgDistribution([
                'date' => $defaultDate,
                'target_teachers' => $defaultTargetTeachers,
                'distributed_teachers' => $defaultTargetTeachers,
                'leftover_portions' => 0,
                'status' => 'selesai',
            ]),
            'classes' => $classes,
            'isEdit' => false,
            'existingClassDistributions' => collect(),
        ]);
    }

    /**
     * Simpan catatan distribusi MBG per kelas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'unique:mbg_distributions,date'],
            'menu_name' => ['required', 'string', 'max:255'],
            'target_teachers' => ['required', 'integer', 'min:0'],
            'distributed_teachers' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:selesai,sebagian,dibatalkan'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'documentation_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*.school_class_id' => ['required', 'exists:school_classes,id'],
            'classes.*.target_portions' => ['required', 'integer', 'min:0'],
            'classes.*.distributed_portions' => ['required', 'integer', 'min:0'],
            'classes.*.leftover_portions' => ['nullable', 'integer', 'min:0'],
            'classes.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'date.unique' => 'Distribusi untuk tanggal ini sudah pernah dicatat. Silakan edit data yang sudah ada.',
            'documentation_image.max' => 'Ukuran foto maksimal adalah 3 MB.',
            'classes.required' => 'Daftar kelas wajib diisi.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $totalTargetStudents = 0;
            $totalDistStudents = 0;
            $totalClassLeftovers = 0;

            foreach ($validated['classes'] as $item) {
                $target = (int) $item['target_portions'];
                $dist = (int) $item['distributed_portions'];
                $leftover = isset($item['leftover_portions']) ? (int) $item['leftover_portions'] : max(0, $target - $dist);

                $totalTargetStudents += $target;
                $totalDistStudents += $dist;
                $totalClassLeftovers += $leftover;
            }

            $teacherLeftovers = max(0, (int) $validated['target_teachers'] - (int) $validated['distributed_teachers']);
            $totalLeftovers = $totalClassLeftovers + $teacherLeftovers;

            $imagePath = null;
            if ($request->hasFile('documentation_image')) {
                $imagePath = $request->file('documentation_image')->store('mbg_documentation', 'public');
            }

            $distribution = MbgDistribution::create([
                'date' => $validated['date'],
                'menu_name' => $validated['menu_name'],
                'target_students' => $totalTargetStudents,
                'distributed_students' => $totalDistStudents,
                'target_teachers' => $validated['target_teachers'],
                'distributed_teachers' => $validated['distributed_teachers'],
                'leftover_portions' => $totalLeftovers,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'documentation_image' => $imagePath,
                'recorded_by' => Auth::id(),
            ]);

            foreach ($validated['classes'] as $item) {
                $target = (int) $item['target_portions'];
                $dist = (int) $item['distributed_portions'];
                $leftover = isset($item['leftover_portions']) ? (int) $item['leftover_portions'] : max(0, $target - $dist);

                $distribution->classDistributions()->create([
                    'school_class_id' => $item['school_class_id'],
                    'target_portions' => $target,
                    'distributed_portions' => $dist,
                    'leftover_portions' => $leftover,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('mbg.dashboard')->with('success', 'Catatan distribusi MBG per kelas berhasil disimpan.');
    }

    /**
     * Form Koreksi / Edit Distribusi MBG.
     */
    public function edit(MbgDistribution $distribution): View
    {
        $distribution->load('classDistributions.schoolClass');
        $existingClassDistributions = $distribution->classDistributions->keyBy('school_class_id');

        $classes = SchoolClass::withCount(['students' => function ($q) {
            $q->whereHas('user', function ($uq) {
                $uq->where('role', 'siswa');
            });
        }])->orderBy('name')->get();

        return view('mbg.form', [
            'distribution' => $distribution,
            'classes' => $classes,
            'isEdit' => true,
            'existingClassDistributions' => $existingClassDistributions,
        ]);
    }

    /**
     * Perbarui data distribusi MBG.
     */
    public function update(Request $request, MbgDistribution $distribution): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'unique:mbg_distributions,date,'.$distribution->id],
            'menu_name' => ['required', 'string', 'max:255'],
            'target_teachers' => ['required', 'integer', 'min:0'],
            'distributed_teachers' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:selesai,sebagian,dibatalkan'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'documentation_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*.school_class_id' => ['required', 'exists:school_classes,id'],
            'classes.*.target_portions' => ['required', 'integer', 'min:0'],
            'classes.*.distributed_portions' => ['required', 'integer', 'min:0'],
            'classes.*.leftover_portions' => ['nullable', 'integer', 'min:0'],
            'classes.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'date.unique' => 'Distribusi untuk tanggal ini sudah pernah dicatat pada data lain.',
            'documentation_image.max' => 'Ukuran foto maksimal adalah 3 MB.',
            'classes.required' => 'Daftar kelas wajib diisi.',
        ]);

        DB::transaction(function () use ($request, $validated, $distribution) {
            $totalTargetStudents = 0;
            $totalDistStudents = 0;
            $totalClassLeftovers = 0;

            foreach ($validated['classes'] as $item) {
                $target = (int) $item['target_portions'];
                $dist = (int) $item['distributed_portions'];
                $leftover = isset($item['leftover_portions']) ? (int) $item['leftover_portions'] : max(0, $target - $dist);

                $totalTargetStudents += $target;
                $totalDistStudents += $dist;
                $totalClassLeftovers += $leftover;
            }

            $teacherLeftovers = max(0, (int) $validated['target_teachers'] - (int) $validated['distributed_teachers']);
            $totalLeftovers = $totalClassLeftovers + $teacherLeftovers;

            $imagePath = $distribution->documentation_image;
            if ($request->hasFile('documentation_image')) {
                if ($distribution->documentation_image && Storage::disk('public')->exists($distribution->documentation_image)) {
                    Storage::disk('public')->delete($distribution->documentation_image);
                }
                $imagePath = $request->file('documentation_image')->store('mbg_documentation', 'public');
            }

            $distribution->update([
                'date' => $validated['date'],
                'menu_name' => $validated['menu_name'],
                'target_students' => $totalTargetStudents,
                'distributed_students' => $totalDistStudents,
                'target_teachers' => $validated['target_teachers'],
                'distributed_teachers' => $validated['distributed_teachers'],
                'leftover_portions' => $totalLeftovers,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'documentation_image' => $imagePath,
            ]);

            // Sync per-class records
            $distribution->classDistributions()->delete();
            foreach ($validated['classes'] as $item) {
                $target = (int) $item['target_portions'];
                $dist = (int) $item['distributed_portions'];
                $leftover = isset($item['leftover_portions']) ? (int) $item['leftover_portions'] : max(0, $target - $dist);

                $distribution->classDistributions()->create([
                    'school_class_id' => $item['school_class_id'],
                    'target_portions' => $target,
                    'distributed_portions' => $dist,
                    'leftover_portions' => $leftover,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('mbg.dashboard')->with('success', 'Data distribusi MBG per kelas berhasil diperbarui.');
    }

    /**
     * Hapus data catatan distribusi.
     */
    public function destroy(MbgDistribution $distribution): RedirectResponse
    {
        if ($distribution->documentation_image && Storage::disk('public')->exists($distribution->documentation_image)) {
            Storage::disk('public')->delete($distribution->documentation_image);
        }

        $distribution->delete();

        return redirect()->route('mbg.dashboard')->with('success', 'Catatan distribusi MBG berhasil dihapus.');
    }
}
