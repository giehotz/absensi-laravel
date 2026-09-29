@php
    $currentRouteName = Route::currentRouteName();
@endphp

<div class="space-y-5">
    <!-- Header & Filter Toolbar -->
    <div class="bg-white neo-box p-4 sm:p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-2xl">📅</span>
                <h1 class="font-heading font-black text-lg sm:text-xl text-black">
                    Kalender Pendidikan & Hari Libur
                </h1>
                <span class="bg-[#FFD43B] text-black text-xs font-black px-2.5 py-0.5 rounded border border-black uppercase shadow-[1px_1px_0px_0px_#000]">
                    TA {{ $selectedYear }}
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Jadwal agenda kegiatan akademik resmi sekolah dan daftar hari libur nasional/sekolah.
            </p>
        </div>

        <!-- Filter Tahun Ajaran -->
        <form method="GET" action="{{ route($currentRouteName) }}" class="flex items-center gap-2 self-start md:self-auto">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <label for="yearSelect" class="text-xs font-black uppercase font-heading text-slate-700">Tahun:</label>
            <select id="yearSelect" name="academic_year_name" onchange="this.form.submit()" class="neo-input text-xs font-black py-2 px-3 bg-[#FFF9DB] border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Card Dokumen SK Dasar Penetapan (PDF) -->
    <div class="bg-white neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-lg bg-[#FFE3E3] border-2 border-black flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                    <span class="text-xl">📜</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-900 text-white font-heading">
                            Dasar Penetapan Resmi
                        </span>
                        @if($document)
                            <span class="text-[9px] font-black bg-[#D3F9D8] text-green-950 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                Berkas Tersedia ({{ $document->formatted_size }})
                            </span>
                        @else
                            <span class="text-[9px] font-black bg-[#FFF3BF] text-amber-950 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                Belum Ada Berkas SK
                            </span>
                        @endif
                    </div>
                    <h3 class="font-heading font-black text-sm text-black mt-1">
                        {{ $document ? $document->title : 'Dokumen SK Penetapan Kalender TA ' . $selectedYear }}
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium">
                        @if($document)
                            Berkas resmi: <strong class="text-black">{{ $document->file_name }}</strong>
                        @else
                            Dokumen surat keputusan resmi belum diunggah oleh pihak kurikulum/administrator.
                        @endif
                    </p>
                </div>
            </div>

            @if($document)
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                <button type="button" onclick="openModal('previewPdfModalShared')" 
                        class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-3 py-1.5 text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    <span>👁️</span> Pratinjau SK
                </button>
                <a href="{{ $document->file_url }}" target="_blank" download="{{ $document->file_name }}"
                   class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3 py-1.5 text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    <span>⬇️</span> Unduh PDF
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white neo-box p-3 sm:p-4 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase text-slate-500 font-heading">Total Agenda</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-black mt-0.5">{{ $stats['total'] }} Kegiatan</div>
        </div>
        <div class="bg-[#FFF9DB] neo-box p-3 sm:p-4 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase text-amber-900 font-heading">Semester Ganjil</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-amber-950 mt-0.5">{{ $stats['ganjil'] }} Kegiatan</div>
        </div>
        <div class="bg-[#E7F5FF] neo-box p-3 sm:p-4 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase text-blue-900 font-heading">Semester Genap</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-blue-950 mt-0.5">{{ $stats['genap'] }} Kegiatan</div>
        </div>
        <div class="bg-[#FFE3E3] neo-box p-3 sm:p-4 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase text-red-900 font-heading">Hari Libur</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-red-950 mt-0.5">{{ $stats['libur_nasional'] }} Hari</div>
        </div>
    </div>

    <!-- Tab Navigasi: Ganjil, Genap, Libur -->
    <div class="border-b-2 border-black flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-0.5">
        <!-- Tab 1: Semester Ganjil -->
        <a href="{{ route($currentRouteName, ['academic_year_name' => $selectedYear, 'tab' => 'ganjil']) }}"
           class="px-4 py-2.5 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-1.5 border-2 border-b-0 border-black transition-all whitespace-nowrap
           {{ $activeTab === 'ganjil' 
                ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
            <span>🍁</span>
            <span>Ganjil (Jul &ndash; Des)</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full border border-black {{ $activeTab === 'ganjil' ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                {{ $calendarsGanjil->count() }}
            </span>
        </a>

        <!-- Tab 2: Semester Genap -->
        <a href="{{ route($currentRouteName, ['academic_year_name' => $selectedYear, 'tab' => 'genap']) }}"
           class="px-4 py-2.5 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-1.5 border-2 border-b-0 border-black transition-all whitespace-nowrap
           {{ $activeTab === 'genap' 
                ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
            <span>🌸</span>
            <span>Genap (Jan &ndash; Jul)</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full border border-black {{ $activeTab === 'genap' ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                {{ $calendarsGenap->count() }}
            </span>
        </a>

        <!-- Tab 3: Hari Libur -->
        <a href="{{ route($currentRouteName, ['academic_year_name' => $selectedYear, 'tab' => 'libur']) }}"
           class="px-4 py-2.5 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-1.5 border-2 border-b-0 border-black transition-all whitespace-nowrap
           {{ $activeTab === 'libur' 
                ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
            <span>🏖️</span>
            <span>Hari Libur Resmi</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full border border-black {{ $activeTab === 'libur' ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                {{ $holidays->count() }}
            </span>
        </a>
    </div>

    <!-- Konten Tabel Berdasarkan Tab Aktif -->
    <div class="bg-white neo-box overflow-hidden border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        @if($activeTab === 'libur')
            <!-- TAB HARI LIBUR -->
            <div class="bg-[#FFF4E6] p-3.5 border-b-2 border-black flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-xs sm:text-sm text-amber-950 uppercase tracking-wider flex items-center gap-1.5">
                        <span>🏖️</span> Daftar Hari Libur Nasional & Sekolah
                    </h2>
                    <p class="text-[10px] sm:text-[11px] text-amber-900 font-semibold mt-0.5">
                        Berdasarkan SKB 3 Menteri dan ketetapan hari libur khusus internal sekolah.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8F9FA] border-b-2 border-black text-xs font-black uppercase tracking-wider font-heading">
                        <tr>
                            <th class="p-3 border-r border-black w-12 text-center">No</th>
                            <th class="p-3 border-r border-black w-40 text-center">Tanggal & Hari</th>
                            <th class="p-3 border-r border-black">Nama Hari Libur</th>
                            <th class="p-3 text-center w-36">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black">
                        @forelse($holidays as $idx => $holiday)
                            @php
                                $cDate = \Carbon\Carbon::parse($holiday->holiday_date);
                                $day = $cDate->locale('id')->isoFormat('dddd');
                            @endphp
                            <tr class="hover:bg-slate-50 font-medium">
                                <td class="p-3 font-bold text-center border-r border-black text-xs text-slate-900">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="p-3 border-r border-black text-center">
                                    <div class="font-mono font-black text-xs text-black">{{ $cDate->format('d/m/Y') }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase">{{ $day }}</div>
                                </td>
                                <td class="p-3 font-bold text-black border-r border-black">
                                    <div class="text-xs">{{ $holiday->name }}</div>
                                    @if($holiday->description)
                                        <div class="text-[10px] text-slate-500 font-normal mt-0.5">{{ $holiday->description }}</div>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    @if($holiday->is_cuti_bersama)
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border border-amber-900 bg-[#FFF3BF] text-amber-950">
                                            Cuti Bersama
                                        </span>
                                    @elseif($holiday->is_national)
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border border-blue-900 bg-[#E7F5FF] text-blue-950">
                                            Libur Nasional
                                        </span>
                                    @else
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border border-purple-900 bg-[#F3F0FF] text-purple-950">
                                            Libur Sekolah
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-xs font-bold text-slate-500">
                                    Tidak ada data hari libur pada periode tahun ajaran ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- TAB SEMESTER GANJIL / GENAP -->
            @php
                $currentItems = $activeTab === 'ganjil' ? $calendarsGanjil : $calendarsGenap;
                $periodLabel = $activeTab === 'ganjil' ? 'Semester Ganjil (Juli - Desember)' : 'Semester Genap (Januari - Juli)';
            @endphp
            <div class="bg-[#FFF9DB] p-3.5 border-b-2 border-black flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-xs sm:text-sm text-black uppercase tracking-wider flex items-center gap-1.5">
                        <span>📋</span> Agenda &bull; {{ $periodLabel }}
                    </h2>
                    <p class="text-[10px] sm:text-[11px] text-slate-600 font-semibold mt-0.5">
                        Jadwal kegiatan akademik disusun secara kronologis tanggal kegiatan.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8F9FA] border-b-2 border-black text-xs font-black uppercase tracking-wider font-heading">
                        <tr>
                            <th class="p-3 border-r border-black w-12 text-center">No</th>
                            <th class="p-3 border-r border-black w-36 text-center">Hari</th>
                            <th class="p-3 border-r border-black w-44 text-center">Tanggal (dd/mm/yyyy)</th>
                            <th class="p-3 border-r border-black">Keterangan Kegiatan</th>
                            <th class="p-3 text-center w-32">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black">
                        @forelse($currentItems as $idx => $item)
                            @php
                                $catColors = [
                                    'libur' => 'bg-[#FFE3E3] text-red-950 border-red-900',
                                    'ujian' => 'bg-[#FFF3BF] text-amber-950 border-amber-900',
                                    'rapat' => 'bg-[#F3F0FF] text-purple-950 border-purple-900',
                                    'kegiatan' => 'bg-[#E7F5FF] text-blue-950 border-blue-900',
                                    'umum' => 'bg-slate-100 text-slate-800 border-slate-700',
                                ];
                                $badgeClass = $catColors[$item->category] ?? 'bg-slate-100 text-slate-800 border-slate-700';
                            @endphp
                            <tr class="hover:bg-slate-50 font-medium">
                                <td class="p-3 font-bold text-center border-r border-black text-xs text-slate-900">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="p-3 border-r border-black text-center">
                                    <span class="font-bold text-xs text-slate-800 uppercase font-heading">
                                        {{ $item->day_name }}
                                    </span>
                                </td>
                                <td class="p-3 border-r border-black text-center">
                                    <div class="font-mono font-black text-xs text-black bg-[#FFF9DB] px-2 py-0.5 rounded border border-black inline-block shadow-[1px_1px_0px_0px_#000]">
                                        {{ $item->formatted_date }}
                                    </div>
                                </td>
                                <td class="p-3 font-bold text-black border-r border-black">
                                    <div class="text-xs leading-relaxed">{{ $item->description }}</div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border shadow-[1px_1px_0px_0px_#000] {{ $badgeClass }}">
                                        {{ $item->category }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-xs font-bold text-slate-500">
                                    Belum ada agenda kegiatan yang terdaftar untuk {{ $periodLabel }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal Pratinjau SK PDF Bersama -->
@if($document)
<div id="previewPdfModalShared" class="fixed inset-0 z-50 bg-black/80 hidden flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white neo-box max-w-4xl w-full border-4 border-black p-4 sm:p-5 shadow-[10px_10px_0px_0px_#000] relative my-4 flex flex-col h-[90vh]">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-xl">📜</span>
                <div>
                    <h3 class="font-heading font-black text-sm text-black truncate max-w-md">
                        {{ $document->title }}
                    </h3>
                    <p class="text-[10px] text-slate-500 font-bold">
                        {{ $document->file_name }} &bull; {{ $document->formatted_size }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ $document->file_url }}" target="_blank" download="{{ $document->file_name }}"
                   class="neo-btn bg-[#20C997] text-black px-3 py-1.5 text-xs font-black uppercase border-2 border-black shadow-[1px_1px_0px_0px_#000]">
                    ⬇️ Unduh
                </a>
                <button type="button" onclick="closeModal('previewPdfModalShared')" class="font-black text-xl text-slate-700 hover:text-black px-2">
                    ✕
                </button>
            </div>
        </div>

        <div class="flex-1 mt-3 border-2 border-black overflow-hidden bg-slate-100 relative">
            <iframe src="{{ $document->file_url }}" class="w-full h-full border-0" title="Pratinjau PDF"></iframe>
        </div>
    </div>
</div>
@endif
