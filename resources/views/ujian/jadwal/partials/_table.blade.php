{{-- Tabel Jadwal Ujian --}}
<div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
    {{-- Filter Tingkat (Pill Tabs) --}}
    <div class="mb-4 flex flex-wrap items-center gap-1.5 pb-3 border-b-2 border-black">
        <span class="text-xs font-black uppercase text-slate-700 mr-1 flex items-center gap-1">
            <span>Filter Tingkat:</span>
        </span>
        @php $curTingkat = request('tingkat', $selectedTingkat ?? 'all'); @endphp
        <a href="{{ route('ujian.jadwal.index', array_merge(request()->query(), ['tingkat' => 'all'])) }}"
           class="px-3 py-1 text-xs font-black rounded-lg border-2 border-black transition-all cursor-pointer {{ $curTingkat === 'all' ? 'bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            Semua Tingkat
        </a>
        @foreach([1, 2, 3, 4, 5, 6] as $lvl)
            <a href="{{ route('ujian.jadwal.index', array_merge(request()->query(), ['tingkat' => $lvl])) }}"
               class="px-3 py-1 text-xs font-black rounded-lg border-2 border-black transition-all cursor-pointer {{ (string)$curTingkat === (string)$lvl ? 'bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Kelas {{ $lvl }}
            </a>
        @endforeach
    </div>

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <input type="search" id="search_jadwal" oninput="filterJadwalTable()" placeholder="Cari mapel atau kelas…"
                       class="w-full sm:w-60 px-3 py-1.5 pl-8 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[2px_2px_0px_0px_#000] focus:outline-none focus:bg-yellow-50/50">
                <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                </svg>
            </div>
            <select id="filter_bentuk_ujian" onchange="filterJadwalTable()"
                    class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[2px_2px_0px_0px_#000] focus:outline-none cursor-pointer">
                <option value="">Semua Bentuk Ujian</option>
                @foreach($bentukUjianList ?? [] as $bu)
                    <option value="{{ strtolower($bu) }}">{{ $bu }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <span id="jadwal_count_badge" class="px-2.5 py-1 text-[11px] font-black border-2 border-black rounded-lg bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]">
                {{ $jadwals->count() }} Slot Jadwal
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse border-2 border-black">
            <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                <tr>
                    <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                    <th class="p-2.5 border-r border-black">Tanggal</th>
                    <th class="p-2.5 border-r border-black text-center w-20">Sesi Ke-</th>
                    <th class="p-2.5 border-r border-black text-center w-24">Jam Mulai</th>
                    <th class="p-2.5 border-r border-black text-center w-24">Jam Selesai</th>
                    <th class="p-2.5 border-r border-black text-center w-28">Durasi (Menit)</th>
                    <th class="p-2.5 border-r border-black text-center w-28">Bentuk Ujian</th>
                    <th class="p-2.5 border-r border-black">Mata Pelajaran</th>
                    <th class="p-2.5 border-r border-black">Rombel / Tingkat</th>
                    <th class="p-2.5 text-center w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black font-medium">
                @forelse($jadwals as $idx => $j)
                    <tr class="jadwal-row hover:bg-amber-50/50"
                        data-jenis-soal="{{ strtolower($j->jenis_soal) }}"
                        data-search="{{ strtolower($j->nama_mapel . ' ' . $j->target_kelas . ' ' . $j->jenis_soal . ' ' . $j->tanggal_ujian->format('d/m/Y')) }}">
                        <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="p-2.5 border-r border-black font-bold text-black whitespace-nowrap">
                            <div>{{ $j->tanggal_ujian->locale('id')->translatedFormat('l') }}</div>
                            <div class="text-[11px] font-mono font-normal text-slate-600">{{ $j->tanggal_ujian->format('d/m/Y') }}</div>
                        </td>
                        <td class="p-2.5 border-r border-black text-center">
                            <span class="px-2 py-0.5 rounded bg-[#FFD43B] border border-black font-black text-[11px] shadow-[1px_1px_0px_0px_#000]">
                                Sesi {{ $j->sesi_ke }}
                            </span>
                        </td>
                        <td class="p-2.5 border-r border-black text-center font-mono font-bold text-black">
                            {{ substr($j->jam_mulai, 0, 5) }}
                        </td>
                        <td class="p-2.5 border-r border-black text-center font-mono font-bold text-black">
                            {{ substr($j->jam_selesai, 0, 5) }}
                        </td>
                        <td class="p-2.5 border-r border-black text-center font-bold">
                            <span class="px-2 py-0.5 rounded bg-blue-100 border border-blue-400 text-blue-950 text-[11px]">
                                {{ $j->durasi_menit }} Menit
                            </span>
                        </td>
                        <td class="p-2.5 border-r border-black text-center">
                            @php
                                $js = strtolower($j->jenis_soal);
                                $badgeClass = 'bg-slate-100 text-slate-900 border-slate-900';
                                if (str_contains($js, 'cbt') || str_contains($js, 'digital')) {
                                    $badgeClass = 'bg-[#D0EBFF] text-blue-950 border-blue-900';
                                } elseif (str_contains($js, 'tulis')) {
                                    $badgeClass = 'bg-[#FFF3BF] text-amber-950 border-amber-900';
                                } elseif (str_contains($js, 'praktik')) {
                                    $badgeClass = 'bg-[#D3F9D8] text-emerald-950 border-emerald-900';
                                } elseif (str_contains($js, 'portofolio') || str_contains($js, 'lisan')) {
                                    $badgeClass = 'bg-[#F3D9FA] text-purple-950 border-purple-900';
                                }
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded border-2 {{ $badgeClass }} text-[10px] font-black shadow-[1px_1px_0px_0px_#000] whitespace-nowrap">
                                {{ $j->jenis_soal }}
                            </span>
                        </td>
                        <td class="p-2.5 border-r border-black font-bold text-black text-sm">
                            {{ $j->nama_mapel }}
                        </td>
                        <td class="p-2.5 border-r border-black">
                            <span class="px-2 py-0.5 rounded font-black text-[11px] bg-[#E7F5FF] text-blue-950 border border-black">
                                {{ $j->target_kelas }}
                            </span>
                        </td>
                        <td class="p-2.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                        onclick="openEditJadwalModal({{ json_encode($j) }})" 
                                        class="px-2 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('ujian.jadwal.destroy', $j) }}" method="POST" onsubmit="return confirm('Hapus slot jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-[#FF6B6B] hover:bg-red-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="p-6 text-center text-slate-500 italic">
                            Belum ada slot jadwal ujian untuk periode ini. Klik <strong>+ Tambah Slot Jadwal</strong> untuk mulai menyusun.
                        </td>
                    </tr>
                @endforelse
                <tr id="empty_search_row" style="display: none;">
                    <td colspan="10" class="p-6 text-center text-slate-500 font-bold italic">
                        Tidak ada jadwal ujian yang cocok dengan pencarian atau filter bentuk ujian.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
