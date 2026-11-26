<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKepalaMonitoring
{
    /**
     * Batasi akses akun Kepala Madrasah pada mode monitoring (hanya lihat, cetak, ekspor)
     * dan blokir total modul privasi tabungan serta pengaturan teknis sistem.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isKepala()) {
            return $next($request);
        }

        if (! $request->isMethodSafe()) {
            abort(403, 'Akses Ditolak: Akun Kepala Madrasah berada dalam mode monitoring (hanya lihat, cetak, dan ekspor).');
        }

        if ($request->routeIs(
            'admin.savings.*',
            'guru.savings.*',
            'admin.settings.*',
            'admin.database.*',
            'admin.api-clients.*',
            'admin.petugas-mbg.*',
            'admin.qr-generator.*',
            'admin.attendances.*',
            'admin.students.promotion.*',
            'admin.students.cek-duplikat.*',
            'admin.students.cards.settings',
            'admin.students.template',
            'admin.students.sync-sis*',
            'admin.classes.transfer*',
            'admin.classes.students.template',
            'admin.teachers.template',
            'admin.teachers.assignments',
            'admin.schedules.slots.*',
            'admin.academic-calendar.template.*',
            'ujian.pengaturan-ttd.*',
            'ujian.jadwal.template',
            'ujian.kartu.available-students',
            'ujian.pengawas.swap.candidates',
            'mbg.dashboard',
            'mbg.distributions.*',
        )) {
            abort(403, 'Akses Ditolak: Fitur tersebut dikhususkan untuk Administrator atau petugas yang berwenang.');
        }

        return $next($request);
    }
}
