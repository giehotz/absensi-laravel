@extends('layouts.mbg')

@section('title', 'Dashboard Petugas MBG')
@section('page_title', 'Dashboard & Riwayat Distribusi MBG')

@section('content')
<div class="space-y-6">

    <!-- Status Banner Hari Ini -->
    @if(!$todayDistribution)
        <div class="p-5 sm:p-6 rounded-2xl border-4 border-black bg-[#FFFBEB] shadow-[5px_5px_0px_0px_#000] flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl border-2 border-black bg-[#FFD43B] flex items-center justify-center text-2xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                    🍱
                </div>
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-200 text-amber-900 border border-black mb-1">
                        Belum Dicatat
                    </span>
                    <h2 class="text-base sm:text-lg font-black font-heading tracking-tight">Distribusi MBG Hari Ini Belum Diinput</h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Catat jumlah porsi makanan bergizi gratis yang berhasil disalurkan kepada siswa dan guru hari ini.
                    </p>
                </div>
            </div>
            <a href="{{ route('mbg.distributions.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#20C997] hover:bg-[#1BA87E] text-black font-black text-xs sm:text-sm border-2 border-black shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all shrink-0">
                <span>➕</span>
                <span>Catat Distribusi Hari Ini</span>
            </a>
        </div>
    @else
        <div class="p-5 sm:p-6 rounded-2xl border-4 border-black bg-[#F0FDF4] shadow-[5px_5px_0px_0px_#000] flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl border-2 border-black bg-[#20C997] text-white flex items-center justify-center text-2xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                    ✅
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-200 text-emerald-900 border border-black">
                            Sudah Dicatat
                        </span>
                        <span class="text-xs font-bold text-slate-500">Menu Hari Ini:</span>
                        <span class="text-xs font-black text-slate-900">{{ $todayDistribution->menu_name }}</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black font-heading tracking-tight">
                        {{ $todayDistribution->total_distributed }} Porsi Berhasil Disalurkan Hari Ini
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Siswa: <span class="font-bold text-black">{{ $todayDistribution->distributed_students }}</span> / {{ $todayDistribution->target_students }} | 
                        Guru: <span class="font-bold text-black">{{ $todayDistribution->distributed_teachers }}</span> / {{ $todayDistribution->target_teachers }} | 
                        Sisa: <span class="font-bold text-red-600">{{ $todayDistribution->leftover_portions }}</span> porsi (Capaian: {{ $todayDistribution->success_rate }}%)
                    </p>
                </div>
            </div>
            <a href="{{ route('mbg.distributions.edit', $todayDistribution) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFD43B] hover:bg-[#FACC15] text-black font-black text-xs border-2 border-black shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all shrink-0">
                <span>✏️</span>
                <span>Koreksi / Edit Hari Ini</span>
            </a>
        </div>
    @endif

    <!-- Kartu Statistik Bulan Ini -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-heading font-black text-sm uppercase tracking-wider text-slate-600">
                Ringkasan Penyaluran Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
            </h3>
            <a href="{{ route('mbg.rekap.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                Lihat Rekap Lengkap & Export &rarr;
            </a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Hari Distribusi -->
            <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Hari Aktif</span>
                    <span class="p-1.5 rounded-lg border border-black bg-[#E0E7FF] text-xs">📆</span>
                </div>
                <div class="text-2xl font-black font-heading">{{ $monthStats['total_days'] }} <span class="text-xs font-bold text-slate-500">Hari</span></div>
                <div class="text-[11px] font-semibold text-slate-500 mt-1">Rata-rata: {{ $monthStats['avg_daily_distributed'] }} porsi/hari</div>
            </div>

            <!-- Total Distribusi Siswa -->
            <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Porsi Siswa</span>
                    <span class="p-1.5 rounded-lg border border-black bg-[#D1F2D9] text-xs">🎒</span>
                </div>
                <div class="text-2xl font-black font-heading text-emerald-700">{{ number_format($monthStats['distributed_students'], 0, ',', '.') }}</div>
                <div class="text-[11px] font-semibold text-slate-500 mt-1">Target: {{ number_format($monthStats['target_students'], 0, ',', '.') }} porsi</div>
            </div>

            <!-- Total Distribusi Guru -->
            <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Porsi Guru & Staf</span>
                    <span class="p-1.5 rounded-lg border border-black bg-[#FEF08A] text-xs">👨‍🏫</span>
                </div>
                <div class="text-2xl font-black font-heading text-amber-700">{{ number_format($monthStats['distributed_teachers'], 0, ',', '.') }}</div>
                <div class="text-[11px] font-semibold text-slate-500 mt-1">Target: {{ number_format($monthStats['target_teachers'], 0, ',', '.') }} porsi</div>
            </div>

            <!-- Capaian & Sisa -->
            <div class="p-4 rounded-xl border-3 border-black bg-white shadow-[4px_4px_0px_0px_#000]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase">Tingkat Capaian</span>
                    <span class="p-1.5 rounded-lg border border-black bg-[#CFFAFE] text-xs">🎯</span>
                </div>
                <div class="text-2xl font-black font-heading text-blue-700">{{ $monthStats['success_rate'] }}%</div>
                <div class="text-[11px] font-semibold text-red-600 mt-1">Total Sisa: {{ number_format($monthStats['total_leftovers'], 0, ',', '.') }} porsi</div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Distribusi -->
    <div class="rounded-2xl border-4 border-black bg-white shadow-[5px_5px_0px_0px_#000] overflow-hidden">
        <div class="p-5 border-b-3 border-black bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-heading font-black text-base text-slate-900">Riwayat Penyaluran MBG</h3>
                <p class="text-xs text-slate-500 font-semibold">Daftar transaksi distribusi harian yang telah dicatat</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('mbg.distributions.create') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#FFD43B] hover:bg-[#FACC15] text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                    <span>➕</span>
                    <span>Tambah Data</span>
                </a>
                <a href="{{ route('mbg.rekap.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white hover:bg-slate-100 text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                    <span>📊</span>
                    <span>Rekap & Filter</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-slate-700 uppercase font-black tracking-wider text-[11px]">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Menu Makanan</th>
                        <th class="py-3 px-4 text-center">Siswa (Target / Realisasi)</th>
                        <th class="py-3 px-4 text-center">Guru (Target / Realisasi)</th>
                        <th class="py-3 px-4 text-center">Total Penyaluran</th>
                        <th class="py-3 px-4 text-center">Sisa</th>
                        <th class="py-3 px-4 text-center">Dokumentasi</th>
                        <th class="py-3 px-4 text-center">Petugas</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($recentDistributions as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold whitespace-nowrap">
                                <div class="text-slate-900">{{ $item->date ? $item->date->translatedFormat('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->date ? $item->date->translatedFormat('l') : '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 max-w-xs">
                                <div class="line-clamp-2">{{ $item->menu_name }}</div>
                                @if($item->notes)
                                    <div class="text-[10px] text-slate-500 italic mt-0.5">Catatan: {{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="font-black text-emerald-700">{{ $item->distributed_students }}</span>
                                <span class="text-slate-400">/</span>
                                <span class="text-slate-500 font-medium">{{ $item->target_students }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="font-black text-amber-700">{{ $item->distributed_teachers }}</span>
                                <span class="text-slate-400">/</span>
                                <span class="text-slate-500 font-medium">{{ $item->target_teachers }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="font-black text-sm text-slate-900">{{ $item->total_distributed }} <span class="text-[10px] font-normal text-slate-500">porsi</span></div>
                                <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold {{ $item->success_rate >= 95 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item->success_rate }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $item->leftover_portions > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                {{ $item->leftover_portions }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($item->documentation_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->documentation_image))
                                    <a href="{{ asset('storage/' . $item->documentation_image) }}" target="_blank" 
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 border border-black text-[10px] font-bold text-slate-700">
                                        <span>📷</span>
                                        <span>Lihat Foto</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap text-[11px] text-slate-600 font-semibold">
                                {{ $item->recordedByUser?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($item->classDistributions->isNotEmpty())
                                        <button type="button" 
                                                onclick='openClassDetailModal(@json($item), @json($item->classDistributions))'
                                                class="p-1.5 rounded-lg border-2 border-black bg-[#5294FF] hover:bg-blue-600 text-white shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                                title="Lihat Rincian Per Kelas">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('mbg.distributions.edit', $item) }}" 
                                       class="p-1.5 rounded-lg border-2 border-black bg-[#FFD43B] hover:bg-[#FACC15] text-black shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                       title="Edit Data">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('mbg.distributions.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan distribusi tanggal {{ $item->date ? $item->date->format('d/m/Y') : '' }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 rounded-lg border-2 border-black bg-red-100 hover:bg-red-200 text-red-700 shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                                title="Hapus Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-500 font-semibold">
                                <div class="text-3xl mb-2">🍱</div>
                                <p>Belum ada catatan distribusi makanan yang tersimpan.</p>
                                <a href="{{ route('mbg.distributions.create') }}" class="inline-block mt-3 text-xs font-bold text-blue-600 hover:underline">
                                    + Catat Distribusi Pertama Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentDistributions->hasPages())
            <div class="p-4 border-t-2 border-black bg-slate-50">
                {{ $recentDistributions->links() }}
            </div>
        @endif
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

@push('scripts')
<script>
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
