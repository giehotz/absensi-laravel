@extends('layouts.guru')

@section('title', 'Kelola Tabungan Siswa')
@section('page-title', 'Kelola Tabungan Siswa')

@section('content')
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
                Layanan pencatatan setoran & penarikan tabungan santri/siswa, cetak kuitansi bukti transaksi, dan rekapitulasi buku tabungan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.savings.transactions') }}" class="neo-btn bg-white text-black text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Buku Kas / Mutasi</span>
            </a>
            <a href="{{ route('guru.savings.export') }}" class="neo-btn bg-[#20C997] text-white text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Excel/CSV</span>
            </a>
        </div>
    </div>

    @if(session('last_transaction_id'))
        <div class="bg-[#D3F9D8] border-3 border-black p-4 neo-box flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🖨️</span>
                <div>
                    <div class="font-heading font-black text-sm text-emerald-950">Transaksi Berhasil Dicatat!</div>
                    <div class="text-xs text-emerald-900">Ingin mencetak bukti transaksi / kuitansi untuk siswa?</div>
                </div>
            </div>
            <a href="{{ route('guru.savings.receipt', session('last_transaction_id')) }}" target="_blank" class="neo-btn bg-black text-white text-xs font-bold px-4 py-2 flex items-center gap-1.5 hover:bg-slate-800">
                <span>Cetak Kuitansi</span>
                <span>→</span>
            </a>
        </div>
    @endif

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Kas -->
        <div class="bg-[#E7F5FF] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-blue-950">Total Saldo Siswa</span>
                <span class="text-xl">🏦</span>
            </div>
            <div class="font-mono font-black text-2xl text-blue-950 mt-2">
                Rp {{ number_format($stats['total_balance'], 0, ',', '.') }}
            </div>
            <div class="text-[10px] font-semibold text-blue-800 mt-1">
                Dari {{ $stats['total_accounts'] }} Rekening Siswa Terdaftar
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

    <!-- Main Grid: Form Transaksi Cepat & Detail Siswa -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Transaksi (Left: 7 Cols) -->
        <div class="lg:col-span-7 bg-white neo-box p-5 sm:p-6 border-3 border-black space-y-5">
            <div class="border-b-2 border-black pb-3 flex items-center justify-between">
                <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span>⚡</span> Layani Transaksi Siswa
                </h3>
                <span class="text-[11px] font-bold text-slate-500">Pilih Siswa & Nominal</span>
            </div>

            <!-- Cari Siswa Cepat (Live Autocomplete) -->
            <div class="space-y-1 relative">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    1. Cari Siswa (Nama / NISN / NIS / Scan QR)
                </label>
                <div class="relative">
                    <input type="text" id="studentSearchInput" 
                        value="{{ $selectedStudent ? $selectedStudent->user?->name . ' (' . $selectedStudent->schoolClass?->name . ')' : '' }}"
                        placeholder="Ketik minimal 2 karakter atau scan QR kartu siswa..." 
                        autocomplete="off"
                        class="w-full neo-input text-xs font-medium pl-9 pr-8 py-2.5 bg-slate-50 focus:bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" id="clearStudentBtn" class="absolute right-3 top-3 text-slate-400 hover:text-black hidden" title="Hapus pilihan">
                        ✕
                    </button>
                </div>

                <!-- Dropdown Hasil Pencarian -->
                <div id="studentSearchResults" class="absolute left-0 right-0 top-full mt-1 bg-white border-3 border-black shadow-[4px_4px_0px_0px_#000] z-50 max-h-60 overflow-y-auto hidden">
                </div>
            </div>

            <!-- Tab Switcher Jenis Transaksi: Setor vs Tarik -->
            <div class="grid grid-cols-2 gap-2 bg-slate-100 border-2 border-black p-1">
                <button type="button" onclick="setTxType('deposit')" id="btnTxTypeDeposit" 
                    class="py-2.5 text-xs font-black uppercase border-2 border-black bg-[#20C997] text-white shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>📥</span> Setor Tunai
                </button>
                <button type="button" onclick="setTxType('withdraw')" id="btnTxTypeWithdraw" 
                    class="py-2.5 text-xs font-black uppercase border-2 border-transparent text-slate-700 hover:text-black flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>📤</span> Tarik Tunai
                </button>
            </div>

            <!-- Form Setoran Tunai -->
            <form id="formDeposit" action="{{ route('guru.savings.deposit') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="student_id" id="depositStudentId" value="{{ $selectedStudent?->id ?? '' }}">

                <!-- Input Nominal -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-black">
                        2. Nominal Setoran (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 font-mono font-bold text-sm text-black">Rp</span>
                        <input type="number" name="amount" id="depositAmount" min="500" step="500" required
                            placeholder="0"
                            class="w-full neo-input text-base font-mono font-black pl-10 pr-4 py-2 bg-emerald-50/40">
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button type="button" onclick="setDepositAmount(5000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">5.000</button>
                        <button type="button" onclick="setDepositAmount(10000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">10.000</button>
                        <button type="button" onclick="setDepositAmount(20000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">20.000</button>
                        <button type="button" onclick="setDepositAmount(50000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">50.000</button>
                        <button type="button" onclick="setDepositAmount(100000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">100.000</button>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase tracking-wider text-black">
                        3. Keterangan / Catatan (Opsional)
                    </label>
                    <input type="text" name="description" placeholder="Contoh: Setoran tabungan mingguan" maxlength="255"
                        class="w-full neo-input text-xs font-medium py-2 bg-white">
                </div>

                <button type="submit" id="submitDepositBtn" 
                    class="w-full neo-btn bg-[#20C997] hover:bg-emerald-600 text-white font-black text-sm py-3 flex items-center justify-center gap-2 shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Transaksi Setoran</span>
                </button>
            </form>

            <!-- Form Penarikan Tunai -->
            <form id="formWithdraw" action="{{ route('guru.savings.withdraw') }}" method="POST" class="space-y-4 hidden">
                @csrf
                <input type="hidden" name="student_id" id="withdrawStudentId" value="{{ $selectedStudent?->id ?? '' }}">

                <!-- Input Nominal Penarikan -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black uppercase tracking-wider text-black">
                            2. Nominal Penarikan (Rp)
                        </label>
                        <span id="withdrawMaxBalanceInfo" class="text-[11px] font-mono font-bold text-slate-500">
                            Saldo: {{ $selectedStudent?->savingsAccount?->formatted_balance ?? 'Rp 0' }}
                        </span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 font-mono font-bold text-sm text-black">Rp</span>
                        <input type="number" name="amount" id="withdrawAmount" min="500" step="500" required
                            placeholder="0"
                            class="w-full neo-input text-base font-mono font-black pl-10 pr-4 py-2 bg-rose-50/40">
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button type="button" onclick="setWithdrawAmount(10000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">10.000</button>
                        <button type="button" onclick="setWithdrawAmount(20000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">20.000</button>
                        <button type="button" onclick="setWithdrawAmount(50000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[11px] font-mono font-bold px-2 py-1">50.000</button>
                        <button type="button" onclick="setWithdrawAll()" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-[11px] font-mono font-black px-2 py-1">Tarik Semua</button>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase tracking-wider text-black">
                        3. Keterangan / Keperluan Penarikan
                    </label>
                    <input type="text" name="description" placeholder="Contoh: Keperluan beli buku / uang saku lomba" maxlength="255"
                        class="w-full neo-input text-xs font-medium py-2 bg-white">
                </div>

                <button type="submit" id="submitWithdrawBtn" 
                    class="w-full neo-btn bg-[#FF6B6B] hover:bg-rose-600 text-white font-black text-sm py-3 flex items-center justify-center gap-2 shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Proses Penarikan Uang</span>
                </button>
            </form>
        </div>

        <!-- Student Information Card (Right: 5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div id="studentInfoCard" class="bg-white neo-box p-5 border-3 border-black {{ $selectedStudent ? '' : 'hidden' }}">
                <div class="border-b-2 border-black pb-3 flex items-center justify-between">
                    <h3 class="font-heading font-black text-sm uppercase text-black">Buku Tabungan Siswa</h3>
                    <span id="infoAccountStatus" class="neo-badge bg-[#20C997] text-white text-[10px]">AKTIF</span>
                </div>

                <div class="flex items-center gap-3.5 my-4">
                    <div class="w-14 h-14 rounded-lg bg-slate-100 border-2 border-black overflow-hidden shrink-0 flex items-center justify-center shadow-[2px_2px_0px_0px_#000]">
                        <img id="infoStudentPhoto" src="{{ $selectedStudent?->photo_url ?: '' }}" 
                            alt="" class="w-full h-full object-cover {{ $selectedStudent?->photo_url ? '' : 'hidden' }}">
                        <span id="infoStudentPlaceholder" class="text-2xl {{ $selectedStudent?->photo_url ? 'hidden' : '' }}">🎓</span>
                    </div>
                    <div class="min-w-0">
                        <div id="infoStudentName" class="font-heading font-black text-base text-black truncate">
                            {{ $selectedStudent?->user?->name ?? '-' }}
                        </div>
                        <div class="text-xs font-semibold text-slate-600">
                            Kelas: <span id="infoStudentClass" class="text-black font-bold">{{ $selectedStudent?->schoolClass?->name ?? '-' }}</span>
                        </div>
                        <div class="text-[11px] font-mono text-slate-500">
                            NIS: <span id="infoStudentNis">{{ $selectedStudent?->nis ?? '-' }}</span> • NISN: <span id="infoStudentNisn">{{ $selectedStudent?->nisn ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Saldo Card -->
                <div class="bg-gradient-to-br from-[#1E293B] to-[#0F172A] border-3 border-black p-4 text-white rounded-none shadow-[3px_3px_0px_0px_#000] space-y-1 mb-4">
                    <div class="flex items-center justify-between text-[10px] text-slate-400 uppercase font-semibold">
                        <span>No. Rekening</span>
                        <span id="infoAccountNumber" class="font-mono text-[#FFD43B] font-bold">{{ $selectedStudent?->savingsAccount?->account_number ?? '-' }}</span>
                    </div>
                    <div class="text-xs text-slate-300 font-bold uppercase">Saldo Saat Ini</div>
                    <div id="infoFormattedBalance" class="font-mono font-black text-2xl text-white">
                        {{ $selectedStudent?->savingsAccount?->formatted_balance ?? 'Rp 0' }}
                    </div>
                </div>

                <!-- Mutasi Terakhir Siswa Ini -->
                <div class="space-y-2">
                    <div class="text-xs font-black uppercase text-black">Mutasi Terakhir Siswa</div>
                    <div id="infoStudentTxList" class="space-y-1.5 max-h-48 overflow-y-auto">
                        @if($selectedStudent && $selectedStudent->savingsAccount && $selectedStudent->savingsAccount->transactions->isNotEmpty())
                            @foreach($selectedStudent->savingsAccount->transactions->take(5) as $tx)
                                <div class="bg-slate-50 border border-black p-2 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-black">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-[10px] text-slate-500 truncate max-w-[140px]">{{ $tx->description ?: '-' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-mono font-black {{ $tx->isDeposit() ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $tx->isDeposit() ? '+' : '-' }}{{ $tx->formatted_amount }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-xs text-slate-500 italic py-2 text-center">Belum ada mutasi transaksi untuk siswa ini.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Placeholder saat siswa belum dipilih -->
            <div id="studentInfoPlaceholder" class="bg-slate-50 border-3 border-dashed border-slate-300 p-8 text-center text-slate-500 space-y-2 {{ $selectedStudent ? 'hidden' : '' }}">
                <div class="text-4xl">🔍</div>
                <div class="font-heading font-black text-sm text-black">Pilih Siswa Terlebih Dahulu</div>
                <p class="text-xs text-slate-600 max-w-xs mx-auto">
                    Ketik nama, NISN, atau scan QR kartu pelajar siswa di form sebelah kiri untuk melihat informasi rekening tabungan dan melayani transaksi.
                </p>
            </div>
        </div>
    </div>

    <!-- Tabel 10 Transaksi Terkini Semua Siswa -->
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
                        <th class="p-2.5 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-black font-medium">
                    @forelse($recentTransactions as $idx => $t)
                        <tr class="hover:bg-slate-50">
                            <td class="p-2.5 text-center font-bold border-r border-black">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-mono">
                                <div class="font-bold text-black">{{ $t->transaction_code }}</div>
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
                                {{ $t->isDeposit() ? '+' : '-' }}{{ $t->formatted_amount }}
                            </td>
                            <td class="p-2.5 border-r border-black text-right font-mono font-bold text-slate-900">
                                {{ $t->formatted_balance_after }}
                            </td>
                            <td class="p-2.5 border-r border-black text-slate-700">
                                {{ $t->handler?->name ?? '-' }}
                            </td>
                            <td class="p-2.5 text-center">
                                <a href="{{ route('guru.savings.receipt', $t) }}" target="_blank" 
                                    class="neo-btn bg-slate-100 hover:bg-[#FFD43B] text-black text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1"
                                    title="Cetak Kuitansi / Slip">
                                    <span>🖨️</span> Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-500 italic">
                                Belum ada transaksi tabungan yang tercatat. Gunakan form di atas untuk memulai transaksi perdana.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript Interactivity: Autocomplete & Live Calculations -->
<script>
    let currentRawBalance = {{ (float) ($selectedStudent?->savingsAccount?->balance ?? 0) }};

    function setTxType(type) {
        const btnDeposit = document.getElementById('btnTxTypeDeposit');
        const btnWithdraw = document.getElementById('btnTxTypeWithdraw');
        const formDeposit = document.getElementById('formDeposit');
        const formWithdraw = document.getElementById('formWithdraw');

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

    function setDepositAmount(val) {
        document.getElementById('depositAmount').value = val;
    }

    function setWithdrawAmount(val) {
        document.getElementById('withdrawAmount').value = val;
    }

    function setWithdrawAll() {
        if (currentRawBalance > 0) {
            document.getElementById('withdrawAmount').value = Math.floor(currentRawBalance);
        } else {
            alert('Saldo siswa adalah Rp 0, tidak dapat melakukan penarikan.');
        }
    }

    // Autocomplete Search
    const searchInput = document.getElementById('studentSearchInput');
    const searchResults = document.getElementById('studentSearchResults');
    const clearBtn = document.getElementById('clearStudentBtn');
    let searchTimeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }

            if (query.length < 2) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('guru.savings.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length === 0) {
                            searchResults.innerHTML = `<div class="p-3 text-xs text-slate-500 italic text-center">Siswa tidak ditemukan</div>`;
                        } else {
                            data.forEach(item => {
                                const row = document.createElement('div');
                                row.className = 'p-2.5 border-b border-slate-200 hover:bg-[#FFF4E6] cursor-pointer flex items-center justify-between text-xs transition-colors';
                                row.innerHTML = `
                                    <div>
                                        <div class="font-bold text-black">${item.name}</div>
                                        <div class="text-[11px] text-slate-500">Kelas: ${item.class_name} • NISN: ${item.nisn || item.nis}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-bold text-emerald-700">${item.formatted_balance}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">${item.account_number}</div>
                                    </div>
                                `;
                                row.addEventListener('click', () => selectStudent(item));
                                searchResults.appendChild(row);
                            });
                        }
                        searchResults.classList.remove('hidden');
                    })
                    .catch(() => {
                        searchResults.classList.add('hidden');
                    });
            }, 250);
        });

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchResults.classList.add('hidden');
            clearBtn.classList.add('hidden');
            document.getElementById('depositStudentId').value = '';
            document.getElementById('withdrawStudentId').value = '';
            document.getElementById('studentInfoCard').classList.add('hidden');
            document.getElementById('studentInfoPlaceholder').classList.remove('hidden');
            currentRawBalance = 0;
        });

        // Close search results on click outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }

    function selectStudent(student) {
        document.getElementById('depositStudentId').value = student.id;
        document.getElementById('withdrawStudentId').value = student.id;
        searchInput.value = `${student.name} (${student.class_name})`;
        searchResults.classList.add('hidden');
        clearBtn.classList.remove('hidden');

        currentRawBalance = student.balance;

        // Populate info card
        document.getElementById('infoStudentName').innerText = student.name;
        document.getElementById('infoStudentClass').innerText = student.class_name;
        document.getElementById('infoStudentNis').innerText = student.nis || '-';
        document.getElementById('infoStudentNisn').innerText = student.nisn || '-';
        document.getElementById('infoAccountNumber').innerText = student.account_number;
        document.getElementById('infoFormattedBalance').innerText = student.formatted_balance;
        document.getElementById('withdrawMaxBalanceInfo').innerText = `Saldo: ${student.formatted_balance}`;

        const photoEl = document.getElementById('infoStudentPhoto');
        const placeholderEl = document.getElementById('infoStudentPlaceholder');
        if (student.photo_url) {
            photoEl.src = student.photo_url;
            photoEl.classList.remove('hidden');
            placeholderEl.classList.add('hidden');
        } else {
            photoEl.classList.add('hidden');
            placeholderEl.classList.remove('hidden');
        }

        document.getElementById('studentInfoPlaceholder').classList.add('hidden');
        document.getElementById('studentInfoCard').classList.remove('hidden');
    }
</script>
@endsection
