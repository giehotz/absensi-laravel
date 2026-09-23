<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SavingsController extends Controller
{
    /**
     * Tampilkan halaman mandiri Tabungan Siswa lengkap dengan visualisasi grafik,
     * metrik performa mingguan, dan gatekeeper status rekening.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        if ($student) {
            $student->load(['schoolClass', 'savingsAccount']);
        }

        $savingsAccount = $student?->savingsAccount;
        $isEnrolled = $savingsAccount && $savingsAccount->isActive();

        // -------------------------------------------------------------
        // State A: Siswa Belum Terdaftar Menabung
        // -------------------------------------------------------------
        if (! $isEnrolled) {
            $savingsOfficer = Teacher::where('is_savings_officer', true)->with('user')->first();
            $officerName = $savingsOfficer?->user?->name ?? 'Petugas Tabungan Sekolah';
            $officerPhone = $savingsOfficer?->phone ?? '';

            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $officerPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62'.substr($cleanPhone, 1);
            }

            $studentName = $student?->user?->name ?? $user->name;
            $studentNis = $student?->nis ?? '-';
            $className = $student?->schoolClass?->name ?? '-';

            $waMessage = "Halo Bapak/Ibu Petugas Tabungan ({$officerName}),\n\nSaya ingin mendaftar dan membuka rekening tabungan sekolah:\nNama: {$studentName}\nNIS: {$studentNis}\nKelas: {$className}\n\nMohon bantuannya untuk proses pembukaan tabungan. Terima kasih.";
            $waUrl = $cleanPhone ? 'https://wa.me/'.$cleanPhone.'?text='.rawurlencode($waMessage) : null;

            return view('siswa.savings.index', [
                'isEnrolled' => false,
                'student' => $student,
                'savingsOfficer' => $savingsOfficer,
                'officerName' => $officerName,
                'officerPhone' => $officerPhone,
                'waUrl' => $waUrl,
            ]);
        }

        // -------------------------------------------------------------
        // State B: Siswa Terdaftar (Aktif)
        // -------------------------------------------------------------
        $now = Carbon::now();
        $startThisWeek = $now->copy()->startOfWeek();
        $endThisWeek = $now->copy()->endOfWeek();

        $startLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endLastWeek = $now->copy()->subWeek()->endOfWeek();

        // Metrik Mingguan (Setoran)
        $thisWeekDeposit = (float) $savingsAccount->transactions()
            ->where('type', 'deposit')
            ->whereBetween('created_at', [$startThisWeek, $endThisWeek])
            ->sum('amount');

        $lastWeekDeposit = (float) $savingsAccount->transactions()
            ->where('type', 'deposit')
            ->whereBetween('created_at', [$startLastWeek, $endLastWeek])
            ->sum('amount');

        $diffDeposit = $thisWeekDeposit - $lastWeekDeposit;
        $isMore = $diffDeposit > 0;
        $isEqual = $diffDeposit == 0;

        if ($lastWeekDeposit > 0) {
            $growthPercent = round((abs($diffDeposit) / $lastWeekDeposit) * 100);
        } else {
            $growthPercent = $thisWeekDeposit > 0 ? 100 : 0;
        }

        // Akumulasi Keseluruhan
        $totalDeposit = (float) $savingsAccount->transactions()->where('type', 'deposit')->sum('amount');
        $totalWithdrawal = (float) $savingsAccount->transactions()->where('type', 'withdrawal')->sum('amount');
        $depositCount = $savingsAccount->transactions()->where('type', 'deposit')->count();

        // Data Grafik: 4 Minggu Terakhir
        $chartLabels = [];
        $chartDeposits = [];
        $chartWithdrawals = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();

            if ($i === 0) {
                $label = 'Minggu Ini';
            } elseif ($i === 1) {
                $label = 'Minggu Lalu';
            } else {
                $label = $weekStart->translatedFormat('d M');
            }

            $chartLabels[] = $label;

            $chartDeposits[] = (float) $savingsAccount->transactions()
                ->where('type', 'deposit')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('amount');

            $chartWithdrawals[] = (float) $savingsAccount->transactions()
                ->where('type', 'withdrawal')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('amount');
        }

        // Riwayat Mutasi Lengkap
        $transactions = $savingsAccount->transactions()
            ->with(['handler', 'corrector'])
            ->take(50)
            ->get();

        return view('siswa.savings.index', [
            'isEnrolled' => true,
            'student' => $student,
            'savingsAccount' => $savingsAccount,
            'thisWeekDeposit' => $thisWeekDeposit,
            'lastWeekDeposit' => $lastWeekDeposit,
            'diffDeposit' => $diffDeposit,
            'isMore' => $isMore,
            'isEqual' => $isEqual,
            'growthPercent' => $growthPercent,
            'totalDeposit' => $totalDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'depositCount' => $depositCount,
            'chartLabels' => $chartLabels,
            'chartDeposits' => $chartDeposits,
            'chartWithdrawals' => $chartWithdrawals,
            'transactions' => $transactions,
        ]);
    }
}
