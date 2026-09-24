@extends('layouts.guru')

@section('title', 'Kelola Tabungan Siswa')
@section('page-title', 'Kelola Tabungan Siswa')

@section('content')
<script>
    (function() {
        const STORAGE_KEY = 'guru_tabungan_selected_class_{{ auth()->id() }}';
        const urlParams = new URLSearchParams(window.location.search);
        const availableClassIds = [{{ $classes->pluck('id')->implode(',') }}];
        const currentClassId = {{ (int) $selectedClassId }};

        if (urlParams.has('class_id')) {
            const urlClassId = parseInt(urlParams.get('class_id'), 10);
            if (availableClassIds.includes(urlClassId)) {
                try {
                    localStorage.setItem(STORAGE_KEY, urlClassId);
                } catch (e) {}
            }
        } else {
            let savedClassId = null;
            try {
                savedClassId = parseInt(localStorage.getItem(STORAGE_KEY), 10);
            } catch (e) {}

            if (savedClassId && availableClassIds.includes(savedClassId) && savedClassId !== currentClassId) {
                urlParams.set('class_id', savedClassId);
                window.location.replace(window.location.pathname + '?' + urlParams.toString());
                return;
            } else if (currentClassId > 0 && availableClassIds.includes(currentClassId)) {
                try {
                    localStorage.setItem(STORAGE_KEY, currentClassId);
                } catch (e) {}
            }
        }
    })();
