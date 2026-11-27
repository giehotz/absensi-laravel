@extends('layouts.admin')

@section('title', 'Kalender Pendidikan')
@section('page-title', 'Kalender Pendidikan')

@section('content')
@php
    $categoryMeta = [
        'libur' => ['label' => 'Libur', 'icon' => '🏖️', 'badge' => 'bg-[#FFE3E3] text-red-950 border-red-900'],
        'ujian' => ['label' => 'Ujian', 'icon' => '📝', 'badge' => 'bg-[#FFF3BF] text-amber-950 border-amber-900'],
        'rapat' => ['label' => 'Rapat', 'icon' => '👥', 'badge' => 'bg-[#F3F0FF] text-purple-950 border-purple-900'],
        'kegiatan' => ['label' => 'Kegiatan', 'icon' => '🎯', 'badge' => 'bg-[#E7F5FF] text-blue-950 border-blue-900'],
        'umum' => ['label' => 'Umum', 'icon' => '📌', 'badge' => 'bg-slate-100 text-slate-800 border-slate-700'],
    ];

    $semesterPanels = [
        'ganjil' => [
            'title' => 'Semester Ganjil (Juli – Desember)',
            'short' => 'Semester Ganjil',
            'icon' => '🍁',
            'items' => $calendarsGanjil,
        ],
        'genap' => [
            'title' => 'Semester Genap (Januari – Juli)',
            'short' => 'Semester Genap',
            'icon' => '🌸',
            'items' => $calendarsGenap,
        ],
    ];
@endphp

