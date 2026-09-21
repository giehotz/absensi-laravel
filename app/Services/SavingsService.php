<?php

namespace App\Services;

use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SavingsService
{
    /**
     * Dapatkan atau buat akun tabungan untuk siswa (on-demand).
     */
    public function getOrCreateAccount(Student $student): SavingsAccount
    {
        return SavingsAccount::firstOrCreate(
            ['student_id' => $student->id],
            [
                'account_number' => 'TAB-'.($student->nisn ?: ($student->nis ?: str_pad((string) $student->id, 5, '0', STR_PAD_LEFT))),
                'balance' => 0,
                'status' => 'active',
            ]
        );
    }

    /**
     * Proses transaksi setoran tunai dengan database transaction & row lock.
     */
    public function deposit(SavingsAccount|int $account, float $amount, ?string $note, User $officer): SavingsTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal setoran harus lebih besar dari 0.');
        }

        $accountId = $account instanceof SavingsAccount ? $account->id : $account;

        return DB::transaction(function () use ($accountId, $amount, $note, $officer) {
            /** @var SavingsAccount $lockedAccount */
            $lockedAccount = SavingsAccount::where('id', $accountId)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $lockedAccount->balance;
            $balanceAfter = $balanceBefore + $amount;

            $lockedAccount->update(['balance' => $balanceAfter]);

            $code = 'TRX-D-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));

            return SavingsTransaction::create([
                'savings_account_id' => $lockedAccount->id,
                'transaction_code' => $code,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $note ?: 'Setoran tunai',
                'handled_by' => $officer->id,
            ]);
        });
    }

    /**
     * Proses transaksi penarikan tunai dengan database transaction & row lock.
     */
    public function withdraw(SavingsAccount|int $account, float $amount, ?string $note, User $officer): SavingsTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal penarikan harus lebih besar dari 0.');
        }

        $accountId = $account instanceof SavingsAccount ? $account->id : $account;

        return DB::transaction(function () use ($accountId, $amount, $note, $officer) {
            /** @var SavingsAccount $lockedAccount */
            $lockedAccount = SavingsAccount::where('id', $accountId)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $lockedAccount->balance;

            if ($balanceBefore < $amount) {
                throw new InvalidArgumentException('Saldo tabungan tidak mencukupi. Saldo saat ini: Rp '.number_format($balanceBefore, 0, ',', '.'));
            }

            $balanceAfter = $balanceBefore - $amount;

            $lockedAccount->update(['balance' => $balanceAfter]);

            $code = 'TRX-W-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));

            return SavingsTransaction::create([
                'savings_account_id' => $lockedAccount->id,
                'transaction_code' => $code,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $note ?: 'Penarikan tunai',
                'handled_by' => $officer->id,
            ]);
        });
    }

    /**
     * Ringkasan statistik keuangan tabungan (opsional dibatasi pada rombel tertentu).
     */
    public function getSummaryStats(?array $allowedClassIds = null): array
    {
        $accountsQuery = SavingsAccount::query();
        $txQuery = SavingsTransaction::whereDate('created_at', today());

        if ($allowedClassIds !== null) {
            $accountsQuery->whereHas('student', fn ($q) => $q->whereIn('school_class_id', $allowedClassIds));
            $txQuery->whereHas('savingsAccount.student', fn ($q) => $q->whereIn('school_class_id', $allowedClassIds));
        }

        $totalBalance = (float) (clone $accountsQuery)->sum('balance');
        $totalAccounts = (clone $accountsQuery)->count();

        $todayDeposits = (float) (clone $txQuery)->where('type', 'deposit')->sum('amount');
        $todayWithdrawals = (float) (clone $txQuery)->where('type', 'withdrawal')->sum('amount');

        $monthTxQuery = SavingsTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        if ($allowedClassIds !== null) {
            $monthTxQuery->whereHas('savingsAccount.student', fn ($q) => $q->whereIn('school_class_id', $allowedClassIds));
        }

        $thisMonthDeposits = (float) (clone $monthTxQuery)->where('type', 'deposit')->sum('amount');
        $thisMonthWithdrawals = (float) (clone $monthTxQuery)->where('type', 'withdrawal')->sum('amount');

        return [
            'total_balance' => $totalBalance,
            'total_accounts' => $totalAccounts,
            'today_deposits' => $todayDeposits,
            'today_withdrawals' => $todayWithdrawals,
            'this_month_deposits' => $thisMonthDeposits,
            'this_month_withdrawals' => $thisMonthWithdrawals,
        ];
    }

    /**
     * Batalkan pendaftaran siswa sebagai penabung (hanya jika transaksi masih 0 dan saldo Rp 0).
     */
    public function cancelRegistration(SavingsAccount $account): bool
    {
        if ($account->transactions()->exists() || (float) $account->balance > 0) {
            throw new InvalidArgumentException('Pendaftaran tidak dapat dibatalkan karena siswa sudah memiliki riwayat transaksi atau saldo. Silakan gunakan fitur Tutup Buku.');
        }

        return (bool) $account->delete();
    }

    /**
     * Tutup buku / berhenti menabung:
     * Otomatis mencairkan seluruh sisa saldo (jika ada) dan mengubah status akun menjadi 'inactive'.
     */
    public function closeAccount(SavingsAccount|int $account, ?string $reason, User $officer): ?SavingsTransaction
    {
        $accountId = $account instanceof SavingsAccount ? $account->id : $account;

        return DB::transaction(function () use ($accountId, $reason, $officer) {
            /** @var SavingsAccount $lockedAccount */
            $lockedAccount = SavingsAccount::where('id', $accountId)->lockForUpdate()->firstOrFail();

            $currentBalance = (float) $lockedAccount->balance;
            $finalTransaction = null;

            // Jika masih ada saldo, lakukan penarikan akhir penutupan buku
            if ($currentBalance > 0) {
                $code = 'TRX-W-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
                $desc = 'Pencairan saldo akhir - Penutupan buku tabungan'.($reason ? " ({$reason})" : '');

                $finalTransaction = SavingsTransaction::create([
                    'savings_account_id' => $lockedAccount->id,
                    'transaction_code' => $code,
                    'type' => 'withdrawal',
                    'amount' => $currentBalance,
                    'balance_before' => $currentBalance,
                    'balance_after' => 0,
                    'description' => $desc,
                    'handled_by' => $officer->id,
                ]);

                $lockedAccount->balance = 0;
            }

            $lockedAccount->status = 'inactive';
            $lockedAccount->save();

            return $finalTransaction;
        });
    }

    /**
     * Buka kembali rekening tabungan yang sebelumnya ditutup.
     */
    public function reopenAccount(SavingsAccount|int $account): bool
    {
        $accountId = $account instanceof SavingsAccount ? $account->id : $account;
        $acc = SavingsAccount::findOrFail($accountId);
        $acc->update(['status' => 'active']);

        return true;
    }

    /**
     * Koreksi nominal transaksi yang salah input dengan audit trail dan sinkronisasi saldo.
     */
    public function correctTransaction(SavingsTransaction|int $transaction, float $newAmount, string $reason, User $officer): SavingsTransaction
    {
        if ($newAmount <= 0) {
            throw new InvalidArgumentException('Nominal transaksi baru harus lebih besar dari Rp 0.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan koreksi transaksi wajib diisi.');
        }

        $txId = $transaction instanceof SavingsTransaction ? $transaction->id : $transaction;

        return DB::transaction(function () use ($txId, $newAmount, $reason, $officer) {
            /** @var SavingsTransaction $lockedTx */
            $lockedTx = SavingsTransaction::where('id', $txId)->lockForUpdate()->firstOrFail();

            /** @var SavingsAccount $lockedAccount */
            $lockedAccount = SavingsAccount::where('id', $lockedTx->savings_account_id)->lockForUpdate()->firstOrFail();

            $oldAmount = (float) $lockedTx->amount;

            // Hitung perubahan terhadap saldo rekening
            // Jika setoran: new > old -> saldo bertambah, new < old -> saldo berkurang
            // Jika penarikan: new > old -> saldo berkurang, new < old -> saldo bertambah
            $delta = $lockedTx->isDeposit()
                ? ($newAmount - $oldAmount)
                : ($oldAmount - $newAmount);

            $newBalance = (float) $lockedAccount->balance + $delta;

            if ($newBalance < 0) {
                throw new InvalidArgumentException('Koreksi transaksi ditolak karena akan mengakibatkan saldo tabungan siswa menjadi minus (Rp '.number_format($newBalance, 0, ',', '.').'). Saldo saat ini tidak mencukupi untuk pengurangan nominal tersebut.');
            }

            // Simpan nominal asli sebelum koreksi pertama kali
            $originalAmount = $lockedTx->original_amount ?? $oldAmount;

            $lockedTx->update([
                'original_amount' => $originalAmount,
                'amount' => $newAmount,
                'balance_after' => (float) $lockedTx->balance_after + $delta,
                'is_corrected' => true,
                'correction_reason' => $reason,
                'corrected_by' => $officer->id,
                'corrected_at' => now(),
            ]);

            $lockedAccount->update(['balance' => $newBalance]);

            return $lockedTx;
        });
    }
}
