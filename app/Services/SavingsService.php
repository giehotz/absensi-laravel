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
     * Ringkasan statistik keuangan tabungan.
     */
    public function getSummaryStats(): array
    {
        $totalBalance = (float) SavingsAccount::sum('balance');
        $totalAccounts = SavingsAccount::count();

        $todayDeposits = (float) SavingsTransaction::where('type', 'deposit')
            ->whereDate('created_at', today())
            ->sum('amount');

        $todayWithdrawals = (float) SavingsTransaction::where('type', 'withdrawal')
            ->whereDate('created_at', today())
            ->sum('amount');

        return [
            'total_balance' => $totalBalance,
            'total_accounts' => $totalAccounts,
            'today_deposits' => $todayDeposits,
            'today_withdrawals' => $todayWithdrawals,
        ];
    }
}
