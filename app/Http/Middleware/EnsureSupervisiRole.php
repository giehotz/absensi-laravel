<?php

namespace App\Http\Middleware;

use App\Models\AttendanceSetting;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupervisiRole
{
    public const ROLE_LABELS = [
        'admin' => 'Administrator',
        'kepala' => 'Kepala Madrasah',
        'supervisor' => 'Supervisor',
        'guru' => 'Guru',
    ];

    /**
     * Resolusi seluruh peran supervisi yang dimiliki pengguna saat ini.
     *
     * @return array<int, string>
     */
    public static function resolveRoles(User $user): array
    {
        $roles = [];

        if ($user->isAdmin()) {
            $roles[] = 'admin';
        }

        $headmasterTeacherId = AttendanceSetting::query()->value('headmaster_teacher_id');
        if ($user->isKepala() || ($user->teacher && $headmasterTeacherId && (int) $user->teacher->id === (int) $headmasterTeacherId)) {
            $roles[] = 'kepala';
            $roles[] = 'supervisor';
        }

        if ($user->teacher && SupervisiSupervisor::query()->where('teacher_id', $user->teacher->id)->where('is_active', true)->exists()) {
            $roles[] = 'supervisor';
        }

        if ($user->isGuru() && $user->teacher !== null) {
            $roles[] = 'guru';
        }

        return array_values(array_unique($roles));
    }

    public static function resolveActiveRole(Request $request, array $roles): ?string
    {
        if (empty($roles)) {
            return null;
        }

        foreach (['admin', 'kepala', 'supervisor', 'guru'] as $prefix) {
            if ($request->is("supervisi/{$prefix}") || $request->is("supervisi/{$prefix}/*")) {
                if (in_array($prefix, $roles, true)) {
                    $request->session()->put('supervisi_active_role', $prefix);

                    return $prefix;
                }
            }
        }

        $sessionRole = $request->session()->get('supervisi_active_role');
        if (is_string($sessionRole) && in_array($sessionRole, $roles, true)) {
            return $sessionRole;
        }

        return $roles[0];
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $userRoles = self::resolveRoles($user);
        if (empty($userRoles)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki peran pada modul Supervisi Guru.');
        }

        if (! empty($allowedRoles) && empty(array_intersect($userRoles, $allowedRoles))) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk halaman supervisi ini.');
        }

        $activeRole = self::resolveActiveRole($request, $userRoles);
        $request->attributes->set('supervisi_roles', $userRoles);
        $request->attributes->set('supervisi_active_role', $activeRole);

        View::share('supervisiRoles', $userRoles);
        View::share('activeSupervisiRole', $activeRole);
        View::share('supervisiRoleLabels', self::ROLE_LABELS);

        return $next($request);
    }
}
