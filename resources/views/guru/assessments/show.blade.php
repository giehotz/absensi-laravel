@extends('layouts.guru')

@section('title', $package->title . ' - Matriks Nilai')
@section('page-title', 'Matriks Nilai Sumatif')

@section('content')
<div class="space-y-6 pb-16">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('guru.penilaian.index') }}" class="text-xs font-bold text-slate-600 hover:text-black flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Paket</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded border-2 border-black {{ $package->isLocked() ? 'bg-[#D3F9D8] text-emerald-950 shadow-[2px_2px_0px_0px_#000]' : 'bg-[#FFF9DB] text-amber-950 shadow-[2px_2px_0px_0px_#000]' }}">
                Status: {{ $package->isLocked() ? 'TERKUNCI (FINAL)' : 'DRAFT (AKTIF)' }}
            </span>
        </div>
    </div>

    <!-- Header Card -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                    <span>{{ $package->schoolClass->name }}</span>
                    <span>•</span>
                    <span class="text-black">{{ $package->subject->name }}</span>
                    <span>•</span>
                    <span>Tahun Ajaran {{ $package->academicYear->name }} ({{ ucfirst($package->academicYear->semester) }})</span>
                </div>
                <h2 class="font-heading font-black text-2xl text-black">{{ $package->title }}</h2>
                <p class="text-xs text-slate-600 mt-0.5">
                    Guru Pengampu: <strong class="text-black">{{ $package->teacher->user?->name ?? '-' }}</strong> | Standar KKTP: <strong class="text-black">{{ $package->kktp_default }}</strong>
                </p>
            </div>

            <!-- Toolbar Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Unduh Template -->
                <a href="{{ route('guru.penilaian.template', $package) }}" 
                   class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer"
                   title="Unduh Template Excel 15 Sheet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Download Template</span>
                </a>

                @if(! $package->isLocked())
                    <!-- Tambah Sumatif Berikutnya -->
                    @if($nextSheetNum !== null)
                        <button type="button" onclick="openAddSumatifModal()"
                                class="neo-btn bg-[#FF922B] hover:bg-[#f76707] text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer"
                                title="Aktifkan Kolom Penilaian Sumatif Berikutnya (SUM {{ $nextSheetNum }})">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>+ Tambah Sumatif (S{{ $nextSheetNum }})</span>
                        </button>
                    @endif

                    <!-- Upload Nilai Excel -->
                    <button type="button" onclick="openUploadModal()"
                            class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>Upload Excel</span>
                    </button>
                @endif

                <!-- Export Excel Rekap -->
                <a href="{{ route('guru.penilaian.export', $package) }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer"
                   title="Ekspor Rekapitulasi Nilai Seluruh Sumatif">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export Rekap</span>
                </a>

                <!-- Cetak PDF Resmi Ber-Kop Surat -->
                <a href="{{ route('guru.penilaian.print', $package) }}" target="_blank"
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer"
                   title="Cetak Laporan Rekap Nilai Resmi Ber-Kop Surat">
                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak PDF</span>
                </a>

                @php
                    $isOwner = $package->teacher_id === auth()->user()->teacher?->id;
                @endphp

                @if($isOwner)
                    @if(! $package->isLocked())
                        <!-- Tombol Kunci Nilai -->
                        <form action="{{ route('guru.penilaian.lock', $package) }}" method="POST" 
                              onsubmit="return confirm('Kunci paket nilai ini? Setelah dikunci, data nilai menjadi final, tidak dapat diubah lagi oleh guru, dan langsung tampil pada portal Siswa & Orang Tua.');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Finalisasi & Kunci</span>
                            </button>
                        </form>

                        <!-- Tombol Hapus Paket (Draft) -->
                        <form action="{{ route('guru.penilaian.destroy', $package) }}" method="POST"
                              onsubmit="return confirm('Hapus paket penilaian \'{{ addslashes($package->title) }}\'?\n\nSeluruh data nilai sumatif (SUM 1 s.d. SUM 15) di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="neo-btn bg-[#FF6B6B] hover:bg-red-600 text-white px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer transition-colors"
                                    title="Hapus Paket Penilaian">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus Paket</span>
                            </button>
                        </form>
                    @else
                        <!-- Tombol Hapus (Disabled - Info Buka Kunci) -->
                        <button type="button" 
                                onclick="showLockedModal('{{ addslashes($package->title) }}')"
                                class="neo-btn bg-slate-200 hover:bg-slate-300 text-slate-600 px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#94a3b8] border-2 border-slate-400 rounded-lg cursor-pointer transition-colors"
                                title="Paket Terkunci (Klik untuk info membuka kunci)">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Hapus (Terkunci)</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Siswa</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $totalStudents }}</div>
            <div class="text-[10px] text-slate-600 mt-0.5">Siswa terdaftar di rombel</div>
        </div>
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Tuntas (&ge; {{ $package->kktp_default }})</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $tuntasCount }}</div>
            <div class="text-[10px] text-emerald-900 mt-0.5">Mencapai target KKTP</div>
        </div>
        <div class="bg-[#FFE3E3] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Remedial (&lt; {{ $package->kktp_default }})</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $remedialCount }}</div>
            <div class="text-[10px] text-rose-900 mt-0.5">Perlu bimbingan remedial</div>
        </div>
        <div class="bg-slate-50 border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Belum Dinilai</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $belumDinilaiCount }}</div>
            <div class="text-[10px] text-slate-600 mt-0.5">Belum ada sumatif terisi</div>
        </div>
    </div>

    <!-- Matriks Rekap Nilai Siswa Table -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="p-4 bg-slate-100 border-b-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span>Matriks Capaian Sumatif Aktif ({{ $activeAssessments->count() }} Penilaian)</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-white border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                        S1 @if($activeAssessments->count() > 1) s.d. S{{ $activeAssessments->max('sheet_number') }} @endif
                    </span>
                </h3>
                <p class="text-xs text-slate-600">
                    Menampilkan kolom penilaian aktif. Nilai baru dari upload atau input web akan tersimpan secara bertahap tanpa menimpa nilai lama.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(! $package->isLocked() && $nextSheetNum !== null)
                    <button type="button" onclick="openAddSumatifModal()"
                            class="neo-btn bg-[#FF922B] hover:bg-[#f76707] text-black px-3 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] border-2 border-black rounded cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>+ Tambah S{{ $nextSheetNum }}</span>
                    </button>
                @endif
                <span class="text-[10px] font-bold bg-white text-slate-700 border border-slate-300 px-2 py-1 rounded">
                    Keterangan: Hijau (&ge; KKTP) | Merah (&lt; KKTP)
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b-2 border-black text-[11px] font-black uppercase text-black tracking-wider">
                        <th class="py-3 px-3 border-r-2 border-black w-10 text-center">No</th>
                        <th class="py-3 px-3 border-r-2 border-black w-24">NIS</th>
                        <th class="py-3 px-3 border-r-2 border-black min-w-[160px]">Nama Siswa</th>
                        @foreach($activeAssessments as $asm)
                            @php
                                $i = $asm->sheet_number;
                                $hasScore = ($sheetAverages[$i] ?? null) !== null;
                            @endphp
                            <th class="py-2 px-2 border-r border-slate-300 text-center min-w-[55px] {{ $hasScore ? 'bg-amber-50/60' : '' }}" title="{{ $asm->materi ? 'Materi: ' . $asm->materi : 'SUM ' . $i }}">
                                <div class="font-black text-black">S{{ $i }}</div>
                                <div class="text-[9px] font-normal text-slate-500">KKTP {{ $asm->kktp ?? $package->kktp_default }}</div>
                            </th>
                        @endforeach
                        <th class="py-3 px-3 border-l-2 border-black text-center w-20 bg-slate-100">Rata-Rata</th>
                        <th class="py-3 px-3 border-l border-slate-300 text-center w-24 bg-slate-100">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($students as $idx => $student)
                        @php
                            $avg = $studentAverages[$student->id];
                            $status = $avg !== null ? ($avg >= $package->kktp_default ? 'Tuntas' : 'Remedial') : 'Belum Dinilai';
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 px-3 border-r-2 border-black text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3 border-r-2 border-black font-mono text-slate-600">{{ $student->nis ?? '-' }}</td>
                            <td class="py-2.5 px-3 border-r-2 border-black font-bold text-black whitespace-nowrap">{{ $student->user?->name ?? '-' }}</td>

                            @foreach($activeAssessments as $asm)
                                @php
                                    $i = $asm->sheet_number;
                                    $scoreObj = $asm->scores->firstWhere('student_id', $student->id);
                                    $sc = $scoreObj?->score;
                                    $isRemed = $sc !== null && $sc < ($asm->kktp ?? $package->kktp_default);
                                    $isTuntas = $sc !== null && $sc >= ($asm->kktp ?? $package->kktp_default);
                                @endphp
                                <td class="py-2 px-1 border-r border-slate-200 text-center font-bold {{ $sc !== null ? ($isRemed ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700') : 'text-slate-300' }}">
                                    @if(! $package->isLocked())
                                        <button type="button" 
                                                onclick="editQuickScore({{ $asm->id }}, {{ $student->id }}, '{{ addslashes($student->user?->name ?? 'Siswa') }}', 'SUM {{ $i }}', {{ $sc !== null ? $sc : 'null' }})"
                                                class="w-full py-1 rounded hover:bg-black/10 cursor-pointer font-mono text-xs">
                                            {{ $sc !== null ? (float)$sc : '-' }}
                                        </button>
                                    @else
                                        <span class="font-mono text-xs">{{ $sc !== null ? (float)$sc : '-' }}</span>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Rata-rata Siswa -->
                            <td class="py-2.5 px-3 border-l-2 border-black text-center font-mono font-black text-xs {{ $avg !== null ? ($avg >= $package->kktp_default ? 'text-emerald-700' : 'text-rose-600') : 'text-slate-400' }}">
                                {{ $avg !== null ? $avg : '-' }}
                            </td>

                            <!-- Status Siswa -->
                            <td class="py-2.5 px-3 border-l border-slate-300 text-center">
                                @if($avg !== null)
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded border {{ $avg >= $package->kktp_default ? 'bg-emerald-100 text-emerald-950 border-emerald-400' : 'bg-rose-100 text-rose-950 border-rose-400' }}">
                                        {{ $status }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeAssessments->count() + 5 }}" class="py-8 text-center text-slate-500 font-bold">
                                Tidak ada siswa yang terdaftar di kelas rombel ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer Rata-Rata per Lembar -->
                <tfoot>
                    <tr class="bg-slate-100 border-t-2 border-black font-black text-[11px] text-black">
                        <td colspan="3" class="py-3 px-3 border-r-2 border-black text-right uppercase">Rata-Rata Nilai Sumatif:</td>
                        @foreach($activeAssessments as $asm)
                            <td class="py-3 px-1 border-r border-slate-300 text-center font-mono text-xs">
                                {{ ($sheetAverages[$asm->sheet_number] ?? null) !== null ? $sheetAverages[$asm->sheet_number] : '-' }}
                            </td>
                        @endforeach
                        <td colspan="2" class="border-l-2 border-black bg-slate-200"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Upload Excel -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border-2 border-black rounded-lg shadow-[6px_6px_0px_0px_#000] max-w-md w-full overflow-hidden">
        <div class="bg-[#5294FF] border-b-2 border-black p-4 flex items-center justify-between text-white">
            <h3 class="font-heading font-black text-base">Unggah File Nilai Excel</h3>
            <button type="button" onclick="closeUploadModal()" class="text-white hover:text-black font-black text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('guru.penilaian.upload', $package) }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-1">
                    Pilih File Excel (.xlsx / .xls)
                </label>
                <input type="file" name="file" required accept=".xlsx,.xls"
                       class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 file:mr-3 file:py-1 file:px-3 file:rounded file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419]">
                <p class="text-[11px] text-slate-500 mt-1">
                    Pastikan file berasal dari template resmi 15 sheet (SUM 1 s.d. SUM 15). Maksimal ukuran file 5 MB.
                </p>
            </div>

            <div class="p-3 bg-amber-50 border border-amber-300 rounded text-[11px] text-amber-950 font-medium">
                <strong>Catatan Alur Ponytail:</strong> File akan divalidasi terlebih dahulu di memori dan disajikan pada halaman pratinjau sebelum disimpan permanen ke database.
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="closeUploadModal()" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-black">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-4 py-2 text-xs font-black rounded border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Unggah & Periksa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Quick Edit Score -->
<div id="quickEditModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border-2 border-black rounded-lg shadow-[6px_6px_0px_0px_#000] max-w-sm w-full overflow-hidden">
        <div class="bg-[#FFD43B] border-b-2 border-black p-4 flex items-center justify-between text-black">
            <h3 class="font-heading font-black text-sm" id="modalStudentTitle">Edit Nilai Siswa</h3>
            <button type="button" onclick="closeQuickEditModal()" class="text-black hover:text-slate-700 font-black text-xl leading-none">&times;</button>
        </div>
        <form id="quickEditForm" onsubmit="submitQuickScore(event)" class="p-5 space-y-4">
            <input type="hidden" id="quickAssessmentId">
            <input type="hidden" id="quickStudentId">

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-1">
                    Input Perolehan Nilai (0 s.d. 100)
                </label>
                <input type="number" step="0.01" min="0" max="100" id="quickScoreInput"
                       placeholder="Kosongkan jika belum dinilai"
                       class="w-full text-base font-black border-2 border-black p-2.5 rounded bg-white text-center focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                <p class="text-[10px] text-slate-500 mt-1">
                    Kosongkan kolom bila ingin me-reset status siswa menjadi belum dinilai.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="closeQuickEditModal()" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-black">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-black rounded border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Simpan Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function editQuickScore(assessmentId, studentId, studentName, sumTitle, currentScore) {
    document.getElementById('quickAssessmentId').value = assessmentId;
    document.getElementById('quickStudentId').value = studentId;
    document.getElementById('quickScoreInput').value = currentScore !== null ? currentScore : '';
    document.getElementById('modalStudentTitle').innerText = `${sumTitle} - ${studentName}`;
    document.getElementById('quickEditModal').classList.remove('hidden');
    document.getElementById('quickScoreInput').focus();
}

