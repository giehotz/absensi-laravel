<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSavingsOfficer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isSavingsOfficer()) {
            abort(403, 'Akses Ditolak: Fitur ini hanya dapat diakses oleh Admin Utama atau Guru yang ditunjuk sebagai Pengelola Tabungan Siswa.');
        }

        return $next($request);
    }
}