<div class="space-y-6">
    <!-- Header & Toolbar Utama (Tanpa Tombol Duplikat) -->
    <section class="bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000] flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-2xl" aria-hidden="true">📅</span>
                <h1 class="font-heading font-black text-xl text-black">Kalender Pendidikan</h1>
                <span class="bg-[#FFD43B] text-black text-xs font-black px-2.5 py-0.5 rounded border border-black uppercase shadow-[1px_1px_0px_0px_#000]">
                    TA {{ $selectedYear }}
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Agenda kegiatan akademik semester ganjil &amp; genap serta dokumen SK penetapan hari libur.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <search>
                <form method="GET" action="{{ route('admin.academic-calendar.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" id="filterActiveTab" value="{{ $activeTab }}">
                    <label for="yearSelect" class="text-xs font-black uppercase font-heading text-slate-700">Tahun Ajaran</label>
                    <select id="yearSelect" name="academic_year_name" class="neo-input text-xs font-black py-2 px-3 min-h-[40px] bg-[#FFF9DB] border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="neo-btn bg-slate-900 hover:bg-slate-700 text-white px-3.5 py-2 min-h-[40px] text-xs uppercase font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        Tampilkan
                    </button>
                </form>
            </search>

            <button type="button" data-open-dialog="uploadExcelDialog"
                class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-3.5 py-2 min-h-[40px] text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span aria-hidden="true">📤</span> Upload Excel
            </button>

            <button type="button" id="btnOpenCreateCalendar"
                class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3.5 py-2 min-h-[40px] text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span aria-hidden="true">+</span> Tambah Agenda
            </button>
        </div>
    </section>

    <!-- Notifikasi Status -->
    @if(session('success'))
        <div role="status" aria-live="polite" class="bg-[#D3F9D8] border-2 border-black rounded-lg p-4 shadow-[3px_3px_0px_0px_#000] flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 text-xs font-black text-green-950">
                <span class="text-base" aria-hidden="true">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" data-dismiss-alert aria-label="Tutup notifikasi sukses" class="font-black text-xs text-green-900 hover:text-black px-2 py-1 cursor-pointer">✕</button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div role="alert" aria-live="assertive" class="bg-[#FFE3E3] border-2 border-black rounded-lg p-4 shadow-[3px_3px_0px_0px_#000] space-y-1">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 text-xs font-black text-red-950">
                    <span class="text-base" aria-hidden="true">⚠️</span>
                    <span>{{ session('error') ?? 'Terjadi kesalahan pada input data:' }}</span>
                </div>
                <button type="button" data-dismiss-alert aria-label="Tutup peringatan" class="font-black text-xs text-red-900 hover:text-black px-2 py-1 cursor-pointer">✕</button>
            </div>
            @if($errors->any())
                <ul class="text-[11px] font-bold text-red-900 list-disc list-inside pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Card Dokumen SK PDF (Lokasi Tunggal Aksi PDF) -->
    <section class="bg-white neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-lg bg-[#FFE3E3] border-2 border-black flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]" aria-hidden="true">
                    <span class="text-2xl">📜</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-900 text-white font-heading">
                            Dasar Penetapan SK PDF
                        </span>
                        @if($document)
                            <span class="text-[10px] font-black bg-[#D3F9D8] text-green-950 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                ✓ Terunggah ({{ $document->formatted_size }})
                            </span>
                        @else
                            <span class="text-[10px] font-black bg-[#FFF3BF] text-amber-950 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                Belum Ada Berkas
                            </span>
                        @endif
                    </div>
                    <h2 class="font-heading font-black text-sm text-black mt-1">
                        {{ $document ? $document->title : 'Surat Keputusan (SK) Kalender Pendidikan TA ' . $selectedYear }}
                    </h2>
                    <p class="text-xs text-slate-600 font-medium mt-0.5 break-words">
                        @if($document)
                            Berkas: <strong class="text-black">{{ $document->file_name }}</strong> &bull; Diperbarui {{ $document->updated_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                        @else
                            Unggah SK atau Surat Edaran penetapan kalender akademik &amp; hari libur resmi.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if($document)
                    <button type="button" data-open-dialog="previewPdfDialog"
                        class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-3 py-2 min-h-[38px] text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <span aria-hidden="true">👁️</span> Pratinjau
                    </button>
                    <a href="{{ $document->file_url }}" target="_blank" rel="noopener" download="{{ $document->file_name }}"
                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3 py-2 min-h-[38px] text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <span aria-hidden="true">⬇️</span> Unduh
                    </a>
                    <button type="button" data-open-dialog="uploadPdfDialog"
                        class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 min-h-[38px] text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <span aria-hidden="true">🔄</span> Ganti
                    </button>
                    <button type="button"
                        data-delete-url="{{ route('admin.academic-calendar.pdf.destroy') }}"
                        data-delete-year="{{ $selectedYear }}"
                        data-delete-message="Hapus berkas SK PDF '{{ $document->file_name }}' untuk TA {{ $selectedYear }}?"
                        class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-red-950 px-3 py-2 min-h-[38px] text-xs font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer"
                        aria-label="Hapus Dokumen SK PDF">
                        <span aria-hidden="true">🗑️</span>
                    </button>
                @else
                    <button type="button" data-open-dialog="uploadPdfDialog"
                        class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-4 py-2 min-h-[40px] text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <span aria-hidden="true">📄</span> Unggah SK PDF
                    </button>
                @endif
            </div>
        </div>
    </section>

    <!-- Statistik Ringkas -->
    <section aria-label="Ringkasan Agenda Kalender" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-slate-500 font-heading">Total Agenda ({{ $selectedYear }})</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-black mt-1 tabular-nums">{{ $stats['total'] }} Kegiatan</div>
            <div class="text-[10px] text-slate-600 font-bold mt-1">Gabungan Ganjil &amp; Genap</div>
        </div>
        <div class="bg-[#FFF9DB] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-amber-900 font-heading">Semester Ganjil</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-amber-950 mt-1 tabular-nums">{{ $stats['ganjil'] }} Kegiatan</div>
            <div class="text-[10px] text-amber-800 font-bold mt-1">Juli – Desember</div>
        </div>
        <div class="bg-[#E7F5FF] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-blue-900 font-heading">Semester Genap</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-blue-950 mt-1 tabular-nums">{{ $stats['genap'] }} Kegiatan</div>
            <div class="text-[10px] text-blue-800 font-bold mt-1">Januari – Juli</div>
        </div>
        <div class="bg-[#FFE3E3] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-red-900 font-heading">Libur Akademik</div>
            <div class="font-heading font-black text-xl sm:text-2xl text-red-950 mt-1 tabular-nums">{{ $stats['libur'] }} Libur</div>
            <div class="text-[10px] text-red-800 font-bold mt-1">{{ $stats['ujian'] }} agenda ujian/asesmen</div>
        </div>
    </section>

    <!-- Tabs Aksesibel Ganjil & Genap (Zero Reload) -->
    <div>
        <div role="tablist" aria-label="Semester Kalender Pendidikan" class="mwg-tablist">
            @foreach($semesterPanels as $semKey => $semPanel)
                @php $isSelected = $activeTab === $semKey; @endphp
                <button type="button"
                    role="tab"
                    id="tab-{{ $semKey }}"
                    data-semester-tab="{{ $semKey }}"
                    aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                    tabindex="{{ $isSelected ? '0' : '-1' }}"
                    class="px-4 sm:px-5 py-3 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-b-0 border-black rounded-t-lg transition-colors cursor-pointer {{ $isSelected ? 'bg-[#FFD43B] text-black' : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
                    <span aria-hidden="true">{{ $semPanel['icon'] }}</span>
                    <span>{{ $semPanel['short'] }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full border border-black tabular-nums {{ $isSelected ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                        {{ $semPanel['items']->count() }}
                    </span>
                </button>
            @endforeach
        </div>

        @foreach($semesterPanels as $semKey => $semPanel)
            @php
                $isActivePanel = $activeTab === $semKey;
                $items = $semPanel['items'];
            @endphp
            <div id="panel-{{ $semKey }}"
                role="tabpanel"
                aria-labelledby="tab-{{ $semKey }}"
                data-semester-panel="{{ $semKey }}"
                @if(! $isActivePanel) hidden="until-found" @endif>
                <!-- Child wrapper: border & padding tidak diletakkan pada node hidden=until-found -->
                <div class="bg-white neo-box overflow-hidden border-2 border-black shadow-[4px_4px_0px_0px_#000] rounded-tl-none">
                    <div class="bg-[#FFF9DB] p-4 border-b-2 border-black flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 id="heading-table-{{ $semKey }}" class="font-heading font-black text-sm text-black uppercase tracking-wider flex items-center gap-2">
                                <span aria-hidden="true">📋</span> Kalender &bull; {{ $semPanel['title'] }}
                            </h2>
                            <p class="text-[11px] font-semibold text-slate-600 mt-0.5">
                                Diurutkan secara kronologis berdasarkan tanggal mulai kegiatan.
                            </p>
                        </div>
                    </div>

                    <div tabindex="0" role="region" aria-labelledby="heading-table-{{ $semKey }}" class="supervisi-table-wrap p-3 sm:p-0">
                        <table class="w-full text-left text-xs border-collapse"
                            style="--label-1: 'No'; --label-2: 'Hari'; --label-3: 'Tanggal'; --label-4: 'Keterangan Kegiatan'; --label-5: 'Kategori'; --label-6: 'Aksi';">
                            <caption class="sr-only">Daftar agenda {{ $semPanel['title'] }}</caption>
                            <thead class="bg-[#F8F9FA] border-b-2 border-black text-xs font-black uppercase tracking-wider font-heading">
                                <tr>
                                    <th scope="col" class="p-3.5 border-r border-black w-14 text-center">No</th>
                                    <th scope="col" class="p-3.5 border-r border-black w-40 text-center">Hari</th>
                                    <th scope="col" class="p-3.5 border-r border-black w-48 text-center">Tanggal</th>
                                    <th scope="col" class="p-3.5 border-r border-black">Keterangan Kegiatan</th>
                                    <th scope="col" class="p-3.5 border-r border-black w-32 text-center">Kategori</th>
                                    <th scope="col" class="p-3.5 text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-black">
                                @forelse($items as $index => $item)
                                    @php
                                        $cat = $categoryMeta[$item->category] ?? $categoryMeta['umum'];
                                    @endphp
                                    <tr class="hover:bg-slate-50 font-medium">
                                        <td class="p-3.5 font-black text-center border-r border-black text-slate-900 tabular-nums">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="p-3.5 border-r border-black text-center">
                                            <span class="font-bold text-slate-800 uppercase font-heading">{{ $item->day_name }}</span>
                                        </td>
                                        <td class="p-3.5 border-r border-black text-center">
                                            <span class="font-mono font-black text-black bg-[#FFF9DB] px-2 py-1 rounded border border-black inline-block shadow-[1px_1px_0px_0px_#000]">
                                                {{ $item->formatted_date }}
                                            </span>
                                        </td>
                                        <td class="p-3.5 font-bold text-black border-r border-black leading-relaxed">
                                            {{ $item->description }}
                                        </td>
                                        <td class="p-3.5 border-r border-black text-center">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase px-2 py-1 rounded border shadow-[1px_1px_0px_0px_#000] {{ $cat['badge'] }}">
                                                <span aria-hidden="true">{{ $cat['icon'] }}</span>
                                                <span>{{ $cat['label'] }}</span>
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center">
                                            <div class="inline-flex items-center justify-center gap-1.5">
                                                <button type="button"
                                                    data-edit-calendar="{{ json_encode([
                                                        'id' => $item->id,
                                                        'semester' => $item->semester,
                                                        'category' => $item->category,
                                                        'start_date' => $item->start_date ? substr((string) $item->start_date, 0, 10) : '',
                                                        'end_date' => $item->end_date ? substr((string) $item->end_date, 0, 10) : '',
                                                        'description' => $item->description,
                                                    ]) }}"
                                                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2.5 py-1.5 text-xs font-black border-2 border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                    aria-label="Edit agenda {{ $item->description }}">
                                                    <span aria-hidden="true">✏️</span>
                                                </button>
                                                <button type="button"
                                                    data-delete-url="{{ route('admin.academic-calendar.destroy', $item->id) }}"
                                                    data-delete-message="Hapus agenda '{{ $item->description }}' ({{ $item->formatted_date }})?"
                                                    class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-red-950 px-2.5 py-1.5 text-xs font-black border-2 border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                    aria-label="Hapus agenda {{ $item->description }}">
                                                    <span aria-hidden="true">🗑️</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-10 text-center">
                                            <div class="max-w-md mx-auto space-y-2">
                                                <div class="text-3xl" aria-hidden="true">🗓️</div>
                                                <div class="font-heading font-black text-sm text-black">
                                                    Belum Ada Agenda di {{ $semPanel['title'] }}
                                                </div>
                                                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                                                    Gunakan tombol <strong>Upload Excel</strong> atau <strong>+ Tambah Agenda</strong> di toolbar atas untuk menambahkan kegiatan.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- ============================================================== -->
<!-- NATIVE DIALOG: UPLOAD EXCEL KALENDER PENDIDIKAN                 -->
<!-- ============================================================== -->
<dialog id="uploadExcelDialog" closedby="any" aria-labelledby="uploadExcelTitle"
    class="bg-white rounded-xl border-4 border-black p-0 shadow-[8px_8px_0px_0px_#000] w-full max-w-lg text-slate-900">
    <div class="p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h1 id="uploadExcelTitle" class="font-heading font-black text-base text-black flex items-center gap-2">
                <span aria-hidden="true">📤</span> Upload Template Kalender Pendidikan
            </h1>
            <button type="button" data-close-dialog="uploadExcelDialog" aria-label="Tutup dialog"
                class="font-black text-lg text-slate-600 hover:text-black px-2 py-1 cursor-pointer">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.academic-calendar.import') }}" enctype="multipart/form-data" class="space-y-4" data-single-submit>
            @csrf
            <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">

            <div class="p-3 bg-[#E7F5FF] border-2 border-black rounded-lg text-xs text-blue-950 font-medium space-y-2">
                <div class="font-black font-heading text-sm">💡 Panduan Template Excel</div>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-[11px] font-semibold">
                    <div>&bull; <strong>Kolom A:</strong> No</div>
                    <div>&bull; <strong>Kolom B:</strong> Hari (Opsional)</div>
                    <div>&bull; <strong>Kolom C:</strong> Tanggal Mulai</div>
                    <div>&bull; <strong>Kolom D:</strong> Tanggal Selesai</div>
                    <div>&bull; <strong>Kolom E:</strong> Keterangan</div>
                    <div>&bull; <strong>Kolom F:</strong> Kategori</div>
                </div>
                <div class="pt-1.5 border-t border-blue-200">
                    <a href="{{ route('admin.academic-calendar.template.download', ['academic_year_name' => $selectedYear]) }}"
                        class="inline-flex items-center gap-1.5 text-xs font-black text-blue-900 underline hover:text-blue-950">
                        <span aria-hidden="true">📥</span> Unduh Template Excel TA {{ $selectedYear }} (.xlsx)
                    </a>
                </div>
            </div>

            <fieldset class="space-y-2">
                <legend class="text-xs font-black uppercase font-heading text-slate-800 mb-1.5">Mode Pengunggahan</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <label for="importModeAppend" class="p-3 border-2 border-black rounded-lg cursor-pointer bg-white has-[:checked]:bg-[#FFF9DB] has-[:checked]:shadow-[2px_2px_0px_0px_#000] transition-all block">
                        <div class="flex items-center gap-2">
                            <input id="importModeAppend" type="radio" name="mode" value="append" checked class="accent-black">
                            <span class="text-xs font-black text-black">Tambahkan (Append)</span>
                        </div>
                        <p class="text-[10px] text-slate-600 font-medium mt-1">Pertahankan agenda lama, tambahkan baris baru.</p>
                    </label>
                    <label for="importModeReplace" class="p-3 border-2 border-black rounded-lg cursor-pointer bg-white has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[2px_2px_0px_0px_#000] transition-all block">
                        <div class="flex items-center gap-2">
                            <input id="importModeReplace" type="radio" name="mode" value="replace" class="accent-red-700">
                            <span class="text-xs font-black text-red-950">Timpa (Replace)</span>
                        </div>
                        <p class="text-[10px] text-slate-600 font-medium mt-1">Hapus seluruh agenda TA {{ $selectedYear }} lalu ganti dari file.</p>
                    </label>
                </div>
            </fieldset>

            <div class="space-y-1">
                <label for="excelFileInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                    Pilih Berkas Excel <span class="text-red-600" aria-hidden="true">*</span>
                </label>
                <input id="excelFileInput" type="file" name="file" accept=".xlsx,.xls,.csv" required aria-describedby="excelFileHelp" autofocus
                    class="neo-input w-full min-h-[44px] text-xs font-bold p-2 border-2 border-black bg-slate-50 cursor-pointer">
                <p id="excelFileHelp" class="text-[10px] text-slate-500 font-medium">Format `.xlsx`, `.xls`, atau `.csv` &bull; Maksimal 10 MB.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" data-close-dialog="uploadExcelDialog"
                    class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-5 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Mulai Impor
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- ============================================================== -->
<!-- NATIVE DIALOG: UPLOAD SK PDF DASAR PENETAPAN                    -->
<!-- ============================================================== -->
<dialog id="uploadPdfDialog" closedby="any" aria-labelledby="uploadPdfTitle"
    class="bg-white rounded-xl border-4 border-black p-0 shadow-[8px_8px_0px_0px_#000] w-full max-w-lg text-slate-900">
    <div class="p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h1 id="uploadPdfTitle" class="font-heading font-black text-base text-black flex items-center gap-2">
                <span aria-hidden="true">📄</span> Unggah Dokumen SK PDF
            </h1>
            <button type="button" data-close-dialog="uploadPdfDialog" aria-label="Tutup dialog"
                class="font-black text-lg text-slate-600 hover:text-black px-2 py-1 cursor-pointer">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.academic-calendar.pdf.upload') }}" enctype="multipart/form-data" class="space-y-4" data-single-submit>
            @csrf
            <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">

            <div class="p-3 bg-[#FFE3E3] border-2 border-black rounded-lg text-xs text-red-950 font-medium">
                Dokumen SK ini menjadi rujukan resmi penetapan kalender pendidikan &amp; hari libur <strong>TA {{ $selectedYear }}</strong>.
            </div>

            <div class="space-y-1">
                <label for="pdfTitleInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                    Judul Dokumen (Opsional)
                </label>
                <input id="pdfTitleInput" type="text" name="title"
                    value="{{ $document->title ?? 'SK Penetapan Kalender Pendidikan & Hari Libur TA ' . $selectedYear }}"
                    placeholder="Contoh: SK Kepala Madrasah No. 421/2026"
                    class="neo-input w-full min-h-[44px] text-xs font-bold p-2.5 border-2 border-black">
            </div>

            <div class="space-y-1">
                <label for="pdfFileInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                    Pilih Berkas PDF <span class="text-red-600" aria-hidden="true">*</span>
                </label>
                <input id="pdfFileInput" type="file" name="pdf_file" accept="application/pdf" required aria-describedby="pdfFileHelp" autofocus
                    class="neo-input w-full min-h-[44px] text-xs font-bold p-2 border-2 border-black bg-slate-50 cursor-pointer">
                <p id="pdfFileHelp" class="text-[10px] text-slate-500 font-medium">Format `.pdf` &bull; Maksimal 20 MB.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" data-close-dialog="uploadPdfDialog"
                    class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-5 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Simpan Berkas SK
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- ============================================================== -->
<!-- NATIVE DIALOG: PRATINJAU SK PDF                                 -->
<!-- ============================================================== -->
@if($document)
<dialog id="previewPdfDialog" closedby="any" aria-labelledby="previewPdfTitle"
    class="bg-white rounded-xl border-4 border-black p-0 shadow-[10px_10px_0px_0px_#000] w-full max-w-4xl h-[90dvh] text-slate-900">
    <div class="flex flex-col h-full p-4 sm:p-5 gap-3">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black shrink-0 gap-3">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0" aria-hidden="true">📜</span>
                <div class="min-w-0">
                    <h1 id="previewPdfTitle" class="font-heading font-black text-sm text-black truncate">
                        {{ $document->title }}
                    </h1>
                    <p class="text-[10px] text-slate-500 font-bold truncate">
                        {{ $document->file_name }} &bull; {{ $document->formatted_size }}
                    </p>
                </div>
            </div>
            <button type="button" data-close-dialog="previewPdfDialog" autofocus
                class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-1.5 text-xs font-black border-2 border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer shrink-0">
                ✕ Tutup
            </button>
        </div>

        <div class="flex-1 border-2 border-black overflow-hidden bg-slate-100 rounded-lg">
            <iframe src="{{ $document->file_url }}" class="w-full h-full border-0" title="Pratinjau {{ $document->title }}"></iframe>
        </div>
    </div>
</dialog>
@endif

<!-- ============================================================== -->
<!-- NATIVE DIALOG TUNGGAL: TAMBAH / EDIT AGENDA                     -->
<!-- ============================================================== -->
<dialog id="calendarFormDialog" closedby="any" aria-labelledby="calendarFormTitle"
    class="bg-white rounded-xl border-4 border-black p-0 shadow-[8px_8px_0px_0px_#000] w-full max-w-lg text-slate-900">
    <div class="p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h1 id="calendarFormTitle" class="font-heading font-black text-base text-black">
                ➕ Tambah Kegiatan Kalender Pendidikan
            </h1>
            <button type="button" data-close-dialog="calendarFormDialog" aria-label="Tutup dialog"
                class="font-black text-lg text-slate-600 hover:text-black px-2 py-1 cursor-pointer">✕</button>
        </div>

        <form id="calendarItemForm" method="POST" action="{{ route('admin.academic-calendar.store') }}" class="space-y-4" data-single-submit>
            @csrf
            <input type="hidden" id="calendarFormMethod" name="_method" value="PUT" disabled>
            <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label for="calendarSemesterInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                        Semester <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <select id="calendarSemesterInput" name="semester" required class="neo-input w-full min-h-[44px] text-xs font-bold p-2 border-2 border-black bg-[#FFF9DB]">
                        <option value="ganjil">Ganjil (Juli – Des)</option>
                        <option value="genap">Genap (Jan – Juli)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="calendarCategoryInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                        Kategori <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <select id="calendarCategoryInput" name="category" required class="neo-input w-full min-h-[44px] text-xs font-bold p-2 border-2 border-black bg-white">
                        <option value="kegiatan">Kegiatan</option>
                        <option value="libur">Libur Sekolah</option>
                        <option value="ujian">Ujian / Asesmen</option>
                        <option value="rapat">Rapat / Dinas</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label for="calendarStartDateInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                        Tanggal Mulai <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input id="calendarStartDateInput" type="date" name="start_date" required autofocus
                        class="neo-input w-full min-h-[44px] text-xs font-bold p-2 border-2 border-black bg-white">
                </div>

                <div class="space-y-1">
                    <label for="calendarEndDateInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                        Tanggal Selesai <span class="text-slate-500 font-normal">(Opsional)</span>
                    </label>
                    <input id="calendarEndDateInput" type="date" name="end_date" aria-describedby="calendarDateHelp"
                        class="neo-input w-full min-h-[44px] text-xs font-bold p-2 border-2 border-black bg-white">
                </div>
            </div>
            <p id="calendarDateHelp" class="text-[10px] text-slate-500 font-medium -mt-2">
                Kosongkan tanggal selesai untuk kegiatan 1 hari. Nama hari dihitung otomatis.
            </p>

            <div class="space-y-1">
                <label for="calendarDescriptionInput" class="block text-xs font-black uppercase font-heading text-slate-800">
                    Keterangan Kegiatan <span class="text-red-600" aria-hidden="true">*</span>
                </label>
                <textarea id="calendarDescriptionInput" name="description" rows="3" required
                    placeholder="Contoh: Masa Pengenalan Lingkungan Sekolah (MPLS)"
                    class="neo-input w-full text-xs font-bold p-2.5 border-2 border-black bg-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" data-close-dialog="calendarFormDialog"
                    class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black cursor-pointer">
                    Batal
                </button>
                <button id="calendarFormSubmitBtn" type="submit"
                    class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Simpan Kegiatan
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- ============================================================== -->
<!-- NATIVE DIALOG: KONFIRMASI HAPUS (PENGGANTI window.confirm)     -->
<!-- ============================================================== -->
<dialog id="confirmDeleteDialog" closedby="closerequest" aria-labelledby="confirmDeleteTitle"
    class="bg-white rounded-xl border-4 border-black p-0 shadow-[8px_8px_0px_0px_#000] w-full max-w-md text-slate-900">
    <div class="p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h1 id="confirmDeleteTitle" class="font-heading font-black text-base text-red-950 flex items-center gap-2">
                <span aria-hidden="true">🗑️</span> Konfirmasi Penghapusan
            </h1>
            <button type="button" data-close-dialog="confirmDeleteDialog" aria-label="Batal hapus"
                class="font-black text-lg text-slate-600 hover:text-black px-2 py-1 cursor-pointer">✕</button>
        </div>

        <p id="confirmDeleteMessage" class="text-xs font-bold text-slate-700 leading-relaxed"></p>

        <form id="confirmDeleteForm" method="POST" action="" data-single-submit class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
            @csrf
            @method('DELETE')
            <input type="hidden" id="confirmDeleteYearInput" name="academic_year_name" value="" disabled>
            <button type="button" data-close-dialog="confirmDeleteDialog" autofocus
                class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black cursor-pointer">
                Batal
            </button>
            <button type="submit"
                class="neo-btn bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                Ya, Hapus
            </button>
        </form>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(() => {
    const storeUrl = @json(route('admin.academic-calendar.store'));
    let activeSemester = @json($activeTab);

    // 1. Dismiss Alert Buttons
    document.querySelectorAll('[data-dismiss-alert]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('[role="status"], [role="alert"]')?.remove();
        });
    });

    // 2. Native <dialog> Open / Close & Light-Dismiss Fallback (Safari / older browsers)
    document.querySelectorAll('[data-open-dialog]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.openDialog)?.showModal();
        });
    });

    document.querySelectorAll('[data-close-dialog]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.closeDialog)?.close();
        });
    });

    if (!('closedBy' in HTMLDialogElement.prototype)) {
        const isOutsideDialog = (event, dialog) => {
            const rect = dialog.getBoundingClientRect();
            return event.clientX < rect.left
                || event.clientX > rect.right
                || event.clientY < rect.top
                || event.clientY > rect.bottom;
        };

        document.querySelectorAll('dialog[closedby="any"]').forEach((dialog) => {
            let pressedOutside = false;
            dialog.addEventListener('pointerdown', (e) => {
                pressedOutside = isOutsideDialog(e, dialog);
            });
            dialog.addEventListener('pointerup', (e) => {
                if (pressedOutside && isOutsideDialog(e, dialog)) {
                    dialog.close();
                }
            });
        });
    }

    // 3. Tabs Aksesibel + URL Sync + Find-in-Page (`beforematch`)
    const tabButtons = Array.from(document.querySelectorAll('[data-semester-tab]'));
    const tabPanels = Array.from(document.querySelectorAll('[data-semester-panel]'));
    const filterTabInput = document.getElementById('filterActiveTab');

    function activateTab(semester, shouldFocus = false) {
        activeSemester = semester;
        if (filterTabInput) {
            filterTabInput.value = semester;
        }

        tabButtons.forEach((tab) => {
            const selected = tab.dataset.semesterTab === semester;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
            tab.classList.toggle('bg-[#FFD43B]', selected);
            tab.classList.toggle('text-black', selected);
            tab.classList.toggle('bg-white', !selected);
            tab.classList.toggle('text-slate-600', !selected);

            const countPill = tab.querySelector('span:last-child');
            if (countPill) {
                countPill.classList.toggle('bg-black', selected);
                countPill.classList.toggle('text-white', selected);
                countPill.classList.toggle('bg-slate-200', !selected);
                countPill.classList.toggle('text-slate-800', !selected);
            }

            if (selected && shouldFocus) {
                tab.focus();
                tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }
        });

        tabPanels.forEach((panel) => {
            const visible = panel.dataset.semesterPanel === semester;
            if (visible) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', 'until-found');
            }
        });

        const url = new URL(window.location.href);
        url.searchParams.set('tab', semester);
        window.history.replaceState({}, '', url.toString());
    }

    tabButtons.forEach((tab, idx) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.semesterTab, false));
        tab.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault();
                const nextIdx = e.key === 'ArrowRight'
                    ? (idx + 1) % tabButtons.length
                    : (idx - 1 + tabButtons.length) % tabButtons.length;
                activateTab(tabButtons[nextIdx].dataset.semesterTab, true);
            }
        });
    });

    tabPanels.forEach((panel) => {
        panel.addEventListener('beforematch', () => {
            activateTab(panel.dataset.semesterPanel, false);
        });
    });

    // 4. Unified Create / Edit Agenda Dialog
    const calendarDialog = document.getElementById('calendarFormDialog');
    const calendarForm = document.getElementById('calendarItemForm');
    const calendarTitle = document.getElementById('calendarFormTitle');
    const calendarMethod = document.getElementById('calendarFormMethod');
    const calendarSubmit = document.getElementById('calendarFormSubmitBtn');

    document.getElementById('btnOpenCreateCalendar')?.addEventListener('click', () => {
        if (!calendarDialog || !calendarForm) {
            return;
        }
        calendarForm.reset();
        calendarForm.action = storeUrl;
        calendarMethod.disabled = true;
        calendarTitle.textContent = '➕ Tambah Kegiatan Kalender Pendidikan';
        calendarSubmit.textContent = 'Simpan Kegiatan';
        calendarSubmit.className = 'neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer';
        document.getElementById('calendarSemesterInput').value = activeSemester;
        calendarDialog.showModal();
    });

    document.querySelectorAll('[data-edit-calendar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!calendarDialog || !calendarForm) {
                return;
            }
            const item = JSON.parse(btn.dataset.editCalendar || '{}');
            calendarForm.action = `/admin/academic-calendar/${item.id}`;
            calendarMethod.disabled = false;
            calendarTitle.textContent = '✏️ Edit Kegiatan Kalender Pendidikan';
            calendarSubmit.textContent = 'Simpan Perubahan';
            calendarSubmit.className = 'neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 min-h-[40px] text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer';

            document.getElementById('calendarSemesterInput').value = item.semester || 'ganjil';
            document.getElementById('calendarCategoryInput').value = item.category || 'kegiatan';
            document.getElementById('calendarStartDateInput').value = item.start_date || '';
            document.getElementById('calendarEndDateInput').value = item.end_date || '';
            document.getElementById('calendarDescriptionInput').value = item.description || '';
            calendarDialog.showModal();
        });
    });

    // 5. Konfirmasi Hapus via Native Dialog (Menggantikan window.confirm)
    const deleteDialog = document.getElementById('confirmDeleteDialog');
    const deleteForm = document.getElementById('confirmDeleteForm');
    const deleteMessage = document.getElementById('confirmDeleteMessage');
    const deleteYearInput = document.getElementById('confirmDeleteYearInput');

    document.querySelectorAll('[data-delete-url]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!deleteDialog || !deleteForm) {
                return;
            }
            deleteForm.action = btn.dataset.deleteUrl;
            deleteMessage.textContent = btn.dataset.deleteMessage || 'Yakin ingin menghapus item ini?';
            if (btn.dataset.deleteYear) {
                deleteYearInput.value = btn.dataset.deleteYear;
                deleteYearInput.disabled = false;
            } else {
                deleteYearInput.value = '';
                deleteYearInput.disabled = true;
            }
            deleteDialog.showModal();
        });
    });

    // 6. Cegah Double-Submit Setelah Validasi Native Lolos
    document.querySelectorAll('form[data-single-submit]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60');
            }
        });
    });
})();
</script>
@endpush