function closeQuickEditModal() {
    document.getElementById('quickEditModal').classList.add('hidden');
}

async function submitQuickScore(e) {
    e.preventDefault();
    const assessmentId = document.getElementById('quickAssessmentId').value;
    const studentId = document.getElementById('quickStudentId').value;
    const scoreVal = document.getElementById('quickScoreInput').value;

    try {
        const response = await fetch("{{ route('guru.penilaian.quick-update', $package) }}", {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                assessment_id: assessmentId,
                student_id: studentId,
                score: scoreVal !== '' ? scoreVal : null
            })
        });

        const res = await response.json();
        if (res.success) {
            closeQuickEditModal();
            window.location.reload();
        } else {
            alert(res.message || 'Gagal menyimpan nilai.');
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi server.');
    }
}

function showLockedModal(title) {
    document.getElementById('lockedModalPackageTitle').textContent = title;
    document.getElementById('lockedModal').classList.remove('hidden');
}

function closeLockedModal() {
    document.getElementById('lockedModal').classList.add('hidden');
}

function openAddSumatifModal() {
    document.getElementById('addSumatifModal')?.classList.remove('hidden');
}

function closeAddSumatifModal() {
    document.getElementById('addSumatifModal')?.classList.add('hidden');
}

// Close on background click
document.getElementById('lockedModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeLockedModal();
    }
});

