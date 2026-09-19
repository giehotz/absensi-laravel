<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            // Redirect to their own role dashboard
            $targetRoute = match ($user->role) {
                'admin' => 'admin.dashboard',
                'guru' => 'guru.dashboard',
                'siswa' => 'siswa.dashboard',
                'orangtua' => 'orangtua.dashboard',
                default => 'login',
            };

            return redirect()->route($targetRoute)->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