</script>
<div class="space-y-6">
    <!-- Header Banner & Action Shortcuts -->
    <div class="bg-gradient-to-r from-[#1E293B] via-[#0F172A] to-[#1E293B] border-4 border-black p-5 sm:p-6 text-white neo-box flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="text-2xl sm:text-3xl">💰</span>
                <h2 class="font-heading font-black text-lg sm:text-2xl text-white">Kas Tabungan Siswa</h2>
                <span class="neo-badge bg-[#FFD43B] text-black text-[10px] font-black uppercase px-2 py-0.5">
                    Pengelola Tabungan
                </span>
            </div>
            <p class="text-xs text-slate-300 max-w-xl">
                Layanan pencatatan transaksi tabungan per rombel/kelas, pendaftaran penabung santri, cetak slip transaksi, dan rekapitulasi buku kas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.savings.transactions') }}" class="neo-btn bg-white text-black text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Buku Kas / Mutasi</span>
            </a>
            <a href="{{ route('guru.savings.export', ['class_id' => $selectedClassId]) }}" class="neo-btn bg-[#20C997] text-white text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Kelas Ini</span>
            </a>
        </div>
    </div>

    @if(session('last_transaction_id'))
        <div class="bg-[#D3F9D8] border-3 border-black p-4 neo-box flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🖨️</span>
                <div>
                    <div class="font-heading font-black text-sm text-emerald-950">Transaksi Berhasil Diproses!</div>
                    <div class="text-xs text-emerald-900">Kuitansi / bukti transaksi dapat langsung dicetak.</div>
                </div>
            </div>
            <a href="{{ route('guru.savings.receipt', session('last_transaction_id')) }}" target="_blank" class="neo-btn bg-black text-white text-xs font-bold px-4 py-2 flex items-center gap-1.5 hover:bg-slate-800">
                <span>Cetak Kuitansi</span>
                <span>→</span>
            </a>
        </div>
    @endif

    <!-- 4 Stats Cards (Global Kas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Kas -->
        <div class="bg-[#E7F5FF] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-blue-950">Total Saldo Seluruh Siswa</span>
                <span class="text-xl">🏦</span>
            </div>
            <div class="font-mono font-black text-2xl text-blue-950 mt-2">
                Rp {{ number_format($stats['total_balance'], 0, ',', '.') }}
            </div>
            <div class="text-[10px] font-semibold text-blue-800 mt-1">
                Dari {{ $stats['total_accounts'] }} Rekening Aktif
            </div>
        </div>

        <!-- Setoran Hari Ini -->
        <div class="bg-[#D3F9D8] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-emerald-950">Setoran Hari Ini</span>
                <span class="text-xl">📥</span>
            </div>
            <div class="font-mono font-black text-2xl text-emerald-900 mt-2">
                Rp {{ number_format($stats['today_deposits'], 0, ',', '.') }}
            </div>
            <div class="text-[10px] font-semibold text-emerald-800 mt-1">
                Total kas masuk hari ini
            </div>
        </div>

        <!-- Penarikan Hari Ini -->
        <div class="bg-[#FFE3E3] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-rose-950">Penarikan Hari Ini</span>
                <span class="text-xl">📤</span>
            </div>
            <div class="font-mono font-black text-2xl text-rose-950 mt-2">
                Rp {{ number_format($stats['today_withdrawals'], 0, ',', '.') }}
            </div>
            <div class="text-[10px] font-semibold text-rose-800 mt-1">
                Total kas keluar hari ini
            </div>
        </div>

        <!-- Arus Kas Bersih Hari Ini -->
        @php
            $netToday = $stats['today_deposits'] - $stats['today_withdrawals'];
        @endphp
        <div class="bg-[#FFF9DB] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-amber-950">Arus Kas Hari Ini</span>
                <span class="text-xl">⚖️</span>
            </div>
            <div class="font-mono font-black text-2xl {{ $netToday >= 0 ? 'text-emerald-900' : 'text-rose-900' }} mt-2">
                {{ $netToday >= 0 ? '+' : '' }}Rp {{ number_format($netToday, 0, ',', '.') }}
            </div>
            <div class="text-[10px] font-semibold text-amber-900 mt-1">
                Selisih kas masuk & keluar
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN UTAMA: LAYANI TRANSAKSI TABUNGAN SISWA BERDASARKAN KELAS -->
    <!-- ========================================================================= -->
    <div class="bg-white neo-box p-5 sm:p-6 border-3 border-black space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-black pb-4">
            <div>
                <h3 class="font-heading font-black text-base sm:text-lg text-black flex items-center gap-2">
                    <span>🏫</span> Layani Transaksi Siswa Per Rombel/Kelas
                </h3>
                <p class="text-xs text-slate-600">Pilih kelas di bawah untuk mengelola daftar penabung dan input setoran/penarikan.</p>
            </div>

            <!-- Tombol Popup Checklist Pendaftaran Siswa -->
            @if($selectedClass && $unregisteredStudents->isNotEmpty())
                <button type="button" onclick="openRegisterChecklistModal()" 
                    class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black px-3.5 py-2 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                    title="Pilih dan daftarkan siswa sebagai penabung">
                    <span>👥</span>
                    <span>+ Daftarkan Siswa ({{ $unregisteredStudents->count() }} Belum Terdaftar)</span>
                </button>
            @endif
        </div>

        <!-- Pemilih Rombel / Kelas (Pills Horizontal Scrollable) -->
        <div class="space-y-2">
            <div class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <span>Pilih Rombel / Kelas:</span>
            </div>
            @if($classes->isEmpty())
                <div class="p-6 bg-[#FFF9DB] border-2 border-black text-center space-y-2">
                    <div class="text-3xl">⚠️</div>
                    <div class="font-heading font-black text-sm text-black">Belum Ada Rombel / Kelas yang Ditugaskan</div>
                    <p class="text-xs text-slate-600 max-w-md mx-auto">
                        Akun Anda memiliki hak akses Pengelola Tabungan, namun Administrator belum memilih rombel/kelas yang ditugaskan kepada Anda. Silakan hubungi Administrator untuk memplot wewenang kelas Anda.
                    </p>
                </div>
            @else
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    @foreach($classes as $c)
                        <a href="{{ route('guru.savings.index', ['class_id' => $c->id]) }}" 
                           onclick="try { localStorage.setItem('guru_tabungan_selected_class_{{ auth()->id() }}', '{{ $c->id }}'); } catch(e) {}"
                           class="neo-btn px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-2
                           {{ $selectedClassId === $c->id 
                                ? 'bg-[#5294FF] text-white shadow-[3px_3px_0px_0px_#000]' 
                                : 'bg-slate-100 hover:bg-slate-200 text-slate-800 shadow-[2px_2px_0px_0px_#000]' }}">
                            <span>{{ $c->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded border border-black {{ $selectedClassId === $c->id ? 'bg-black text-white font-mono' : 'bg-white text-slate-700 font-mono' }}">
                                {{ $c->students_count }} Siswa
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Ringkasan Kelas Terpilih -->
        @if($selectedClass)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#FFF4E6] border-2 border-black p-3.5 text-xs">
                <div>
                    <span class="text-slate-600 font-medium">Kelas Aktif:</span>
                    <div class="font-heading font-black text-base text-black mt-0.5">{{ $selectedClass->name }}</div>
                </div>
                <div>
                    <span class="text-slate-600 font-medium">Penabung Terdaftar:</span>
                    <div class="font-mono font-bold text-sm text-black mt-0.5">
                        <strong class="text-emerald-700 font-black">{{ $classStats['registered_students'] }}</strong> dari {{ $classStats['total_students'] }} Siswa
                        @if($classStats['total_students'] > 0)
                            <span class="text-[10px] text-slate-500">({{ round(($classStats['registered_students'] / $classStats['total_students']) * 100) }}%)</span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="text-slate-600 font-medium">Total Saldo Terkumpul di Kelas:</span>
                    <div class="font-mono font-black text-base text-emerald-800 mt-0.5">
                        Rp {{ number_format($classStats['total_class_balance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter & Sortir Siswa Kelas -->
        <div class="bg-slate-50 border-2 border-black p-3 space-y-3">
            <form action="{{ route('guru.savings.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 text-xs">
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">

                <!-- Search Siswa di Kelas Ini -->
                <div class="sm:col-span-5 relative">
                    <input type="text" name="student_search" value="{{ $studentSearch }}" placeholder="Cari nama / NISN di kelas ini..." 
                        class="w-full neo-input py-1.5 pl-8 text-xs bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Filter Status Penabung (Default: Penabung Aktif Saja) -->
                <div class="sm:col-span-3">
                    <select name="status_filter" class="w-full neo-input py-1.5 text-xs bg-white font-medium">
                        <option value="registered" {{ $statusFilter === 'registered' ? 'selected' : '' }}>Penabung Aktif Saja</option>
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Siswa</option>
                        <option value="unregistered" {{ $statusFilter === 'unregistered' ? 'selected' : '' }}>Belum Terdaftar</option>
                    </select>
                </div>

                <!-- Sortir -->
                <div class="sm:col-span-3">
                    <select name="sort" class="w-full neo-input py-1.5 text-xs bg-white font-medium">
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Nama Siswa (A - Z)</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Nama Siswa (Z - A)</option>
                        <option value="balance_desc" {{ $sort === 'balance_desc' ? 'selected' : '' }}>Saldo Tertinggi</option>
                        <option value="balance_asc" {{ $sort === 'balance_asc' ? 'selected' : '' }}>Saldo Terendah</option>
                    </select>
                </div>

                <!-- Tombol Submit & Reset Filter -->
                <div class="sm:col-span-1 flex items-center gap-1">
                    <button type="submit" class="w-full neo-btn bg-[#FFD43B] text-black font-bold py-1.5 hover:bg-yellow-400" title="Terapkan">
                        Filter
                    </button>
                    @if(!empty($studentSearch) || $statusFilter !== 'registered' || $sort !== 'name_asc')
                        <a href="{{ route('guru.savings.index', ['class_id' => $selectedClassId]) }}" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-2 py-1.5" title="Reset">
                            ↺
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL DAFTAR SISWA (No, Nama Siswa, NISN, Status, Jumlah Saldo, Ditarik, Aksi) -->
        <div class="overflow-x-auto border-2 border-black">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                    <tr>
                        <th class="p-3 border-r border-black text-center w-12">No</th>
                        <th class="p-3 border-r border-black">Nama Siswa</th>
                        <th class="p-3 border-r border-black">NISN</th>
                        <th class="p-3 border-r border-black text-center">Status</th>
                        <th class="p-3 border-r border-black text-right">Jumlah Saldo</th>
                        <th class="p-3 border-r border-black text-right">Total Ditarik</th>
                        <th class="p-3 text-center w-44">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @forelse($classStudents as $idx => $student)
                        @php
                            $account = $student->savingsAccount;
                            $withdrawn = $account ? ($withdrawnTotals[$account->id] ?? 0) : 0;
                            $studentName = $student->user?->name ?? '-';
                            $txCount = $account ? ($account->transactions_count ?? $account->transactions()->count()) : 0;
                            $studentData = [
                                'id' => $student->id,
                                'name' => $studentName,
                                'nis' => $student->nis,
                                'nisn' => $student->nisn ?: '-',
                                'class_name' => $selectedClass?->name ?? '-',
                                'account_number' => $account?->account_number ?? '-',
                                'balance' => (float) ($account?->balance ?? 0),
                                'formatted_balance' => $account ? $account->formatted_balance : 'Rp 0',
                                'photo_url' => $student->photo_url,
                                'status' => $account?->status ?? 'unregistered',
                                'transactions_count' => $txCount,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 text-center font-bold border-r border-black">
                                {{ $idx + 1 }}
                            </td>
                            <td class="p-3 border-r border-black">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-md bg-slate-200 border border-black overflow-hidden shrink-0 flex items-center justify-center font-bold text-xs">
                                        @if($student->photo_url)
                                            <img src="{{ $student->photo_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <span>🎓</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-heading font-bold text-black text-sm">{{ $studentName }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $student->nis }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 border-r border-black font-mono font-bold text-slate-800">
                                {{ $student->nisn ?: '-' }}
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                @if(!$account)
                                    <span class="neo-badge bg-[#FFF3BF] text-amber-950 text-[10px] font-bold px-2 py-0.5">
                                        BELUM TERDAFTAR
                                    </span>
                                @elseif($account->isActive())
                                    <span class="neo-badge bg-[#D3F9D8] text-emerald-950 text-[10px] font-black px-2 py-0.5">
                                        ● AKTIF
                                    </span>
                                @else
                                    <span class="neo-badge bg-slate-200 text-slate-700 text-[10px] font-bold px-2 py-0.5">
                                        TUTUP BUKU
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-black text-sm {{ $account && $account->balance > 0 ? 'text-black' : 'text-slate-400' }}">
                                {{ $account ? $account->formatted_balance : 'Rp 0' }}
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-bold text-xs {{ $withdrawn > 0 ? 'text-rose-700' : 'text-slate-400' }}">
                                Rp {{ number_format($withdrawn, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                @if(!$account)
                                    <!-- Siswa Belum Terdaftar -->
                                    <form action="{{ route('guru.savings.register-student') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <button type="submit" 
                                            class="w-full neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black font-bold text-[11px] py-1 px-2.5 flex items-center justify-center gap-1 shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer"
                                            title="Buka Rekening & Aktifkan Buku Tabungan Siswa">
                                            <span>+</span>
                                            <span>Daftarkan</span>
                                        </button>
                                    </form>
                                @elseif($account->isActive())
                                    <!-- Rekening Aktif: Tombol Input & Aksi Ber-Tooltip (Bebas Glitch) -->
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- 1. Tombol Input Setor / Tarik -->
                                        <button type="button" onclick="openInputModal({{ json_encode($studentData) }})" 
                                            class="neo-btn bg-[#20C997] hover:bg-emerald-500 text-white font-black text-[11px] py-1 px-2.5 inline-flex items-center justify-center gap-1 shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer group relative">
                                            <span>⚡</span>
                                            <span>Input</span>
                                            <!-- Floating Tooltip -->
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                                Setor / Tarik Saldo
                                            </span>
                                        </button>

                                        <!-- 2. Tombol Tutup Buku Tabungan -->
                                        <button type="button" onclick="openCloseAccountModal({{ json_encode($studentData) }})" 
                                            class="neo-btn bg-white hover:bg-rose-50 text-rose-600 border border-black p-1 text-xs inline-flex items-center justify-center shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer group relative" 
                                            title="Tutup Buku Tabungan" aria-label="Tutup Buku Tabungan">
                                            <span>🛑</span>
                                            <!-- Floating Tooltip -->
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                                Tutup Buku
                                            </span>
                                        </button>

                                        <!-- 3. Tombol Batalkan Pendaftaran (Jika Transaksi 0 & Saldo 0) -->
                                        @if($txCount === 0 && (float)$account->balance == 0)
                                            <form id="cancelRegistrationForm_{{ $student->id }}" action="{{ route('guru.savings.cancel-registration') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                <button type="button" 
                                                    onclick="confirmCancelRegistration('cancelRegistrationForm_{{ $student->id }}', '{{ addslashes($studentName) }}')"
                                                    class="neo-btn bg-white hover:bg-amber-50 text-amber-700 border border-black p-1 text-xs inline-flex items-center justify-center shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer group relative" 
                                                    title="Batalkan Pendaftaran" aria-label="Batalkan Pendaftaran">
                                                    <span>↺</span>
                                                    <!-- Floating Tooltip -->
                                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                                        Batal Daftar
                                                    </span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <!-- Rekening Ditutup: Tombol Buka Kembali -->
                                    <form action="{{ route('guru.savings.reopen-account') }}" method="POST" onsubmit="return confirm('Aktifkan kembali rekening buku tabungan untuk siswa {{ addslashes($studentName) }}?')">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <button type="submit" class="w-full neo-btn bg-slate-100 hover:bg-[#20C997] hover:text-white text-slate-800 font-bold text-[11px] py-1 px-2 flex items-center justify-center gap-1 shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer" title="Buka Kembali Rekening">
                                            <span>↻</span>
                                            <span>Buka Kembali</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                                <div class="text-3xl">📭</div>
                                <div class="font-bold text-slate-800">Tidak ada data siswa yang cocok di kelas ini.</div>
                                <p class="text-xs text-slate-500">Silakan pilih kelas lain atau reset filter pencarian di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 10 TRANSAKSI TERAKHIR (GLOBAL MUTASI KAS SEKOLAH) -->
    <!-- ========================================================================= -->
    <div class="bg-white neo-box p-5 border-3 border-black space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b-2 border-black pb-3">
            <div>
                <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span>📜</span> Transaksi Tabungan Terkini
                </h3>
                <p class="text-xs text-slate-500 font-semibold">10 Transaksi setor & tarik terbaru yang telah diproses</p>
            </div>
            <a href="{{ route('guru.savings.transactions') }}" class="text-xs font-black text-blue-600 hover:underline">
                Buka Semua Riwayat Transaksi →
            </a>
        </div>

        <div class="overflow-x-auto border-2 border-black">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black text-center w-10">No</th>
                        <th class="p-2.5 border-r border-black">Kode & Waktu</th>
                        <th class="p-2.5 border-r border-black">Nama Siswa / Kelas</th>
                        <th class="p-2.5 border-r border-black text-center">Jenis</th>
                        <th class="p-2.5 border-r border-black text-right">Nominal</th>
                        <th class="p-2.5 border-r border-black text-right">Saldo Akhir</th>
                        <th class="p-2.5 border-r border-black">Petugas</th>
                        <th class="p-2.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-black font-medium">
                    @forelse($recentTransactions as $idx => $t)
                        <tr class="hover:bg-slate-50">
                            <td class="p-2.5 text-center font-bold border-r border-black">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-mono">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-black">{{ $t->transaction_code }}</span>
                                    @if($t->is_corrected)
                                        <span class="neo-badge bg-[#FFE066] text-[#664D03] text-[9px] px-1.5 py-0.2 font-bold inline-flex items-center gap-0.5 border border-black shadow-[1px_1px_0px_0px_#000]">
                                            <span>⚠️</span> Koreksi
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black">
                                <div class="font-bold text-black">{{ $t->savingsAccount?->student?->user?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $t->savingsAccount?->student?->schoolClass?->name ?? '-' }} • Rek: {{ $t->savingsAccount?->account_number }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                @if($t->isDeposit())
                                    <span class="neo-badge bg-[#20C997] text-white text-[10px] px-2 py-0.5 font-bold">SETOR</span>
                                @else
                                    <span class="neo-badge bg-[#FF6B6B] text-white text-[10px] px-2 py-0.5 font-bold">TARIK</span>
                                @endif
                            </td>
                            <td class="p-2.5 border-r border-black text-right font-mono font-black {{ $t->isDeposit() ? 'text-emerald-700' : 'text-rose-700' }}">
                                <div>{{ $t->isDeposit() ? '+' : '-' }}{{ $t->formatted_amount }}</div>
                                @if($t->is_corrected)
                                    <div class="text-[10px] text-slate-400 font-mono line-through mt-0.5" title="Nominal sebelum koreksi">
                                        {{ $t->formatted_original_amount }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-2.5 border-r border-black text-right font-mono font-bold text-slate-900">
                                {{ $t->formatted_balance_after }}
                            </td>
                            <td class="p-2.5 border-r border-black text-slate-700">
                                <div>{{ $t->handler?->name ?? '-' }}</div>
                                @if($t->is_corrected)
                                    <div class="text-[10px] text-amber-900 font-semibold italic mt-0.5" title="Alasan koreksi">
                                        "{{ $t->correction_reason }}"
                                    </div>
                                @endif
                            </td>
                            <td class="p-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('guru.savings.receipt', $t) }}" target="_blank" 
                                        class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1 shadow-[1px_1px_0px_0px_#000]"
                                        title="Cetak Kuitansi / Slip">
                                        <span>🖨️</span> Slip
                                    </a>
                                    <button type="button"
                                        onclick="openCorrectModal({
                                            code: '{{ $t->transaction_code }}',
                                            student: '{{ addslashes($t->savingsAccount?->student?->user?->name ?? 'Siswa') }}',
                                            accountNumber: '{{ $t->savingsAccount?->account_number ?? '-' }}',
                                            type: '{{ $t->type }}',
                                            typeName: '{{ $t->type === 'deposit' ? 'Setoran (+)' : 'Penarikan (-)' }}',
                                            amount: {{ (float) $t->amount }},
                                            currentBalance: {{ (float) ($t->savingsAccount?->balance ?? 0) }},
                                            actionUrl: '{{ route('guru.savings.transactions.correct', $t) }}'
                                        })"
                                        class="neo-btn bg-[#FFE066] hover:bg-yellow-400 text-black text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1 shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                        title="Koreksi nominal transaksi">
                                        <span>✏️</span> Koreksi
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-500 italic">
                                Belum ada transaksi tabungan yang tercatat. Gunakan tombol "Input Tabungan" pada tabel di atas untuk memulai transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: CHECKLIST PENDAFTARAN SISWA SELEKTIF -->
<!-- ========================================================================= -->
<div id="modalRegisterChecklist" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white border-4 border-black max-w-lg w-full p-5 sm:p-6 neo-box space-y-4 relative">
        <button type="button" onclick="closeRegisterChecklistModal()" class="absolute right-4 top-4 text-black hover:text-rose-600 font-black text-xl p-1 cursor-pointer">
            ✕
        </button>

        <div class="border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-base sm:text-lg text-black flex items-center gap-2">
                <span>👥</span> Daftarkan Siswa Sebagai Penabung
            </h3>
            <p class="text-xs text-slate-600">Pilih siswa di kelas <strong>{{ $selectedClass?->name }}</strong> yang ingin dibuatkan buku tabungan.</p>
        </div>

        <form action="{{ route('guru.savings.register-selected-students') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Select All Control -->
            <div class="bg-slate-100 border-2 border-black p-3 flex items-center justify-between">
                <label class="flex items-center gap-2 text-xs font-black text-black cursor-pointer">
                    <input type="checkbox" id="selectAllStudents" onchange="toggleSelectAll(this)" class="w-4 h-4 accent-black rounded-none">
                    <span>PILIH SEMUA SISWA ({{ $unregisteredStudents->count() }})</span>
                </label>
                <span id="selectedCountBadge" class="text-[11px] font-mono font-bold bg-[#FFD43B] text-black px-2 py-0.5 border border-black rounded">
                    0 Dipilih
                </span>
            </div>

            <!-- List of Unregistered Students -->
            <div class="max-h-64 overflow-y-auto space-y-1.5 border-2 border-black p-2 bg-slate-50">
                @forelse($unregisteredStudents as $uStudent)
                    <label class="flex items-center justify-between p-2 bg-white border border-slate-300 hover:border-black hover:bg-[#FFF4E6] cursor-pointer text-xs transition-colors">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <input type="checkbox" name="student_ids[]" value="{{ $uStudent->id }}" onchange="updateSelectedCount()" class="student-checkbox w-4 h-4 accent-black rounded-none">
                            <div class="min-w-0">
                                <div class="font-bold text-black truncate">{{ $uStudent->user?->name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NISN: {{ $uStudent->nisn ?: $uStudent->nis }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400">Belum Ada Buku</span>
                    </label>
                @empty
                    <div class="text-center py-6 text-xs text-slate-500 italic">
                        Semua siswa di kelas ini sudah terdaftar sebagai penabung.
                    </div>
                @endforelse
            </div>

            <div class="flex items-center gap-2 pt-2 border-t-2 border-black">
                <button type="button" onclick="closeRegisterChecklistModal()" class="w-1/3 neo-btn bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs py-2.5 cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="submitRegisterSelectedBtn" class="w-2/3 neo-btn bg-[#20C997] hover:bg-emerald-600 text-white font-black text-xs py-2.5 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer" disabled>
                    <span>Daftarkan Siswa Terpilih</span>
                    <span>✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: INPUT TABUNGAN SISWA (SETOR / TARIK) -->
<!-- ========================================================================= -->
<div id="modalInputTabungan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white border-4 border-black max-w-lg w-full p-5 sm:p-6 neo-box space-y-5 relative">
        <!-- Close Button -->
        <button type="button" onclick="closeInputModal()" class="absolute right-4 top-4 text-black hover:text-rose-600 font-black text-xl p-1 cursor-pointer">
            ✕
        </button>

        <!-- Modal Header -->
        <div class="border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>⚡</span> Input Transaksi Tabungan
            </h3>
            <p class="text-xs text-slate-600">Layanan setor atau tarik tunai untuk siswa terpilih</p>
        </div>

        <!-- Student Mini Card -->
        <div class="bg-slate-100 border-2 border-black p-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-lg bg-white border-2 border-black overflow-hidden shrink-0 flex items-center justify-center font-bold text-base shadow-[1px_1px_0px_0px_#000]">
                    <img id="modalStudentPhoto" src="" alt="" class="w-full h-full object-cover hidden">
                    <span id="modalStudentPlaceholder">🎓</span>
                </div>
                <div class="min-w-0">
                    <div id="modalStudentName" class="font-heading font-black text-sm text-black truncate">Nama Siswa</div>
                    <div class="text-[11px] text-slate-600">
                        <span id="modalStudentClass">Kelas</span> • Rek: <span id="modalStudentAccount" class="font-mono font-bold text-black">-</span>
                    </div>
                </div>
            </div>
            <div class="shrink-0 text-right">
                <div class="text-[10px] uppercase font-bold text-slate-500">Saldo Saat Ini</div>
                <div id="modalStudentBalance" class="font-mono font-black text-sm text-emerald-800">Rp 0</div>
            </div>
        </div>

        <!-- Switcher Setor vs Tarik -->
        <div class="grid grid-cols-2 gap-2 bg-slate-200 border-2 border-black p-1">
            <button type="button" onclick="setModalTxType('deposit')" id="modalBtnDeposit"
                class="py-2 text-xs font-black uppercase border-2 border-black bg-[#20C997] text-white shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                <span>📥</span> Setor Tunai
            </button>
            <button type="button" onclick="setModalTxType('withdraw')" id="modalBtnWithdraw"
                class="py-2 text-xs font-black uppercase border-2 border-transparent text-slate-700 hover:text-black flex items-center justify-center gap-1.5 cursor-pointer">
                <span>📤</span> Tarik Tunai
            </button>
        </div>

        <!-- Form Setoran -->
        <form id="modalFormDeposit" action="{{ route('guru.savings.deposit') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="student_id" id="modalDepositStudentId" value="">

            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Nominal Setoran (Rp)
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 font-mono font-black text-sm text-black select-none pointer-events-none z-10">Rp</span>
                    <input type="number" name="amount" id="modalDepositAmount" min="500" step="500" required
                        placeholder="0"
                        style="padding-left: 3.25rem !important;"
                        class="w-full neo-input text-base font-mono font-black !pl-13 pr-4 py-2 bg-emerald-50/40">
                </div>
                <!-- Quick Amount Buttons -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" onclick="setModalDepositAmount(1000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">1.000</button>
                    <button type="button" onclick="setModalDepositAmount(2000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">2.000</button>
                    <button type="button" onclick="setModalDepositAmount(5000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">5.000</button>
                    <button type="button" onclick="setModalDepositAmount(10000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">10.000</button>
                    <button type="button" onclick="setModalDepositAmount(20000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">20.000</button>
                    <button type="button" onclick="setModalDepositAmount(50000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">50.000</button>
                    <button type="button" onclick="setModalDepositAmount(100000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">100.000</button>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Catatan / Keterangan (Opsional)
                </label>
                <input type="text" name="description" placeholder="Contoh: Setoran tabungan mingguan" maxlength="255"
                    class="w-full neo-input text-xs font-medium py-2 bg-white">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeInputModal()" class="w-1/3 neo-btn bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs py-2.5 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 neo-btn bg-[#20C997] hover:bg-emerald-600 text-white font-black text-xs py-2.5 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>Simpan Setoran</span>
                    <span>✓</span>
                </button>
            </div>
        </form>

        <!-- Form Penarikan -->
        <form id="modalFormWithdraw" action="{{ route('guru.savings.withdraw') }}" method="POST" class="space-y-4 hidden">
            @csrf
            <input type="hidden" name="student_id" id="modalWithdrawStudentId" value="">

            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-black">
                        Nominal Penarikan (Rp)
                    </label>
                    <span id="modalMaxWithdrawInfo" class="text-[11px] font-mono font-bold text-slate-500">Maks: Rp 0</span>
                </div>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 font-mono font-black text-sm text-black select-none pointer-events-none z-10">Rp</span>
                    <input type="number" name="amount" id="modalWithdrawAmount" min="500" step="500" required
                        placeholder="0"
                        style="padding-left: 3.25rem !important;"
                        class="w-full neo-input text-base font-mono font-black !pl-13 pr-4 py-2 bg-rose-50/40">
                </div>
                <!-- Quick Amount Buttons -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" onclick="setModalWithdrawAmount(1000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">1.000</button>
                    <button type="button" onclick="setModalWithdrawAmount(2000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">2.000</button>
                    <button type="button" onclick="setModalWithdrawAmount(5000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">5.000</button>
                    <button type="button" onclick="setModalWithdrawAmount(10000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">10.000</button>
                    <button type="button" onclick="setModalWithdrawAmount(20000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">20.000</button>
                    <button type="button" onclick="setModalWithdrawAmount(50000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">50.000</button>
                    <button type="button" onclick="setModalWithdrawAmount(100000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">100.000</button>
                    <button type="button" onclick="setModalWithdrawAll()" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-[11px] font-mono font-black px-2 py-1">Tarik Semua</button>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Keperluan Penarikan
                </label>
                <input type="text" name="description" placeholder="Contoh: Beli buku / uang saku lomba" maxlength="255"
                    class="w-full neo-input text-xs font-medium py-2 bg-white">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeInputModal()" class="w-1/3 neo-btn bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs py-2.5 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 neo-btn bg-[#FF6B6B] hover:bg-rose-600 text-white font-black text-xs py-2.5 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>Proses Penarikan</span>
                    <span>✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: TUTUP BUKU TABUNGAN -->
<!-- ========================================================================= -->
<div id="modalCloseAccount" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white border-4 border-black max-w-md w-full p-5 sm:p-6 neo-box space-y-4 relative">
        <button type="button" onclick="closeCloseAccountModal()" class="absolute right-4 top-4 text-black hover:text-rose-600 font-black text-xl p-1 cursor-pointer">
            ✕
        </button>

        <div class="border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-base sm:text-lg text-rose-700 flex items-center gap-2">
                <span>🛑</span> Tutup Buku Tabungan
            </h3>
            <p class="text-xs text-slate-600">Konfirmasi penghentian tabungan santri/siswa</p>
        </div>

        <form action="{{ route('guru.savings.close-account') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="student_id" id="closeAccountStudentId" value="">

            <div class="bg-rose-50 border-2 border-rose-600 p-3.5 space-y-2 text-xs">
                <div class="font-bold text-rose-950">Informasi Rekening:</div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Nama Siswa:</span>
                    <strong id="closeAccountStudentName" class="text-black">-</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">No. Rekening:</span>
                    <span id="closeAccountAccountNumber" class="font-mono font-bold text-black">-</span>
                </div>
                <div class="flex items-center justify-between border-t border-rose-200 pt-1.5">
                    <span class="text-rose-950 font-bold">Sisa Saldo Dicairkan:</span>
                    <span id="closeAccountBalance" class="font-mono font-black text-sm text-rose-700">Rp 0</span>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Alasan Penutupan Buku (Opsional)
                </label>
                <input type="text" name="reason" placeholder="Contoh: Lulus sekolah / Pindah sekolah / Penghentian mandiri" maxlength="255"
                    class="w-full neo-input text-xs font-medium py-2 bg-white">
            </div>

            <div class="p-2.5 bg-yellow-50 border border-yellow-400 text-[11px] text-yellow-950">
                ⚠ Seluruh sisa saldo di atas akan otomatis dicairkan kepada siswa, dicatat sebagai penarikan penutupan buku, dan status rekening dinonaktifkan.
            </div>

            <div class="flex items-center gap-2 pt-2 border-t-2 border-black">
                <button type="button" onclick="closeCloseAccountModal()" class="w-1/3 neo-btn bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs py-2.5 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 neo-btn bg-[#FF6B6B] hover:bg-rose-600 text-white font-black text-xs py-2.5 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>Proses Tutup Buku</span>
                    <span>✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL POPUP: KOREKSI TRANSAKSI TABUNGAN (GURU) -->
<!-- ========================================================================= -->
<div id="modalCorrectTransaction" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-lg w-full p-6 my-8 flex flex-col border-4 border-black shadow-[6px_6px_0px_0px_#000] relative">
        <button type="button" onclick="closeCorrectModal()" class="absolute right-4 top-4 text-black hover:text-rose-600 font-black text-xl p-1 cursor-pointer">
            ✕
        </button>

        <div class="border-b-2 border-black pb-3 mb-4">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Koreksi Nominal Transaksi
            </h3>
            <p class="text-xs text-slate-600 mt-0.5">
                Perbaikan kesalahan input nominal transaksi tabungan santri/siswa
            </p>
        </div>

        <div class="bg-amber-50 border-2 border-black p-3 text-xs text-amber-950 mb-4 space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <span>⚠️</span> Ketentuan Audit Koreksi:
            </div>
            <p class="text-[11px] text-slate-700 leading-relaxed">
                Perubahan nominal akan otomatis menyesuaikan saldo rekening siswa, meninggalkan tanda audit koreksi di mutasi siswa, dan mencatat nama Anda beserta alasan perbaikan.
            </p>
        </div>

        <form id="correctTransactionForm" method="POST" action="" class="space-y-4">
            @csrf

            <!-- Info Transaksi Terpilih -->
            <div class="bg-slate-100 border-2 border-black p-3 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Kode Transaksi:</span>
                    <span id="correctModalCode" class="font-mono font-bold text-black">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Nama Siswa:</span>
                    <strong id="correctModalStudent" class="text-black">-</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">No. Rekening:</span>
                    <span id="correctModalAccount" class="font-mono text-slate-700">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Jenis Transaksi:</span>
                    <span id="correctModalType" class="font-black">-</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-300 pt-1.5">
                    <span class="text-slate-700 font-bold">Nominal Tercatat Saat Ini:</span>
                    <span id="correctModalCurrentAmount" class="font-mono font-black text-slate-900">Rp 0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-700 font-bold">Saldo Siswa Saat Ini:</span>
                    <span id="correctModalCurrentBalance" class="font-mono font-black text-emerald-800">Rp 0</span>
                </div>
            </div>

            <!-- Input Nominal Baru -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Nominal Sebenarnya (Baru) <span class="text-rose-600">*</span>
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 font-mono font-black text-sm text-black select-none pointer-events-none z-10">Rp</span>
                    <input type="number" name="new_amount" id="correctModalNewAmount" min="0" step="500" required
                        placeholder="0"
                        style="padding-left: 3.25rem !important;"
                        class="w-full neo-input text-base font-mono font-black !pl-13 pr-4 py-2 bg-yellow-50/50">
                </div>
                <!-- Quick Amount Buttons -->
                <div class="flex flex-wrap gap-1.5 pt-0.5">
                    <button type="button" onclick="setModalCorrectAmount(1000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">1.000</button>
                    <button type="button" onclick="setModalCorrectAmount(2000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">2.000</button>
                    <button type="button" onclick="setModalCorrectAmount(5000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">5.000</button>
                    <button type="button" onclick="setModalCorrectAmount(10000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">10.000</button>
                    <button type="button" onclick="setModalCorrectAmount(20000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">20.000</button>
                    <button type="button" onclick="setModalCorrectAmount(50000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">50.000</button>
                    <button type="button" onclick="setModalCorrectAmount(100000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">100.000</button>
                </div>
                
                <!-- Perhitungan Dampak Saldo -->
                <div id="correctModalCalculation" class="bg-blue-50 border border-blue-300 p-2 text-xs space-y-0.5 text-blue-950">
                    <div class="flex justify-between items-center text-[11px]">
                        <span>Selisih Penyesuaian:</span>
                        <span id="correctModalDeltaText" class="font-mono font-bold">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center font-bold">
                        <span>Estimasi Saldo Baru Siswa:</span>
                        <span id="correctModalNewBalanceText" class="font-mono font-black text-blue-900">Rp 0</span>
                    </div>
                </div>

                <div id="correctModalWarningNegative" class="hidden bg-rose-100 border-2 border-rose-600 p-2.5 text-xs text-rose-950 font-bold">
                    🚫 Peringatan: Perubahan ini menyebabkan saldo akhir siswa menjadi minus (&lt; Rp 0). Koreksi nominal tidak dapat diproses!
                </div>
            </div>

            <!-- Input Alasan Koreksi -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Alasan Perubahan / Koreksi <span class="text-rose-600">*</span>
                </label>
                <input type="text" name="reason" id="correctModalReason" required minlength="5" maxlength="255"
                    placeholder="Contoh: Salah ketik kelebihan nol saat melayani setoran santri"
                    class="w-full neo-input text-xs font-medium py-2 bg-white">
                <p class="text-[10px] text-slate-500">Minimal 5 karakter. Alasan ini akan tampil pada buku tabungan digital siswa.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeCorrectModal()" class="w-1/3 neo-btn bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs py-2.5 cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="correctModalSubmitBtn" class="w-2/3 neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black font-black text-xs py-2.5 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>Simpan Koreksi</span>
                    <span>✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript Interactivity -->
<script>
    let activeModalStudentBalance = 0;

    // --- Modal Checklist Pendaftaran Massal ---
    function openRegisterChecklistModal() {
        document.getElementById('modalRegisterChecklist').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeRegisterChecklistModal() {
        document.getElementById('modalRegisterChecklist').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        const submitBtn = document.getElementById('submitRegisterSelectedBtn');
        const countBadge = document.getElementById('selectedCountBadge');

        countBadge.innerText = `${count} Dipilih`;

        if (count > 0) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // --- Modal Input Tabungan ---
    function openInputModal(student) {
        activeModalStudentBalance = student.balance;

        document.getElementById('modalDepositStudentId').value = student.id;
        document.getElementById('modalWithdrawStudentId').value = student.id;

        document.getElementById('modalStudentName').innerText = student.name;
        document.getElementById('modalStudentClass').innerText = student.class_name;
        document.getElementById('modalStudentAccount').innerText = student.account_number;
        document.getElementById('modalStudentBalance').innerText = student.formatted_balance;
        document.getElementById('modalMaxWithdrawInfo').innerText = `Maks: ${student.formatted_balance}`;

        const photoEl = document.getElementById('modalStudentPhoto');
        const placeholderEl = document.getElementById('modalStudentPlaceholder');
        if (student.photo_url) {
            photoEl.src = student.photo_url;
            photoEl.classList.remove('hidden');
            placeholderEl.classList.add('hidden');
        } else {
            photoEl.classList.add('hidden');
            placeholderEl.classList.remove('hidden');
        }

        // Reset inputs
        document.getElementById('modalDepositAmount').value = '';
        document.getElementById('modalWithdrawAmount').value = '';

        setModalTxType('deposit');

        document.getElementById('modalInputTabungan').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeInputModal() {
        document.getElementById('modalInputTabungan').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function setModalTxType(type) {
        const btnDeposit = document.getElementById('modalBtnDeposit');
        const btnWithdraw = document.getElementById('modalBtnWithdraw');
        const formDeposit = document.getElementById('modalFormDeposit');
        const formWithdraw = document.getElementById('modalFormWithdraw');

        if (type === 'deposit') {
            btnDeposit.classList.add('bg-[#20C997]', 'text-white', 'shadow-[2px_2px_0px_0px_#000]');
            btnDeposit.classList.remove('border-transparent', 'text-slate-700');
            btnWithdraw.classList.remove('bg-[#FF6B6B]', 'text-white', 'shadow-[2px_2px_0px_0px_#000]');
            btnWithdraw.classList.add('border-transparent', 'text-slate-700');

            formDeposit.classList.remove('hidden');
            formWithdraw.classList.add('hidden');
        } else {
            btnWithdraw.classList.add('bg-[#FF6B6B]', 'text-white', 'shadow-[2px_2px_0px_0px_#000]');
            btnWithdraw.classList.remove('border-transparent', 'text-slate-700');
            btnDeposit.classList.remove('bg-[#20C997]', 'text-white', 'shadow-[2px_2px_0px_0px_#000]');
            btnDeposit.classList.add('border-transparent', 'text-slate-700');

            formWithdraw.classList.remove('hidden');
            formDeposit.classList.add('hidden');
        }
    }

    function setModalDepositAmount(val) {
        document.getElementById('modalDepositAmount').value = val;
    }

    function setModalWithdrawAmount(val) {
        document.getElementById('modalWithdrawAmount').value = val;
    }

    function setModalWithdrawAll() {
        if (activeModalStudentBalance > 0) {
            document.getElementById('modalWithdrawAmount').value = Math.floor(activeModalStudentBalance);
        } else {
            alert('Saldo siswa Rp 0, tidak dapat melakukan penarikan.');
        }
    }

    // --- Modal Tutup Buku ---
    function openCloseAccountModal(student) {
        document.getElementById('closeAccountStudentId').value = student.id;
        document.getElementById('closeAccountStudentName').innerText = student.name;
        document.getElementById('closeAccountAccountNumber').innerText = student.account_number;
        document.getElementById('closeAccountBalance').innerText = student.formatted_balance;

        document.getElementById('modalCloseAccount').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeCloseAccountModal() {
        document.getElementById('modalCloseAccount').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // --- Modal Koreksi Transaksi ---
    let activeCorrectTx = null;

    function openCorrectModal(data) {
        activeCorrectTx = data;
        
        document.getElementById('correctTransactionForm').action = data.actionUrl;
        document.getElementById('correctModalCode').textContent = data.code;
        document.getElementById('correctModalStudent').textContent = data.student;
        document.getElementById('correctModalAccount').textContent = data.accountNumber;
        document.getElementById('correctModalType').textContent = data.typeName;
        document.getElementById('correctModalCurrentAmount').textContent = 'Rp ' + Number(data.amount).toLocaleString('id-ID');
        document.getElementById('correctModalCurrentBalance').textContent = 'Rp ' + Number(data.currentBalance).toLocaleString('id-ID');
        
        const inputNewAmount = document.getElementById('correctModalNewAmount');
        inputNewAmount.value = data.amount;
        document.getElementById('correctModalReason').value = '';
        
        updateCorrectCalculation();
        document.getElementById('modalCorrectTransaction').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeCorrectModal() {
        document.getElementById('modalCorrectTransaction').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function updateCorrectCalculation() {
        if (!activeCorrectTx) return;
        
        const newAmount = parseFloat(document.getElementById('correctModalNewAmount').value) || 0;
        const oldAmount = parseFloat(activeCorrectTx.amount) || 0;
        const currentBalance = parseFloat(activeCorrectTx.currentBalance) || 0;
        
        let delta = 0;
        if (activeCorrectTx.type === 'deposit') {
            delta = newAmount - oldAmount;
        } else {
            delta = oldAmount - newAmount;
        }
        
        const newBalance = currentBalance + delta;
        
        const deltaEl = document.getElementById('correctModalDeltaText');
        const newBalanceEl = document.getElementById('correctModalNewBalanceText');
        const warningEl = document.getElementById('correctModalWarningNegative');
        const submitBtn = document.getElementById('correctModalSubmitBtn');
        
        deltaEl.textContent = (delta >= 0 ? '+' : '') + 'Rp ' + Number(delta).toLocaleString('id-ID');
        deltaEl.className = 'font-mono font-bold ' + (delta >= 0 ? 'text-emerald-700' : 'text-rose-700');
        
        newBalanceEl.textContent = 'Rp ' + Number(newBalance).toLocaleString('id-ID');
        
        if (newBalance < 0) {
            warningEl.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warningEl.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function setModalCorrectAmount(val) {
        document.getElementById('correctModalNewAmount').value = val;
        updateCorrectCalculation();
    }

    document.getElementById('correctModalNewAmount')?.addEventListener('input', updateCorrectCalculation);

    // Konfirmasi Pembatalan Pendaftaran Tabungan Siswa via SweetAlert2
    function confirmCancelRegistration(formId, studentName) {
        if (typeof Swal === 'undefined') {
            const promptVal = prompt(`Apakah Anda yakin ingin membatalkan pendaftaran siswa ${studentName}? Rekening tabungan akan dihapus.\n\nKetik "Batalkan" untuk konfirmasi:`);
            if (promptVal && promptVal.trim().toLowerCase() === 'batalkan') {
                document.getElementById(formId).submit();
            }
            return;
        }

        Swal.fire({
            title: 'Batalkan Pendaftaran?',
            html: `
                <div class="text-left space-y-3">
                    <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                        Apakah Anda yakin ingin membatalkan pendaftaran siswa <b class="text-black font-black font-mono underline">${studentName}</b>? Rekening tabungan akan dihapus.
                    </p>
                    <div class="p-3 bg-[#FFE3E3] border-2 border-black neo-box-sm text-xs text-rose-950 font-bold space-y-1">
                        <div class="font-black uppercase flex items-center gap-1.5 text-rose-900">
                            <span>⚠️</span> Peringatan
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Rekening tabungan dan seluruh data pendaftaran siswa ini akan dihapus.
                        </p>
                    </div>
                    <p class="text-xs font-bold text-slate-800">
                        Ketik <span class="bg-[#FFD43B] px-1.5 py-0.5 border border-black font-mono font-black text-black">Batalkan</span> di bawah ini untuk mengonfirmasi:
                    </p>
                </div>
            `,
            input: 'text',
            inputPlaceholder: 'Ketik "Batalkan"',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off',
                class: 'w-full neo-input text-center font-bold text-sm bg-white'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FF6B6B',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '🗑️ Ya, Batalkan Pendaftaran',
            cancelButtonText: 'Kembali',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value || value.trim().toLowerCase() !== 'batalkan') {
                    return 'Ketik kata "Batalkan" dengan benar untuk melanjutkan.';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeInputModal();
            closeRegisterChecklistModal();
            closeCloseAccountModal();
            closeCorrectModal();
        }
    });
</script>
@endsection
