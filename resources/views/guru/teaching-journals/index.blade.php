@extends('layouts.guru')

@section('title', 'Jurnal Kegiatan Mengajar Guru')
@section('page-title', 'Jurnal Kegiatan Guru')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>Aktivitas KBM</span>
                <span>/</span>
                <span class="text-black">Jurnal Harian Mengajar</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Jurnal Harian Guru Mengajar</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Catatan agenda pembelajaran, tujuan KBM, presensi kelas terintegrasi, dan evaluasi proses mengajar.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Unduh Template Excel -->
            <a href="{{ route('guru.teaching-journals.template') }}" 
               class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] cursor-pointer"
               title="Unduh Template Excel sesuai jadwal mengajar Anda">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Template</span>
            </a>

            <!-- Tombol Upload Excel -->
            <button type="button" 
                    onclick="openUploadModal()"
                    class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] cursor-pointer"
                    title="Unggah file Excel jurnal KBM">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Upload Excel</span>
            </button>

            <!-- Tombol Cetak Dokumen PDF Resmi -->
            <a href="{{ route('guru.teaching-journals.print', request()->all()) }}" 
               target="_blank"
               class="neo-btn bg-white hover:bg-slate-100 text-black px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak PDF</span>
            </a>

            <!-- Tombol Buat Jurnal Baru (Primary CTA) -->
            <a href="{{ route('guru.teaching-journals.create') }}" 
               class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-black flex items-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Isi Jurnal Baru</span>
            </a>
        </div>
    </div>

    <!-- 3 Kartu Metrik KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-[#FFF4E6] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-amber-900">Total Jurnal Disimpan</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($totalJournals) }}</div>
            <div class="text-[11px] text-amber-800 font-medium mt-0.5">Seluruh aktivitas KBM Anda</div>
        </div>
        <div class="bg-[#E7F5FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-blue-900">Jurnal Bulan Ini</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($thisMonthJournals) }}</div>
            <div class="text-[11px] text-blue-800 font-medium mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
        </div>
        <div class="bg-[#E6FCF5] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-900">Rata-rata Kehadiran Siswa</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($avgAttendance, 1) }}%</div>
            <div class="text-[11px] text-emerald-800 font-medium mt-0.5">Persentase kehadiran di kelas Anda</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <form method="GET" action="{{ route('guru.teaching-journals.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Kelas</label>
                <select name="school_class_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)$subjectId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black text-xs font-black py-2.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Filter
                </button>
                @if($classId || $subjectId || $startDate || $endDate)
                    <a href="{{ route('guru.teaching-journals.index') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold py-2.5 px-3 shadow-[2px_2px_0px_0px_#000]" title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Jurnal 9 Kolom Sesuai Format Resmi + Aksi Icon Tooltip & Quick Share -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b-2 border-black flex items-center justify-between">
            <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                Daftar Rekam KBM Harian
            </span>
            <span class="text-xs text-slate-500 font-bold">Total: {{ $journals->total() }} Catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-black uppercase tracking-wider font-heading">
                        <th class="p-3 text-center border-r border-black w-10">No</th>
                        <th class="p-3 border-r border-black min-w-[130px]">Hari / Tanggal</th>
                        <th class="p-3 text-center border-r border-black min-w-[90px]">Kelas</th>
                        <th class="p-3 text-center border-r border-black w-20">Pertemuan</th>
                        <th class="p-3 border-r border-black min-w-[200px]">Tujuan Pembelajaran</th>
                        <th class="p-3 border-r border-black min-w-[220px]">Kegiatan KBM</th>
                        <th class="p-3 text-center border-r border-black min-w-[100px]">Absensi (S, I, A)</th>
                        <th class="p-3 text-center border-r border-black min-w-[90px]">% Hadir</th>
                        <th class="p-3 border-r border-black min-w-[180px]">Permasalahan KBM</th>
                        <th class="p-3 text-center w-36">Aksi & Visibilitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/20">
                    @forelse($journals as $index => $journal)
                    <tr class="hover:bg-amber-50/50 transition-colors">
                        <td class="p-3 text-center font-bold border-r border-black/20">
                            {{ $journals->firstItem() + $index }}
                        </td>
                        <td class="p-3 border-r border-black/20">
                            <div class="font-black text-black">{{ $journal->day_name }}</div>
                            <div class="font-mono text-[11px] text-slate-600">{{ $journal->formatted_date }}</div>
                            <div class="text-[10px] font-bold text-blue-700 mt-0.5">{{ $journal->subject?->name }}</div>
                        </td>
                        <td class="p-3 text-center font-black border-r border-black/20">
                            <span class="inline-block bg-slate-100 px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                                {{ $journal->schoolClass?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3 text-center font-mono font-black text-sm border-r border-black/20">
                            Ke-{{ $journal->meeting_number }}
                        </td>
                        <td class="p-3 border-r border-black/20 font-medium">
                            <div class="line-clamp-3">{{ $journal->learning_objective }}</div>
                        </td>
                        <td class="p-3 border-r border-black/20 font-medium">
                            <div class="line-clamp-3">{{ $journal->teaching_activity }}</div>
                        </td>
                        <td class="p-3 text-center border-r border-black/20">
                            <div class="flex items-center justify-center gap-1.5 font-mono text-[11px]">
                                <span class="bg-amber-100 border border-black px-1.5 py-0.2 rounded font-black text-amber-950" title="Sakit: {{ $journal->count_sakit }}">
                                    S: {{ $journal->count_sakit }}
                                </span>
                                <span class="bg-blue-100 border border-black px-1.5 py-0.2 rounded font-black text-blue-950" title="Izin: {{ $journal->count_izin }}">
                                    I: {{ $journal->count_izin }}
                                </span>
                                <span class="bg-rose-100 border border-black px-1.5 py-0.2 rounded font-black text-rose-950" title="Alpa: {{ $journal->count_alpa }}">
                                    A: {{ $journal->count_alpa }}
                                </span>
                            </div>
                        </td>
                        <td class="p-3 text-center border-r border-black/20">
                            <span class="inline-block px-2 py-0.5 border border-black rounded text-[11px] font-mono font-black {{ $journal->attendance_percentage >= 80 ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-[#FFE3E3] text-rose-950' }}">
                                {{ number_format($journal->attendance_percentage, 1) }}%
                            </span>
                        </td>
                        <td class="p-3 border-r border-black/20 text-slate-700">
                            @if($journal->teaching_problem)
                                <div class="line-clamp-2 text-rose-900 font-medium italic">
                                    {{ $journal->teaching_problem }}
                                </div>
                            @else
                                <span class="text-slate-400 font-bold">-</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Aksi Cepat Tampilkan/Sembunyikan dari Siswa -->
                                <form action="{{ route('guru.teaching-journals.toggle-share', $journal) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($journal->is_shared_with_students)
                                        <button type="submit" 
                                                class="p-1.5 bg-[#D3F9D8] hover:bg-[#b2f2bb] text-emerald-950 border border-black rounded shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                title="Status: Tampil di Siswa (Klik untuk sembunyikan)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                    @else
                                        <button type="submit" 
                                                class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-500 border border-black rounded shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                title="Status: Disembunyikan dari Siswa (Klik untuk tampilkan)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                            </svg>
                                        </button>
                                    @endif
                                </form>

                                <!-- Tombol Pensil Edit Tanpa Text dengan Tooltip -->
                                <a href="{{ route('guru.teaching-journals.edit', $journal) }}" 
                                   class="p-1.5 bg-[#FFD43B] hover:bg-[#fcc419] text-black border border-black rounded shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                   title="Edit Jurnal">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>

                                <!-- Tombol Hapus Tanpa Text dengan Tooltip -->
                                <form action="{{ route('guru.teaching-journals.destroy', $journal) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan jurnal ini?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 bg-rose-400 hover:bg-rose-500 text-black border border-black rounded shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                            title="Hapus Jurnal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-slate-500">
                            <div class="text-3xl mb-2">📋</div>
                            <div class="font-bold text-sm text-black">Belum Ada Catatan Jurnal Mengajar</div>
                            <p class="text-xs text-slate-500 mt-1">
                                Klik tombol <strong>+ Isi Jurnal Baru</strong> atau <strong>Upload Excel</strong> untuk mencatat aktivitas KBM Anda.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journals->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Upload Excel Jurnal KBM -->
