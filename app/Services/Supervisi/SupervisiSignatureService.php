<?php

namespace App\Services\Supervisi;

use App\Enums\SupervisiStatus;
use App\Http\Middleware\EnsureSupervisiRole;
use App\Models\AttendanceSetting;
use App\Models\Supervisi\SupervisiDokumenSignature;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupervisiSignatureService
{
    /**
     * @return array<string, SupervisiDokumenSignature|null>
     */
    public function getSignaturesByJadwal(SupervisiJadwal $jadwal, string $dokumenType = 'supervisi'): array
    {
        $rows = $jadwal->signatures()
            ->where('dokumen_type', $dokumenType)
            ->get()
            ->keyBy('signer_role');

        return [
            'guru' => $rows->get(SupervisiDokumenSignature::ROLE_GURU),
            'supervisor' => $rows->get(SupervisiDokumenSignature::ROLE_SUPERVISOR),
            'kepala' => $rows->get(SupervisiDokumenSignature::ROLE_KEPALA),
        ];
    }

    public function hasAnySignature(SupervisiJadwal $jadwal, string $dokumenType = 'supervisi'): bool
    {
        return $jadwal->signatures()->where('dokumen_type', $dokumenType)->exists();
    }

    public function isFullySigned(SupervisiJadwal $jadwal, string $dokumenType = 'supervisi'): bool
    {
        $sigs = $this->getSignaturesByJadwal($jadwal, $dokumenType);

        return $sigs['kepala'] !== null;
    }

    /**
     * Validasi urutan sekuensial tanda tangan digital: Guru -> Supervisor -> Kepala Madrasah.
     *
     * @return array{allowed: bool, message: string}
     */
    public function canSign(SupervisiJadwal $jadwal, string $role, string $dokumenType = 'supervisi'): array
    {
        if ($jadwal->status !== SupervisiStatus::Selesai->value) {
            return [
                'allowed' => false,
                'message' => 'Tanda tangan digital hanya dapat dilakukan setelah supervisi berstatus Selesai.',
            ];
        }

        $sigs = $this->getSignaturesByJadwal($jadwal, $dokumenType);
        $role = strtolower($role);

        if (! empty($sigs[$role])) {
            return [
                'allowed' => false,
                'message' => 'Anda sudah menandatangani dokumen ini pada '.$sigs[$role]->signed_at->format('d/m/Y H:i'),
            ];
        }

        if ($role === SupervisiDokumenSignature::ROLE_GURU) {
            return ['allowed' => true, 'message' => ''];
        }

        if ($role === SupervisiDokumenSignature::ROLE_SUPERVISOR) {
            if (empty($sigs['guru'])) {
                return [
                    'allowed' => false,
                    'message' => 'Menunggu tanda tangan Guru yang bersangkutan terlebih dahulu.',
                ];
            }

            return ['allowed' => true, 'message' => ''];
        }

        if ($role === SupervisiDokumenSignature::ROLE_KEPALA) {
            if (empty($sigs['guru'])) {
                return ['allowed' => false, 'message' => 'Guru belum menandatangani dokumen ini.'];
            }
            if (empty($sigs['supervisor'])) {
                return ['allowed' => false, 'message' => 'Menunggu tanda tangan Supervisor terlebih dahulu.'];
            }

            return ['allowed' => true, 'message' => ''];
        }

        return ['allowed' => false, 'message' => 'Peran penandatangan tidak valid.'];
    }

    /**
     * @throws AuthorizationException
     */
    public function sign(
        SupervisiJadwal $jadwal,
        User $user,
        string $role,
        string $tanggalTtd,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        string $dokumenType = 'supervisi'
    ): SupervisiDokumenSignature {
        $role = strtolower($role);
        $userRoles = EnsureSupervisiRole::resolveRoles($user);

        if (! in_array($role, $userRoles, true)) {
            throw new AuthorizationException('Anda tidak memiliki peran penandatangan yang diminta.');
        }

        if ($role === SupervisiDokumenSignature::ROLE_GURU) {
            if (! $user->teacher || (int) $jadwal->teacher_id !== (int) $user->teacher->id) {
                throw new AuthorizationException('Anda tidak berhak menandatangani jadwal supervisi guru lain.');
            }
        } elseif ($role === SupervisiDokumenSignature::ROLE_SUPERVISOR) {
            $isAssignedTeacher = $user->teacher && (int) $jadwal->supervisor_teacher_id === (int) $user->teacher->id;
            $isAssignedUser = (int) $jadwal->supervisor_user_id === (int) $user->id;
            if (! $isAssignedTeacher && ! $isAssignedUser && ! in_array('kepala', $userRoles, true)) {
                throw new AuthorizationException('Anda bukan supervisor pada jadwal supervisi ini.');
            }
        }

        $check = $this->canSign($jadwal, $role, $dokumenType);
        if (! $check['allowed']) {
            throw new \DomainException($check['message']);
        }

        [$signerNama, $signerNip] = $this->resolveSignerIdentity($jadwal, $user, $role);

        return SupervisiDokumenSignature::create([
            'jadwal_id' => $jadwal->id,
            'dokumen_type' => $dokumenType,
            'signer_role' => $role,
            'user_id' => $user->id,
            'signer_nama' => $signerNama,
            'signer_nip' => $signerNip,
            'token_verifikasi' => hash('sha256', $jadwal->id.'|'.$role.'|'.$user->id.'|'.Str::uuid()),
            'tanggal_ttd' => $tanggalTtd,
            'signed_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 250) : null,
        ]);
    }

    public function signAllAdmin(
        SupervisiJadwal $jadwal,
        User $admin,
        string $tanggalTtd,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        string $dokumenType = 'supervisi'
    ): void {
        if ($jadwal->status !== SupervisiStatus::Selesai->value) {
            throw new \DomainException('Tanda tangan digital hanya dapat dilakukan setelah status supervisi Selesai.');
        }

        DB::transaction(function () use ($jadwal, $admin, $tanggalTtd, $ipAddress, $userAgent, $dokumenType): void {
            $existing = $this->getSignaturesByJadwal($jadwal, $dokumenType);
            $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();

            $rolesIdentity = [
                SupervisiDokumenSignature::ROLE_GURU => [
                    'nama' => $jadwal->teacher?->user?->name ?? 'Guru',
                    'nip' => $jadwal->teacher?->nip,
                ],
                SupervisiDokumenSignature::ROLE_SUPERVISOR => [
                    'nama' => $jadwal->supervisor_display_name,
                    'nip' => $jadwal->supervisor_nip,
                ],
                SupervisiDokumenSignature::ROLE_KEPALA => [
                    'nama' => $setting?->headmasterTeacher?->user?->name
                        ?? $setting?->card_principal_name
                        ?? 'Kepala Madrasah',
                    'nip' => $setting?->headmasterTeacher?->nip
                        ?? $setting?->card_principal_nip,
                ],
            ];

            foreach ($rolesIdentity as $roleKey => $info) {
                if (! empty($existing[$roleKey])) {
                    continue;
                }

                SupervisiDokumenSignature::create([
                    'jadwal_id' => $jadwal->id,
                    'dokumen_type' => $dokumenType,
                    'signer_role' => $roleKey,
                    'user_id' => $admin->id,
                    'signer_nama' => $info['nama'],
                    'signer_nip' => $info['nip'],
                    'token_verifikasi' => hash('sha256', $jadwal->id.'|'.$roleKey.'|'.$admin->id.'|'.Str::uuid()),
                    'tanggal_ttd' => $tanggalTtd,
                    'signed_at' => now(),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent ? substr($userAgent, 0, 250) : null,
                ]);
            }
        });
    }

    public function resetSignatures(
        SupervisiJadwal $jadwal,
        User $admin,
        string $alasan,
        string $dokumenType = 'supervisi'
    ): void {
        $jadwal->signatures()->where('dokumen_type', $dokumenType)->delete();

        Log::info('Reset tanda tangan digital supervisi', [
            'jadwal_id' => $jadwal->id,
            'dokumen_type' => $dokumenType,
            'admin_user_id' => $admin->id,
            'alasan' => $alasan,
        ]);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function resolveSignerIdentity(SupervisiJadwal $jadwal, User $user, string $role): array
    {
        if ($role === SupervisiDokumenSignature::ROLE_KEPALA) {
            $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();
            $nama = $user->name ?: ($setting?->headmasterTeacher?->user?->name ?? $setting?->card_principal_name ?? 'Kepala Madrasah');
            $nip = $user->teacher?->nip ?? $user->nip ?? $setting?->headmasterTeacher?->nip ?? $setting?->card_principal_nip;

            return [$nama, $nip];
        }

        if ($role === SupervisiDokumenSignature::ROLE_GURU) {
            return [
                $jadwal->teacher?->user?->name ?? $user->name,
                $jadwal->teacher?->nip ?? $user->nip,
            ];
        }

        return [
            $user->name,
            $user->teacher?->nip ?? $user->nip,
        ];
    }
}
