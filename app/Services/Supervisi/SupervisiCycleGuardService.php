<?php

namespace App\Services\Supervisi;

use App\Enums\SupervisiStatus;
use App\Http\Middleware\EnsureSupervisiRole;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiPraObservasi;
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
        if ($jadwal->relationLoaded('signatures')) {
            return $jadwal->signatures->where('dokumen_type', $dokumenType)->isNotEmpty();
        }

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

    /**
     * Hitung status pengisian 4 tahap Guru (done = hijau, partial = kuning, empty = merah).
     *
     * @return array<int, array{
     *     step: int,
     *     key: string,
     *     title: string,
     *     short_title: string,
     *     state: string,
     *     icon: string,
     *     status_label: string,
     *     badge_class: string,
     *     card_class: string,
     *     description: string
     * }>
     */
    public function resolveGuruStepStates(SupervisiJadwal $jadwal): array
    {
        $dokumenAjars = $jadwal->dokumenAjars;
        $hasRejectedDoc = $dokumenAjars->contains('status', 'ditolak');
        if ($dokumenAjars->isEmpty()) {
            $perangkatState = ['empty', '✕', 'Belum Diisi', 'Belum ada perangkat ajar diunggah atau ditautkan.'];
        } elseif ($hasRejectedDoc) {
            $perangkatState = ['partial', '⏳', 'Perlu Revisi', 'Ada dokumen ajar yang perlu diperbaiki sesuai catatan supervisor.'];
        } else {
            $perangkatState = ['done', '✓', 'Selesai Diisi', $dokumenAjars->count().' perangkat ajar telah dilampirkan.'];
        }

        $pra = $jadwal->praObservasi;
        if (! $pra) {
            $formAState = ['empty', '✕', 'Belum Diisi', 'Formulir A Pra-Observasi belum diisi.'];
        } elseif ($pra->status_kesepakatan === SupervisiPraObservasi::STATUS_DRAFT) {
            $formAState = ['partial', '⏳', 'Draf (Belum Diajukan)', 'Formulir A masih berstatus draf dan belum diajukan ke supervisor.'];
        } else {
            $formALabel = $pra->isAgreed() ? 'Disepakati' : 'Sudah Diajukan';
            $formADesc = $pra->isAgreed()
                ? 'Formulir A telah disepakati bersama supervisor.'
                : 'Formulir A sudah diajukan dan menunggu kesepakatan supervisor.';
            $formAState = ['done', '✓', $formALabel, $formADesc];
        }

        $tl = $jadwal->tindakLanjut;
        if (! $tl) {
            $formCDState = ['empty', '✕', 'Belum Diisi', 'Formulir C & D belum diisi.'];
        } elseif (blank($tl->inspirasi_baru)) {
            $formCDState = ['partial', '⏳', 'Belum Lengkap', 'Formulir C sudah diisi; Formulir D (Refleksi) belum dilengkapi.'];
        } else {
            $formCDState = ['done', '✓', 'Selesai Diisi', 'Formulir C dan Formulir D telah lengkap diisi.'];
        }

        $guruSigned = $jadwal->signatures->firstWhere('signer_role', 'guru') !== null;
        if ($jadwal->status !== SupervisiStatus::Selesai->value) {
            $hasilState = ['empty', '✕', 'Belum Tersedia', 'Menunggu penilaian observasi & siklus selesai terlebih dahulu.'];
        } elseif (! $guruSigned) {
            $hasilState = ['partial', '⏳', 'Menunggu TTD', 'Hasil sudah terbit; silakan lakukan tanda tangan digital Guru.'];
        } else {
            $hasilState = ['done', '✓', 'Selesai & Sah', 'Hasil penilaian telah dilihat dan ditandatangani digital oleh Guru.'];
        }

        return [
            $this->formatStepState(1, 'perangkat', 'Perangkat Ajar', 'Perangkat', ...$perangkatState),
            $this->formatStepState(2, 'form_a', 'Formulir A (Pra-Observasi)', 'Form A', ...$formAState),
            $this->formatStepState(3, 'form_cd', 'Formulir C & D (Tindak Lanjut)', 'Form C/D', ...$formCDState),
            $this->formatStepState(4, 'hasil_ttd', 'Hasil & Tanda Tangan Digital', 'Hasil & TTD', ...$hasilState),
        ];
    }

    /**
     * @return array{
     *     step: int,
     *     key: string,
     *     title: string,
     *     short_title: string,
     *     state: string,
     *     icon: string,
     *     status_label: string,
     *     badge_class: string,
     *     card_class: string,
     *     description: string
     * }
     */
    private function formatStepState(
        int $step,
        string $key,
        string $title,
        string $shortTitle,
        string $state,
        string $icon,
        string $statusLabel,
        string $description
    ): array {
        [$badgeClass, $cardClass] = match ($state) {
            'done' => [
                'bg-emerald-200 text-emerald-950 border-black',
                'bg-emerald-50 border-emerald-700',
            ],
            'partial' => [
                'bg-[#FFD43B] text-black border-black',
                'bg-amber-50 border-amber-600',
            ],
            default => [
                'bg-rose-200 text-rose-950 border-black',
                'bg-rose-50 border-rose-700',
            ],
        };

        return [
            'step' => $step,
            'key' => $key,
            'title' => $title,
            'short_title' => $shortTitle,
            'state' => $state,
            'icon' => $icon,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'card_class' => $cardClass,
            'description' => $description,
        ];
    }
}
