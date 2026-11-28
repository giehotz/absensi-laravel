<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CbtPortalController extends Controller
{
    public function manageIndex(Request $request): JsonResponse|Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'area' => 'manage',
            ]);
        }

        return response('Panel Manajemen CBT siap.', 200);
    }

    public function siswaIndex(Request $request): JsonResponse|Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'area' => 'siswa',
            ]);
        }

        return response('Portal Ujian CBT Siswa siap.', 200);
    }
}
