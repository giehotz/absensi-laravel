@extends('layouts.admin')

@section('title', 'Monitoring Tabungan Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-[#FFD43B] border-3 border-black p-5 sm:p-6 neo-box flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-3xl">💰</span>
                <h1 class="font-heading font-black text-xl sm:text-2xl text-black">
                    Monitoring & Audit Tabungan Siswa
                </h1>
            </div>
            <p class="text-xs sm:text-sm font-bold text-slate-800 mt-1">
                Pusat pemantauan saldo kas sekolah, evaluasi rekap per kelas, audit mutasi transaksi, dan kontrol wewenang pengelola tabungan.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.savings.export', request()->query()) }}" 
               class="neo-btn bg-black text-white hover:bg-slate-800 text-xs font-black px-4 py-2.5 flex items-center gap-2 shadow-[2px_2px_0px_0px_#fff]"
               title="Ekspor seluruh riwayat transaksi sesuai filter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Ekspor Rekap (Excel/CSV)</span>
            </a>
            <a href="{{ route('admin.teachers.index') }}" 
               class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-4 py-2.5 flex items-center gap-2 shadow-[2px_2px_0px_0px_#000]">
                <span>⚙</span>
                <span>Atur Petugas Pengelola</span>
            </a>
        </div>
    </div>

    <!-- 4 STAT CARDS UTAMA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Saldo Sekolah -->
        <div class="bg-[#E7F5FF] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-blue-950">Total Saldo Kas Sekolah</span>
                <span class="text-xl">🏦</span>
            </div>
            <div class="font-mono font-black text-2xl text-blue-900 mt-2">
                Rp {{ number_format($stats['total_balance'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-blue-800 mt-1 flex items-center gap-1">
                <span>✓ Saldo riil seluruh akun penabung aktif</span>
            </div>
        </div>

        <!-- Card 2: Total Penabung Aktif -->
        <div class="bg-[#E6FCF5] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-emerald-950">Penabung Aktif</span>
                <span class="text-xl">👥</span>
            </div>
            <div class="font-mono font-black text-2xl text-emerald-900 mt-2">
                {{ number_format($stats['total_accounts'], 0, ',', '.') }} <span class="text-base font-bold">Siswa</span>
            </div>
            <div class="text-[11px] font-semibold text-emerald-800 mt-1">
                Memiliki buku rekening tabungan
            </div>
        </div>

        <!-- Card 3: Kas Masuk -->
        <div class="bg-[#FFF9DB] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-amber-950">Setoran Masuk</span>
                <span class="text-xl">📥</span>
            </div>
            <div class="font-mono font-black text-2xl text-emerald-900 mt-2">
                +Rp {{ number_format($stats['today_deposits'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-bold text-amber-900 mt-1">
                Hari ini | Bln ini: Rp {{ number_format($stats['this_month_deposits'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 4: Kas Keluar -->
        <div class="bg-[#FFF5F5] neo-box p-4 border-3 border-black">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-rose-950">Penarikan Keluar</span>
                <span class="text-xl">📤</span>
            </div>
            <div class="font-mono font-black text-2xl text-rose-900 mt-2">
                -Rp {{ number_format($stats['today_withdrawals'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-bold text-rose-900 mt-1">
                Hari ini | Bln ini: Rp {{ number_format($stats['this_month_withdrawals'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 1: GURU PENGELOLA TABUNGAN AKTIF -->
    <!-- ========================================================================= -->
    <div class="bg-white neo-box p-5 border-3 border-black space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div>
                <h2 class="font-heading font-black text-base sm:text-lg text-black flex items-center gap-2">
                    <span>👨‍🏫</span> Petugas Guru Pengelola Tabungan
                </h2>
                <p class="text-xs text-slate-600">Guru yang ditugaskan melayani transaksi tabungan siswa beserta wewenang rombelnya.</p>
            </div>
            <a href="{{ route('admin.teachers.index') }}" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black px-3 py-1.5 flex items-center gap-1">
                <span>+ Atur Pengelola</span>
            </a>
        </div>

        @if($savingsOfficers->isEmpty())
            <div class="p-4 bg-[#FFF9DB] border-2 border-black text-center text-xs font-bold text-amber-950">
                ⚠️ Belum ada guru yang ditunjuk sebagai Pengelola Tabungan Siswa. Silakan tunjuk guru di menu <a href="{{ route('admin.teachers.index') }}" class="underline font-black text-black">Data Guru</a>.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($savingsOfficers as $officer)
                    <div class="bg-slate-50 border-2 border-black p-3.5 flex flex-col justify-between space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full border-2 border-black bg-[#5294FF] text-white flex items-center justify-center font-heading font-black text-sm shrink-0">
                                {{ strtoupper(substr($officer->user?->name ?? 'G', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-heading font-black text-xs sm:text-sm text-black truncate" title="{{ $officer->user?->name }}">
                                    {{ $officer->user?->name ?? '-' }}
                                </h3>
                                <p class="text-[10px] text-slate-500 font-mono">
                                    NIP: {{ $officer->nip ?: ($officer->nuptk ?: '-') }}
                                </p>
                                <div class="mt-1.5">
                                    @if($officer->savings_scope === 'all')
                                        <span class="neo-badge bg-[#20C997] text-white text-[9px] font-black px-2 py-0.5 inline-flex items-center gap-1 border border-black shadow-[1px_1px_0px_0px_#000]">
                                            <span>✓</span> Mengelola Seluruh Kelas
                                        </span>
                                    @else
                                        @php
                                            $cCount = $officer->managedSavingsClasses->count();
                                            $cNames = $officer->managedSavingsClasses->pluck('name')->implode(', ');
                                        @endphp
                                        <span class="neo-badge bg-[#FFD43B] text-amber-950 text-[9px] font-black px-2 py-0.5 inline-flex items-center gap-1 border border-black shadow-[1px_1px_0px_0px_#000]" title="{{ $cNames }}">
                                            <span>🏫</span> {{ $cCount > 0 ? ($cCount <= 2 ? $cNames : $cCount . ' Rombel Kelas') : 'Belum Dipilih' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-600 text-[11px]">Total Transaksi Dilayani:</span>
                            <span class="font-mono font-black text-black text-xs bg-white px-2 py-0.5 border border-black">
                                {{ $officer->handled_transactions_count }} TRX
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 2: REKAPITULASI KAS PER ROMBEL / KELAS -->
    <!-- ========================================================================= -->
    <div class="bg-white neo-box p-5 border-3 border-black space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-black pb-3">
            <div>
                <h2 class="font-heading font-black text-base sm:text-lg text-black flex items-center gap-2">
                    <span>🏫</span> Rekapitulasi Kas Tabungan Per Rombel / Kelas
                </h2>
                <p class="text-xs text-slate-600">Ringkasan perolehan kas, partisipasi penabung aktif, dan rincian siswa per kelas.</p>
            </div>
            <div class="text-xs font-bold text-slate-700 bg-slate-100 px-3 py-1.5 border border-slate-300">
                Total Rombel: <strong>{{ $classes->count() }} Kelas</strong>
            </div>
        </div>

        <div class="overflow-x-auto border-2 border-black">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                    <tr>
                        <th class="p-3 border-r border-black text-center w-12">No</th>
                        <th class="p-3 border-r border-black">Rombel / Kelas</th>
                        <th class="p-3 border-r border-black">Wali Kelas</th>
                        <th class="p-3 border-r border-black text-center">Total Siswa</th>
                        <th class="p-3 border-r border-black text-center">Penabung Aktif</th>
                        <th class="p-3 border-r border-black text-right">Total Saldo Terkumpul</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @php
                        $grandTotalBalance = 0;
                        $grandTotalStudents = 0;
                        $grandTotalActiveSavings = 0;
                    @endphp
                    @forelse($classes as $idx => $class)
                        @php
                            $cBal = (float) ($classBalances[$class->id] ?? 0);
                            $grandTotalBalance += $cBal;
                            $grandTotalStudents += $class->students_count;
                            $grandTotalActiveSavings += $class->active_savings_count;
                            $percent = $class->students_count > 0 ? round(($class->active_savings_count / $class->students_count) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-amber-50/50 transition-colors">
                            <td class="p-3 border-r border-black text-center font-mono font-bold">{{ $idx + 1 }}</td>
                            <td class="p-3 border-r border-black">
                                <div class="font-heading font-black text-black text-sm">{{ $class->name }}</div>
                                <span class="text-[10px] text-slate-500 font-semibold">Tingkat {{ $class->level }}</span>
                            </td>
                            <td class="p-3 border-r border-black">
                                <span class="text-slate-800 font-semibold">{{ $class->homeroomTeacher?->user?->name ?? '-' }}</span>
                            </td>
                            <td class="p-3 border-r border-black text-center font-mono font-bold">
                                {{ $class->students_count }} Siswa
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                <div class="font-mono font-black {{ $class->active_savings_count > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ $class->active_savings_count }} <span class="text-[10px] text-slate-500">({{ $percent }}%)</span>
                                </div>
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-black text-sm text-emerald-900">
                                Rp {{ number_format($cBal, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <button type="button" 
                                    onclick="openClassStudentsModal({{ $class->id }})"
                                    class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-2.5 py-1 text-[11px] font-bold flex items-center justify-center gap-1 mx-auto shadow-[1.5px_1.5px_0px_0px_#000]"
                                    title="Lihat rincian buku tabungan siswa di kelas ini">
                                    <span>🔍</span>
                                    <span>Lihat Siswa</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500 font-bold">
                                Tidak ada data rombel kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($classes->isNotEmpty())
                    <tfoot class="bg-slate-100 font-black border-t-2 border-black">
                        <tr>
                            <td colspan="3" class="p-3 border-r border-black text-right uppercase text-xs">Total Seluruh Sekolah:</td>
                            <td class="p-3 border-r border-black text-center font-mono">{{ $grandTotalStudents }} Siswa</td>
                            <td class="p-3 border-r border-black text-center font-mono text-emerald-800">{{ $grandTotalActiveSavings }} Penabung</td>
                            <td class="p-3 border-r border-black text-right font-mono text-sm text-emerald-900">
                                Rp {{ number_format($grandTotalBalance, 0, ',', '.') }}
                            </td>
                            <td class="p-3 bg-slate-200"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 3: AUDIT LOG MUTASI SELURUH TRANSAKSI TABUNGAN -->
    <!-- ========================================================================= -->
    <div class="bg-white neo-box p-5 border-3 border-black space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-black pb-3">
            <div>
                <h2 class="font-heading font-black text-base sm:text-lg text-black flex items-center gap-2">
                    <span>📑</span> Audit Log Seluruh Mutasi Transaksi Tabungan
                </h2>
                <p class="text-xs text-slate-600">Catatan transaksi setoran dan penarikan tunai dari seluruh rombel/kelas secara real-time.</p>
            </div>
            <div class="text-xs font-bold text-slate-700">
                Ditemukan: <strong class="text-black">{{ $transactions->total() }}</strong> transaksi
            </div>
        </div>

        <!-- Filter Bar Interaktif -->
        <div class="bg-slate-50 border-2 border-black p-3.5 space-y-3 text-xs">
            <form action="{{ route('admin.savings.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5">
                <!-- Search Input -->
                <div class="space-y-1 sm:col-span-2">
                    <label class="font-bold text-slate-700">Cari Transaksi</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Kode TRX / Nama / NISN / No. Rek..." 
                        class="w-full neo-input py-1.5 text-xs bg-white">
                </div>

                <!-- Jenis Transaksi -->
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Jenis Transaksi</label>
                    <select name="type" class="w-full neo-input py-1.5 text-xs bg-white font-medium">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Setoran Tunai (+)</option>
                        <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai (-)</option>
                    </select>
                </div>

                <!-- Kelas Filter -->
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Rombel / Kelas</label>
                    <select name="class_id" class="w-full neo-input py-1.5 text-xs bg-white font-medium">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (string) $classId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Petugas Pengelola -->
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Petugas Pengelola</label>
                    <select name="handler_id" class="w-full neo-input py-1.5 text-xs bg-white font-medium">
                        <option value="">Semua Petugas</option>
                        @foreach($savingsOfficers as $off)
                            <option value="{{ $off->user_id }}" {{ (string) $handlerId === (string) $off->user_id ? 'selected' : '' }}>
                                {{ $off->user?->name ?? 'Guru' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Mulai -->
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full neo-input py-1.5 text-xs bg-white">
                </div>

                <!-- Tanggal Sampai & Buttons -->
                <div class="space-y-1 sm:col-span-2 md:col-span-3 lg:col-span-6 flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200">
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-slate-700 whitespace-nowrap">Sampai Tanggal:</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="neo-input py-1.5 text-xs bg-white">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="neo-btn bg-[#FFD43B] text-black font-black px-4 py-1.5 hover:bg-yellow-400 cursor-pointer">
                            Terapkan Filter
                        </button>
                        @if(!empty($search) || $type !== 'all' || !empty($classId) || !empty($handlerId) || !empty($startDate) || !empty($endDate))
                            <a href="{{ route('admin.savings.index') }}" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-3 py-1.5" title="Reset Filter">
                                ↺ Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Log Transaksi -->
        <div class="overflow-x-auto border-2 border-black">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                    <tr>
                        <th class="p-3 border-r border-black text-center w-12">No</th>
                        <th class="p-3 border-r border-black">Kode Transaksi</th>
                        <th class="p-3 border-r border-black">Waktu</th>
                        <th class="p-3 border-r border-black">Nama Siswa</th>
                        <th class="p-3 border-r border-black text-center">Kelas</th>
                        <th class="p-3 border-r border-black text-center">Jenis</th>
                        <th class="p-3 border-r border-black text-right">Nominal</th>
                        <th class="p-3 border-r border-black text-right">Saldo Sesudah</th>
                        <th class="p-3 border-r border-black">Petugas</th>
                        <th class="p-3 text-center w-28">Kuitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @forelse($transactions as $idx => $tx)
                        @php
                            $student = $tx->savingsAccount?->student;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 border-r border-black text-center font-mono text-slate-500">
                                {{ $transactions->firstItem() + $idx }}
                            </td>
                            <td class="p-3 border-r border-black font-mono font-bold text-black whitespace-nowrap">
                                {{ $tx->transaction_code }}
                                <div class="text-[10px] text-slate-500">{{ $tx->savingsAccount?->account_number ?? '-' }}</div>
                            </td>
                            <td class="p-3 border-r border-black text-slate-700 whitespace-nowrap">
                                <div class="font-bold">{{ $tx->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $tx->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="p-3 border-r border-black font-bold text-black">
                                <div>{{ $student?->user?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NISN: {{ $student?->nisn ?: '-' }}</div>
                            </td>
                            <td class="p-3 border-r border-black text-center font-bold text-slate-800">
                                <span class="bg-slate-100 px-2 py-0.5 border border-slate-300 rounded font-mono">
                                    {{ $student?->schoolClass?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                @if($tx->type === 'deposit')
                                    <span class="neo-badge bg-[#E6FCF5] text-emerald-900 border border-emerald-500 px-2 py-0.5 text-[10px] font-black">
                                        + Setoran
                                    </span>
                                @else
                                    <span class="neo-badge bg-[#FFF5F5] text-rose-900 border border-rose-500 px-2 py-0.5 text-[10px] font-black">
                                        - Penarikan
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-black text-sm {{ $tx->type === 'deposit' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $tx->formatted_amount }}
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-bold text-slate-900">
                                {{ $tx->formatted_balance_after }}
                            </td>
                            <td class="p-3 border-r border-black text-slate-700 text-[11px]">
                                <div class="font-bold text-black">{{ $tx->handler?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 truncate max-w-[120px]">{{ $tx->description ?: 'Transaksi tunai' }}</div>
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('admin.savings.receipt', $tx) }}" target="_blank" 
                                   class="neo-btn bg-white hover:bg-slate-100 text-black px-2 py-1 text-[11px] font-bold inline-flex items-center gap-1 shadow-[1.5px_1.5px_0px_0px_#000]"
                                   title="Cetak ulang slip kuitansi transaksi ini">
                                    <span>🖨</span>
                                    <span>Slip</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-8 text-center text-slate-500 font-bold">
                                Belum ada riwayat transaksi yang cocok dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-2 border-t-2 border-black">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL POPUP: RINCIAN PENABUNG PER KELAS -->
<!-- ========================================================================= -->
<div id="classStudentsModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-3xl w-full p-6 my-8 max-h-[90vh] flex flex-col border-3 border-black shadow-[6px_6px_0px_0px_#000]">
        <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
            <div>
                <h3 id="class_modal_title" class="font-heading font-black text-lg text-black flex items-center gap-2">
                    <span class="w-3 h-3 bg-[#5294FF] border border-black inline-block"></span>
                    Rincian Penabung Kelas: <span id="class_modal_name">Memuat...</span>
                </h3>
                <p id="class_modal_subtitle" class="text-xs text-slate-600 mt-0.5 font-medium">
                    Wali Kelas: - | Total Siswa: -
                </p>
            </div>
            <button type="button" onclick="closeModal('classStudentsModal')" class="text-black font-black hover:text-rose-600 text-lg">✕</button>
        </div>

        <!-- Ringkasan Kelas di Modal -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4 p-3 bg-amber-50 border-2 border-black text-xs">
            <div>
                <span class="text-slate-600 font-medium">Penabung Terdaftar:</span>
                <div id="class_modal_registered_text" class="font-mono font-bold text-sm text-black mt-0.5">-</div>
            </div>
            <div>
                <span class="text-slate-600 font-medium">Total Saldo Terkumpul di Rombel:</span>
                <div id="class_modal_balance_text" class="font-mono font-black text-sm text-emerald-800 mt-0.5">-</div>
            </div>
        </div>

        <!-- Input Filter Siswa di Modal -->
        <div class="mb-3">
            <input type="text" id="class_student_filter" oninput="filterModalStudents()" placeholder="Cari nama siswa atau NISN di kelas ini..." 
                   class="w-full neo-input py-1.5 px-3 text-xs bg-slate-50">
        </div>

        <!-- Tabel Siswa Modal -->
        <div class="flex-1 overflow-y-auto border-2 border-black max-h-80">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black sticky top-0 border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black text-center w-10">No</th>
                        <th class="p-2.5 border-r border-black">Nama Siswa</th>
                        <th class="p-2.5 border-r border-black">NISN</th>
                        <th class="p-2.5 border-r border-black text-center">Status</th>
                        <th class="p-2.5 text-right">Saldo Tabungan</th>
                    </tr>
                </thead>
                <tbody id="class_students_tbody" class="divide-y divide-black font-medium">
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">Memuat data siswa...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t-2 border-slate-200 flex items-center justify-end mt-4">
            <button type="button" onclick="closeModal('classStudentsModal')" class="neo-btn bg-black text-white px-5 py-2 text-xs font-black">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentModalStudents = [];

    function openClassStudentsModal(classId) {
        document.getElementById('class_modal_name').textContent = 'Memuat...';
        document.getElementById('class_modal_subtitle').textContent = 'Mengambil data dari server...';
        document.getElementById('class_modal_registered_text').textContent = '...';
        document.getElementById('class_modal_balance_text').textContent = '...';
        document.getElementById('class_student_filter').value = '';
        
        const tbody = document.getElementById('class_students_tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-500 font-bold">Memuat data siswa...</td></tr>';

        openModal('classStudentsModal');

        fetch(`{{ url('admin/savings/classes') }}/${classId}/students`)
            .then(res => res.json())
            .then(data => {
                const cls = data.class;
                document.getElementById('class_modal_name').textContent = cls.name;
                document.getElementById('class_modal_subtitle').textContent = `Wali Kelas: ${cls.homeroom_teacher} | Total: ${cls.total_students} Siswa`;
                document.getElementById('class_modal_registered_text').textContent = `${cls.registered_count} dari ${cls.total_students} Siswa`;
                document.getElementById('class_modal_balance_text').textContent = cls.formatted_total_balance;

                currentModalStudents = data.students || [];
                renderModalStudents(currentModalStudents);
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-rose-600 font-bold">Gagal memuat data siswa kelas.</td></tr>';
            });
    }

    function renderModalStudents(students) {
        const tbody = document.getElementById('class_students_tbody');
        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-500 font-bold">Tidak ada data siswa ditemukan.</td></tr>';
            return;
        }

        let html = '';
        students.forEach((s, idx) => {
            const statusBadge = s.is_registered 
                ? (s.status === 'active' 
                    ? '<span class="neo-badge bg-[#E6FCF5] text-emerald-900 border border-emerald-500 text-[10px] px-2 py-0.5 font-bold">Penabung Aktif</span>' 
                    : '<span class="neo-badge bg-[#FFF5F5] text-rose-900 border border-rose-500 text-[10px] px-2 py-0.5 font-bold">Tutup Buku</span>')
                : '<span class="neo-badge bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 font-bold">Belum Terdaftar</span>';

            html += `
                <tr class="hover:bg-slate-50">
                    <td class="p-2.5 border-r border-black text-center font-mono font-bold">${idx + 1}</td>
                    <td class="p-2.5 border-r border-black font-bold text-black">${s.name}</td>
                    <td class="p-2.5 border-r border-black font-mono text-slate-600">${s.nisn || '-'}</td>
                    <td class="p-2.5 border-r border-black text-center">${statusBadge}</td>
                    <td class="p-2.5 text-right font-mono font-bold ${s.balance > 0 ? 'text-emerald-800' : 'text-slate-500'}">
                        ${s.formatted_balance}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function filterModalStudents() {
        const q = document.getElementById('class_student_filter').value.toLowerCase().trim();
        if (!q) {
            renderModalStudents(currentModalStudents);
            return;
        }

        const filtered = currentModalStudents.filter(s => {
            return (s.name && s.name.toLowerCase().includes(q)) || 
                   (s.nisn && s.nisn.includes(q)) || 
                   (s.account_number && s.account_number.toLowerCase().includes(q));
        });

        renderModalStudents(filtered);
    }
</script>
@endpush
