<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsMbgOfficer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isPetugasMbg()) {
            return $next($request);
        }

        $targetRoute = match ($user->role) {
            'admin' => 'admin.dashboard',
            'guru' => 'guru.dashboard',
            'siswa' => 'siswa.dashboard',
            'orangtua' => 'orangtua.dashboard',
            'petugas_mbg' => 'mbg.dashboard',
            default => 'login',
        };

        return redirect()->route($targetRoute)->with('error', 'Akses Ditolak: Modul ini hanya dapat diakses oleh Admin atau Petugas MBG yang ditunjuk.');
    }
}
