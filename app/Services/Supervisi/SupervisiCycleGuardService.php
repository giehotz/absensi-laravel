<?php

namespace App\Services\Supervisi;

use App\Http\Middleware\EnsureSupervisiRole;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SupervisiCycleGuardService
{
    public function isKurmerMode(SupervisiJadwal $jadwal): bool
    {
        return $jadwal->isKurmer();
    }

    public function canAccessObservasi(SupervisiJadwal $jadwal): bool
    {
        if (! $this->isKurmerMode($jadwal)) {
            return $jadwal->statusEnum()->canAccessObservasi(false);
        }

        $pra = $jadwal->praObservasi;

        return $jadwal->statusEnum()->canAccessObservasi(true)
            && $pra !== null
            && $pra->isAgreed();
    }

    public function canAccessPascaObservasi(SupervisiJadwal $jadwal): bool
    {
        return $jadwal->statusEnum()->canAccessPascaObservasi($this->isKurmerMode($jadwal));
    }

    public function isLocked(SupervisiJadwal $jadwal, string $dokumenType = 'supervisi'): bool
    {
        return $jadwal->signatures()
            ->where('dokumen_type', $dokumenType)
            ->exists();
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureNotLocked(SupervisiJadwal $jadwal, string $dokumenType = 'supervisi'): void
    {
        if ($this->isLocked($jadwal, $dokumenType)) {
            throw new AuthorizationException('Dokumen supervisi terkunci karena sudah mulai ditandatangani secara digital. Hubungi Admin untuk reset tanda tangan.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureGuruOwnsJadwal(User $user, SupervisiJadwal $jadwal): void
    {
        if (! $user->teacher || (int) $jadwal->teacher_id !== (int) $user->teacher->id) {
            throw new AuthorizationException('Anda tidak berhak mengakses jadwal supervisi guru lain.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureSupervisorOwnsJadwal(User $user, SupervisiJadwal $jadwal): void
    {
        $roles = EnsureSupervisiRole::resolveRoles($user);
        if (in_array('admin', $roles, true) || in_array('kepala', $roles, true)) {
            return;
        }

        $isOwnerByTeacher = $user->teacher && (int) $jadwal->supervisor_teacher_id === (int) $user->teacher->id;
        $isOwnerByUser = (int) $jadwal->supervisor_user_id === (int) $user->id;

        if (! $isOwnerByTeacher && ! $isOwnerByUser) {
            throw new AuthorizationException('Anda bukan supervisor yang ditugaskan pada jadwal supervisi ini.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureCanViewJadwal(User $user, SupervisiJadwal $jadwal): void
    {
        $roles = EnsureSupervisiRole::resolveRoles($user);
        if (in_array('admin', $roles, true) || in_array('kepala', $roles, true)) {
            return;
        }

        $isGuruOwner = $user->teacher && (int) $jadwal->teacher_id === (int) $user->teacher->id;
        $isSupOwner = ($user->teacher && (int) $jadwal->supervisor_teacher_id === (int) $user->teacher->id)
            || (int) $jadwal->supervisor_user_id === (int) $user->id;

        if (! $isGuruOwner && ! $isSupOwner) {
            throw new AuthorizationException('Anda tidak memiliki akses ke jadwal supervisi ini.');
        }
    }
}
