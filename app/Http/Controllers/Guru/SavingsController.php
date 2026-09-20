<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\SavingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SavingsController extends Controller
{
    public function __construct(
        protected SavingsService $savingsService
    ) {}

    /**
     * Dashboard Pengelola Tabungan: Ringkasan kas, form transaksi cepat & transaksi terkini.
     */
    public function index(Request $request): View
    {
        $classes = $this->getAllowedClasses();
        $allowedClassIds = $this->getAllowedClassIds();
        $stats = $this->savingsService->getSummaryStats($allowedClassIds);

        $requestedClassId = (int) $request->query('class_id');
        if ($requestedClassId > 0 && $classes->contains('id', $requestedClassId)) {
            $selectedClassId = $requestedClassId;
        } else {
            $selectedClassId = (int) ($classes->first()?->id ?? 0);
        }

        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        // Filter & Sortir Siswa di Kelas Terpilih (Default: Penabung Aktif Saja)
        $studentSearch = trim((string) $request->query('student_search', ''));
        $statusFilter = (string) $request->query('status_filter', 'registered');
        $sort = (string) $request->query('sort', 'name_asc');

        $classStudents = collect();
        $withdrawnTotals = collect();
        $classStats = [
            'total_students' => 0,
            'registered_students' => 0,
            'total_class_balance' => 0,
        ];

        if ($selectedClassId > 0) {
            $studentsQuery = Student::with(['user', 'schoolClass', 'savingsAccount'])
                ->where('students.school_class_id', $selectedClassId)
                ->leftJoin('users', 'students.user_id', '=', 'users.id')
                ->leftJoin('savings_accounts', 'students.id', '=', 'savings_accounts.student_id')
                ->select('students.*');

            if ($studentSearch !== '') {
                $studentsQuery->where(function ($q) use ($studentSearch) {
                    $q->where('users.name', 'like', "%{$studentSearch}%")
                        ->orWhere('students.nis', 'like', "%{$studentSearch}%")
                        ->orWhere('students.nisn', 'like', "%{$studentSearch}%");
                });
            }

            if ($statusFilter === 'registered') {
                $studentsQuery->whereNotNull('savings_accounts.id');
            } elseif ($statusFilter === 'unregistered') {
                $studentsQuery->whereNull('savings_accounts.id');
            }

            match ($sort) {
                'name_desc' => $studentsQuery->orderBy('users.name', 'desc'),
                'balance_desc' => $studentsQuery->orderByRaw('COALESCE(savings_accounts.balance, 0) DESC'),
                'balance_asc' => $studentsQuery->orderByRaw('COALESCE(savings_accounts.balance, 0) ASC'),
                default => $studentsQuery->orderBy('users.name', 'asc'),
            };

            $classStudents = $studentsQuery->get();

            // Siswa yang belum terdaftar di kelas ini (untuk checklist popup modal)
            $unregisteredStudents = Student::where('students.school_class_id', $selectedClassId)
                ->whereDoesntHave('savingsAccount')
                ->with('user')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->orderBy('users.name')
                ->select('students.*')
                ->get();

            // Total penarikan per akun tabungan yang ada
            $accountIds = $classStudents->pluck('savingsAccount.id')->filter()->values();
            if ($accountIds->isNotEmpty()) {
                $withdrawnTotals = SavingsTransaction::where('type', 'withdrawal')
                    ->whereIn('savings_account_id', $accountIds)
                    ->groupBy('savings_account_id')
                    ->selectRaw('savings_account_id, SUM(amount) as total_withdrawn')
                    ->pluck('total_withdrawn', 'savings_account_id');
            }

            $allClassStudents = Student::with('savingsAccount')->where('school_class_id', $selectedClassId)->get();
            $classStats = [
                'total_students' => $allClassStudents->count(),
                'registered_students' => $allClassStudents->filter(fn ($s) => $s->savingsAccount !== null)->count(),
                'total_class_balance' => (float) $allClassStudents->sum(fn ($s) => (float) ($s->savingsAccount?->balance ?? 0)),
            ];
        } else {
            $unregisteredStudents = collect();
        }

        $selectedStudent = null;
        if ($request->filled('student_id')) {
            $selectedStudent = Student::with(['user', 'schoolClass', 'savingsAccount.transactions.handler'])
                ->find($request->query('student_id'));

            if ($selectedStudent) {
                // Pastikan akun tabungan hanya diakses jika siswa berada di kelas yang diizinkan
                if ($allowedClassIds !== null && ! in_array($selectedStudent->school_class_id, $allowedClassIds, true)) {
                    $selectedStudent = null;
                } else {
                    $this->savingsService->getOrCreateAccount($selectedStudent);
                    $selectedStudent->load('savingsAccount.transactions.handler');
                }
            }
        }

        $recentTransactionsQuery = SavingsTransaction::with(['savingsAccount.student.user', 'savingsAccount.student.schoolClass', 'handler'])
            ->orderByDesc('id');

        if ($allowedClassIds !== null) {
            $recentTransactionsQuery->whereHas('savingsAccount.student', function ($sq) use ($allowedClassIds) {
                $sq->whereIn('school_class_id', $allowedClassIds);
            });
        }

        $recentTransactions = $recentTransactionsQuery->limit(10)->get();

        return view('guru.tabungan.index', compact(
            'stats',
            'classes',
            'selectedClass',
            'selectedClassId',
            'classStudents',
            'unregisteredStudents',
            'withdrawnTotals',
            'classStats',
            'studentSearch',
            'statusFilter',
            'sort',
            'selectedStudent',
            'recentTransactions'
        ));
    }

    /**
     * Daftarkan satu siswa sebagai penabung (buat rekening tabungan manual).
     */
    public function registerStudent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::with('user')->findOrFail($validated['student_id']);
        $this->authorizeStudent($student);

        $account = $this->savingsService->getOrCreateAccount($student);

        return back()->with('success', "Buku tabungan untuk siswa {$student->user?->name} ({$account->account_number}) berhasil diaktifkan!");
    }

    /**
     * Daftarkan siswa terpilih secara selektif via modal checklist.
     */
    public function registerSelectedStudents(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
        ], [
            'student_ids.required' => 'Pilih minimal 1 siswa yang ingin didaftarkan.',
            'student_ids.min' => 'Pilih minimal 1 siswa yang ingin didaftarkan.',
        ]);

        $allowedClassIds = $this->getAllowedClassIds();
        $query = Student::with('user')->whereIn('id', $validated['student_ids']);
        if ($allowedClassIds !== null) {
            $query->whereIn('school_class_id', $allowedClassIds);
        }
        $students = $query->get();

        $count = 0;
        foreach ($students as $student) {
            $this->savingsService->getOrCreateAccount($student);
            $count++;
        }

        return back()->with('success', "Berhasil mendaftarkan {$count} siswa terpilih sebagai penabung aktif!");
    }

    /**
     * Daftarkan seluruh siswa dalam satu rombel/kelas sebagai penabung aktif secara massal.
     */
    public function registerClassStudents(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $allowedClassIds = $this->getAllowedClassIds();
        if ($allowedClassIds !== null && ! in_array((int) $validated['class_id'], $allowedClassIds, true)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengelola tabungan di kelas ini.');
        }

        $students = Student::where('school_class_id', $validated['class_id'])->get();
        $count = 0;

        foreach ($students as $student) {
            $this->savingsService->getOrCreateAccount($student);
            $count++;
        }

        $class = SchoolClass::find($validated['class_id']);

        return back()->with('success', "Berhasil mendaftarkan {$count} siswa di kelas {$class?->name} sebagai penabung aktif!");
    }

    /**
     * Batalkan pendaftaran penabung (hanya bisa jika riwayat transaksi masih 0 dan saldo Rp 0).
     */
    public function cancelRegistration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::with(['user', 'savingsAccount.transactions'])->findOrFail($validated['student_id']);
        $this->authorizeStudent($student);

        if (! $student->savingsAccount) {
            return back()->with('error', 'Siswa belum terdaftar sebagai penabung.');
        }

        try {
            $this->savingsService->cancelRegistration($student->savingsAccount);

            return back()->with('success', "Pendaftaran penabung untuk siswa {$student->user?->name} berhasil dibatalkan. Status siswa kembali menjadi Belum Terdaftar.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tutup buku tabungan / berhenti menabung:
     * Otomatis mencairkan sisa saldo (jika ada) dan mengubah status akun menjadi 'inactive'.
     */
    public function closeAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::with(['user', 'savingsAccount'])->findOrFail($validated['student_id']);
        $this->authorizeStudent($student);

        if (! $student->savingsAccount) {
            return back()->with('error', 'Siswa tidak memiliki akun tabungan.');
        }

        try {
            $finalTx = $this->savingsService->closeAccount($student->savingsAccount, $validated['reason'] ?? null, Auth::user());

            if ($finalTx) {
                return back()
                    ->with('success', "Buku tabungan {$student->user?->name} berhasil ditutup. Sisa saldo {$finalTx->formatted_amount} telah dicairkan.")
                    ->with('last_transaction_id', $finalTx->id);
            }

            return back()->with('success', "Buku tabungan {$student->user?->name} berhasil ditutup (Status: Tutup Buku).");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Buka kembali rekening tabungan yang ditutup.
     */
    public function reopenAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::with(['user', 'savingsAccount'])->findOrFail($validated['student_id']);
        $this->authorizeStudent($student);

        if (! $student->savingsAccount) {
            return back()->with('error', 'Siswa tidak memiliki akun tabungan.');
        }

        $this->savingsService->reopenAccount($student->savingsAccount);

        return back()->with('success', "Rekening tabungan {$student->user?->name} berhasil dibuka kembali (Aktif).");
    }

    /**
     * Endpoint API pencarian siswa untuk autocomplete form setor / tarik tunai.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $allowedClassIds = $this->getAllowedClassIds();

        $studentsQuery = Student::with(['user', 'schoolClass', 'savingsAccount'])
            ->where(function ($query) use ($q) {
                $query->where('nis', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%")
                    ->orWhere('qr_code_identifier', $q)
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%");
                    });
            });

        if ($allowedClassIds !== null) {
            $studentsQuery->whereIn('school_class_id', $allowedClassIds);
        }

        $students = $studentsQuery->limit(15)
            ->get()
            ->map(function ($student) {
                $account = $student->savingsAccount ?? $this->savingsService->getOrCreateAccount($student);

                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? '-',
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'class_name' => $student->schoolClass?->name ?? '-',
                    'account_number' => $account->account_number,
                    'balance' => (float) $account->balance,
                    'formatted_balance' => $account->formatted_balance,
                    'photo_url' => $student->photo_url,
                ];
            });

        return response()->json($students);
    }

    /**
     * Proses Setoran Tunai Tabungan.
     */
    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:500'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'student_id.required' => 'Pilih siswa terlebih dahulu.',
            'student_id.exists' => 'Data siswa tidak valid.',
            'amount.required' => 'Nominal setoran wajib diisi.',
            'amount.min' => 'Nominal setoran minimal Rp 500.',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $this->authorizeStudent($student);

        $account = $this->savingsService->getOrCreateAccount($student);

        try {
            $transaction = $this->savingsService->deposit(
                $account,
                (float) $validated['amount'],
                $validated['description'] ?? 'Setoran tunai tabungan',
                Auth::user()
            );

            return redirect()->route('guru.savings.index', ['student_id' => $student->id])
                ->with('success', "Setoran tunai sebesar {$transaction->formatted_amount} untuk {$student->user?->name} berhasil dicatat!")
                ->with('last_transaction_id', $transaction->id);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Proses Penarikan Tunai Tabungan.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:500'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'student_id.required' => 'Pilih siswa terlebih dahulu.',
            'student_id.exists' => 'Data siswa tidak valid.',
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.min' => 'Nominal penarikan minimal Rp 500.',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $this->authorizeStudent($student);

        $account = $this->savingsService->getOrCreateAccount($student);

        try {
            $transaction = $this->savingsService->withdraw(
                $account,
                (float) $validated['amount'],
                $validated['description'] ?? 'Penarikan tunai tabungan',
                Auth::user()
            );

            return redirect()->route('guru.savings.index', ['student_id' => $student->id])
                ->with('success', "Penarikan tunai sebesar {$transaction->formatted_amount} untuk {$student->user?->name} berhasil diproses!")
                ->with('last_transaction_id', $transaction->id);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Daftar Riwayat Seluruh Transaksi Tabungan dengan Filter.
     */
    public function transactions(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type', 'all');
        $classId = $request->query('class_id', '');
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');

        $allowedClassIds = $this->getAllowedClassIds();

        $query = SavingsTransaction::with(['savingsAccount.student.user', 'savingsAccount.student.schoolClass', 'handler'])
            ->orderByDesc('id');

        if ($allowedClassIds !== null) {
            $query->whereHas('savingsAccount.student', function ($sq) use ($allowedClassIds) {
                $sq->whereIn('school_class_id', $allowedClassIds);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('savingsAccount.student.user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('savingsAccount.student', function ($sq) use ($search) {
                        $sq->where('nis', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    })
                    ->orWhereHas('savingsAccount', function ($aq) use ($search) {
                        $aq->where('account_number', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($type, ['deposit', 'withdrawal'])) {
            $query->where('type', $type);
        }

        if (! empty($classId)) {
            if ($allowedClassIds === null || in_array((int) $classId, $allowedClassIds, true)) {
                $query->whereHas('savingsAccount.student', function ($sq) use ($classId) {
                    $sq->where('school_class_id', $classId);
                });
            }
        }

        if (! empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $classes = $this->getAllowedClasses();

        return view('guru.tabungan.transactions', compact(
            'transactions',
            'classes',
            'search',
            'type',
            'classId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Cetak Slip / Kuitansi Transaksi Tabungan.
     */
    public function receipt(SavingsTransaction $transaction): View
    {
        $transaction->load(['savingsAccount.student.user', 'savingsAccount.student.schoolClass', 'handler']);

        if ($transaction->savingsAccount?->student) {
            $this->authorizeStudent($transaction->savingsAccount->student);
        }

        return view('guru.tabungan.receipt', compact('transaction'));
    }

    /**
     * Export Riwayat Transaksi ke Excel/CSV.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');
        $classId = $request->query('class_id', '');
        $type = $request->query('type', 'all');

        $allowedClassIds = $this->getAllowedClassIds();

        $query = SavingsTransaction::with(['savingsAccount.student.user', 'savingsAccount.student.schoolClass', 'handler'])
            ->orderByDesc('id');

        if ($allowedClassIds !== null) {
            $query->whereHas('savingsAccount.student', function ($sq) use ($allowedClassIds) {
                $sq->whereIn('school_class_id', $allowedClassIds);
            });
        }

        if (in_array($type, ['deposit', 'withdrawal'])) {
            $query->where('type', $type);
        }

        if (! empty($classId)) {
            if ($allowedClassIds === null || in_array((int) $classId, $allowedClassIds, true)) {
                $query->whereHas('savingsAccount.student', function ($sq) use ($classId) {
                    $sq->where('school_class_id', $classId);
                });
            }
        }

        if (! empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();
        $fileName = 'Rekap_Tabungan_Siswa_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transactions) {
            $output = fopen('php://output', 'w');
            // Menulis UTF-8 BOM untuk Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'No',
                'Kode Transaksi',
                'Tanggal & Waktu',
                'Nama Siswa',
                'NIS',
                'NISN',
                'Kelas',
                'No. Rekening',
                'Jenis Transaksi',
                'Nominal (Rp)',
                'Saldo Sebelum (Rp)',
                'Saldo Sesudah (Rp)',
                'Keterangan',
                'Petugas (Guru Pengelola)',
            ], ';');

            foreach ($transactions as $index => $tx) {
                $student = $tx->savingsAccount?->student;
                fputcsv($output, [
                    $index + 1,
                    $tx->transaction_code,
                    $tx->created_at->format('d/m/Y H:i:s'),
                    $student?->user?->name ?? '-',
                    $student?->nis ?? '-',
                    $student?->nisn ?? '-',
                    $student?->schoolClass?->name ?? '-',
                    $tx->savingsAccount?->account_number ?? '-',
                    $tx->type_label,
                    number_format((float) $tx->amount, 0, ',', '.'),
                    number_format((float) $tx->balance_before, 0, ',', '.'),
                    number_format((float) $tx->balance_after, 0, ',', '.'),
                    $tx->description ?? '-',
                    $tx->handler?->name ?? '-',
                ], ';');
            }

            fclose($output);
        }, 200, $headers);
    }

    /**
     * Dapatkan daftar rombel/kelas yang diizinkan untuk dikelola user saat ini.
     * Admin: seluruh kelas.
     * Guru Pengelola: jika scope 'all' -> seluruh kelas, jika 'restricted' -> hanya kelas di managedSavingsClasses.
     */
    protected function getAllowedClasses()
    {
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            return SchoolClass::withCount('students')->orderBy('level')->orderBy('name')->get();
        }

        $teacher = $user?->teacher;

        if ($teacher && $teacher->managesAllSavingsClasses()) {
            return SchoolClass::withCount('students')->orderBy('level')->orderBy('name')->get();
        }

        if ($teacher) {
            return $teacher->managedSavingsClasses()->withCount('students')->orderBy('level')->orderBy('name')->get();
        }

        return collect();
    }

    /**
     * Dapatkan array ID kelas yang diizinkan, atau null jika tidak dibatasi (Semua Kelas / Admin).
     */
    protected function getAllowedClassIds(): ?array
    {
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            return null;
        }

        $teacher = $user?->teacher;

        if ($teacher && $teacher->managesAllSavingsClasses()) {
            return null;
        }

        if ($teacher) {
            return $teacher->managedSavingsClasses()->pluck('school_classes.id')->all();
        }

        return [];
    }

    /**
     * Validasi otorisasi siswa terhadap rombel/kelas yang diizinkan.
     */
    protected function authorizeStudent(Student $student): void
    {
        $allowedIds = $this->getAllowedClassIds();

        if ($allowedIds !== null && ! in_array($student->school_class_id, $allowedIds, true)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengelola data tabungan siswa dari kelas ini.');
        }
    }
}
