<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCbtStudent
{
    /**
     * Pastikan pengguna adalah siswa aktif yang belum di-soft-delete.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSiswa() && $user->student !== null) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Portal ujian CBT hanya tersedia untuk akun siswa aktif.');
    }
}
