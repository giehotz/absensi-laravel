@extends('layouts.admin')

@section('title', 'Kalender Pendidikan')
@section('page-title', 'Kalender Pendidikan')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-2xl">📅</span>
                <h1 class="font-heading font-black text-xl text-black">
                    Kalender Pendidikan
                </h1>
                <span class="bg-[#FFD43B] text-black text-xs font-black px-2.5 py-0.5 rounded border border-black uppercase shadow-[1px_1px_0px_0px_#000]">
                    TA {{ $selectedYear }}
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Agenda kegiatan akademik semester ganjil (Juli - Desember) dan semester genap (Januari - Juli), serta dokumen SK penetapan hari libur.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Selector Tahun Ajaran -->
            <form method="GET" action="{{ route('admin.academic-calendar.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <label for="yearSelect" class="text-xs font-black uppercase font-heading text-slate-700">Tahun:</label>
                <select id="yearSelect" name="academic_year_name" onchange="this.form.submit()" class="neo-input text-xs font-black py-2 px-3 bg-[#FFF9DB] border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </form>

            <!-- Unduh Template Excel -->
            <a href="{{ route('admin.academic-calendar.template.download', ['academic_year_name' => $selectedYear]) }}" 
               class="neo-btn bg-[#FFF4E6] hover:bg-[#ffe8cc] text-amber-950 px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]"
               title="Unduh format template Excel kalender pendidikan">
                <span>📥</span> Template
            </a>

            <!-- Tombol Upload Excel -->
            <button type="button" onclick="openModal('uploadExcelModal')" 
                    class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span>📤</span> Upload Excel
            </button>

            <!-- Tombol Upload SK PDF -->
            <button type="button" onclick="openModal('uploadPdfModal')" 
                    class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span>📄</span> Upload SK PDF
            </button>

            <!-- Tombol Tambah Kegiatan Manual -->
            <button type="button" onclick="openCreateModal('{{ $activeTab }}')" 
                    class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span>+</span> Tambah Agenda
            </button>
        </div>
    </div>

    <!-- Alert Success / Error Feedback -->
    @if(session('success'))
        <div class="bg-[#D3F9D8] border-2 border-black p-4 shadow-[3px_3px_0px_0px_#000] flex items-center justify-between">
            <div class="flex items-center gap-2.5 text-xs font-black text-green-950">
                <span class="text-base">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="font-black text-xs text-green-900 hover:text-black">✕</button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="bg-[#FFE3E3] border-2 border-black p-4 shadow-[3px_3px_0px_0px_#000] space-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-xs font-black text-red-950">
                    <span class="text-base">⚠️</span>
                    <span>{{ session('error') ?? 'Terjadi kesalahan pada input data:' }}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="font-black text-xs text-red-900 hover:text-black">✕</button>
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

    <!-- Card Dokumen Dasar Penetapan (SK PDF) -->
    <div class="bg-white neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-lg bg-[#FFE3E3] border-2 border-black flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                    <span class="text-2xl">📜</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-900 text-white font-heading">
                            Dasar Penetapan Hari Libur & Kalender
                        </span>
                        @if($document)
                            <span class="text-[10px] font-black bg-[#D3F9D8] text-green-950 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                Terunggah ({{ $document->formatted_size }})
                            </span>
                        @else
                            <span class="text-[10px] font-black bg-[#FFF3BF] text-amber-950 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                Belum Ada Berkas PDF
                            </span>
                        @endif
                    </div>
                    <h3 class="font-heading font-black text-sm text-black mt-1">
                        {{ $document ? $document->title : 'Surat Keputusan (SK) Dasar Penetapan Kalender Pendidikan TA ' . $selectedYear }}
                    </h3>
                    <p class="text-xs text-slate-600 font-medium mt-0.5">
                        @if($document)
                            Berkas resmi: <strong class="text-black">{{ $document->file_name }}</strong> &bull; Diperbarui pada {{ $document->updated_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                        @else
                            Silakan unggah dokumen SK / Surat Edaran resmi penetapan kalender akademik & hari libur dari Dinas/Kemenag.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Dokumen PDF -->
            <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
                @if($document)
                    <!-- Tombol Preview PDF -->
                    <button type="button" onclick="openModal('previewPdfModal')" 
                            class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-3 py-1.5 text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <span>👁️</span> Pratinjau PDF
                    </button>

                    <!-- Tombol Unduh PDF -->
                    <a href="{{ $document->file_url }}" target="_blank" download="{{ $document->file_name }}"
                       class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3 py-1.5 text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <span>⬇️</span> Unduh
                    </a>

                    <!-- Tombol Ganti PDF -->
                    <button type="button" onclick="openModal('uploadPdfModal')" 
                            class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-1.5 text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]"
                            title="Ganti berkas PDF">
                        <span>🔄</span> Ganti
                    </button>

                    <!-- Tombol Hapus PDF -->
                    <form method="POST" action="{{ route('admin.academic-calendar.pdf.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas SK PDF ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">
                        <button type="submit" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-red-950 px-2.5 py-1.5 text-xs font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]" title="Hapus Dokumen PDF">
                            🗑️
                        </button>
                    </form>
                @else
                    <button type="button" onclick="openModal('uploadPdfModal')" 
                            class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-4 py-2 text-xs font-black font-heading uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <span>📤</span> Unggah SK Sekarang
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-slate-500 font-heading">Total Agenda ({{ $selectedYear }})</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $stats['total'] }} Kegiatan</div>
            <div class="text-[10px] text-slate-600 font-bold mt-1">Gabungan Ganjil & Genap</div>
        </div>
        <div class="bg-[#FFF9DB] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-amber-900 font-heading">Semester Ganjil</div>
            <div class="font-heading font-black text-2xl text-amber-950 mt-1">{{ $stats['ganjil'] }} Kegiatan</div>
            <div class="text-[10px] text-amber-800 font-bold mt-1">Periode Juli &ndash; Desember</div>
        </div>
        <div class="bg-[#E7F5FF] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-blue-900 font-heading">Semester Genap</div>
            <div class="font-heading font-black text-2xl text-blue-950 mt-1">{{ $stats['genap'] }} Kegiatan</div>
            <div class="text-[10px] text-blue-800 font-bold mt-1">Periode Januari &ndash; Juli</div>
        </div>
        <div class="bg-[#FFE3E3] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-red-900 font-heading">Libur Akademik</div>
            <div class="font-heading font-black text-2xl text-red-950 mt-1">{{ $stats['libur'] }} Agenda Libur</div>
            <div class="text-[10px] text-red-800 font-bold mt-1">{{ $stats['ujian'] }} agenda masa ujian/asesmen</div>
        </div>
    </div>

    <!-- Tab Navigasi Semester Ganjil & Genap -->
    <div class="border-b-2 border-black flex items-center gap-2">
        <a href="{{ route('admin.academic-calendar.index', ['academic_year_name' => $selectedYear, 'tab' => 'ganjil']) }}"
           class="px-5 py-3 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-b-0 border-black transition-all
           {{ $activeTab === 'ganjil' 
                ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
            <span>🍁</span>
            <span>Semester Ganjil (Juli &ndash; Desember)</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ $activeTab === 'ganjil' ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                {{ $calendarsGanjil->count() }}
            </span>
        </a>

        <a href="{{ route('admin.academic-calendar.index', ['academic_year_name' => $selectedYear, 'tab' => 'genap']) }}"
           class="px-5 py-3 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-b-0 border-black transition-all
           {{ $activeTab === 'genap' 
                ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
            <span>🌸</span>
            <span>Semester Genap (Januari &ndash; Juli)</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ $activeTab === 'genap' ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                {{ $calendarsGenap->count() }}
            </span>
        </a>
    </div>

    <!-- Konten Tabel Kalender -->
    @php
        $currentItems = $activeTab === 'ganjil' ? $calendarsGanjil : $calendarsGenap;
        $periodLabel = $activeTab === 'ganjil' ? 'Semester Ganjil (Juli - Desember)' : 'Semester Genap (Januari - Juli)';
    @endphp

    <div class="bg-white neo-box overflow-hidden border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        <!-- Bar Judul Tabel -->
        <div class="bg-[#FFF9DB] p-4 border-b-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-sm text-black uppercase tracking-wider flex items-center gap-2">
                    <span>📋</span> Tabel Kalender Pendidikan &bull; {{ $periodLabel }}
                </h2>
                <p class="text-[11px] font-semibold text-slate-600 mt-0.5">
                    Format tabel resmi berurutan secara kronologis tanggal kegiatan.
                </p>
            </div>
            <button type="button" onclick="openCreateModal('{{ $activeTab }}')" 
                    class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3 py-1.5 text-xs font-heading font-black uppercase flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] self-start sm:self-auto">
                <span>+</span> Tambah di {{ ucfirst($activeTab) }}
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#F8F9FA] border-b-2 border-black text-xs font-black uppercase tracking-wider font-heading">
                    <tr>
                        <th class="p-3.5 border-r border-black w-14 text-center">No</th>
                        <th class="p-3.5 border-r border-black w-44 text-center">Hari</th>
                        <th class="p-3.5 border-r border-black w-48 text-center">Tanggal (dd/mm/yyyy)</th>
                        <th class="p-3.5 border-r border-black">Keterangan Kegiatan</th>
                        <th class="p-3.5 border-r border-black w-36 text-center">Kategori</th>
                        <th class="p-3.5 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($currentItems as $index => $item)
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
                            <td class="p-3.5 font-black text-center border-r border-black text-xs text-slate-900">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3.5 border-r border-black text-center">
                                <span class="font-bold text-xs text-slate-800 uppercase font-heading">
                                    {{ $item->day_name }}
                                </span>
                            </td>
                            <td class="p-3.5 border-r border-black text-center">
                                <div class="font-mono font-black text-xs text-black bg-[#FFF9DB] px-2 py-1 rounded border border-black inline-block shadow-[1px_1px_0px_0px_#000]">
                                    {{ $item->formatted_date }}
                                </div>
                            </td>
                            <td class="p-3.5 font-bold text-black border-r border-black">
                                <div class="text-xs leading-relaxed">{{ $item->description }}</div>
                            </td>
                            <td class="p-3.5 border-r border-black text-center">
                                <span class="text-[10px] font-black uppercase px-2 py-1 rounded border shadow-[1px_1px_0px_0px_#000] {{ $badgeClass }}">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Edit -->
                                    <button type="button" 
                                            onclick='openEditModal(@json($item))'
                                            class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2.5 py-1 text-xs font-black border-2 border-black shadow-[1px_1px_0px_0px_#000]"
                                            title="Edit agenda">
                                        ✏️
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <form method="POST" action="{{ route('admin.academic-calendar.destroy', $item->id) }}" onsubmit="return confirm('Hapus agenda kegiatan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-red-950 px-2.5 py-1 text-xs font-black border-2 border-black shadow-[1px_1px_0px_0px_#000]" title="Hapus agenda">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="text-4xl">🗓️</div>
                                    <div class="font-heading font-black text-base text-black">
                                        Belum Ada Agenda di {{ $periodLabel }}
                                    </div>
                                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                                        Silakan unggah berkas template Excel yang sudah diisi atau buat agenda secara manual melalui tombol di bawah.
                                    </p>
                                    <div class="flex items-center justify-center gap-2 pt-2">
                                        <button type="button" onclick="openModal('uploadExcelModal')" class="neo-btn bg-[#5294FF] text-white px-3 py-1.5 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                                            📤 Upload Excel
                                        </button>
                                        <button type="button" onclick="openCreateModal('{{ $activeTab }}')" class="neo-btn bg-[#20C997] text-black px-3 py-1.5 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                                            + Tambah Manual
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: UPLOAD EXCEL KALENDER PENDIDIKAN                        -->
<!-- ============================================================== -->
<div id="uploadExcelModal" class="fixed inset-0 z-50 bg-black/70 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-lg w-full border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000] relative my-8">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                <span>📤</span> Upload Template Kalender Pendidikan
            </h3>
            <button type="button" onclick="closeModal('uploadExcelModal')" class="font-black text-lg text-slate-600 hover:text-black">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.academic-calendar.import') }}" enctype="multipart/form-data" class="space-y-4 mt-4">
            @csrf
            <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">

            <div class="p-3 bg-[#E7F5FF] border-2 border-black text-xs text-blue-950 font-medium space-y-1.5">
                <div class="font-black font-heading text-sm">💡 Struktur Kolom Template Excel:</div>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-[11px] font-semibold">
                    <div>&bull; <strong>Kolom A:</strong> No</div>
                    <div>&bull; <strong>Kolom B:</strong> Hari (Opsional)</div>
                    <div>&bull; <strong>Kolom C:</strong> Tanggal Mulai</div>
                    <div>&bull; <strong>Kolom D:</strong> Tanggal Selesai (Opsional)</div>
                    <div>&bull; <strong>Kolom E:</strong> Keterangan Kegiatan</div>
                    <div>&bull; <strong>Kolom F:</strong> Kategori</div>
                </div>
                <p class="text-[11px] text-blue-900 pt-1">
                    Tanggal otomatis dipetakan ke <strong>Semester Ganjil</strong> (Juli&ndash;Desember) dan <strong>Semester Genap</strong> (Januari&ndash;Juli).
                </p>
                <div class="pt-1 border-t border-blue-200">
                    <a href="{{ route('admin.academic-calendar.template.download', ['academic_year_name' => $selectedYear]) }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-black text-blue-900 underline hover:text-blue-950">
                        <span>📥</span> Unduh Format Template Excel (.xlsx)
                    </a>
                </div>
            </div>

            <!-- Opsi Mode Impor -->
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase font-heading text-slate-800">
                    Mode Pengunggahan:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label class="p-3 border-2 border-black rounded cursor-pointer bg-white has-[:checked]:bg-[#FFF9DB] has-[:checked]:shadow-[2px_2px_0px_0px_#000] transition-all">
                        <input type="radio" name="mode" value="append" checked class="mr-1.5">
                        <span class="text-xs font-black text-black">Tambahkan (Append)</span>
                        <p class="text-[10px] text-slate-600 font-medium mt-0.5">Pertahankan agenda lama, tambahkan agenda baru.</p>
                    </label>

                    <label class="p-3 border-2 border-black rounded cursor-pointer bg-white has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[2px_2px_0px_0px_#000] transition-all">
                        <input type="radio" name="mode" value="replace" class="mr-1.5">
                        <span class="text-xs font-black text-red-950">Timpa (Replace)</span>
                        <p class="text-[10px] text-slate-600 font-medium mt-0.5">Hapus seluruh agenda TA {{ $selectedYear }} lalu ganti dari file.</p>
                    </label>
                </div>
            </div>

            <!-- Pilih File -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase font-heading text-slate-800">
                    Pilih Berkas Excel (.xlsx / .xls / .csv) <span class="text-red-600">*</span>
                </label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-slate-50 cursor-pointer">
                <p class="text-[10px] text-slate-500 font-medium">Maksimal ukuran file: 10 MB.</p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeModal('uploadExcelModal')" 
                        class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-black font-heading uppercase border-2 border-black">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-5 py-2 text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000]">
                    Mulai Impor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: UPLOAD SK PDF DASAR PENETAPAN                           -->
<!-- ============================================================== -->
<div id="uploadPdfModal" class="fixed inset-0 z-50 bg-black/70 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-lg w-full border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000] relative my-8">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                <span>📄</span> Upload Dokumen SK PDF Dasar Penetapan
            </h3>
            <button type="button" onclick="closeModal('uploadPdfModal')" class="font-black text-lg text-slate-600 hover:text-black">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.academic-calendar.pdf.upload') }}" enctype="multipart/form-data" class="space-y-4 mt-4">
            @csrf
            <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">

            <div class="p-3 bg-[#FFE3E3] border-2 border-black text-xs text-red-950 font-medium">
                Dokumen SK ini berfungsi sebagai dasar hukum dan rujukan penetapan kalender pendidikan serta hari libur sekolah untuk <strong>Tahun Ajaran {{ $selectedYear }}</strong>.
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase font-heading text-slate-800">
                    Judul Dokumen (Opsional)
                </label>
                <input type="text" name="title" value="{{ $document->title ?? 'SK Penetapan Kalender Pendidikan & Hari Libur TA ' . $selectedYear }}"
                       class="neo-input w-full text-xs font-bold p-2 border-2 border-black" placeholder="Contoh: SK Kepala Sekolah No. 421/2026">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black uppercase font-heading text-slate-800">
                    Pilih Berkas PDF <span class="text-red-600">*</span>
                </label>
                <input type="file" name="pdf_file" accept="application/pdf" required
                       class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-slate-50 cursor-pointer">
                <p class="text-[10px] text-slate-500 font-medium">Format: .pdf &bull; Ukuran maksimal: 20 MB.</p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeModal('uploadPdfModal')" 
                        class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-black font-heading uppercase border-2 border-black">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-5 py-2 text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000]">
                    Simpan Berkas SK
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: PRATINJAU (PREVIEW) SK PDF                              -->
<!-- ============================================================== -->
@if($document)
<div id="previewPdfModal" class="fixed inset-0 z-50 bg-black/80 hidden flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
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
                <button type="button" onclick="closeModal('previewPdfModal')" class="font-black text-xl text-slate-700 hover:text-black px-2">
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

<!-- ============================================================== -->
<!-- MODAL: TAMBAH AGENDA MANUAL                                    -->
<!-- ============================================================== -->
<div id="createCalendarModal" class="fixed inset-0 z-50 bg-black/70 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-lg w-full border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000] relative my-8">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                <span>➕</span> Tambah Kegiatan Kalender Pendidikan
            </h3>
            <button type="button" onclick="closeModal('createCalendarModal')" class="font-black text-lg text-slate-600 hover:text-black">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.academic-calendar.store') }}" class="space-y-4 mt-4">
            @csrf
            <input type="hidden" name="academic_year_name" value="{{ $selectedYear }}">

            <!-- Semester & Kategori -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Semester <span class="text-red-600">*</span>
                    </label>
                    <select id="createSemester" name="semester" required class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-[#FFF9DB]">
                        <option value="ganjil">Ganjil (Juli - Des)</option>
                        <option value="genap">Genap (Jan - Juli)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Kategori <span class="text-red-600">*</span>
                    </label>
                    <select name="category" required class="neo-input w-full text-xs font-bold p-2 border-2 border-black">
                        <option value="kegiatan">Kegiatan</option>
                        <option value="libur">Libur Sekolah</option>
                        <option value="ujian">Ujian / Asesmen</option>
                        <option value="rapat">Rapat / Dinas</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
            </div>

            <!-- Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Tanggal Mulai <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="start_date" required
                           class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Tanggal Selesai <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input type="date" name="end_date"
                           class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-white">
                </div>
            </div>
            <p class="text-[10px] text-slate-500 font-medium -mt-2">
                Kosongkan tanggal selesai jika kegiatan hanya berlangsung 1 hari. Nama hari dihitung otomatis.
            </p>

            <!-- Keterangan Kegiatan -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase font-heading text-slate-800">
                    Keterangan Kegiatan <span class="text-red-600">*</span>
                </label>
                <textarea name="description" rows="3" required placeholder="Contoh: Masa Pengenalan Lingkungan Sekolah (MPLS)"
                          class="neo-input w-full text-xs font-bold p-2 border-2 border-black"></textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeModal('createCalendarModal')" 
                        class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-black font-heading uppercase border-2 border-black">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2 text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000]">
                    Simpan Kegiatan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: EDIT AGENDA MANUAL                                      -->
<!-- ============================================================== -->
<div id="editCalendarModal" class="fixed inset-0 z-50 bg-black/70 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-lg w-full border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000] relative my-8">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                <span>✏️</span> Edit Kegiatan Kalender
            </h3>
            <button type="button" onclick="closeModal('editCalendarModal')" class="font-black text-lg text-slate-600 hover:text-black">✕</button>
        </div>

        <form id="editCalendarForm" method="POST" action="" class="space-y-4 mt-4">
            @csrf
            @method('PUT')

            <!-- Semester & Kategori -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Semester <span class="text-red-600">*</span>
                    </label>
                    <select id="editSemester" name="semester" required class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-[#FFF9DB]">
                        <option value="ganjil">Ganjil (Juli - Des)</option>
                        <option value="genap">Genap (Jan - Juli)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Kategori <span class="text-red-600">*</span>
                    </label>
                    <select id="editCategory" name="category" required class="neo-input w-full text-xs font-bold p-2 border-2 border-black">
                        <option value="kegiatan">Kegiatan</option>
                        <option value="libur">Libur Sekolah</option>
                        <option value="ujian">Ujian / Asesmen</option>
                        <option value="rapat">Rapat / Dinas</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
            </div>

            <!-- Tanggal Mulai & Tanggal Selesai -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Tanggal Mulai <span class="text-red-600">*</span>
                    </label>
                    <input type="date" id="editStartDate" name="start_date" required
                           class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase font-heading text-slate-800">
                        Tanggal Selesai <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input type="date" id="editEndDate" name="end_date"
                           class="neo-input w-full text-xs font-bold p-2 border-2 border-black bg-white">
                </div>
            </div>

            <!-- Keterangan Kegiatan -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase font-heading text-slate-800">
                    Keterangan Kegiatan <span class="text-red-600">*</span>
                </label>
                <textarea id="editDescription" name="description" rows="3" required
                          class="neo-input w-full text-xs font-bold p-2 border-2 border-black"></textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeModal('editCalendarModal')" 
                        class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-black font-heading uppercase border-2 border-black">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-black font-heading uppercase border-2 border-black shadow-[3px_3px_0px_0px_#000]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateModal(defaultSemester) {
        const semesterSelect = document.getElementById('createSemester');
        if (semesterSelect && defaultSemester) {
            semesterSelect.value = defaultSemester;
        }
        openModal('createCalendarModal');
    }

    function openEditModal(item) {
        const form = document.getElementById('editCalendarForm');
        form.action = `/admin/academic-calendar/${item.id}`;

        document.getElementById('editSemester').value = item.semester;
        document.getElementById('editCategory').value = item.category || 'kegiatan';
        document.getElementById('editStartDate').value = item.start_date ? item.start_date.substring(0, 10) : '';
        document.getElementById('editEndDate').value = item.end_date ? item.end_date.substring(0, 10) : '';
        document.getElementById('editDescription').value = item.description || '';

        openModal('editCalendarModal');
    }
</script>
@endpush
