<?php

namespace App\Policies;

use App\Models\Cbt\CbtQuestionBank;
use App\Models\User;

class CbtQuestionBankPolicy
{
    /**
     * Determine whether the user can view any question banks.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isKepala() || $user->isGuru();
    }

    /**
     * Determine whether the user can view the question bank.
     */
    public function view(User $user, CbtQuestionBank $bank): bool
    {
        if ($user->isAdmin() || $user->isKepala() || $user->isPanitiaUjian()) {
            return true;
        }

        return $user->isGuru() && $user->teacher?->id === $bank->teacher_id;
    }

    /**
     * Determine whether the user can create question banks.
     */
    public function create(User $user): bool
    {
        if ($user->isKepala()) {
            return false;
        }

        return $user->isAdmin() || $user->isPanitiaUjian() || ($user->isGuru() && $user->teacher !== null);
    }

    /**
     * Determine whether the user can update the question bank.
     */
    public function update(User $user, CbtQuestionBank $bank): bool
    {
        if ($user->isKepala()) {
            return false;
        }

        if ($user->isAdmin() || $user->isPanitiaUjian()) {
            return true;
        }

        return $user->isGuru() && $user->teacher?->id === $bank->teacher_id;
    }

    /**
     * Determine whether the user can delete the question bank.
     */
    public function delete(User $user, CbtQuestionBank $bank): bool
    {
        return $this->update($user, $bank);
    }
}
