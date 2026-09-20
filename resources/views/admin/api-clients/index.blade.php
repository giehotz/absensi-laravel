@extends('layouts.admin')

@section('title', 'Manajemen API Client & Integrasi')
@section('page-title', 'API Client & Integrasi')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>🔑</span> Manajemen API Client & Integrasi
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Atur otorisasi aplikasi eksternal mitra untuk mengakses Student Transfer API secara aman.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openModal('createClientModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading">
                <span>+</span> Tambah API Client
            </button>
        </div>
    </div>

    <!-- Alert API Key Baru Dibuat / Diregenerate -->
    @if(session('new_api_key'))
    <div class="bg-[#FFF9DB] border-3 border-black p-5 neo-box-lg space-y-3 relative">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚠️</span>
                <h3 class="font-heading font-black text-sm uppercase text-black">
                    Simpan API Key Klien Baru (Hanya Muncul Sekali)
                </h3>
            </div>
            <span class="neo-badge bg-[#FF6B6B] text-white text-[10px]">Rahasia</span>
        </div>
        <p class="text-xs font-medium text-slate-700">
            API Key untuk mitra <strong>{{ session('new_client_name') }}</strong> berhasil dibuat. Salin dan simpan di file konfigurasi aplikasi mitra sekarang.
        </p>
        <div class="flex items-center gap-2 max-w-2xl">
            <input type="text" id="newApiKeyInput" readonly value="{{ session('new_api_key') }}" class="w-full px-3 py-2 neo-input font-mono text-xs font-black bg-white select-all">
            <button type="button" onclick="copyApiKey('newApiKeyInput')" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-heading font-black shrink-0">
                📋 Salin Key
            </button>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white neo-box p-4">
            <div class="text-[10px] uppercase font-bold text-slate-500">Total Klien Terdaftar</div>
            <div class="text-2xl font-heading font-black text-black mt-1">{{ $stats['total_clients'] }}</div>
        </div>
        <div class="bg-[#D3F9D8] text-green-950 neo-box p-4">
            <div class="text-[10px] uppercase font-bold text-green-800">Klien Aktif</div>
            <div class="text-2xl font-heading font-black text-green-950 mt-1">{{ $stats['active_clients'] }}</div>
        </div>
        <div class="bg-[#E7F5FF] text-blue-950 neo-box p-4">
            <div class="text-[10px] uppercase font-bold text-blue-800">Total Permintaan API</div>
            <div class="text-2xl font-heading font-black text-blue-950 mt-1">{{ $stats['total_requests'] }}</div>
        </div>
        <div class="bg-[#FFF9DB] text-amber-950 neo-box p-4">
            <div class="text-[10px] uppercase font-bold text-amber-800">Permintaan Hari Ini</div>
            <div class="text-2xl font-heading font-black text-amber-950 mt-1">{{ $stats['today_requests'] }}</div>
        </div>
    </div>

    <!-- Main Tabs -->
    <div class="bg-white neo-box p-4 space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <button type="button" id="tabBtnClients" onclick="switchTab('clients')" class="px-3.5 py-1.5 text-xs font-heading font-black uppercase border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]">
                    Daftar Klien API ({{ $clients->total() }})
                </button>
                <button type="button" id="tabBtnLogs" onclick="switchTab('logs')" class="px-3.5 py-1.5 text-xs font-heading font-bold uppercase border-2 border-black bg-white text-slate-700 hover:bg-slate-50">
                    Audit Log Permintaan ({{ $recentLogs->count() }})
                </button>
            </div>
            
            <button onclick="openModal('apiDocModal')" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-3 py-1.5 text-xs font-heading flex items-center gap-1.5">
                <span>📖</span> Dokumentasi Integrasi
            </button>
        </div>

        <!-- TAB 1: KLIEN API -->
        <div id="tabContentClients" class="space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                        <tr>
                            <th class="p-3.5 border-r border-black">No</th>
                            <th class="p-3.5 border-r border-black">Nama Aplikasi / Mitra</th>
                            <th class="p-3.5 border-r border-black">App ID</th>
                            <th class="p-3.5 border-r border-black">API Key</th>
                            <th class="p-3.5 border-r border-black">Rate / Whitelist</th>
                            <th class="p-3.5 border-r border-black text-center">Status</th>
                            <th class="p-3.5 border-r border-black text-center">Aktivitas</th>
                            <th class="p-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black text-xs font-medium">
                        @forelse($clients as $index => $client)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3.5 font-bold text-center border-r border-black">
                                {{ $clients->firstItem() + $index }}
                            </td>
                            <td class="p-3.5 font-bold text-black border-r border-black">
                                {{ $client->name }}
                            </td>
                            <td class="p-3.5 font-mono font-bold text-slate-700 border-r border-black">
                                <span class="bg-slate-100 px-2 py-0.5 border border-black text-[11px]">
                                    {{ $client->app_id }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono border-r border-black">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-slate-600 font-bold text-[11px]">
                                        {{ substr($client->api_key, 0, 12) }}...{{ substr($client->api_key, -4) }}
                                    </span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $client->api_key }}'); alert('API Key disalin ke clipboard!');" title="Salin Full Key" class="p-1 hover:bg-slate-200 rounded text-slate-600 cursor-pointer">
                                        📋
                                    </button>
                                </div>
                            </td>
                            <td class="p-3.5 border-r border-black text-[11px]">
                                <div><span class="text-slate-500">Rate:</span> <strong>{{ $client->rate_limit }}</strong> req/min</div>
                                <div><span class="text-slate-500">IP:</span> {{ $client->ip_whitelist ? Str::limit($client->ip_whitelist, 20) : 'Semua IP' }}</div>
                            </td>
                            <td class="p-3.5 border-r border-black text-center">
                                @if($client->is_active)
                                    <span class="neo-badge bg-[#D3F9D8] text-green-950 text-[10px]">Aktif</span>
                                @else
                                    <span class="neo-badge bg-[#FFE3E3] text-rose-950 text-[10px]">Revoked</span>
                                @endif
                            </td>
                            <td class="p-3.5 border-r border-black text-center text-[11px] font-mono">
                                <div><strong>{{ $client->logs_count }}</strong> hits</div>
                                <div class="text-[10px] text-slate-400">{{ $client->last_used_at ? $client->last_used_at->diffForHumans() : 'Belum pernah' }}</div>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Toggle Status -->
                                    <form action="{{ route('admin.api-clients.toggle', $client) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="neo-btn {{ $client->is_active ? 'bg-[#FFE3E3] hover:bg-rose-200 text-rose-900' : 'bg-[#D3F9D8] hover:bg-green-200 text-green-900' }} p-1.5 text-xs" title="{{ $client->is_active ? 'Nonaktifkan Key' : 'Aktifkan Key' }}">
                                            {{ $client->is_active ? '⛔' : '✓' }}
                                        </button>
                                    </form>

                                    <!-- Regenerate Key -->
                                    <form action="{{ route('admin.api-clients.regenerate', $client) }}" method="POST" onsubmit="return confirm('Regenerate akan membuat API Key lama tidak berfungsi lagi. Lanjutkan?')">
                                        @csrf
                                        <button type="submit" class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black p-1.5 text-xs" title="Regenerate Key">
                                            🔄
                                        </button>
                                    </form>

                                    <!-- Edit Client -->
                                    <button type="button" onclick="editClient(this)" 
                                            data-id="{{ $client->id }}"
                                            data-name="{{ $client->name }}"
                                            data-whitelist="{{ $client->ip_whitelist }}"
                                            data-rate="{{ $client->rate_limit }}"
                                            class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white p-1.5 text-xs" title="Edit Data Klien">
                                        ✏️
                                    </button>

                                    <!-- Hapus Client -->
                                    <form action="{{ route('admin.api-clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Hapus klien API ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white p-1.5 text-xs" title="Hapus Klien">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 font-semibold">
                                Belum ada API Client yang didaftarkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
            <div class="pt-3 border-t-2 border-black">
                {{ $clients->links() }}
            </div>
            @endif
        </div>

        <!-- TAB 2: AUDIT LOG -->
        <div id="tabContentLogs" class="hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FFF9DB] border-b-2 border-black font-black uppercase tracking-wider">
                        <tr>
                            <th class="p-3 border-r border-black">Waktu</th>
                            <th class="p-3 border-r border-black">Klien</th>
                            <th class="p-3 border-r border-black">Method & Endpoint</th>
                            <th class="p-3 border-r border-black">IP Address</th>
                            <th class="p-3 border-r border-black text-center">Status</th>
                            <th class="p-3 border-r border-black text-center">Data Siswa</th>
                            <th class="p-3 text-center">Latency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black font-mono">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-slate-600 border-r border-black whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="p-3 font-bold font-sans text-black border-r border-black">
                                {{ $log->client->name ?? 'Deleted Client' }}
                            </td>
                            <td class="p-3 border-r border-black font-bold text-black">
                                <span class="bg-[#5294FF] text-white px-1.5 py-0.5 border border-black text-[10px] mr-1">{{ $log->method }}</span>
                                {{ $log->endpoint }}
                            </td>
                            <td class="p-3 border-r border-black text-slate-700">
                                {{ $log->ip_address }}
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                <span class="px-2 py-0.5 border border-black text-[10px] font-bold {{ $log->status_code === 200 ? 'bg-[#D3F9D8] text-green-950' : 'bg-[#FFE3E3] text-rose-950' }}">
                                    {{ $log->status_code }}
                                </span>
                            </td>
                            <td class="p-3 border-r border-black text-center font-bold">
                                {{ $log->records_count }} baris
                            </td>
                            <td class="p-3 text-center text-slate-600">
                                {{ $log->response_time_ms }} ms
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-sans font-semibold">
                                Belum ada riwayat aktivitas pemanggilan API.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Klien -->
