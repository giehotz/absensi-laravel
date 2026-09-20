<!-- Tab 3: Manajemen Tahun Ajaran & Semester -->
<div id="tab-periode" class="tab-panel hidden">
    <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b-2 border-black pb-4">
            <div>
                <span class="neo-badge bg-[#20C997] text-black">PERIODE AKADEMIK</span>
                <h2 class="font-heading font-black text-xl text-black mt-2">
                    Manajemen Tahun Ajaran & Semester
                </h2>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">
                    Kelola data tahun ajaran, semester, periode tanggal, serta aktifkan atau nonaktifkan tahun ajaran terpilih.
                </p>
            </div>
            <div>
                <button type="button" onclick="openModal('createAcademicYearModal')" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs font-heading font-black flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                    <span>➕</span> Tambah Tahun Ajaran Baru
                </button>
            </div>
        </div>

        <!-- Tabel Tahun Ajaran -->
        <div class="border-2 border-black overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3 border-r border-black text-center w-12">No</th>
                        <th class="p-3 border-r border-black">Tahun Ajaran</th>
                        <th class="p-3 border-r border-black">Semester</th>
                        <th class="p-3 border-r border-black">Periode Tanggal</th>
                        <th class="p-3 border-r border-black text-center">Kelas Terdaftar</th>
                        <th class="p-3 border-r border-black text-center">Status</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($academicYears as $index => $year)
                    <tr class="hover:bg-slate-50 font-medium {{ $year->is_active ? 'bg-[#FFF9DB]/40' : '' }}">
                        <td class="p-3 font-bold text-center border-r border-black">
                            {{ $index + 1 }}
                        </td>
                        <td class="p-3 font-black text-black border-r border-black">
                            {{ $year->name }}
                        </td>
                        <td class="p-3 border-r border-black">
                            <span class="neo-badge {{ $year->semester === 'ganjil' ? 'bg-[#5294FF] text-white' : 'bg-[#FFD43B] text-black' }} text-[10px]">
                                {{ ucfirst($year->semester) }}
                            </span>
                        </td>
                        <td class="p-3 border-r border-black text-xs font-mono">
                            {{ $year->start_date ? \Carbon\Carbon::parse($year->start_date)->format('d M Y') : '-' }} s/d {{ $year->end_date ? \Carbon\Carbon::parse($year->end_date)->format('d M Y') : '-' }}
                        </td>
                        <td class="p-3 border-r border-black text-center font-bold">
                            <span class="bg-[#D3F9D8] px-2 py-0.5 border border-black text-xs font-mono">
                                {{ $year->school_classes_count ?? $year->schoolClasses()->count() }}
                            </span>
                        </td>
                        <td class="p-3 border-r border-black text-center">
                            @if($year->is_active)
                                <span class="bg-[#20C997] text-black font-heading font-black text-[11px] px-2.5 py-1 border border-black shadow-[1px_1px_0px_0px_#000] inline-flex items-center gap-1">
                                    ★ Aktif
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-600 font-bold text-[11px] px-2.5 py-1 border border-black inline-flex items-center">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Toggle Status -->
                                <div class="relative group inline-block">
                                    <form action="{{ route('admin.academic-years.toggle', $year) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="neo-btn {{ $year->is_active ? 'bg-[#FFD43B] hover:bg-[#fcc419]' : 'bg-[#20C997] hover:bg-[#12b886]' }} text-black p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                                aria-label="{{ $year->is_active ? 'Nonaktifkan Tahun Ajaran' : 'Jadikan Tahun Ajaran Aktif' }}">
                                            @if($year->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                        <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                            {{ $year->is_active ? 'Nonaktifkan Tahun Ajaran Ini' : 'Aktifkan Tahun Ajaran Ini' }}
                                        </div>
                                        <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                    </div>
                                </div>

                                <!-- Tombol Edit -->
                                <div class="relative group inline-block">
                                    <button type="button"
                                            onclick="editAcademicYear({{ json_encode([
                                                'id' => $year->id,
                                                'name' => $year->name,
                                                'semester' => $year->semester,
                                                'start_date' => $year->start_date ? \Carbon\Carbon::parse($year->start_date)->format('Y-m-d') : '',
                                                'end_date' => $year->end_date ? \Carbon\Carbon::parse($year->end_date)->format('Y-m-d') : '',
                                                'is_active' => (bool)$year->is_active,
                                            ]) }})"
                                            class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                            aria-label="Edit Tahun Ajaran">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                        <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                            Edit Tahun Ajaran
                                        </div>
                                        <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                    </div>
                                </div>

                                <!-- Tombol Hapus -->
                                <div class="relative group inline-block">
                                    <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST"
                                          class="form-delete-academic-year">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                                aria-label="Hapus Tahun Ajaran">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                        <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                            Hapus Tahun Ajaran
                                        </div>
                                        <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada data tahun ajaran. Klik tombol "+ Tambah Tahun Ajaran Baru" di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
