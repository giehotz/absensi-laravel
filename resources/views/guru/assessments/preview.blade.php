@extends('layouts.guru')

@section('title', 'Pratinjau Impor Nilai - ' . $package->title)
@section('page-title', 'Pratinjau Impor Nilai')

@section('content')
<div class="space-y-6 pb-16">
    <!-- Header Card -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                    <span>{{ $package->schoolClass->name }}</span>
                    <span>/</span>
                    <span>{{ $package->subject->name }}</span>
                    <span>/</span>
                    <span class="text-black">Pratinjau Impor</span>
                </div>
                <h2 class="font-heading font-black text-2xl text-black">Pratinjau Data Nilai Excel</h2>
                <p class="text-xs text-slate-600 mt-0.5">
                    File: <strong class="text-black font-mono">{{ $filename }}</strong>. Silakan periksa data sebelum disimpan secara permanen ke database.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.penilaian.show', $package) }}" 
                   class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-black">
                    Batal / Batal Impor
                </a>
                <form action="{{ route('guru.penilaian.publish', $package) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-5 py-2.5 text-xs font-black flex items-center gap-2 rounded border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Konfirmasi & Simpan Nilai</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sheet Terisi</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $summary['sheets_with_data'] ?? 0 }}</div>
            <div class="text-[10px] text-slate-600 mt-0.5">Dari 15 sheet SUM</div>
        </div>
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Nilai Valid</div>
            <div class="font-heading font-black text-2xl text-emerald-950 mt-1">{{ $summary['total_valid_scores'] ?? 0 }}</div>
            <div class="text-[10px] text-emerald-900 mt-0.5">Siap dimasukkan</div>
        </div>
        <div class="bg-[#FFE3E3] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Nilai Error</div>
            <div class="font-heading font-black text-2xl text-rose-950 mt-1">{{ $summary['total_error_scores'] ?? 0 }}</div>
            <div class="text-[10px] text-rose-900 mt-0.5">Siswa tak cocok / format salah</div>
        </div>
        <div class="bg-slate-50 border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nilai Kosong</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $summary['total_empty_scores'] ?? 0 }}</div>
            <div class="text-[10px] text-slate-600 mt-0.5">Dilewati secara otomatis</div>
        </div>
    </div>

    <!-- Sheet Tabs & Content -->
    @php
        $activeSheets = collect($sheets)->filter(fn($s) => !empty($s['has_data']));
    @endphp

    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden" x-data="{ activeTab: {{ $activeSheets->keys()->first() ?? 1 }} }">
        <!-- Tab Navigation Bar -->
        <div class="bg-slate-100 border-b-2 border-black flex overflow-x-auto p-2 gap-1.5 sidebar-scroll">
            @foreach($sheets as $num => $sh)
                @php $hasData = !empty($sh['has_data']); @endphp
                <button type="button" 
                        onclick="switchTab({{ $num }})"
                        id="tab-btn-{{ $num }}"
                        class="tab-button px-3.5 py-2 text-xs font-black rounded border-2 cursor-pointer transition-all shrink-0 flex items-center gap-1.5
                        {{ $loop->first ? 'bg-[#FFD43B] text-black border-black shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-slate-700 border-slate-300 hover:border-black' }}">
                    <span>SUM {{ $num }}</span>
                    @if($hasData)
                        <span class="w-2 h-2 rounded-full bg-emerald-500" title="Memiliki data"></span>
                    @else
                        <span class="text-[9px] text-slate-400 font-normal">(Kosong)</span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Sheets Content Panels -->
        @foreach($sheets as $num => $sh)
            <div id="tab-panel-{{ $num }}" class="tab-panel {{ $loop->first ? '' : 'hidden' }} p-5">
                <!-- Sheet Header Info -->
                <div class="mb-4 p-3.5 bg-slate-50 border-2 border-slate-200 rounded flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 font-bold">Materi / Lingkup TP:</span>
                        <strong class="text-black ml-1">{{ !empty($sh['materi']) ? $sh['materi'] : '(Belum diisi di Cell B3)' }}</strong>
                    </div>
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-slate-500 font-bold">Standar KKTP:</span>
                            <strong class="text-black ml-1">{{ $sh['kktp'] ?? $package->kktp_default }}</strong>
                        </div>
                        <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase border {{ !empty($sh['has_data']) ? 'bg-emerald-100 text-emerald-950 border-emerald-300' : 'bg-slate-200 text-slate-700 border-slate-300' }}">
                            {{ !empty($sh['has_data']) ? 'Aktif Disimpan' : 'Akan Dilewati' }}
                        </span>
                    </div>
                </div>

                @if(empty($sh['rows']))
                    <div class="py-8 text-center text-slate-400 text-xs font-bold">
                        Tidak ada baris data siswa yang terbaca pada lembar ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 border-b-2 border-black text-[11px] font-black uppercase text-black">
                                    <th class="py-2.5 px-3 border-r border-slate-300 text-center w-12">Baris</th>
                                    <th class="py-2.5 px-3 border-r border-slate-300 w-28">NIS</th>
                                    <th class="py-2.5 px-3 border-r border-slate-300 min-w-[180px]">Nama Siswa</th>
                                    <th class="py-2.5 px-3 border-r border-slate-300 text-center w-24">Nilai</th>
                                    <th class="py-2.5 px-3 border-r border-slate-300 text-center w-28">Capaian</th>
                                    <th class="py-2.5 px-3 border-r border-slate-300 text-center w-28">Status Baris</th>
                                    <th class="py-2.5 px-3">Catatan / Error</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 font-medium">
                                @foreach($sh['rows'] as $r)
                                    <tr class="{{ $r['row_status'] === 'error' ? 'bg-red-50/60' : ($r['row_status'] === 'valid' ? 'hover:bg-slate-50' : 'bg-slate-50/40 text-slate-400') }}">
                                        <td class="py-2 px-3 border-r border-slate-200 text-center font-mono text-slate-500">{{ $r['row'] }}</td>
                                        <td class="py-2 px-3 border-r border-slate-200 font-mono">{{ $r['nis'] ?? '-' }}</td>
                                        <td class="py-2 px-3 border-r border-slate-200 font-bold {{ $r['row_status'] === 'error' ? 'text-red-700' : 'text-black' }}">
                                            {{ $r['student_name'] ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 border-r border-slate-200 text-center font-mono font-black text-sm">
                                            {{ $r['score'] !== null ? $r['score'] : '-' }}
                                        </td>
                                        <td class="py-2 px-3 border-r border-slate-200 text-center">
                                            @if($r['score'] !== null)
                                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded border {{ $r['completion_status'] === 'tuntas' ? 'bg-emerald-100 text-emerald-950 border-emerald-400' : 'bg-rose-100 text-rose-950 border-rose-400' }}">
                                                    {{ $r['completion_status'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 border-r border-slate-200 text-center">
                                            @if($r['row_status'] === 'valid')
                                                <span class="text-[10px] font-black uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Valid</span>
                                            @elseif($r['row_status'] === 'error')
                                                <span class="text-[10px] font-black uppercase text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Error</span>
                                            @else
                                                <span class="text-[10px] font-bold text-slate-400">Kosong</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-[11px] {{ $r['row_status'] === 'error' ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                            {{ $r['error_message'] ?? ($r['row_status'] === 'empty' ? 'Dilewati (nilai kosong)' : 'Siap disimpan') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
function switchTab(num) {
    document.querySelectorAll('.tab-panel').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('bg-[#FFD43B]', 'text-black', 'border-black', 'shadow-[2px_2px_0px_0px_#000]');
        btn.classList.add('bg-white', 'text-slate-700', 'border-slate-300');
    });

    const activePanel = document.getElementById('tab-panel-' + num);
    const activeBtn = document.getElementById('tab-btn-' + num);

    if (activePanel) activePanel.classList.remove('hidden');
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-300');
        activeBtn.classList.add('bg-[#FFD43B]', 'text-black', 'border-black', 'shadow-[2px_2px_0px_0px_#000]');
    }
}
</script>
@endsection
