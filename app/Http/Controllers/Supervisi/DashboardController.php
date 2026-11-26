<?php

namespace App\Http\Controllers\Supervisi;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSupervisiRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $roles = EnsureSupervisiRole::resolveRoles($request->user());
        $activeRole = EnsureSupervisiRole::resolveActiveRole($request, $roles);

        return redirect()->route("supervisi.{$activeRole}.dashboard");
    }

    public function switchRole(Request $request, string $role): RedirectResponse
    {
        $roles = EnsureSupervisiRole::resolveRoles($request->user());

        if (! in_array($role, $roles, true)) {
            abort(403, 'Anda tidak memiliki peran supervisi tersebut.');
        }

        $request->session()->put('supervisi_active_role', $role);

        return redirect()->route("supervisi.{$role}.dashboard");
    }
}
