{{-- TAB 1: PENUGASAN JADWAL PENGAWAS --}}
<div id="content-penugasan" class="tab-content space-y-4">
    @php
        $ruangTerpakai = $pengawasList->pluck('ujian_ruang_id')->filter()->unique()->count();
        $guruBertugas = $pengawasList->flatMap(fn ($pw) => [$pw->pengawas_1_id, $pw->pengawas_2_id])->filter()->unique()->count();
        $groupedPengawas = $pengawasList
            ->sortBy(fn ($pw) => ($pw->jadwal?->tanggal_ujian?->format('Y-m-d') ?? '9999-12-31') . ' ' . ($pw->jadwal?->jam_mulai ?? '00:00:00'))
            ->groupBy(fn ($pw) => $pw->jadwal?->tanggal_ujian?->format('Y-m-d') ?? 'Tanpa Tanggal');
    @endphp

    {{-- Statistik Ringkas Neo-Brutalism --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Total Penugasan</div>
            <div class="mt-0.5 text-2xl font-black text-slate-900">{{ $pengawasList->count() }}</div>
            <div class="text-[10px] font-semibold text-slate-500">Slot pengawas terjadwal</div>
        </div>
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Ruang Terpakai</div>
            <div class="mt-0.5 text-2xl font-black text-slate-900">{{ $ruangTerpakai }} <span class="text-sm font-bold text-slate-400">/ {{ $ruangs->count() }}</span></div>
            <div class="text-[10px] font-semibold text-slate-500">Ruang aktif melaksanakan ujian</div>
        </div>
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Hari Ujian</div>
            <div class="mt-0.5 text-2xl font-black text-slate-900">{{ $examDates->count() }}</div>
            <div class="text-[10px] font-semibold text-slate-500">Tanggal pelaksanaan periode</div>
        </div>
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Guru Bertugas</div>
            <div class="mt-0.5 text-2xl font-black text-slate-900">{{ $guruBertugas }}</div>
            <div class="text-[10px] font-semibold text-slate-500">Guru yang mendapat jadwal</div>
        </div>
    </div>

    {{-- Tabel Penugasan Neo-Brutalism --}}
    <div data-table-card data-pagination-mode="group" class="overflow-hidden rounded-xl border-2 border-slate-900 bg-white shadow-[4px_4px_0px_0px_#0f172a]">
        <div class="flex flex-col gap-2 border-b-2 border-slate-900 px-5 py-4 sm:flex-row sm:items-center sm:justify-between bg-slate-50">
            <div>
                <h2 class="font-heading text-sm font-black uppercase tracking-wide text-slate-900">
                    Matriks Penugasan Pengawas
                </h2>
                <p class="text-[11px] font-medium text-slate-600">
                    Dikelompokkan per tanggal ujian. Gunakan tombol tukar untuk memindahkan jadwal pengawas secara aman.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                    </svg>
                    <input type="search" data-table-search placeholder="Cari mapel, guru, atau ruang…"
                           class="w-full rounded-lg border-2 border-slate-900 bg-white py-1.5 pl-8 pr-3 text-[11px] font-bold text-slate-900 shadow-[2px_2px_0px_0px_#0f172a] placeholder:text-slate-400 focus:outline-none focus:bg-yellow-50/50 sm:w-56">
                </div>
                <div class="relative">
                    <select data-table-filter-class
                            class="w-full rounded-lg border-2 border-slate-900 bg-white py-1.5 px-3 text-[11px] font-bold text-slate-900 shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none focus:bg-yellow-50/50 cursor-pointer sm:w-auto">
                        <option value="">Semua Kelas</option>
                        <optgroup label="Tingkat Kelas">
                            @for($lvl = 1; $lvl <= 6; $lvl++)
                                <option value="tingkat:{{ $lvl }}">Tingkat {{ $lvl }} (Kelas {{ $lvl }})</option>
                            @endfor
                        </optgroup>
                        @if(isset($classes) && $classes->isNotEmpty())
                            <optgroup label="Ruang Kelas / Rombel">
                                @foreach($classes as $c)
                                    <option value="rombel:{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <span class="inline-flex w-fit items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#FFD43B] px-2.5 py-1 text-[10px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                    <span>📋</span>
                    <span>{{ $pengawasList->count() }} Penugasan</span>
                </span>
            </div>
        </div>

        <div class="data-table-scroll">
            <table id="table-penugasan" data-table class="data-table w-full min-w-[960px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-900 bg-slate-100 text-[10px] uppercase tracking-wider text-slate-900 font-black">
                        <th class="w-12 px-3 py-2.5 text-center font-black border-r border-slate-300">No</th>
                        <th class="px-3 py-2.5 font-black border-r border-slate-300">Jadwal &amp; Mata Pelajaran</th>
                        <th class="w-40 px-3 py-2.5 text-center font-black border-r border-slate-300">Ruang Ujian</th>
                        <th class="px-3 py-2.5 font-black border-r border-slate-300">Pengawas 1 (Utama)</th>
                        <th class="px-3 py-2.5 font-black border-r border-slate-300">Pengawas 2 (Pendamping)</th>
                        <th class="w-28 px-3 py-2.5 text-center font-black border-r border-slate-300">Kehadiran</th>
                        <th class="w-24 px-3 py-2.5 text-center font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNumber = 0; @endphp
                    @forelse($groupedPengawas as $tanggal => $items)
                        @php $groupDate = $items->first()?->jadwal?->tanggal_ujian; @endphp
                        <tr class="pagination-group-row border-y-2 border-slate-900 bg-amber-50"
                            data-group="{{ $tanggal }}"
                            data-group-label="{{ $groupDate ? $groupDate->locale('id')->translatedFormat('l, d F Y') : $tanggal }}">
                            <td colspan="7" class="px-3.5 py-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="grid h-5 w-5 place-items-center rounded border border-slate-900 bg-[#FFD43B] text-[10px] font-black text-slate-900 shadow-[1px_1px_0px_0px_#0f172a]">
                                            📅
                                        </span>
                                        <span class="text-[11px] font-black uppercase tracking-wide text-slate-900">
                                            {{ $groupDate ? $groupDate->locale('id')->translatedFormat('l, d F Y') : $tanggal }}
                                        </span>
                                    </div>
                                    <span class="rounded border border-slate-900 bg-white px-2 py-0.5 text-[10px] font-black text-slate-800 shadow-[1px_1px_0px_0px_#0f172a]">
                                        {{ $items->count() }} Penugasan
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($items as $pw)
                            @php
                                $roomLevel = $pw->ruang?->getLevel();
                                $jadwalLevels = $pw->jadwal?->getTargetLevels() ?? [];
                                $classId = $pw->ruang?->school_class_id ?? $pw->jadwal?->school_class_id ?? '';
                            @endphp
                            <tr class="pagination-row border-b border-slate-300 align-top transition-colors hover:bg-amber-50/50"
                                data-class-id="{{ $classId }}"
                                data-room-level="{{ $roomLevel ?? '' }}"
                                data-jadwal-levels="{{ implode(',', $jadwalLevels) }}">
                                <td class="px-3 py-3 text-center font-mono text-[11px] font-bold text-slate-500 border-r border-slate-200">{{ ++$rowNumber }}</td>
                                <td class="px-3 py-3 border-r border-slate-200">
                                    <div class="font-bold text-slate-900">{{ $pw->jadwal?->nama_mapel }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 font-mono text-[10px] font-semibold text-slate-600">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ substr($pw->jadwal?->jam_mulai ?? '', 0, 5) }}–{{ substr($pw->jadwal?->jam_selesai ?? '', 0, 5) }}
                                        </span>
                                        <span class="rounded border border-blue-900 bg-blue-100 px-1.5 py-0.5 text-[10px] font-black text-blue-900 shadow-[1px_1px_0px_0px_#0f172a]">
                                            {{ $pw->jadwal?->target_kelas }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center border-r border-slate-200">
                                    <div class="font-bold text-slate-900 whitespace-nowrap">{{ $pw->ruang?->nama_ruang }}</div>
                                    <div class="font-mono text-[10px] text-slate-500 whitespace-nowrap">({{ $pw->ruang?->kode_ruang }})</div>
                                </td>
                                <td class="px-3 py-3 border-r border-slate-200">
                                    @if($pw->pengawas1)
                                        @php $p1Code = $pengawasCodeMap[$pw->pengawas_1_id] ?? null; @endphp
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    @if($p1Code)
                                                        <span class="px-1.5 py-0.2 rounded border border-black bg-[#FFD43B] font-black text-[10px] text-black shadow-[1px_1px_0px_#000]">
                                                            KODE {{ $p1Code }}
                                                        </span>
                                                    @endif
                                                    <span class="truncate font-bold text-slate-900">{{ $pw->pengawas1?->user?->name }}</span>
                                                </div>
                                                <div class="font-mono text-[10px] text-slate-600">NIP {{ $pw->pengawas1?->nip ?: '-' }}</div>
                                            </div>
                                            @if($pw->pengawas_1_id)
                                                <button type="button"
                                                        onclick="openModalTukar('{{ $pw->id }}', 'pengawas_1', '{{ addslashes($pw->pengawas1?->user?->name) }}', '{{ addslashes($pw->jadwal?->nama_mapel) }}', '{{ $pw->jadwal?->tanggal_ujian?->format('d/m/Y') }}', '{{ substr($pw->jadwal?->jam_mulai ?? '', 0, 5) }}-{{ substr($pw->jadwal?->jam_selesai ?? '', 0, 5) }}', '{{ addslashes($pw->ruang?->nama_ruang) }}', '{{ addslashes($pw->jadwal?->target_kelas) }}')"
                                                        title="Tukar jadwal pengawas 1"
                                                        class="grid h-6 w-6 shrink-0 cursor-pointer place-items-center rounded-md border-2 border-slate-900 bg-[#F3D9FA] text-purple-950 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-purple-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[11px] italic text-slate-400">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 border-r border-slate-200">
                                    @if($pw->pengawas2)
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate font-semibold text-slate-800">{{ $pw->pengawas2?->user?->name }}</div>
                                                <div class="font-mono text-[10px] text-slate-600">NIP {{ $pw->pengawas2?->nip ?: '-' }}</div>
                                            </div>
                                            @if($pw->pengawas_2_id)
                                                <button type="button"
                                                        onclick="openModalTukar('{{ $pw->id }}', 'pengawas_2', '{{ addslashes($pw->pengawas2?->user?->name) }}', '{{ addslashes($pw->jadwal?->nama_mapel) }}', '{{ $pw->jadwal?->tanggal_ujian?->format('d/m/Y') }}', '{{ substr($pw->jadwal?->jam_mulai ?? '', 0, 5) }}-{{ substr($pw->jadwal?->jam_selesai ?? '', 0, 5) }}', '{{ addslashes($pw->ruang?->nama_ruang) }}', '{{ addslashes($pw->jadwal?->target_kelas) }}')"
                                                        title="Tukar jadwal pengawas 2"
                                                        class="grid h-6 w-6 shrink-0 cursor-pointer place-items-center rounded-md border-2 border-slate-900 bg-[#F3D9FA] text-purple-950 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-purple-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($pw->pengawas_2_id)
                                        <span class="text-[11px] italic text-slate-400">Belum ditugaskan</span>
                                    @else
                                        <span class="text-[11px] text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center border-r border-slate-200">
                                    @php
                                        $statusClass = match ($pw->status_kehadiran) {
                                            'Hadir' => 'bg-emerald-100 text-emerald-950 border-slate-900',
                                            'Izin' => 'bg-amber-100 text-amber-950 border-slate-900',
                                            'Digantikan' => 'bg-blue-100 text-blue-950 border-slate-900',
                                            default => 'bg-slate-100 text-slate-800 border-slate-900',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-md border-2 px-2 py-0.5 text-[10px] font-black uppercase shadow-[1px_1px_0px_0px_#0f172a] {{ $statusClass }}">
                                        {{ $pw->status_kehadiran }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openEditPengawasModal('{{ $pw->ujian_jadwal_id }}', '{{ $pw->ujian_ruang_id }}', '{{ $pw->pengawas_1_id }}', '{{ $pw->pengawas_2_id }}', '{{ $pw->status_kehadiran }}')"
                                                title="Ubah penugasan"
                                                class="grid h-6 w-6 cursor-pointer place-items-center rounded-md border-2 border-slate-900 bg-[#FFD43B] text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-yellow-400 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('ujian.pengawas.destroy', $pw->id) }}" method="POST" onsubmit="return confirm('Hapus penugasan pengawas untuk ruang {{ addslashes($pw->ruang?->nama_ruang) }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus penugasan"
                                                    class="grid h-6 w-6 cursor-pointer place-items-center rounded-md border-2 border-slate-900 bg-[#FFE3E3] text-rose-950 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-rose-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <span class="grid h-12 w-12 place-items-center rounded-xl border-2 border-slate-900 bg-amber-100 text-slate-800 shadow-[2px_2px_0px_0px_#0f172a]">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                        </svg>
                                    </span>
                                    <p class="text-xs font-black text-slate-900 uppercase">Belum ada penugasan pengawas</p>
                                    <p class="text-[11px] font-medium text-slate-600">
                                        Gunakan <strong>Auto-Plot</strong> untuk alokasi otomatis atau <strong>Tugaskan Manual</strong> untuk menambah satu per satu.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr class="pagination-search-empty" style="display: none;">
                        <td colspan="7" class="px-6 py-10 text-center text-xs font-bold text-slate-500">
                            Tidak ada penugasan yang cocok dengan pencarian atau filter kelas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-2 border-t-2 border-slate-900 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[11px] font-black text-slate-700" data-pagination-info>Menampilkan semua hari</p>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-white px-2.5 py-1 text-[10px] font-black text-slate-800 shadow-[1px_1px_0px_0px_#0f172a]">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    Satu halaman = satu hari ujian
                </span>
                <div class="flex items-center gap-1">
                    <button type="button" data-pagination-prev class="pagination-arrow" aria-label="Halaman sebelumnya">‹</button>
                    <div class="flex items-center gap-1" data-pagination-pages></div>
                    <button type="button" data-pagination-next class="pagination-arrow" aria-label="Halaman berikutnya">›</button>
                </div>
            </div>
        </div>
    </div>
</div>
