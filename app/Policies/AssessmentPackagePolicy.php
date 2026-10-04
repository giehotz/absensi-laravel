<?php

namespace App\Policies;

use App\Models\AssessmentPackage;
use App\Models\User;

class AssessmentPackagePolicy
{
    /**
     * Determine whether the user can view any packages.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGuru();
    }

    /**
     * Determine whether the user can view the specific package.
     */
    public function view(User $user, AssessmentPackage $package): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuru()) {
            $teacher = $user->teacher;
            if (! $teacher) {
                return false;
            }

            // Guru pengampu paket atau wali kelas dari rombel terkait
            return $package->teacher_id === $teacher->id
                || $package->schoolClass->homeroom_teacher_id === $teacher->id;
        }

        if ($user->isSiswa()) {
            // Siswa hanya boleh melihat jika sudah locked dan berada di kelas bersangkutan
            return $package->isLocked() && $user->student?->school_class_id === $package->school_class_id;
        }

        if ($user->isOrangTua()) {
            // Orang tua hanya boleh melihat jika sudah locked dan punya anak di kelas bersangkutan
            $parent = $user->parentProfile;
            if (! $parent) {
                return false;
            }

            return $package->isLocked() && $parent->students()->where('school_class_id', $package->school_class_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create packages.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isGuru();
    }

    /**
     * Determine whether the user can update the package (upload, edit, publish).
     */
    public function update(User $user, AssessmentPackage $package): bool
    {
        if ($package->isLocked()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuru()) {
            $teacher = $user->teacher;

            return $teacher && $package->teacher_id === $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the package.
     */
    public function delete(User $user, AssessmentPackage $package): bool
    {
        if ($package->isLocked()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuru()) {
            $teacher = $user->teacher;

            return $teacher && $package->teacher_id === $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can lock the package.
     */
    public function lock(User $user, AssessmentPackage $package): bool
    {
        if ($package->isLocked()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuru()) {
            $teacher = $user->teacher;

            return $teacher && $package->teacher_id === $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can unlock the package.
     */
    public function unlock(User $user, AssessmentPackage $package): bool
    {
        return $user->isAdmin();
    }
}
