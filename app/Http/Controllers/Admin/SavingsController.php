<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\SavingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SavingsController extends Controller
{
    public function __construct(
        protected SavingsService $savingsService
    ) {}

    /**
     * Hub Monitoring & Kontrol Menyeluruh Tabungan Siswa (Admin).
     */
    public function index(Request $request): View
    {
        // 1. Ringkasan Kas Global Sekolah
        $stats = $this->savingsService->getSummaryStats();

        // 2. Rekapitulasi Kas Per Rombel / Kelas
        $classes = SchoolClass::with(['homeroomTeacher.user'])
            ->withCount('students')
            ->withCount(['students as active_savings_count' => function ($q) {
                $q->whereHas('savingsAccount', fn ($aq) => $aq->where('status', 'active'));
            }])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $classBalances = Student::join('savings_accounts', 'students.id', '=', 'savings_accounts.student_id')
            ->groupBy('students.school_class_id')
            ->selectRaw('students.school_class_id, SUM(savings_accounts.balance) as total_balance')
            ->pluck('total_balance', 'school_class_id');

        // 3. Guru Pengelola Tabungan Aktif
        $savingsOfficers = Teacher::with(['user', 'managedSavingsClasses'])
            ->where('is_savings_officer', true)
            ->get();

        $handledCounts = SavingsTransaction::groupBy('handled_by')
            ->selectRaw('handled_by, COUNT(*) as total')
            ->pluck('total', 'handled_by');

        foreach ($savingsOfficers as $officer) {
            $officer->handled_transactions_count = $handledCounts[$officer->user_id] ?? 0;
        }

        // 4. Audit Log Seluruh Mutasi Transaksi
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type', 'all');
        $classId = $request->query('class_id', '');
        $handlerId = $request->query('handler_id', '');
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');

        $transactionsQuery = SavingsTransaction::with([
            'savingsAccount.student.user',
            'savingsAccount.student.schoolClass',
            'handler',
        ])->orderByDesc('id');

        if ($search !== '') {
            $transactionsQuery->where(function ($q) use ($search) {
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
            $transactionsQuery->where('type', $type);
        }

        if (! empty($classId)) {
            $transactionsQuery->whereHas('savingsAccount.student', function ($sq) use ($classId) {
                $sq->where('school_class_id', $classId);
            });
        }

        if (! empty($handlerId)) {
            $transactionsQuery->where('handled_by', $handlerId);
        }

        if (! empty($startDate)) {
            $transactionsQuery->whereDate('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $transactionsQuery->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $transactionsQuery->paginate(20)->withQueryString();

        return view('admin.savings.index', compact(
            'stats',
            'classes',
            'classBalances',
            'savingsOfficers',
            'transactions',
            'search',
            'type',
            'classId',
            'handlerId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Endpoint data detail siswa di kelas tertentu (untuk modal popup).
     */
    public function classStudents(SchoolClass $class): JsonResponse
    {
        $class->load(['homeroomTeacher.user']);

        $students = Student::with(['user', 'savingsAccount'])
            ->where('school_class_id', $class->id)
            ->leftJoin('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user?->name ?? '-',
                'nis' => $s->nis,
                'nisn' => $s->nisn,
                'is_registered' => $s->savingsAccount !== null,
                'account_number' => $s->savingsAccount?->account_number ?? '-',
                'balance' => (float) ($s->savingsAccount?->balance ?? 0),
                'formatted_balance' => 'Rp '.number_format((float) ($s->savingsAccount?->balance ?? 0), 0, ',', '.'),
                'status' => $s->savingsAccount?->status ?? 'unregistered',
                'status_label' => $s->savingsAccount ? $s->savingsAccount->status_label : 'Belum Terdaftar',
            ]);

        $totalBalance = (float) $students->sum('balance');
        $registeredCount = $students->where('is_registered', true)->count();

        return response()->json([
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'level' => $class->level,
                'homeroom_teacher' => $class->homeroomTeacher?->user?->name ?? '-',
                'total_students' => $students->count(),
                'registered_count' => $registeredCount,
                'total_balance' => $totalBalance,
                'formatted_total_balance' => 'Rp '.number_format($totalBalance, 0, ',', '.'),
            ],
            'students' => $students->values(),
        ]);
    }

    /**
     * Cetak Slip / Kuitansi Transaksi Tabungan (Admin).
     */
    public function receipt(SavingsTransaction $transaction): View
    {
        $transaction->load(['savingsAccount.student.user', 'savingsAccount.student.schoolClass', 'handler']);

        return view('guru.tabungan.receipt', compact('transaction'));
    }

    /**
     * Ekspor Seluruh Mutasi Transaksi Tabungan Sekolah ke Excel/CSV.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');
        $classId = $request->query('class_id', '');
        $type = $request->query('type', 'all');
        $handlerId = $request->query('handler_id', '');

        $query = SavingsTransaction::with(['savingsAccount.student.user', 'savingsAccount.student.schoolClass', 'handler'])
            ->orderByDesc('id');

        if (in_array($type, ['deposit', 'withdrawal'])) {
            $query->where('type', $type);
        }

        if (! empty($classId)) {
            $query->whereHas('savingsAccount.student', function ($sq) use ($classId) {
                $sq->where('school_class_id', $classId);
            });
        }

        if (! empty($handlerId)) {
            $query->where('handled_by', $handlerId);
        }

        if (! empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();
        $fileName = 'Rekap_Audit_Tabungan_Sekolah_'.now()->format('Y-m-d_His').'.csv';

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
}