document.getElementById('addSumatifModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddSumatifModal();
    }
});
</script>

@if(! $package->isLocked() && $nextSheetNum !== null)
<!-- Modal Tambah Sumatif Baru -->
<div id="addSumatifModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border-2 border-black rounded-lg shadow-[6px_6px_0px_0px_#000] max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-[#FF922B] border-b-2 border-black p-4 flex items-center justify-between text-black">
            <h3 class="font-heading font-black text-base">+ Aktifkan Lembar Penilaian Sumatif {{ $nextSheetNum }} (SUM {{ $nextSheetNum }})</h3>
            <button type="button" onclick="closeAddSumatifModal()" class="text-black hover:text-slate-800 font-black text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('guru.penilaian.add-assessment', $package) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="sheet_number" value="{{ $nextSheetNum }}">

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-1">
                    Nomor Lembar Sumatif
                </label>
                <div class="p-2.5 bg-slate-100 border-2 border-black rounded font-mono font-bold text-sm text-black flex items-center justify-between">
                    <span>SUM {{ $nextSheetNum }} (S{{ $nextSheetNum }})</span>
                    <span class="text-[11px] px-2 py-0.5 bg-white border border-black rounded font-sans">Kolom Ke-{{ $activeAssessments->count() + 1 }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-1">
                    Nama Materi / Lingkup Materi <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                </label>
                <input type="text" name="materi" placeholder="Contoh: Bab 2 - Operasi Aljabar"
                       class="w-full text-xs font-bold border-2 border-black p-2.5 rounded bg-white focus:outline-none focus:ring-2 focus:ring-[#FF922B]">
            </div>

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-1">
                    Standar KKTP
                </label>
                <input type="number" name="kktp" min="0" max="100" value="{{ $package->kktp_default }}" required
                       class="w-full text-xs font-bold border-2 border-black p-2.5 rounded bg-white focus:outline-none focus:ring-2 focus:ring-[#FF922B]">
                <p class="text-[10px] text-slate-500 mt-1">
                    Default mengikuti standar paket penilaian: {{ $package->kktp_default }}.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="closeAddSumatifModal()" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-black">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FF922B] hover:bg-[#f76707] text-black px-4 py-2 text-xs font-black rounded border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Aktifkan Kolom S{{ $nextSheetNum }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal Dialog Info Paket Terkunci (Neo-brutalism) -->
<div id="lockedModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border-3 border-black rounded-xl max-w-md w-full p-6 shadow-[6px_6px_0px_0px_#000] space-y-4 animate-in fade-in zoom-in duration-150">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-lg bg-[#FFF9DB] border-2 border-black flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-900 bg-[#FFF9DB] px-2 py-0.5 rounded border border-black">
                    Status: Terkunci (Final)
                </span>
                <h3 class="font-heading font-black text-lg text-black mt-1">Paket Nilai Telah Dikunci</h3>
                <p id="lockedModalPackageTitle" class="text-xs font-bold text-slate-700"></p>
            </div>
        </div>

        <div class="bg-slate-50 border-2 border-black rounded-lg p-3 text-xs text-slate-700 space-y-2.5 leading-relaxed">
            <p>
                Paket penilaian ini telah <strong>dikunci (final)</strong> dan nilai siswa telah resmi ditampilkan pada portal Siswa & Orang Tua.
            </p>
            <div class="p-2.5 bg-[#FFF0F6] border border-rose-300 rounded font-medium text-rose-950 flex items-start gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    Untuk menjaga integritas data rapor, paket nilai yang telah dikunci <strong>tidak dapat dihapus atau diubah</strong> langsung oleh Guru.
                </span>
            </div>
            <div class="p-2.5 bg-[#E7F5FF] border border-blue-300 rounded text-blue-950 flex items-start gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>
                    <strong>Cara Menghapus / Merevisi:</strong> Silakan hubungi <strong>Administrator Sekolah</strong> untuk meminta pembukaan kunci (<em>Unlock</em>). Setelah kunci dibuka, tombol hapus dan tombol edit akan aktif kembali.
                </span>
            </div>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="button" onclick="closeLockedModal()"
                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2.5 text-xs font-black rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                Mengerti & Tutup
            </button>
        </div>
    </div>
</div>
@endsection
