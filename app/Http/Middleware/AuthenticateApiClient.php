<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiClient
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // 1. Extract API Key
        $apiKey = $request->header('X-API-Key');
        if (empty($apiKey)) {
            $authHeader = $request->header('Authorization', '');
            if (str_starts_with($authHeader, 'Bearer ')) {
                $apiKey = trim(substr($authHeader, 7));
            }
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak ditemukan. Sertakan header X-API-Key atau Authorization: Bearer <key>.',
            ], 401);
        }

        // 2. Validate API Client
        $client = ApiClient::where('api_key', $apiKey)->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak valid atau tidak terdaftar.',
            ], 401);
        }

        if (! $client->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: API Key ini telah dinonaktifkan (revoked).',
            ], 403);
        }

        if (! $client->isIpAllowed($request->ip())) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Alamat IP '.$request->ip().' tidak terdaftar dalam whitelist.',
            ], 403);
        }

        // Attach client to request
        $request->attributes->set('api_client', $client);

        // Update last used timestamp quietly
        $client->updateQuietly(['last_used_at' => now()]);

        // 3. Process the request
        $response = $next($request);

        // 4. Calculate execution time & records count
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        $recordsCount = (int) ($response->headers->get('X-Records-Count') ?? 0);

        // 5. Record Audit Log
        try {
            ApiLog::create([
                'api_client_id' => $client->id,
                'endpoint' => '/'.$request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'status_code' => $response->getStatusCode(),
                'records_count' => $recordsCount,
                'response_time_ms' => $durationMs,
            ]);
        } catch (\Throwable) {
            // Ignore logging failures to not disrupt API responses
        }

        return $response;
    }
}