<div id="createClientModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Daftarkan API Client Baru
            </h3>
            <button onclick="closeModal('createClientModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.api-clients.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-heading font-bold text-black mb-1">Nama Aplikasi / Instansi Mitra *</label>
                <input type="text" name="name" required placeholder="Contoh: Aplikasi CBT Online, E-Rapor Madrasah" class="w-full px-3 py-2 neo-input text-xs bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-black mb-1">ID Unik Klien (App ID) *</label>
                <input type="text" name="app_id" required placeholder="Contoh: APP-CBT-01" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono uppercase">
                <p class="text-[10px] text-slate-500 mt-1">Gunakan kode alfanumerik tanpa spasi.</p>
            </div>

            <div>
                <label class="block font-heading font-bold text-black mb-1">IP Whitelist (Opsional)</label>
                <textarea name="ip_whitelist" rows="2" placeholder="Contoh: 192.168.1.50, 103.20.10.1 (pisahkan koma jika lebih dari 1)" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono"></textarea>
                <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika diizinkan diakses dari IP mana saja.</p>
            </div>

            <div>
                <label class="block font-heading font-bold text-black mb-1">Batas Request (Rate Limit)</label>
                <input type="number" name="rate_limit" value="60" min="10" max="1000" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                <p class="text-[10px] text-slate-500 mt-1">Maksimal request per menit (default: 60).</p>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createClientModal')" class="neo-btn bg-white text-black px-4 py-2">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 font-heading font-black">
                    Generate API Key
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Klien -->
<div id="editClientModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data API Client
            </h3>
            <button onclick="closeModal('editClientModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editClientForm" action="" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-black mb-1">Nama Aplikasi / Instansi Mitra *</label>
                <input type="text" id="editClientName" name="name" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-black mb-1">IP Whitelist (Opsional)</label>
                <textarea id="editClientWhitelist" name="ip_whitelist" rows="2" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono"></textarea>
            </div>

            <div>
                <label class="block font-heading font-bold text-black mb-1">Batas Request (Rate Limit per menit)</label>
                <input type="number" id="editClientRate" name="rate_limit" min="10" max="1000" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editClientModal')" class="neo-btn bg-white text-black px-4 py-2">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FFD43B] text-black px-5 py-2 font-heading font-black">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dokumentasi Integrasi -->
