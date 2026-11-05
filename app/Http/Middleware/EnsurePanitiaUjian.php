<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanitiaUjian
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

        if ($user->isAdmin() || $user->isPanitiaUjian()) {
            return $next($request);
        }

        abort(403, 'Akses dibatasi. Hanya Administrator dan Guru yang telah ditunjuk sebagai Panitia Ujian yang dapat mengakses modul ini.');
    }
}