<div id="uploadExcelModal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white border-3 border-black rounded-lg shadow-[6px_6px_0px_0px_#000] max-w-md w-full p-5 space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-2.5">
            <div class="flex items-center gap-2">
                <span class="text-xl">📊</span>
                <h3 class="font-heading font-black text-base text-black">Upload Jurnal Excel</h3>
            </div>
            <button type="button" onclick="closeUploadModal()" class="text-black font-black text-lg hover:opacity-70 cursor-pointer">✕</button>
        </div>

        <form action="{{ route('guru.teaching-journals.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1">
                    Pilih File Excel (.xlsx / .xls)
                </label>
                <input type="file" 
                       name="file" 
                       accept=".xlsx,.xls,.csv" 
                       required 
                       class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white cursor-pointer">
                <p class="text-[11px] text-slate-500 mt-1">
                    Gunakan file yang diunduh dari tombol <strong>Unduh Template</strong> di atas agar jadwal dan format kolom terisi akurat.
                </p>
            </div>

            <div class="bg-amber-50 border-2 border-amber-300 p-3 rounded text-xs text-amber-950 font-medium">
                💡 Setelah file diunggah, Anda akan diarahkan ke form pratinjau untuk memeriksa dan mengedit data sebelum menekan tombol <strong>Publish</strong>.
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t-2 border-black/10">
                <button type="button" onclick="closeUploadModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2 text-xs font-bold">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-5 py-2 text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                    Upload & Pratinjau →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal() {
        document.getElementById('uploadExcelModal').classList.remove('hidden');
    }
    function closeUploadModal() {
        document.getElementById('uploadExcelModal').classList.add('hidden');
    }
</script>
@endsection
