{{-- TAB 2: PEMETAAN CALON PENGAWAS (KELAS RENDAH / ATAS) --}}
<div id="content-pemetaan" class="tab-content space-y-4 hidden">
    {{-- Ringkasan Statistik Pemetaan Neo-Brutalism --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Total Guru Aktif</div>
                    <div class="mt-0.5 text-2xl font-black text-slate-900">{{ $totalTeachersCount }}</div>
                    <div class="text-[10px] font-semibold text-slate-500">Guru madrasah</div>
                </div>
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md border-2 border-slate-900 bg-slate-100 text-[11px] font-black text-slate-900 shadow-[1px_1px_0px_0px_#0f172a]">G</span>
            </div>
        </div>
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Panitia Ujian</div>
                    <div class="mt-0.5 text-2xl font-black text-purple-700">{{ $panitiaCount }}</div>
                    <div class="text-[10px] font-semibold text-slate-500">Dikecualikan otomatis</div>
                </div>
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md border-2 border-slate-900 bg-[#F3D9FA] shadow-[1px_1px_0px_0px_#0f172a]">
                    <svg class="h-3.5 w-3.5 text-purple-950" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </span>
            </div>
        </div>
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Guru Terpetakan</div>
                    <div class="mt-0.5 text-2xl font-black text-emerald-700">{{ $mappedCount }}</div>
                    <div class="text-[10px] font-semibold text-slate-500">Siap dialokasikan</div>
                </div>
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md border-2 border-slate-900 bg-[#D3F9D8] shadow-[1px_1px_0px_0px_#0f172a]">
                    <svg class="h-3.5 w-3.5 text-emerald-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </span>
            </div>
        </div>
        <div class="rounded-xl border-2 border-slate-900 bg-white p-3.5 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Belum Dipetakan</div>
                    <div class="mt-0.5 text-2xl font-black text-rose-700">{{ $unmappedCount }}</div>
                    <div class="text-[10px] font-semibold text-slate-500">Perlu pengaturan kelompok</div>
                </div>
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md border-2 border-slate-900 bg-[#FFE3E3] shadow-[1px_1px_0px_0px_#0f172a]">
                    <svg class="h-3.5 w-3.5 text-rose-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Panel Pemetaan Bulk & Tabel Guru Neo-Brutalism --}}
    <div data-table-card class="overflow-hidden rounded-xl border-2 border-slate-900 bg-white shadow-[4px_4px_0px_0px_#0f172a]">
        <div class="border-b-2 border-slate-900 bg-slate-50 px-5 py-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-heading text-sm font-black uppercase tracking-wide text-slate-900">
                        Daftar Guru &amp; Pemetaan Kelompok Kelas
                    </h2>
                    <p class="text-[11px] font-medium text-slate-600">
                        Centang guru untuk penetapan massal, atau atur tingkat kelas per guru secara individual.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                        </svg>
                        <input type="search" data-table-search placeholder="Cari nama guru atau NIP…"
                               class="w-full rounded-lg border-2 border-slate-900 bg-white py-1.5 pl-8 pr-3 text-[11px] font-bold text-slate-900 shadow-[2px_2px_0px_0px_#0f172a] placeholder:text-slate-400 focus:outline-none focus:bg-yellow-50/50 sm:w-60">
                    </div>
                    <span id="teacherSelectionCount" class="inline-flex w-fit items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#FFD43B] px-2.5 py-1 text-[10px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                        0 dipilih
                    </span>
                </div>
            </div>

            {{-- Toolbar Tombol Massal SVG Neo-Brutalism --}}
            <div class="mt-3 flex flex-wrap items-center gap-2 rounded-xl border-2 border-slate-900 bg-white p-2.5 shadow-[2px_2px_0px_0px_#0f172a]">
                <span class="mr-1 text-[10px] font-black uppercase tracking-wider text-slate-700">Tetapkan massal:</span>
                <button type="button" onclick="submitBulkLevels(['1', '2', '3'])"
                        title="Tetapkan Kelompok Kelas Rendah (Tingkat 1, 2, 3)"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-blue-100 px-3 py-1.5 text-[11px] font-black text-blue-950 shadow-[2px_2px_0px_0px_#0f172a] hover:bg-blue-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l-4.5-4.5m4.5 4.5l4.5-4.5M4.5 19.5h15"/>
                    </svg>
                    <span>Rendah 1–3</span>
                </button>
                <button type="button" onclick="submitBulkLevels(['4', '5', '6'])"
                        title="Tetapkan Kelompok Kelas Atas (Tingkat 4, 5, 6)"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-orange-100 px-3 py-1.5 text-[11px] font-black text-orange-950 shadow-[2px_2px_0px_0px_#0f172a] hover:bg-orange-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5V4.5m0 0l4.5 4.5M12 4.5L7.5 9M4.5 4.5h15"/>
                    </svg>
                    <span>Atas 4–6</span>
                </button>
                <button type="button" onclick="submitBulkLevels(['1', '2', '3', '4', '5', '6'])"
                        title="Tetapkan Semua Kelas (Tingkat 1 s/d 6)"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-emerald-100 px-3 py-1.5 text-[11px] font-black text-emerald-950 shadow-[2px_2px_0px_0px_#0f172a] hover:bg-emerald-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Semua (1–6)</span>
                </button>
                <button type="button" onclick="submitBulkDelete()"
                        title="Hapus Pemetaan Guru Terpilih"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#FFE3E3] px-3 py-1.5 text-[11px] font-black text-rose-950 shadow-[2px_2px_0px_0px_#0f172a] hover:bg-rose-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                    </svg>
                    <span>Hapus</span>
                </button>
            </div>
        </div>

        <form id="formBulkMapping" method="POST" action="{{ route('ujian.pengawas.mapping.bulk') }}">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
            <input type="hidden" id="bulk_levels_input" name="assigned_levels[]" value="">

            {{-- Tabel Pemetaan Guru --}}
            <div class="data-table-scroll">
                <table id="table-pemetaan" data-table class="data-table w-full min-w-[920px] border-collapse text-left text-xs">
                    <thead>
                        <tr class="border-b-2 border-slate-900 bg-slate-100 text-[10px] uppercase tracking-wider text-slate-900 font-black">
                            <th class="w-10 px-3 py-2.5 text-center font-black border-r border-slate-300">
                                <input type="checkbox" id="checkAllTeachers" onclick="toggleSelectAllTeachers(this)" class="h-4 w-4 cursor-pointer rounded border-2 border-slate-900 accent-slate-900">
                            </th>
                            <th class="w-12 px-3 py-2.5 text-center font-black border-r border-slate-300">No</th>
                            <th class="px-3 py-2.5 font-black border-r border-slate-300">Nama Guru &amp; NIP</th>
                            <th class="w-44 px-3 py-2.5 text-center font-black border-r border-slate-300">Status Kepanitiaan</th>
                            <th class="px-3 py-2.5 font-black border-r border-slate-300">Pemetaan Saat Ini</th>
                            <th class="w-52 px-3 py-2.5 text-center font-black border-r border-slate-300">Cakupan Tingkat (1–6)</th>
                            <th class="w-20 px-3 py-2.5 text-center font-black">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $idx => $t)
                            @php
                                $isPanitia = in_array($t->id, $panitiaTeacherIds, true);
                                $panitiaRole = $panitiaRoles[$t->id] ?? null;
                                $mapping = $mappings[$t->id] ?? null;
                                $assignedLevels = $mapping?->assigned_levels ?? [];
                            @endphp
                            <tr class="pagination-row border-b border-slate-300 transition-colors hover:bg-amber-50/50 {{ $isPanitia ? 'bg-purple-50/40' : '' }}">
                                <td class="px-3 py-3 text-center border-r border-slate-200">
                                    @if($isPanitia)
                                        <input type="checkbox" disabled title="Guru adalah Panitia Ujian" class="h-4 w-4 cursor-not-allowed rounded opacity-30">
                                    @else
                                        <input type="checkbox" name="teacher_ids[]" value="{{ $t->id }}" class="teacher-check h-4 w-4 cursor-pointer rounded border-2 border-slate-900 accent-slate-900">
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center font-mono text-[11px] font-bold text-slate-500 border-r border-slate-200">{{ $idx + 1 }}</td>
                                <td class="px-3 py-3 border-r border-slate-200">
                                    <div class="font-bold text-slate-900">{{ $t->user?->name }}</div>
                                    <div class="mt-0.5 font-mono text-[10px] text-slate-600">NIP {{ $t->nip ?: '-' }} · NUPTK {{ $t->nuptk ?: '-' }}</div>
                                </td>
                                <td class="px-3 py-3 text-center border-r border-slate-200">
                                    @if($isPanitia)
                                        <span class="inline-flex items-center gap-1 rounded-md border-2 border-slate-900 bg-[#F3D9FA] px-2 py-0.5 text-[10px] font-black text-purple-950 shadow-[1px_1px_0px_0px_#0f172a]">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                            </svg>
                                            {{ $panitiaRole ?: 'Panitia Ujian' }}
                                        </span>
                                        <div class="mt-0.5 text-[9px] font-bold text-purple-900">Dikecualikan otomatis</div>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                            Bukan Panitia
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 border-r border-slate-200">
                                    @if($isPanitia)
                                        <span class="text-[11px] italic text-slate-400 font-medium">— Tidak berhak mengawas —</span>
                                    @elseif($mapping && !empty($assignedLevels))
                                        <span class="font-bold text-slate-900">{{ $mapping->formatted_levels }}</span>
                                        @if($mapping->keterangan)
                                            <div class="mt-0.5 text-[10px] text-slate-600">{{ $mapping->keterangan }}</div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md border-2 border-slate-900 bg-[#FFE3E3] px-2 py-0.5 text-[10px] font-black text-rose-950 shadow-[1px_1px_0px_0px_#0f172a]">
                                            Belum Dipetakan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center border-r border-slate-200">
                                    @if(!$isPanitia)
                                        <div class="inline-flex items-center gap-1">
                                            @foreach(['1', '2', '3', '4', '5', '6'] as $lvl)
                                                @php $active = in_array($lvl, array_map('strval', $assignedLevels), true); @endphp
                                                <span class="grid h-5 w-5 place-items-center rounded-md border-2 text-[10px] font-black {{ $active ? ($lvl <= 3 ? 'border-slate-900 bg-blue-600 text-white shadow-[1px_1px_0px_0px_#0f172a]' : 'border-slate-900 bg-orange-500 text-white shadow-[1px_1px_0px_0px_#0f172a]') : 'border-slate-300 bg-slate-100 text-slate-400' }}">
                                                    {{ $lvl }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-bold">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if(!$isPanitia)
                                        <button type="button" onclick="openModalEditMapping('{{ $t->id }}', '{{ addslashes($t->user?->name) }}', '{{ $t->nip }}', {{ json_encode($assignedLevels) }}, '{{ addslashes($mapping?->keterangan ?? '') }}')"
                                                title="Atur pemetaan kelas: {{ $t->user?->name }}"
                                                class="grid h-6 w-6 cursor-pointer place-items-center rounded-md border-2 border-slate-900 bg-[#FFD43B] text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-yellow-400 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all mx-auto">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 18H7.5m6-6h6m-6 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 12h6"/>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <span class="grid h-12 w-12 place-items-center rounded-xl border-2 border-slate-900 bg-amber-100 text-slate-800 shadow-[2px_2px_0px_0px_#0f172a]">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                            </svg>
                                        </span>
                                        <p class="text-xs font-black text-slate-900 uppercase">Tidak ada data guru aktif</p>
                                        <p class="text-[11px] font-medium text-slate-600">Pastikan data guru dan pengguna aktif sudah tersedia pada sistem.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        <tr class="pagination-search-empty" style="display: none;">
                            <td colspan="7" class="px-6 py-10 text-center text-xs font-bold text-slate-500">
                                Tidak ada guru yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>

        <div class="flex flex-col gap-2 border-t-2 border-slate-900 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[11px] font-black text-slate-700" data-pagination-info>Menampilkan semua data</p>
            <div class="flex flex-wrap items-center gap-2">
                <label class="flex items-center gap-1.5 text-[11px] font-black text-slate-700">
                    Baris
                    <select data-pagination-per-page class="rounded-lg border-2 border-slate-900 bg-white px-2 py-1 text-[11px] font-black text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] focus:outline-none">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">Semua</option>
                    </select>
                </label>
                <div class="flex items-center gap-1">
                    <button type="button" data-pagination-prev class="pagination-arrow" aria-label="Halaman sebelumnya">‹</button>
                    <div class="flex items-center gap-1" data-pagination-pages></div>
                    <button type="button" data-pagination-next class="pagination-arrow" aria-label="Halaman berikutnya">›</button>
                </div>
            </div>
        </div>
    </div>
</div>