<div id="apiDocModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-2xl w-full p-6 space-y-4 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>📖</span> Panduan Integrasi Student Transfer API
            </h3>
            <button onclick="closeModal('apiDocModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <div class="space-y-3 text-xs text-slate-800 leading-relaxed">
            <p>
                Aplikasi eksternal dapat melakukan penarikan data siswa menggunakan HTTP GET Request ke endpoint berikut:
            </p>

            <div class="bg-slate-900 text-slate-100 p-3 rounded font-mono text-[11px] space-y-1">
                <div class="text-slate-400"># 1. Ambil daftar siswa dengan filter & pagination</div>
                <div>GET {{ url('/api/v1/students') }}</div>
                <div class="text-slate-400 mt-2"># 2. Ambil 1 siswa berdasarkan NIS atau NISN</div>
                <div>GET {{ url('/api/v1/students/{identifier}') }}</div>
                <div class="text-slate-400 mt-2"># 3. Bulk export data siswa</div>
                <div>GET {{ url('/api/v1/students/export-transfer') }}</div>
            </div>

            <div class="font-heading font-black text-black text-xs uppercase pt-2">Header Wajib:</div>
            <div class="bg-slate-100 border border-black p-2 font-mono text-[11px]">
                X-API-Key: sk_live_xxxxxxxxxxxxxxxxxxxxxxxx
            </div>

            <div class="font-heading font-black text-black text-xs uppercase pt-2">Contoh Pemanggilan via cURL:</div>
            <pre class="bg-slate-900 text-emerald-400 p-3 rounded font-mono text-[11px] overflow-x-auto">
