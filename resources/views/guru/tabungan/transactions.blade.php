@extends('layouts.guru')

@section('title', 'Buku Mutasi Kas Tabungan Siswa')
@section('page-title', 'Buku Mutasi Kas Tabungan Siswa')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="space-y-0.5">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📜</span> Buku Mutasi & Riwayat Transaksi
            </h2>
            <p class="text-xs text-slate-500 font-semibold">Seluruh rekam jejak setoran dan penarikan tabungan siswa</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.savings.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2 flex items-center gap-1.5">
                <span>←</span> Form Transaksi
            </a>
            <a href="{{ route('guru.savings.export', request()->query()) }}" class="neo-btn bg-[#20C997] hover:bg-emerald-600 text-white text-xs font-bold px-3.5 py-2 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Sesuai Filter</span>
            </a>
        </div>
    </div>

    <!-- Filter Form Box -->
    <div class="bg-white neo-box p-4 border-3 border-black">
        <form action="{{ route('guru.savings.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Search Keyword -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Pencarian</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NISN / Kode TRX..." 
                    class="w-full neo-input py-1.5 text-xs">
            </div>

            <!-- Transaction Type -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Jenis Transaksi</label>
                <select name="type" class="w-full neo-input py-1.5 text-xs">
                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Setoran Tunai (+)</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai (-)</option>
                </select>
            </div>

            <!-- Class Filter -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Rombel / Kelas</label>
                <select name="class_id" class="w-full neo-input py-1.5 text-xs">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string) $classId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full neo-input py-1.5 text-xs">
            </div>

            <!-- End Date & Buttons -->
            <div class="space-y-1 flex flex-col justify-end">
                <label class="font-bold text-slate-700">Sampai Tanggal</label>
                <div class="flex items-center gap-1.5">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full neo-input py-1.5 text-xs">
                    <button type="submit" class="neo-btn bg-[#FFD43B] text-black font-black px-3 py-1.5 hover:bg-yellow-400" title="Terapkan Filter">
                        Filter
                    </button>
                    @if(!empty($search) || $type !== 'all' || !empty($classId) || !empty($startDate) || !empty($endDate))
                        <a href="{{ route('guru.savings.transactions') }}" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-2.5 py-1.5" title="Reset Filter">
                            ↺
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table of Transactions -->
    <div class="bg-white neo-box p-4 border-3 border-black space-y-4">
        <div class="flex items-center justify-between text-xs font-bold text-slate-600">
            <span>Ditemukan: <strong class="text-black">{{ $transactions->total() }}</strong> catatan transaksi</span>
        </div>

        <div class="overflow-x-auto border-2 border-black">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                    <tr>
                        <th class="p-3 border-r border-black text-center w-12">No</th>
                        <th class="p-3 border-r border-black">Kode Transaksi</th>
                        <th class="p-3 border-r border-black">Tanggal & Waktu</th>
                        <th class="p-3 border-r border-black">Nama Siswa</th>
                        <th class="p-3 border-r border-black text-center">Kelas</th>
                        <th class="p-3 border-r border-black text-center">Jenis</th>
                        <th class="p-3 border-r border-black text-right">Nominal</th>
                        <th class="p-3 border-r border-black text-right">Saldo Sesudah</th>
                        <th class="p-3 border-r border-black">Keterangan</th>
                        <th class="p-3 border-r border-black">Petugas</th>
                        <th class="p-3 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @forelse($transactions as $idx => $t)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center font-bold border-r border-black">
                                {{ $transactions->firstItem() + $idx }}
                            </td>
                            <td class="p-3 border-r border-black font-mono font-bold text-black">
                                {{ $t->transaction_code }}
                            </td>
                            <td class="p-3 border-r border-black font-mono text-slate-700">
                                {{ $t->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="p-3 border-r border-black">
                                <div class="font-bold text-black">{{ $t->savingsAccount?->student?->user?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NISN: {{ $t->savingsAccount?->student?->nisn ?: ($t->savingsAccount?->student?->nis ?: '-') }}</div>
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                <span class="neo-badge bg-[#E7F5FF] text-blue-950 text-[10px] px-2 py-0.5 font-bold">
                                    {{ $t->savingsAccount?->student?->schoolClass?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                @if($t->isDeposit())
                                    <span class="neo-badge bg-[#20C997] text-white text-[10px] px-2 py-0.5 font-bold">SETOR</span>
                                @else
                                    <span class="neo-badge bg-[#FF6B6B] text-white text-[10px] px-2 py-0.5 font-bold">TARIK</span>
                                @endif
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-black {{ $t->isDeposit() ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $t->isDeposit() ? '+' : '-' }}{{ $t->formatted_amount }}
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-bold text-slate-900">
                                {{ $t->formatted_balance_after }}
                            </td>
                            <td class="p-3 border-r border-black text-slate-600 text-[11px]">
                                {{ $t->description ?: '-' }}
                            </td>
                            <td class="p-3 border-r border-black text-slate-700 text-xs">
                                {{ $t->handler?->name ?? '-' }}
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('guru.savings.receipt', $t) }}" target="_blank" 
                                    class="neo-btn bg-slate-100 hover:bg-[#FFD43B] text-black text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1"
                                    title="Cetak Kuitansi Transaksi">
                                    <span>🖨️</span> Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                                <div class="text-3xl">📭</div>
                                <div class="text-sm font-bold text-slate-800">Tidak ada data transaksi yang sesuai filter</div>
                                <p class="text-xs text-slate-500">Coba ubah filter atau rentang tanggal pencarian Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
