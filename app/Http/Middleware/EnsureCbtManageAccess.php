<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCbtManageAccess
{
    /**
     * Izinkan Administrator, Kepala Madrasah (read-only via EnsureKepalaMonitoring),
     * Panitia Ujian, serta Guru aktif mengakses panel manajemen CBT.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (
            $user->isAdmin()
            || $user->isKepala()
            || $user->isPanitiaUjian()
            || ($user->isGuru() && $user->teacher !== null)
        ) {
            return $next($request);
        }

        abort(403, 'Akses dibatasi. Anda tidak memiliki wewenang mengakses panel manajemen CBT.');
    }
}