curl -X GET "{{ url('/api/v1/students?per_page=20') }}" \
  -H "X-API-Key: sk_live_YOUR_KEY_HERE" \
  -H "Accept: application/json"</pre>
        </div>

        <div class="pt-3 border-t-2 border-slate-200 flex justify-end">
            <button type="button" onclick="closeModal('apiDocModal')" class="neo-btn bg-[#FFD43B] text-black px-4 py-2 font-heading font-black text-xs">
                Tutup Panduan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        const btnClients = document.getElementById('tabBtnClients');
        const btnLogs = document.getElementById('tabBtnLogs');
        const contentClients = document.getElementById('tabContentClients');
        const contentLogs = document.getElementById('tabContentLogs');

        if (tab === 'clients') {
            btnClients.className = 'px-3.5 py-1.5 text-xs font-heading font-black uppercase border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]';
            btnLogs.className = 'px-3.5 py-1.5 text-xs font-heading font-bold uppercase border-2 border-black bg-white text-slate-700 hover:bg-slate-50';
            contentClients.classList.remove('hidden');
            contentLogs.classList.add('hidden');
        } else {
            btnLogs.className = 'px-3.5 py-1.5 text-xs font-heading font-black uppercase border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]';
            btnClients.className = 'px-3.5 py-1.5 text-xs font-heading font-bold uppercase border-2 border-black bg-white text-slate-700 hover:bg-slate-50';
            contentLogs.classList.remove('hidden');
            contentClients.classList.add('hidden');
        }
    }

    function editClient(button) {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const whitelist = button.getAttribute('data-whitelist');
        const rate = button.getAttribute('data-rate');

        document.getElementById('editClientForm').action = `{{ url('admin/api-clients') }}/${id}`;
        document.getElementById('editClientName').value = name;
        document.getElementById('editClientWhitelist').value = whitelist || '';
        document.getElementById('editClientRate').value = rate || 60;

        openModal('editClientModal');
    }

    function copyApiKey(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value);
            alert('API Key berhasil disalin ke clipboard!');
        }
    }
</script>
@endpush
