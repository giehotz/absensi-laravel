<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\ApiLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiClientController extends Controller
{
    public function index(): View
    {
        $clients = ApiClient::withCount('logs')->latest()->paginate(10);
        $recentLogs = ApiLog::with('client')->latest()->limit(30)->get();

        $stats = [
            'total_clients' => ApiClient::count(),
            'active_clients' => ApiClient::where('is_active', true)->count(),
            'total_requests' => ApiLog::count(),
            'today_requests' => ApiLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.api-clients.index', compact('clients', 'recentLogs', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'app_id' => ['required', 'string', 'max:50', 'unique:api_clients,app_id'],
            'ip_whitelist' => ['nullable', 'string'],
            'rate_limit' => ['nullable', 'integer', 'min:10', 'max:1000'],
        ], [
            'name.required' => 'Nama aplikasi mitra wajib diisi.',
            'app_id.required' => 'ID unik aplikasi wajib diisi.',
            'app_id.unique' => 'ID aplikasi ini sudah digunakan oleh mitra lain.',
        ]);

        $apiKey = ApiClient::generateKey();

        $client = ApiClient::create([
            'name' => $validated['name'],
            'app_id' => Str::upper(Str::slug($validated['app_id'])),
            'api_key' => $apiKey,
            'ip_whitelist' => $validated['ip_whitelist'] ?? null,
            'rate_limit' => $validated['rate_limit'] ?? 60,
            'is_active' => true,
        ]);

        return redirect()->route('admin.api-clients.index')
            ->with('new_api_key', $apiKey)
            ->with('new_client_name', $client->name)
            ->with('success', "API Client '{$client->name}' berhasil didaftarkan. Harap salin API Key sekarang!");
    }

    public function update(Request $request, ApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'ip_whitelist' => ['nullable', 'string'],
            'rate_limit' => ['nullable', 'integer', 'min:10', 'max:1000'],
        ]);

        $client->update([
            'name' => $validated['name'],
            'ip_whitelist' => $validated['ip_whitelist'] ?? null,
            'rate_limit' => $validated['rate_limit'] ?? 60,
        ]);

        return redirect()->route('admin.api-clients.index')
            ->with('success', "Data API Client '{$client->name}' berhasil diperbarui.");
    }

    public function regenerateKey(ApiClient $client): RedirectResponse
    {
        $newKey = ApiClient::generateKey();
        $client->update(['api_key' => $newKey]);

        return redirect()->route('admin.api-clients.index')
            ->with('new_api_key', $newKey)
            ->with('new_client_name', $client->name)
            ->with('success', "API Key untuk '{$client->name}' berhasil dibuat ulang (regenerated). Harap perbarui di aplikasi mitra!");
    }

    public function toggle(ApiClient $client): RedirectResponse
    {
        $client->update(['is_active' => ! $client->is_active]);

        $status = $client->is_active ? 'diaktifkan' : 'dinonaktifkan (revoked)';

        return redirect()->route('admin.api-clients.index')
            ->with('success', "Akses API Client '{$client->name}' berhasil {$status}.");
    }

    public function destroy(ApiClient $client): RedirectResponse
    {
        $name = $client->name;
        $client->delete();

        return redirect()->route('admin.api-clients.index')
            ->with('success', "API Client '{$name}' dan riwayat kuncinya berhasil dihapus.");
    }
}
