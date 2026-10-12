@extends('layouts.admin')

@section('title', 'Pusat Integrasi API SIS-EMIS')
@section('page-title', 'Pusat Integrasi API SIS-EMIS')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-2xl">📡</span>
                <h2 class="font-heading font-black text-xl text-black">
                    Pusat Integrasi & Sinkronisasi SIS-EMIS
                </h2>
                @if($connectionStatus['connected'])
                    <span class="neo-badge bg-[#D3F9D8] text-green-950 text-[10px] font-black uppercase">
                        🟢 Terhubung Aktif
                    </span>
                @else
                    <span class="neo-badge bg-[#FFE3E3] text-red-950 text-[10px] font-black uppercase">
                        🔴 Belum Terhubung
                    </span>
                @endif
            </div>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Kelola kredensial koneksi ke server master SIS-EMIS Kemenag untuk sinkronisasi rombel dan data siswa otomatis.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.api-clients.test-connection') }}" method="POST">
                @csrf
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading font-black shadow-[2px_2px_0px_#000]">
                    <span>🔍</span> Tes Uji Koneksi API
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Notifikasi Flash -->
    @if(session('success'))
    <div class="bg-[#D3F9D8] border-3 border-black p-4 neo-box flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 text-xs font-bold text-green-950">
            <span class="text-lg">✅</span>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-black font-black text-sm hover:opacity-75 cursor-pointer">✕</button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-[#FFE3E3] border-3 border-black p-4 neo-box flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 text-xs font-bold text-red-950">
            <span class="text-lg">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-black font-black text-sm hover:opacity-75 cursor-pointer">✕</button>
    </div>
    @endif

    <!-- Status Koneksi & Informasi Madrasah -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kartu 1: Status Live Handshake -->
        <div class="bg-white neo-box p-5 space-y-4">
            <div class="flex items-center justify-between border-b-2 border-black pb-2">
                <h3 class="font-heading font-black text-xs uppercase tracking-wider text-black flex items-center gap-2">
                    <span>🔌</span> Status Koneksi Live
                </h3>
                <span class="text-[11px] font-mono text-slate-500">{{ $connectionStatus['checked_at'] }}</span>
            </div>

            <div class="space-y-3">
                <div class="p-3 border-2 border-black rounded-lg {{ $connectionStatus['connected'] ? 'bg-[#EBFBEE]' : 'bg-[#FFF5F5]' }}">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full {{ $connectionStatus['connected'] ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
                        <span class="font-heading font-black text-xs {{ $connectionStatus['connected'] ? 'text-green-900' : 'text-red-900' }}">
                            {{ $connectionStatus['connected'] ? 'KONEKSI TERVERIFIKASI (HTTP 200)' : 'TIDAK DAPAT TERHUBUNG' }}
                        </span>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-700 mt-1.5 leading-relaxed">
                        {{ $connectionStatus['message'] }}
                    </p>
                </div>

                @if($connectionStatus['connected'] && $connectionStatus['institution'])
                <div class="space-y-1.5 text-xs bg-slate-50 border-2 border-black p-3 rounded-lg font-medium">
                    <div class="text-[10px] uppercase font-bold text-slate-500">Madrasah Terkoneksi:</div>
                    <div class="font-heading font-black text-sm text-black">
                        {{ $connectionStatus['institution']['name'] ?? 'MIN 2 TANGGAMUS' }}
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-mono text-slate-700 pt-1">
                        <span>NSM: <b>{{ $connectionStatus['institution']['nsm'] ?? '-' }}</b></span>
                        <span>•</span>
                        <span>NPSN: <b>{{ $connectionStatus['institution']['npsn'] ?? '-' }}</b></span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Kartu 2: Komparasi Data (EMIS vs Absensi) -->
        <div class="bg-white neo-box p-5 space-y-4 lg:col-span-2">
            <div class="flex items-center justify-between border-b-2 border-black pb-2">
                <h3 class="font-heading font-black text-xs uppercase tracking-wider text-black flex items-center gap-2">
                    <span>📊</span> Perbandingan Data Terdata (SIS-EMIS vs Absensi Lokal)
                </h3>
                <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px]">Sinkronisasi Realtime</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="border-2 border-black p-3.5 bg-[#FFF9DB] rounded-lg">
                    <div class="text-[10px] uppercase font-bold text-slate-600">Rombel di EMIS</div>
                    <div class="text-2xl font-heading font-black text-black mt-1">
                        {{ $connectionStatus['emis_rombels_count'] ?: 17 }}
                    </div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5">Rombongan Belajar</div>
                </div>

                <div class="border-2 border-black p-3.5 bg-white rounded-lg">
                    <div class="text-[10px] uppercase font-bold text-slate-600">Rombel di Absensi</div>
                    <div class="text-2xl font-heading font-black {{ $localStats['local_classes_count'] > 0 ? 'text-green-700' : 'text-slate-400' }} mt-1">
                        {{ $localStats['local_classes_count'] }}
                    </div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5">Tersimpan di Database</div>
                </div>

                <div class="border-2 border-black p-3.5 bg-[#E7F5FF] rounded-lg">
                    <div class="text-[10px] uppercase font-bold text-slate-600">Siswa di EMIS</div>
                    <div class="text-2xl font-heading font-black text-black mt-1">
                        {{ $connectionStatus['emis_students_count'] ?: 396 }}
                    </div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5">Siswa Terdata Master</div>
                </div>

                <div class="border-2 border-black p-3.5 bg-white rounded-lg">
                    <div class="text-[10px] uppercase font-bold text-slate-600">Siswa di Absensi</div>
                    <div class="text-2xl font-heading font-black {{ $localStats['local_students_count'] > 0 ? 'text-green-700' : 'text-slate-400' }} mt-1">
                        {{ $localStats['local_students_count'] }}
                    </div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5">Siswa + Akun & QR</div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[11px] text-slate-600 font-medium border-t border-slate-200">
                <div class="flex items-center gap-1.5">
                    <span>💡</span>
                    <span>Jika data lokal masih 0, silakan gunakan tombol <b>Pusat Aksi Cepat</b> di bawah.</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.classes.index') }}" class="font-bold text-blue-600 hover:underline">Ke Manajemen Kelas ➔</a>
                    <a href="{{ route('admin.students.index') }}" class="font-bold text-blue-600 hover:underline">Ke Manajemen Siswa ➔</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Pengaturan Konfigurasi & Pusat Aksi Cepat -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Konfigurasi API (5 Kolom) -->
        <div class="lg:col-span-5 bg-white neo-box p-5 space-y-4">
            <div class="flex items-center justify-between border-b-2 border-black pb-2">
                <h3 class="font-heading font-black text-sm text-black flex items-center gap-2">
                    <span>⚙️</span> Konfigurasi Kredensial API SIS-EMIS
                </h3>
                <span class="neo-badge bg-slate-100 text-black text-[10px]">File .env</span>
            </div>

            <form action="{{ route('admin.api-clients.save-config') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Base URL API SIS-EMIS *
                    </label>
                    <input type="url" name="sis_emis_api_url" required value="{{ old('sis_emis_api_url', $apiUrl) }}" 
                        placeholder="http://127.0.0.1:8000/api/v1"
                        class="w-full px-3 py-2 neo-input text-xs font-mono bg-slate-50">
                    <span class="text-[11px] text-slate-500 font-semibold block mt-1">
                        Endpoint root API SIS-EMIS (default lokal: <code>http://127.0.0.1:8000/api/v1</code>).
                    </span>
                </div>

                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Sanctum Bearer Token SIS-EMIS *
                    </label>
                    <div class="relative">
                        <input type="password" id="apiTokenInput" name="sis_emis_api_token" required value="{{ old('sis_emis_api_token', $apiToken) }}"
                            placeholder="7|0FzUJwMGY5pLQ239QunSeYvD..."
                            class="w-full px-3 py-2 neo-input text-xs font-mono bg-slate-50 pr-20">
                        <button type="button" onclick="toggleTokenVisibility()" class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-600 hover:text-black px-1.5 py-0.5 border border-slate-300 rounded bg-white cursor-pointer">
                            <span id="toggleTokenText">Lihat</span>
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-500 font-semibold block mt-1">
                        Token Sanctum dengan ability <code>students:read</code> & <code>view-sensitive-data</code>.
                    </span>
                </div>

                <div class="p-3 bg-amber-50 border-2 border-black rounded-lg text-[11px] text-slate-700 space-y-1 font-medium">
                    <div class="font-bold text-black flex items-center gap-1">
                        <span>ℹ️</span> Cara Menerbitkan Token Baru:
                    </div>
                    <p class="leading-relaxed">
                        Jalankan perintah ini di terminal proyek <code>sis-Emis</code>:
                    </p>
                    <div class="bg-black text-[#20C997] p-2 rounded font-mono text-[10px] break-all select-all">
                        php artisan emis:issue-token --abilities="students:read,view-sensitive-data"
                    </div>
                </div>

                <div class="pt-2 border-t-2 border-slate-200 flex items-center justify-end">
                    <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2.5 text-xs font-heading font-black flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000]">
                        <span>💾</span> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Pusat Aksi Cepat Sinkronisasi (7 Kolom) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Box Aksi 1: Tarik Rombel/Kelas -->
            <div class="bg-white neo-box p-5 space-y-4">
                <div class="flex items-center justify-between border-b-2 border-black pb-2">
                    <h3 class="font-heading font-black text-sm text-black flex items-center gap-2">
                        <span>🏫</span> Aksi 1: Sinkronisasi Rombel Resmi ke Tabel Kelas
                    </h3>
                    <span class="neo-badge bg-[#A5D8FF] text-black text-[10px]">Langkah Pertama</span>
                </div>

                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                    Menarik seluruh 17 Rombongan Belajar resmi dari EMIS (Tingkat 1 s/d 6) dan menyimpannya secara idempoten ke tabel kelas database Absensi.
                </p>

                <form action="{{ route('admin.api-clients.sync-classes') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @csrf
                    <div class="flex-1">
                        <select name="academic_year_id" required class="w-full px-3 py-2 neo-input text-xs font-bold bg-slate-50">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                    Tahun Ajaran: {{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '★ Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="neo-btn bg-[#A5D8FF] hover:bg-[#74c0fc] text-black px-5 py-2 text-xs font-heading font-black flex items-center justify-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000] shrink-0">
                        <span>🔄</span> Tarik 17 Rombel EMIS
                    </button>
                </form>
            </div>

            <!-- Box Aksi 2: Tarik Siswa per Kelas atau Semua -->
            <div class="bg-white neo-box p-5 space-y-4">
                <div class="flex items-center justify-between border-b-2 border-black pb-2">
                    <h3 class="font-heading font-black text-sm text-black flex items-center gap-2">
                        <span>👥</span> Aksi 2: Sinkronisasi Data Siswa & Generate QR
                    </h3>
                    <span class="neo-badge bg-[#FFD43B] text-black text-[10px]">Langkah Kedua</span>
                </div>

                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                    Tarik data siswa dari SIS-EMIS. Siswa baru otomatis dibuatkan <b>akun login (role: siswa)</b> dan <b>kode QR absensi unik</b>.
                </p>

                <form id="syncStudentsForm" action="{{ route('admin.api-clients.sync-students') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <select id="rombelSelect" name="rombel" class="w-full px-3 py-2 neo-input text-xs font-bold bg-slate-50">
                            <option value="">-- Tarik Seluruh Siswa Semua Rombel Sekaligus ({{ $connectionStatus['emis_students_count'] ?: '396' }} Siswa) --</option>
                            @php
                                $grouped = $classes->sortBy(['level', 'name'])->groupBy('level');
                            @endphp
                            @foreach($grouped as $lvl => $list)
                                <optgroup label="Tingkat {{ $lvl }} (Kelas {{ $lvl }})">
                                    @foreach($list as $c)
                                        <option value="{{ $c->name }}">
                                            {{ $c->name }} ({{ $c->students->count() }} Siswa Lokal)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[11px] text-slate-500 font-semibold">
                            Pilih rombel tertentu atau pilih opsi teratas untuk tarik semua.
                        </span>
                        <button type="submit" id="btnSubmitSync" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-heading font-black flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000] shrink-0">
                            <span>🚀</span> Mulai Tarik Data Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog Progress Bar Sinkronisasi SIS-EMIS (Neo-brutalism Real-time) -->
<!-- ========================================================================= -->
<div id="syncProgressModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden transition-opacity duration-200">
    <div class="bg-white border-3 border-black shadow-[6px_6px_0px_#000] w-full max-w-xl rounded-xl overflow-hidden flex flex-col">
        <!-- Header Modal -->
        <div id="syncModalHeader" class="bg-[#FFD43B] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200">
            <div class="flex items-center gap-2">
                <span class="text-xl" id="syncModalIcon">🚀</span>
                <h3 class="font-heading font-black text-sm text-black" id="syncModalTitle">
                    Sinkronisasi Siswa dari SIS-EMIS
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <span id="syncBadgeStatus" class="neo-badge bg-black text-[#20C997] text-[10px] font-mono font-black uppercase">
                    Menghubungkan...
                </span>
                <button type="button" id="btnHeaderClose" onclick="closeSyncModal()" class="text-black hover:text-red-600 font-black text-sm px-1 cursor-pointer hidden" title="Tutup Modal">
                    ✕
                </button>
            </div>
        </div>

        <!-- Body Modal -->
        <div class="p-5 space-y-4">
            <!-- Progress Bar Section -->
            <div>
                <div class="flex items-center justify-between text-xs font-bold text-black mb-1.5">
                    <span id="syncProgressLabel" class="flex items-center gap-1.5 font-heading">
                        <span class="animate-spin inline-block" id="syncSpinner">⏳</span>
                        <span id="syncStatusText">Menghubungkan ke API server SIS-EMIS...</span>
                    </span>
                    <span id="syncPercentText" class="font-mono font-black text-sm text-black bg-slate-100 px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_#000]">
                        0%
                    </span>
                </div>

                <!-- Bar Track & Fill -->
                <div class="w-full bg-slate-100 h-5 border-2 border-black rounded-lg overflow-hidden p-0.5 relative shadow-[2px_2px_0px_#000]">
                    <div id="syncProgressBar" 
                         class="bg-[#20C997] h-full rounded transition-all duration-150 ease-out flex items-center justify-end pr-1 text-[10px] font-black text-black" 
                         style="width: 0%;">
                    </div>
                </div>

                <!-- Sub-info: Counter & Current Student -->
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mt-2">
                    <div class="truncate max-w-[280px]">
                        <span class="text-slate-400">Sedang diproses:</span>
                        <span id="syncCurrentStudent" class="font-bold text-black font-mono">Menyiapkan...</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Progres:</span>
                        <span id="syncCounterText" class="font-mono font-bold text-black">0 / 0 Siswa</span>
                    </div>
                </div>
            </div>

            <!-- Mini Summary Cards (Baru, Diperbarui, Total Remote) -->
            <div class="grid grid-cols-3 gap-2 pt-1">
                <div class="p-2.5 bg-emerald-50 border-2 border-black rounded-lg text-center shadow-[1px_1px_0px_#000]">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase block">Siswa Baru</span>
                    <span id="syncSummaryImported" class="font-mono font-black text-base text-emerald-700">0</span>
                </div>
                <div class="p-2.5 bg-blue-50 border-2 border-black rounded-lg text-center shadow-[1px_1px_0px_#000]">
                    <span class="text-[10px] font-bold text-blue-800 uppercase block">Diperbarui</span>
                    <span id="syncSummaryUpdated" class="font-mono font-black text-base text-blue-700">0</span>
                </div>
                <div class="p-2.5 bg-amber-50 border-2 border-black rounded-lg text-center shadow-[1px_1px_0px_#000]">
                    <span class="text-[10px] font-bold text-amber-800 uppercase block">Total EMIS</span>
                    <span id="syncSummaryTotal" class="font-mono font-black text-base text-amber-700">0</span>
                </div>
            </div>

            <!-- Terminal Log Box (Black background, green text, auto-scroll) -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span class="flex items-center gap-1 font-mono">
                        <span>📟</span> Log Aktivitas Real-time:
                    </span>
                    <button type="button" onclick="clearSyncLogs()" class="text-[10px] text-slate-500 hover:text-black underline cursor-pointer">
                        Bersihkan Log
                    </button>
                </div>
                <div id="syncLogContainer" class="bg-black text-[#20C997] p-3 rounded-lg border-2 border-black font-mono text-[11px] h-36 overflow-y-auto space-y-1 shadow-inner select-text">
                    <div class="text-slate-400 italic">[Menunggu stream data dari server...]</div>
                </div>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="bg-slate-50 border-t-2 border-black p-3.5 flex items-center justify-between gap-3">
            <span id="syncFooterHint" class="text-[11px] text-slate-500 font-medium">
                Mohon jangan menutup jendela ini selama proses sinkronisasi berlangsung.
            </span>
            <div class="flex items-center gap-2">
                <button type="button" id="btnCancelSync" onclick="cancelSync()" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-3.5 py-1.5 text-xs font-bold border border-black rounded cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btnRetrySync" onclick="retrySync()" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-1.5 text-xs font-black border-2 border-black rounded cursor-pointer shadow-[2px_2px_0px_#000] hidden">
                    🔄 Coba Lagi
                </button>
                <button type="button" id="btnCloseReloadSync" onclick="finishAndReload()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-1.5 text-xs font-black border-2 border-black rounded cursor-pointer shadow-[2px_2px_0px_#000] hidden">
                    ✅ Selesai & Muat Ulang
                </button>
                <button type="button" id="btnSimpleClose" onclick="closeSyncModal()" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-3.5 py-1.5 text-xs font-bold border border-black rounded cursor-pointer hidden">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleTokenVisibility() {
        const input = document.getElementById('apiTokenInput');
        const text = document.getElementById('toggleTokenText');
        if (input.type === 'password') {
            input.type = 'text';
            text.innerText = 'Sembunyikan';
        } else {
            input.type = 'password';
            text.innerText = 'Lihat';
        }
    }

    // ==========================================
    // LOGIKA STREAMING PROGRES SINKRONISASI (SSE)
    // ==========================================
    let eventSource = null;
    let currentRombel = '';
    let isSyncComplete = false;

    document.addEventListener('DOMContentLoaded', function () {
        const syncForm = document.getElementById('syncStudentsForm');
        if (syncForm) {
            syncForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const rombelVal = document.getElementById('rombelSelect').value;
                currentRombel = rombelVal;
                startSync(rombelVal);
            });
        }
    });

    function getTimestamp() {
        const now = new Date();
        return now.toTimeString().split(' ')[0];
    }

    function addSyncLog(message, type = 'info') {
        const container = document.getElementById('syncLogContainer');
        if (!container) return;

        const time = getTimestamp();
        const line = document.createElement('div');
        line.className = 'leading-relaxed break-words';

        let colorClass = 'text-[#20C997]';
        let prefix = '[•]';

        if (type === 'add') {
            colorClass = 'text-[#51CF66] font-bold';
            prefix = '[+]';
        } else if (type === 'update') {
            colorClass = 'text-[#74C0FC]';
            prefix = '[~]';
        } else if (type === 'skip') {
            colorClass = 'text-[#FFA94D]';
            prefix = '[!]';
        } else if (type === 'error') {
            colorClass = 'text-[#FF6B6B] font-bold';
            prefix = '[✗]';
        } else if (type === 'success') {
            colorClass = 'text-[#FFD43B] font-bold';
            prefix = '[★]';
        }

        line.innerHTML = `<span class="text-slate-500">[${time}]</span> <span class="${colorClass}">${prefix} ${escapeHtml(message)}</span>`;
        container.appendChild(line);
        container.scrollTop = container.scrollHeight;
    }

    function clearSyncLogs() {
        const container = document.getElementById('syncLogContainer');
        if (container) {
            container.innerHTML = '<div class="text-slate-400 italic">[Log telah dibersihkan]</div>';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function resetSyncUI() {
        isSyncComplete = false;
        document.getElementById('syncModalHeader').className = 'bg-[#FFD43B] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200';
        document.getElementById('syncModalIcon').innerText = '🚀';
        document.getElementById('syncModalTitle').innerText = 'Sinkronisasi Siswa dari SIS-EMIS';
        
        document.getElementById('syncBadgeStatus').className = 'neo-badge bg-black text-[#20C997] text-[10px] font-mono font-black uppercase';
        document.getElementById('syncBadgeStatus').innerText = 'Menghubungkan...';

        document.getElementById('syncSpinner').classList.remove('hidden');
        document.getElementById('syncStatusText').innerText = 'Menghubungkan ke API server SIS-EMIS...';
        document.getElementById('syncPercentText').innerText = '0%';
        
        const bar = document.getElementById('syncProgressBar');
        bar.style.width = '0%';
        bar.className = 'bg-[#20C997] h-full rounded transition-all duration-150 ease-out flex items-center justify-end pr-1 text-[10px] font-black text-black';

        document.getElementById('syncCurrentStudent').innerText = 'Memulai proses...';
        document.getElementById('syncCounterText').innerText = '0 / 0 Siswa';

        document.getElementById('syncSummaryImported').innerText = '0';
        document.getElementById('syncSummaryUpdated').innerText = '0';
        document.getElementById('syncSummaryTotal').innerText = '0';

        document.getElementById('syncFooterHint').innerText = 'Mohon jangan menutup jendela ini selama proses sinkronisasi berlangsung.';

        // Tombol-tombol kontrol
        document.getElementById('btnCancelSync').classList.remove('hidden');
        document.getElementById('btnRetrySync').classList.add('hidden');
        document.getElementById('btnCloseReloadSync').classList.add('hidden');
        document.getElementById('btnSimpleClose').classList.add('hidden');
        document.getElementById('btnHeaderClose').classList.add('hidden');

        clearSyncLogs();
    }

    function startSync(rombel) {
        const modal = document.getElementById('syncProgressModal');
        modal.classList.remove('hidden');

        resetSyncUI();

        let streamUrl = "{{ route('admin.api-clients.sync-students-stream') }}";
        if (rombel) {
            streamUrl += '?rombel=' + encodeURIComponent(rombel);
            addSyncLog('Filter rombel: ' + rombel, 'info');
        } else {
            addSyncLog('Mode: Tarik seluruh siswa dari semua rombel', 'info');
        }

        if (eventSource) {
            eventSource.close();
        }

        eventSource = new EventSource(streamUrl);

        eventSource.addEventListener('log', function (e) {
            try {
                const data = JSON.parse(e.data);
                addSyncLog(data.message, data.type || 'info');
            } catch (err) {
                console.error(err);
            }
        });

        eventSource.addEventListener('init', function (e) {
            try {
                const data = JSON.parse(e.data);
                document.getElementById('syncSummaryTotal').innerText = data.total;
                document.getElementById('syncCounterText').innerText = '0 / ' + data.total + ' Siswa';
                document.getElementById('syncBadgeStatus').className = 'neo-badge bg-[#FFD43B] text-black text-[10px] font-mono font-black uppercase';
                document.getElementById('syncBadgeStatus').innerText = 'Memproses...';
                document.getElementById('syncStatusText').innerText = 'Mengunduh & memproses data siswa...';
                if (data.message) {
                    addSyncLog(data.message, 'success');
                }
            } catch (err) {
                console.error(err);
            }
        });

        eventSource.addEventListener('progress', function (e) {
            try {
                const data = JSON.parse(e.data);
                const percent = data.percent || 0;

                // Update Progress Bar & Counter
                document.getElementById('syncPercentText').innerText = percent + '%';
                document.getElementById('syncProgressBar').style.width = percent + '%';
                document.getElementById('syncCounterText').innerText = data.current + ' / ' + data.total + ' Siswa';
                document.getElementById('syncCurrentStudent').innerText = (data.student_name || '-') + (data.rombel ? ' (' + data.rombel + ')' : '');

                // Update summary counters
                document.getElementById('syncSummaryImported').innerText = data.imported_count || 0;
                document.getElementById('syncSummaryUpdated').innerText = data.updated_count || 0;

                if (data.message) {
                    const actionType = data.action === 'imported' ? 'add' : (data.action === 'updated' ? 'update' : 'skip');
                    addSyncLog(data.message, actionType);
                }
            } catch (err) {
                console.error(err);
            }
        });

        eventSource.addEventListener('complete', function (e) {
            isSyncComplete = true;
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }

            try {
                const data = JSON.parse(e.data);
                setSyncSuccess(data);
            } catch (err) {
                setSyncSuccess({ message: 'Sinkronisasi berhasil selesai!' });
            }
        });

        eventSource.addEventListener('error', function (e) {
            let errorMsg = 'Terjadi kesalahan atau koneksi terputus ke server.';
            try {
                if (e.data) {
                    const data = JSON.parse(e.data);
                    if (data.message) errorMsg = data.message;
                }
            } catch (err) {}

            if (!isSyncComplete) {
                setSyncError(errorMsg);
            }

            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
        });

        eventSource.onerror = function () {
            if (!isSyncComplete) {
                setSyncError('Koneksi stream terputus secara tidak terduga.');
                if (eventSource) {
                    eventSource.close();
                    eventSource = null;
                }
            }
        };
    }

    function setSyncSuccess(data) {
        document.getElementById('syncModalHeader').className = 'bg-[#20C997] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200';
        document.getElementById('syncModalIcon').innerText = '🎉';
        document.getElementById('syncModalTitle').innerText = 'Sinkronisasi Berhasil Selesai!';

        document.getElementById('syncBadgeStatus').className = 'neo-badge bg-black text-[#20C997] text-[10px] font-mono font-black uppercase';
        document.getElementById('syncBadgeStatus').innerText = 'Selesai 100%';

        document.getElementById('syncSpinner').classList.add('hidden');
        document.getElementById('syncStatusText').innerText = 'Seluruh data siswa berhasil diproses!';
        document.getElementById('syncPercentText').innerText = '100%';
        document.getElementById('syncProgressBar').style.width = '100%';

        if (data.imported !== undefined) document.getElementById('syncSummaryImported').innerText = data.imported;
        if (data.updated !== undefined) document.getElementById('syncSummaryUpdated').innerText = data.updated;
        if (data.total !== undefined) document.getElementById('syncSummaryTotal').innerText = data.total;

        document.getElementById('syncFooterHint').innerText = 'Data siswa dan akun telah diperbarui di database absensi.';
        addSyncLog(data.message || 'Sinkronisasi berhasil rampung!', 'success');

        // Sembunyikan Batal, tampilkan tombol Selesai & Reload
        document.getElementById('btnCancelSync').classList.add('hidden');
        document.getElementById('btnCloseReloadSync').classList.remove('hidden');
        document.getElementById('btnHeaderClose').classList.remove('hidden');
    }

    function setSyncError(message) {
        document.getElementById('syncModalHeader').className = 'bg-[#FF6B6B] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200';
        document.getElementById('syncModalIcon').innerText = '⚠️';
        document.getElementById('syncModalTitle').innerText = 'Sinkronisasi Gagal';

        document.getElementById('syncBadgeStatus').className = 'neo-badge bg-black text-[#FF6B6B] text-[10px] font-mono font-black uppercase';
        document.getElementById('syncBadgeStatus').innerText = 'Gagal';

        document.getElementById('syncSpinner').classList.add('hidden');
        document.getElementById('syncStatusText').innerText = 'Terjadi kesalahan saat sinkronisasi.';

        const bar = document.getElementById('syncProgressBar');
        bar.className = 'bg-[#FF6B6B] h-full rounded transition-all duration-150 ease-out flex items-center justify-end pr-1 text-[10px] font-black text-black';

        document.getElementById('syncFooterHint').innerText = 'Silakan periksa log di atas atau coba kembali.';
        addSyncLog('GAGAL: ' + message, 'error');

        document.getElementById('btnCancelSync').classList.add('hidden');
        document.getElementById('btnRetrySync').classList.remove('hidden');
        document.getElementById('btnSimpleClose').classList.remove('hidden');
        document.getElementById('btnHeaderClose').classList.remove('hidden');
    }

    function cancelSync() {
        if (eventSource) {
            eventSource.close();
            eventSource = null;
        }
        addSyncLog('Sinkronisasi dibatalkan oleh pengguna.', 'skip');
        document.getElementById('syncBadgeStatus').innerText = 'Dibatalkan';
        document.getElementById('syncSpinner').classList.add('hidden');
        document.getElementById('syncStatusText').innerText = 'Proses dibatalkan.';
        document.getElementById('btnCancelSync').classList.add('hidden');
        document.getElementById('btnSimpleClose').classList.remove('hidden');
        document.getElementById('btnHeaderClose').classList.remove('hidden');
    }

    function retrySync() {
        startSync(currentRombel);
    }

    function closeSyncModal() {
        if (eventSource) {
            eventSource.close();
            eventSource = null;
        }
        document.getElementById('syncProgressModal').classList.add('hidden');
    }

    function finishAndReload() {
        window.location.reload();
    }
</script>
@endpush
@endsection
