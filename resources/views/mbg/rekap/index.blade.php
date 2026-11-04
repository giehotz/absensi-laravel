@extends('layouts.mbg')

@section('title', 'Rekapitulasi Distribusi MBG')
@section('page_title', 'Rekapitulasi Penyaluran Makan Bergizi Gratis')

@section('content')
<div class="space-y-6">

    <!-- Filter Card Periode Rekapitulasi -->
    <div class="rounded-2xl border-4 border-black bg-white shadow-[5px_5px_0px_0px_#000] p-5 sm:p-6 space-y-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-base sm:text-lg text-slate-900">Filter Periode Rekapitulasi</h2>
                <p class="text-xs text-slate-500 font-semibold">Pilih periode laporan untuk menampilkan rekapitulasi 1 bulan, triwulan, semester, atau rentang tanggal bebas</p>
            </div>
            <!-- Action Buttons: Print Ringkas (Versi 1), Print Detail SKP (Versi 2) & Export Excel -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="openPrintPeriodModal('rekap')"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white hover:bg-slate-100 text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all cursor-pointer"
                   title="Cetak format ringkas presensi harian">
                    <span>🖨️</span>
                    <span>Cetak Rekap Ringkas</span>
                </button>
                <button type="button" onclick="openPrintPeriodModal('detail')"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#FFD43B] hover:bg-[#FACC15] text-black font-black text-xs border-2 border-black shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all cursor-pointer"
                   title="Versi 2: Dokumen Laporan Pelaksanaan & Pendistribusian Lengkap (Format Resmi Bukti Dukung SKP e-Kinerja)">
                    <span>📑</span>
                    <span>Cetak Laporan Lengkap (SKP)</span>
                </button>
                <a href="{{ route('mbg.rekap.export', request()->query()) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#20C997] hover:bg-[#1BA87E] text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                    <span>📥</span>
                    <span>Unduh Excel</span>
                </a>
            </div>
        </div>

        <!-- Filter Presets (Pilihan Cepat) -->
        <div class="pt-2 border-t-2 border-black/10">
            <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-2 font-heading">
                Pilihan Cepat Periode:
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('mbg.rekap.index', ['period' => 'this_month']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'this_month' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    🗓️ Bulan Ini (1 Bulan)
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'last_month']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'last_month' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    🗓️ Bulan Lalu
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'triwulan_1']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'triwulan_1' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    📊 Triwulan I (Bulan 1 - 3)
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'triwulan_2']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'triwulan_2' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    📊 Triwulan II (Bulan 4 - 6)
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'triwulan_3']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'triwulan_3' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    📊 Triwulan III (Bulan 7 - 9)
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'triwulan_4']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'triwulan_4' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    📊 Triwulan IV (Bulan 10 - 12)
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'semester_1']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'semester_1' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    🎓 Semester Ganjil
                </a>
                <a href="{{ route('mbg.rekap.index', ['period' => 'semester_2']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black transition-all shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5
                   {{ $period === 'semester_2' ? 'bg-[#FFD43B] text-black font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    🎓 Semester Genap
                </a>
            </div>
        </div>

        <!-- Filter Form Rentang Bulan Bebas & Tanggal Bebas -->
        <div class="pt-2 border-t-2 border-black/10 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Form Rentang Bulan Bebas (Contoh: Jan - Jun, Jan - Mar, dll) -->
            <form action="{{ route('mbg.rekap.index') }}" method="GET" class="flex flex-wrap items-end gap-2.5 p-3.5 bg-amber-50/80 rounded-xl border-2 border-black/30 shadow-[2px_2px_0px_0px_#000]">
                <div class="w-full flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase text-amber-950 font-heading">
                        🗓️ Rentang Bulan Bebas:
                    </span>
                    <span class="text-[10px] text-amber-800 font-bold">Misal: Jan s.d. Jun</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Dari:</label>
                    <select name="from_month" class="px-2 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                        @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $name)
                            <option value="{{ $num }}" {{ ($fromMonth ?? $range['start_date']->month) == $num ? 'selected' : '' }}>{{ $num }}. {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Sampai:</label>
                    <select name="to_month" class="px-2 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                        @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $name)
                            <option value="{{ $num }}" {{ ($toMonth ?? $range['end_date']->month) == $num ? 'selected' : '' }}>{{ $num }}. {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Tahun:</label>
                    <input type="number" name="year" value="{{ $year ?? $range['start_date']->year }}" min="2020" max="2035" class="w-20 px-2 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#FFD43B] hover:bg-amber-400 text-black font-black text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                    Terapkan Bulan
                </button>
            </form>

            <!-- Form Rentang Tanggal Spesifik -->
            <form action="{{ route('mbg.rekap.index') }}" method="GET" class="flex flex-wrap items-end gap-2.5 p-3.5 bg-blue-50/80 rounded-xl border-2 border-black/30 shadow-[2px_2px_0px_0px_#000]">
                <input type="hidden" name="period" value="custom">
                <div class="w-full flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase text-blue-950 font-heading">
                        📅 Rentang Tanggal Spesifik:
                    </span>
                    <span class="text-[10px] text-blue-800 font-bold">Harian Kustom</span>
                </div>
                <div>
                    <label for="start_date" class="block text-[10px] font-bold text-slate-600 mb-0.5">Dari:</label>
                    <input type="date" id="start_date" name="start_date" 
                           value="{{ $startDate ?? $range['start_date']->format('Y-m-d') }}"
                           class="px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                </div>
                <div>
                    <label for="end_date" class="block text-[10px] font-bold text-slate-600 mb-0.5">Sampai:</label>
                    <input type="date" id="end_date" name="end_date" 
                           value="{{ $endDate ?? $range['end_date']->format('Y-m-d') }}"
                           class="px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                </div>
                <button type="submit" 
                        class="px-3.5 py-1.5 rounded-lg bg-[#5294FF] hover:bg-blue-600 text-white font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                    Terapkan Tanggal
                </button>
            </form>
        </div>
    </div>

    <!-- Banner Ringkasan Periode Aktif -->
    <div class="p-4 rounded-xl border-3 border-black bg-[#E0E7FF] shadow-[3px_3px_0px_0px_#000] flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-xl">📊</span>
            <div>
                <span class="text-[11px] font-black uppercase tracking-wider text-indigo-900 font-heading">Periode Rekap Terpilih:</span>
                <span class="text-sm font-black text-slate-900 ml-1">{{ $range['period_label'] }}</span>
                <span class="text-xs text-slate-600 font-semibold ml-2">
                    ({{ $range['start_date']->translatedFormat('d M Y') }} - {{ $range['end_date']->translatedFormat('d M Y') }})
                </span>
            </div>
        </div>
        <div class="text-xs font-black text-slate-800 bg-white px-3 py-1 rounded-lg border border-black shadow-[1px_1px_0px_0px_#000]">
            {{ $stats['total_days'] }} Hari Distribusi
        </div>
    </div>

    <!-- Kartu Statistik Periode Terpilih -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Porsi Siswa -->
        <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Porsi Siswa Terdistribusi</div>
            <div class="text-2xl font-black font-heading text-emerald-700 mt-1">
                {{ number_format($stats['distributed_students'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Dari target {{ number_format($stats['target_students'], 0, ',', '.') }} porsi
            </div>
        </div>

        <!-- Total Porsi Guru -->
        <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Porsi Guru Terdistribusi</div>
            <div class="text-2xl font-black font-heading text-amber-700 mt-1">
                {{ number_format($stats['distributed_teachers'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Dari target {{ number_format($stats['target_teachers'], 0, ',', '.') }} porsi
            </div>
        </div>

        <!-- Total Keseluruhan Porsi -->
        <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Total Seluruh Makanan</div>
            <div class="text-2xl font-black font-heading text-slate-900 mt-1">
                {{ number_format($stats['total_distributed'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Rata-rata: {{ $stats['avg_daily_distributed'] }} porsi / hari
            </div>
        </div>

        <!-- Ketercapaian & Sisa -->
        <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Capaian & Sisa Porsi</div>
            <div class="text-2xl font-black font-heading text-blue-700 mt-1">
                {{ $stats['success_rate'] }}%
            </div>
            <div class="text-[11px] font-semibold text-red-600 mt-1">
                Total Sisa: {{ number_format($stats['total_leftovers'], 0, ',', '.') }} porsi
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Data Harian -->
    <div class="rounded-2xl border-4 border-black bg-white shadow-[5px_5px_0px_0px_#000] overflow-hidden">
        <div class="p-4 border-b-3 border-black bg-slate-50 flex items-center justify-between">
            <h3 class="font-heading font-black text-sm text-slate-900">
                Rincian Harian Distribusi MBG ({{ $distributions->count() }} Data)
            </h3>
            <span class="text-xs font-bold text-slate-500">Urutan Tanggal Kronologis</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-slate-700 uppercase font-black tracking-wider text-[11px]">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-4">Menu Makanan</th>
                        <th class="py-3 px-3 text-center">Target Siswa</th>
                        <th class="py-3 px-3 text-center">Realisasi Siswa</th>
                        <th class="py-3 px-3 text-center">Target Guru</th>
                        <th class="py-3 px-3 text-center">Realisasi Guru</th>
                        <th class="py-3 px-3 text-center">Total Porsi</th>
                        <th class="py-3 px-3 text-center">Sisa</th>
                        <th class="py-3 px-3 text-center">Capaian</th>
                        <th class="py-3 px-3">Keterangan</th>
                        <th class="py-3 px-3 text-center">Petugas</th>
                        <th class="py-3 px-3 text-center">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($distributions as $idx => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3 font-bold whitespace-nowrap">
                                <div>{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->date ? $item->date->translatedFormat('D') : '' }}</div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $item->menu_name }}</td>
                            <td class="py-3 px-3 text-center font-medium">{{ $item->target_students }}</td>
                            <td class="py-3 px-3 text-center font-bold text-emerald-700">{{ $item->distributed_students }}</td>
                            <td class="py-3 px-3 text-center font-medium">{{ $item->target_teachers }}</td>
                            <td class="py-3 px-3 text-center font-bold text-amber-700">{{ $item->distributed_teachers }}</td>
                            <td class="py-3 px-3 text-center font-black text-slate-900">{{ $item->total_distributed }}</td>
                            <td class="py-3 px-3 text-center font-bold {{ $item->leftover_portions > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                {{ $item->leftover_portions }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $item->success_rate >= 95 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item->success_rate }}%
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs truncate">{{ $item->notes ?? '-' }}</td>
                            <td class="py-3 px-3 text-center text-slate-500 whitespace-nowrap text-[11px]">
                                {{ $item->recordedByUser?->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($item->classDistributions->isNotEmpty())
                                    <button type="button" 
                                            onclick='openClassDetailModal(@json($item), @json($item->classDistributions))'
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded bg-[#5294FF] hover:bg-blue-600 text-white font-bold text-[10px] border border-black shadow-[1px_1px_0px_0px_#000]">
                                        <span>🏫</span>
                                        <span>Kelas</span>
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-10 text-center text-slate-500 font-semibold">
                                <div class="text-3xl mb-2">🔍</div>
                                <p>Tidak ditemukan data distribusi pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($distributions->isNotEmpty())
                    <tfoot class="bg-slate-100 border-t-3 border-black font-black text-slate-900">
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-center font-heading uppercase tracking-wider">Total Penyaluran Periode</td>
                            <td class="py-3 px-3 text-center">{{ number_format($stats['target_students'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-emerald-700">{{ number_format($stats['distributed_students'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center">{{ number_format($stats['target_teachers'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-amber-700">{{ number_format($stats['distributed_teachers'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-sm font-heading">{{ number_format($stats['total_distributed'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-red-600">{{ number_format($stats['total_leftovers'], 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center text-blue-700">{{ $stats['success_rate'] }}%</td>
                            <td colspan="3" class="py-3 px-3 text-[11px] text-slate-600 italic">Rata-rata: {{ $stats['avg_daily_distributed'] }} porsi/hari</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<!-- Modal Rincian Kelas -->
<div id="classDetailModal" class="fixed inset-0 z-50 bg-black/60 hidden items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-2xl border-4 border-black shadow-[6px_6px_0px_0px_#000] overflow-hidden max-h-[90vh] flex flex-col">
        <div class="p-4 border-b-3 border-black bg-[#E0E7FF] flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-heading font-black text-sm text-slate-900" id="modal_title">Rincian Penyaluran Per Kelas</h3>
                <p class="text-[11px] font-semibold text-slate-600" id="modal_subtitle">-</p>
            </div>
            <button type="button" onclick="closeClassDetailModal()" class="font-black text-slate-500 hover:text-black">✕</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 space-y-4">
            <div class="overflow-x-auto rounded-xl border-2 border-black">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b-2 border-black text-slate-700 uppercase font-black tracking-wider text-[11px]">
                            <th class="py-2.5 px-3">Rombel / Kelas</th>
                            <th class="py-2.5 px-3 text-center">Target</th>
                            <th class="py-2.5 px-3 text-center">Disalurkan</th>
                            <th class="py-2.5 px-3 text-center">Sisa</th>
                            <th class="py-2.5 px-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="modal_table_body" class="divide-y divide-slate-200">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-3 border-t-2 border-black/10 bg-slate-50 flex justify-end shrink-0">
            <button type="button" onclick="closeClassDetailModal()" class="px-4 py-2 rounded-lg border-2 border-black bg-slate-200 font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Pemilihan Periode Sebelum Cetak (Ringkas & Detail SKP) -->
<div id="printPeriodModal" class="fixed inset-0 z-50 bg-black/60 hidden items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white rounded-2xl border-4 border-black shadow-[8px_8px_0px_0px_#000] overflow-hidden max-h-[92vh] flex flex-col">
        <div class="p-4 border-b-3 border-black bg-[#FFD43B] flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-heading font-black text-base text-slate-900" id="print_modal_title">
                    🖨️ Cetak Laporan Distribusi MBG
                </h3>
                <p class="text-xs font-bold text-slate-700">Tentukan rentang periode laporan rutin sebelum mencetak</p>
            </div>
            <button type="button" onclick="closePrintPeriodModal()" class="w-8 h-8 rounded-lg bg-white border-2 border-black flex items-center justify-center font-black text-black hover:bg-slate-100 shadow-[1px_1px_0px_0px_#000]">
                ✕
            </button>
        </div>

        <form id="printPeriodForm" action="{{ route('mbg.rekap.print-detail') }}" method="GET" target="_blank" class="p-5 overflow-y-auto flex-1 space-y-4">
            <!-- Pilihan Format Laporan -->
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 font-heading">
                    1. Format Dokumen:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <label class="flex items-start gap-2.5 p-3 rounded-xl border-2 border-black bg-amber-50 cursor-pointer shadow-[2px_2px_0px_0px_#000] hover:bg-amber-100/80 transition-all">
                        <input type="radio" name="report_format" id="format_detail" value="detail" checked onchange="toggleReportFormat()" class="mt-0.5 accent-amber-500">
                        <div>
                            <span class="block text-xs font-black text-slate-900 font-heading">📑 Laporan Lengkap (Detail SKP)</span>
                            <span class="block text-[11px] text-slate-600 mt-0.5">5 Halaman lengkap: Cover, SKP/RHK, SOP Alur, Tabel Rombel, Rekap Bulanan, Evaluasi &amp; RTL.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 p-3 rounded-xl border-2 border-black bg-white cursor-pointer shadow-[2px_2px_0px_0px_#000] hover:bg-slate-50 transition-all">
                        <input type="radio" name="report_format" id="format_rekap" value="rekap" onchange="toggleReportFormat()" class="mt-0.5 accent-amber-500">
                        <div>
                            <span class="block text-xs font-black text-slate-900 font-heading">🖨️ Rekapitulasi Ringkas</span>
                            <span class="block text-[11px] text-slate-600 mt-0.5">Tabel rekapitulasi distribusi harian resmi ber-kop surat madrasah.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Mode Pemilihan Periode -->
            <div class="pt-2 border-t-2 border-black/10">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 font-heading">
                    2. Pilihan Rentang Waktu Periode:
                </label>

                <!-- Tabs Mode Periode -->
                <div class="flex gap-2 mb-3">
                    <button type="button" onclick="switchPeriodTab('month_range')" id="tab_btn_month_range"
                            class="px-3 py-1.5 rounded-lg text-xs font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] bg-[#FFD43B] text-black">
                        🗓️ Rentang Bulan Bebas
                    </button>
                    <button type="button" onclick="switchPeriodTab('presets')" id="tab_btn_presets"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black shadow-[2px_2px_0px_0px_#000] bg-slate-100 text-slate-700">
                        ⚡ Preset Periode
                    </button>
                    <button type="button" onclick="switchPeriodTab('custom_date')" id="tab_btn_custom_date"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black shadow-[2px_2px_0px_0px_#000] bg-slate-100 text-slate-700">
                        📅 Tanggal Kustom
                    </button>
                </div>

                <!-- Tab 1: Rentang Bulan Bebas (Default) -->
                <div id="tab_content_month_range" class="p-3.5 rounded-xl border-2 border-black bg-amber-50/70 space-y-3 shadow-[2px_2px_0px_0px_#000]">
                    <div class="text-xs font-bold text-amber-950 flex items-center justify-between">
                        <span>Pilih rentang bulan apa saja sesuai periode pelaporan:</span>
                        <span class="text-[11px] text-amber-800">Bebas memilih</span>
                    </div>

                    <!-- Shortcut Cepat Populer -->
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="setMonthRange(1, 6)" class="px-2.5 py-1 rounded bg-white hover:bg-amber-100 border border-black text-[11px] font-bold">
                            Jan – Jun (6 Bulan)
                        </button>
                        <button type="button" onclick="setMonthRange(1, 3)" class="px-2.5 py-1 rounded bg-white hover:bg-amber-100 border border-black text-[11px] font-bold">
                            Jan – Mar (Triwulan I)
                        </button>
                        <button type="button" onclick="setMonthRange(4, 6)" class="px-2.5 py-1 rounded bg-white hover:bg-amber-100 border border-black text-[11px] font-bold">
                            Apr – Jun (Triwulan II)
                        </button>
                        <button type="button" onclick="setMonthRange(7, 9)" class="px-2.5 py-1 rounded bg-white hover:bg-amber-100 border border-black text-[11px] font-bold">
                            Jul – Sep (Triwulan III)
                        </button>
                        <button type="button" onclick="setMonthRange(10, 12)" class="px-2.5 py-1 rounded bg-white hover:bg-amber-100 border border-black text-[11px] font-bold">
                            Okt – Des (Triwulan IV)
                        </button>
                        <button type="button" onclick="setMonthRange(7, 12)" class="px-2.5 py-1 rounded bg-white hover:bg-amber-100 border border-black text-[11px] font-bold">
                            Jul – Des (Semester II)
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Dari Bulan:</label>
                            <select id="print_from_month" name="from_month" class="w-full px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                                @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $name)
                                    <option value="{{ $num }}" {{ ($fromMonth ?? $range['start_date']->month) == $num ? 'selected' : '' }}>
                                        {{ $num }}. {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Sampai Bulan:</label>
                            <select id="print_to_month" name="to_month" class="w-full px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                                @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $name)
                                    <option value="{{ $num }}" {{ ($toMonth ?? $range['end_date']->month) == $num ? 'selected' : '' }}>
                                        {{ $num }}. {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Tahun:</label>
                            <input type="number" id="print_year" name="year" value="{{ $year ?? $range['start_date']->year }}" min="2020" max="2035" class="w-full px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Preset Periode -->
                <div id="tab_content_presets" class="hidden p-3.5 rounded-xl border-2 border-black bg-slate-50 space-y-2 shadow-[2px_2px_0px_0px_#000]">
                    <div class="text-xs font-bold text-slate-700 mb-1">Pilih preset standar:</div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="triwulan_1" class="accent-amber-500">
                            <span>Triwulan I (Jan - Mar)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="triwulan_2" class="accent-amber-500">
                            <span>Triwulan II (Apr - Jun)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="triwulan_3" class="accent-amber-500">
                            <span>Triwulan III (Jul - Sep)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="triwulan_4" class="accent-amber-500">
                            <span>Triwulan IV (Okt - Des)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="semester_2" class="accent-amber-500">
                            <span>Semester Genap (Jan - Jun)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="semester_1" class="accent-amber-500">
                            <span>Semester Ganjil (Jul - Des)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="this_month" class="accent-amber-500">
                            <span>Bulan Ini</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-black cursor-pointer text-xs font-bold">
                            <input type="radio" name="period" value="last_month" class="accent-amber-500">
                            <span>Bulan Lalu</span>
                        </label>
                    </div>
                </div>

                <!-- Tab 3: Tanggal Kustom -->
                <div id="tab_content_custom_date" class="hidden p-3.5 rounded-xl border-2 border-black bg-blue-50/70 space-y-2 shadow-[2px_2px_0px_0px_#000]">
                    <div class="text-xs font-bold text-blue-950 mb-1">Tentukan tanggal awal dan akhir spesifik:</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Mulai Tanggal:</label>
                            <input type="date" id="print_start_date" name="start_date" value="{{ $range['start_date']->format('Y-m-d') }}" class="w-full px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Sampai Tanggal:</label>
                            <input type="date" id="print_end_date" name="end_date" value="{{ $range['end_date']->format('Y-m-d') }}" class="w-full px-2.5 py-1.5 rounded-lg border-2 border-black bg-white text-xs font-bold shadow-[1px_1px_0px_0px_#000]">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="pt-4 border-t-2 border-black/10 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closePrintPeriodModal()" class="px-4 py-2 rounded-xl border-2 border-black bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#000]">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl border-2 border-black bg-[#FFD43B] hover:bg-[#FACC15] font-black text-xs shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 flex items-center gap-1.5">
                    <span>🖨️</span>
                    <span>Buka &amp; Cetak Dokumen</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentSelectedMode = 'month_range';

    function openPrintPeriodModal(format = 'detail') {
        const form = document.getElementById('printPeriodForm');
        const detailRadio = document.getElementById('format_detail');
        const rekapRadio = document.getElementById('format_rekap');

        if (format === 'rekap') {
            rekapRadio.checked = true;
            form.action = "{{ route('mbg.rekap.print') }}";
            document.getElementById('print_modal_title').innerText = '🖨️ Cetak Rekapitulasi Ringkas MBG';
        } else {
            detailRadio.checked = true;
            form.action = "{{ route('mbg.rekap.print-detail') }}";
            document.getElementById('print_modal_title').innerText = '📑 Cetak Laporan Lengkap MBG (Detail SKP)';
        }

        switchPeriodTab('month_range');

        const modal = document.getElementById('printPeriodModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePrintPeriodModal() {
        const modal = document.getElementById('printPeriodModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleReportFormat() {
        const form = document.getElementById('printPeriodForm');
        const isDetail = document.getElementById('format_detail').checked;
        if (isDetail) {
            form.action = "{{ route('mbg.rekap.print-detail') }}";
            document.getElementById('print_modal_title').innerText = '📑 Cetak Laporan Lengkap MBG (Detail SKP)';
        } else {
            form.action = "{{ route('mbg.rekap.print') }}";
            document.getElementById('print_modal_title').innerText = '🖨️ Cetak Rekapitulasi Ringkas MBG';
        }
    }

    function switchPeriodTab(tab) {
        currentSelectedMode = tab;
        const tabs = ['month_range', 'presets', 'custom_date'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab_btn_' + t);
            const content = document.getElementById('tab_content_' + t);

            if (t === tab) {
                btn.className = "px-3 py-1.5 rounded-lg text-xs font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] bg-[#FFD43B] text-black";
                content.classList.remove('hidden');
            } else {
                btn.className = "px-3 py-1.5 rounded-lg text-xs font-bold border-2 border-black shadow-[2px_2px_0px_0px_#000] bg-slate-100 text-slate-700 hover:bg-slate-200";
                content.classList.add('hidden');
            }
        });

        // Enable or disable fields based on active tab so unwanted parameters aren't submitted
        const monthInputs = document.querySelectorAll('#tab_content_month_range select, #tab_content_month_range input');
        const presetInputs = document.querySelectorAll('#tab_content_presets input');
        const customInputs = document.querySelectorAll('#tab_content_custom_date input');

        monthInputs.forEach(i => i.disabled = (tab !== 'month_range'));
        presetInputs.forEach(i => i.disabled = (tab !== 'presets'));
        customInputs.forEach(i => i.disabled = (tab !== 'custom_date'));
    }

    function setMonthRange(from, to) {
        document.getElementById('print_from_month').value = from;
        document.getElementById('print_to_month').value = to;
    }

    function openClassDetailModal(dist, classItems) {
        document.getElementById('modal_title').innerText = 'Rincian Per Kelas: ' + (dist.date ? dist.date.substring(0, 10) : '');
        document.getElementById('modal_subtitle').innerText = 'Menu: ' + dist.menu_name;

        const tbody = document.getElementById('modal_table_body');
        tbody.innerHTML = '';

        if (!classItems || classItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-500">Tidak ada data rincian kelas.</td></tr>';
        } else {
            classItems.forEach(item => {
                const className = item.school_class ? item.school_class.name : ('Kelas ID ' + item.school_class_id);
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50';
                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-bold text-slate-900">Kelas ${className}</td>
                    <td class="py-2.5 px-3 text-center font-medium">${item.target_portions}</td>
                    <td class="py-2.5 px-3 text-center font-black text-emerald-700">${item.distributed_portions}</td>
                    <td class="py-2.5 px-3 text-center font-bold ${item.leftover_portions > 0 ? 'text-red-600' : 'text-slate-400'}">${item.leftover_portions}</td>
                    <td class="py-2.5 px-3 text-slate-600">${item.notes || '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Add row for Guru & Staff
        const trGuru = document.createElement('tr');
        trGuru.className = 'bg-[#FEF08A]/30 font-black';
        const teacherLeftover = Math.max(0, dist.target_teachers - dist.distributed_teachers);
        trGuru.innerHTML = `
            <td class="py-2.5 px-3 text-slate-900">👨‍🏫 Guru & Tenaga Kependidikan</td>
            <td class="py-2.5 px-3 text-center font-medium">${dist.target_teachers}</td>
            <td class="py-2.5 px-3 text-center font-black text-amber-800">${dist.distributed_teachers}</td>
            <td class="py-2.5 px-3 text-center font-bold ${teacherLeftover > 0 ? 'text-red-600' : 'text-slate-400'}">${teacherLeftover}</td>
            <td class="py-2.5 px-3 text-slate-600">Konsumsi Guru & Staf</td>
        `;
        tbody.appendChild(trGuru);

        const modal = document.getElementById('classDetailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeClassDetailModal() {
        const modal = document.getElementById('classDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection
