{{-- TAB 3: RIWAYAT PERTUKARAN PENGAWAS --}}
<div id="content-riwayat" class="tab-content space-y-4 hidden">
    <div data-table-card class="overflow-hidden rounded-xl border-2 border-slate-900 bg-white shadow-[4px_4px_0px_0px_#0f172a]">
        <div class="flex flex-col gap-2 border-b-2 border-slate-900 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-heading text-sm font-black uppercase tracking-wide text-slate-900">
                    Riwayat Pertukaran Pengawas
                </h2>
                <p class="text-[11px] font-medium text-slate-600">
                    Catatan audit seluruh pertukaran tugas mengawas antarguru untuk pemeriksaan administrasi madrasah.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                    </svg>
                    <input type="search" data-table-search placeholder="Cari guru, mapel, atau alasan…"
                           class="w-full rounded-lg border-2 border-slate-900 bg-white py-1.5 pl-8 pr-3 text-[11px] font-bold text-slate-900 shadow-[2px_2px_0px_0px_#0f172a] placeholder:text-slate-400 focus:outline-none focus:bg-yellow-50/50 sm:w-60">
                </div>
                <span class="inline-flex w-fit items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#F3D9FA] px-2.5 py-1 text-[10px] font-black text-purple-950 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                    <span>🔄</span>
                    <span>Total {{ $swapHistories->count() }} Pertukaran</span>
                </span>
            </div>
        </div>

        <div class="data-table-scroll">
            <table id="table-riwayat" data-table class="data-table w-full min-w-[1000px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-900 bg-slate-100 text-[10px] uppercase tracking-wider text-slate-900 font-black">
                        <th class="w-12 px-3 py-2.5 text-center font-black border-r border-slate-300">No</th>
                        <th class="w-32 px-3 py-2.5 font-black border-r border-slate-300">Waktu</th>
                        <th class="px-3 py-2.5 font-black border-r border-slate-300">Pengawas Asal &amp; Jadwal Semula</th>
                        <th class="w-12 px-3 py-2.5 text-center font-black border-r border-slate-300">Swap</th>
                        <th class="px-3 py-2.5 font-black border-r border-slate-300">Bertukar Dengan</th>
                        <th class="px-3 py-2.5 font-black border-r border-slate-300">Alasan / Catatan</th>
                        <th class="w-36 px-3 py-2.5 font-black border-r border-slate-300">Diproses Oleh</th>
                        <th class="w-24 px-3 py-2.5 text-center font-black">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($swapHistories as $idx => $sw)
                        <tr class="pagination-row border-b border-slate-300 align-top transition-colors hover:bg-purple-50/40">
                            <td class="px-3 py-3 text-center font-mono text-[11px] font-bold text-slate-500 border-r border-slate-200">{{ $idx + 1 }}</td>
                            <td class="px-3 py-3 border-r border-slate-200">
                                <div class="font-mono text-[11px] font-black text-slate-900">{{ $sw->created_at->format('d/m/Y') }}</div>
                                <div class="font-mono text-[10px] text-slate-600 font-semibold">{{ $sw->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="px-3 py-3 border-r border-slate-200">
                                <div class="font-bold text-slate-900">{{ $sw->teacher1?->user?->name ?? 'Guru 1' }}</div>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <span class="rounded border border-blue-900 bg-blue-100 px-1.5 py-0.5 text-[10px] font-black text-blue-900 shadow-[1px_1px_0px_0px_#0f172a]">{{ $sw->jadwal1?->nama_mapel }}</span>
                                    <span class="text-[10px] font-bold text-slate-600">{{ $sw->jadwal1?->target_kelas }}</span>
                                </div>
                                <div class="mt-1 font-mono text-[10px] text-slate-600">
                                    {{ $sw->jadwal1?->tanggal_ujian?->format('d/m/Y') }} ({{ substr($sw->jadwal1?->jam_mulai ?? '', 0, 5) }}–{{ substr($sw->jadwal1?->jam_selesai ?? '', 0, 5) }}) · {{ $sw->sourcePengawas?->ruang?->nama_ruang ?? 'Ruang' }}
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                <span class="inline-grid h-7 w-7 place-items-center rounded-md border-2 border-slate-900 bg-[#F3D9FA] text-purple-950 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                    </svg>
                                </span>
                            </td>
                            <td class="px-3 py-3 border-r border-slate-200">
                                <div class="font-bold text-slate-900">{{ $sw->teacher2?->user?->name ?? 'Guru 2' }}</div>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <span class="rounded border border-blue-900 bg-blue-100 px-1.5 py-0.5 text-[10px] font-black text-blue-900 shadow-[1px_1px_0px_0px_#0f172a]">{{ $sw->jadwal2?->nama_mapel }}</span>
                                    <span class="text-[10px] font-bold text-slate-600">{{ $sw->jadwal2?->target_kelas }}</span>
                                </div>
                                <div class="mt-1 font-mono text-[10px] text-slate-600">
                                    {{ $sw->jadwal2?->tanggal_ujian?->format('d/m/Y') }} ({{ substr($sw->jadwal2?->jam_mulai ?? '', 0, 5) }}–{{ substr($sw->jadwal2?->jam_selesai ?? '', 0, 5) }}) · {{ $sw->targetPengawas?->ruang?->nama_ruang ?? 'Ruang' }}
                                </div>
                            </td>
                            <td class="px-3 py-3 border-r border-slate-200">
                                <span class="text-[11px] italic text-slate-700 font-medium">{{ $sw->alasan ?: 'Pertukaran jadwal tugas pengawas.' }}</span>
                            </td>
                            <td class="px-3 py-3 border-r border-slate-200">
                                <div class="font-bold text-slate-900">{{ $sw->processedBy?->name ?? 'Administrator' }}</div>
                                <div class="text-[10px] font-semibold text-slate-500">Admin</div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 rounded-md border-2 border-slate-900 bg-emerald-100 px-2 py-0.5 text-[10px] font-black text-emerald-950 uppercase shadow-[1px_1px_0px_0px_#0f172a]">
                                    {{ $sw->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <span class="grid h-12 w-12 place-items-center rounded-xl border-2 border-slate-900 bg-purple-100 text-purple-950 shadow-[2px_2px_0px_0px_#0f172a]">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                        </svg>
                                    </span>
                                    <p class="text-xs font-black text-slate-900 uppercase">Belum ada riwayat pertukaran</p>
                                    <p class="text-[11px] font-medium text-slate-600">Pertukaran jadwal pengawas yang dilakukan akan tercatat secara audit di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr class="pagination-search-empty" style="display: none;">
                        <td colspan="8" class="px-6 py-10 text-center text-xs font-bold text-slate-500">
                            Tidak ada riwayat yang cocok dengan pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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
