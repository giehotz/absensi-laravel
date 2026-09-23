@extends('layouts.admin')

@section('title', 'Kenaikan Kelas & Kelulusan Siswa')
@section('page-title', 'Kenaikan Kelas & Kelulusan Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-[#FFD43B] neo-box p-6 border-4 border-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="neo-badge bg-black text-white font-black text-xs">ADMINISTRASI ROMBEL</span>
                <span class="text-xs font-mono font-bold bg-white px-2.5 py-0.5 border-2 border-black">
                    T.A. Asal: {{ $sourceAcademicYear->name ?? '-' }} ({{ ucfirst($sourceAcademicYear->semester ?? '') }})
                </span>
                <span class="text-xs font-black">➔</span>
                <span class="text-xs font-mono font-bold bg-[#D3F9D8] text-emerald-950 px-2.5 py-0.5 border-2 border-black">
                    T.A. Tujuan: {{ $targetAcademicYear->name ?? '-' }} ({{ ucfirst($targetAcademicYear->semester ?? '') }})
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black text-black uppercase tracking-tight">
                Kenaikan Kelas & Kelulusan Siswa
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-3xl leading-relaxed">
                Kelola pemindahan rombongan belajar antar tahun ajaran secara massal. Tinjau kelayakan berdasarkan persentase kehadiran siswa, tentukan kelas tujuan baru, atau luluskan siswa tingkat akhir menjadi alumni.
            </p>
        </div>

        <div class="z-10 shrink-0 flex items-center gap-2">
            <a href="{{ route('admin.students.index') }}" 
               class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-4 py-2 flex items-center gap-2 shadow-[2px_2px_0px_#000]">
                <span>←</span> Kembali ke Data Siswa
            </a>
        </div>
    </div>

    <!-- Panel Pengaturan Tahun Ajaran & Salin Struktur Kelas -->
    <div class="bg-white neo-box p-5 border-3 border-black space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b-2 border-black pb-4">
            <!-- Form Pemilih Tahun Ajaran Asal & Tujuan -->
            <form method="GET" action="{{ route('admin.students.promotion.index') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-600 mb-1">Tahun Ajaran Asal:</label>
                    <select name="source_academic_year_id" onchange="this.form.submit()" 
                            class="bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $sourceAcademicYear?->id == $ay->id ? 'selected' : '' }}>
                                {{ $ay->name }} — Semester {{ ucfirst($ay->semester) }} {{ $ay->is_active ? '★ (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hidden sm:block text-base font-black pt-4">➔</div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-600 mb-1">Tahun Ajaran Tujuan (Kenaikan):</label>
                    <select name="target_academic_year_id" onchange="this.form.submit()" 
                            class="bg-[#D3F9D8] border-2 border-black px-3 py-1.5 text-xs font-black text-emerald-950 focus:outline-hidden">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $targetAcademicYear?->id == $ay->id ? 'selected' : '' }}>
                                {{ $ay->name }} — Semester {{ ucfirst($ay->semester) }} {{ $ay->is_active ? '★ (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($selectedSourceClass)
                    <input type="hidden" name="source_class_id" value="{{ $selectedSourceClass->id }}">
                @endif
            </form>

            <!-- Aksi Pembantu Struktur Kelas Baru -->
            <div class="flex items-center gap-2 flex-wrap">
                @if($sourceAcademicYear && $targetAcademicYear && $sourceAcademicYear->id !== $targetAcademicYear->id)
                    <form method="POST" action="{{ route('admin.students.promotion.copy-classes') }}" 
                          onsubmit="return confirm('Salin seluruh daftar nama kelas dari {{ $sourceAcademicYear->name }} ke {{ $targetAcademicYear->name }}?')">
                        @csrf
                        <input type="hidden" name="from_academic_year_id" value="{{ $sourceAcademicYear->id }}">
                        <input type="hidden" name="to_academic_year_id" value="{{ $targetAcademicYear->id }}">
                        <button type="submit" 
                                class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-3 py-2 flex items-center gap-1.5 shadow-[2px_2px_0px_#000] cursor-pointer"
                                title="Salin struktur kelas jika tahun ajaran tujuan belum memiliki kelas">
                            <span>📋</span> Salin Struktur Kelas ke T.A. Baru
                        </button>
                    </form>
                @endif

                <button type="button" onclick="openQuickClassModal()" 
                        class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-black px-3 py-2 flex items-center gap-1.5 shadow-[2px_2px_0px_#000] cursor-pointer">
                    <span>➕</span> Tambah Rombel Cepat
                </button>
            </div>
        </div>

        <!-- Pemilih Rombel / Kelas Asal -->
        <div class="space-y-2">
            <div class="text-xs font-black uppercase text-black flex items-center justify-between">
                <span>Pilih Kelas Asal Siswa:</span>
                <span class="text-[11px] font-mono text-slate-500 font-semibold">{{ $sourceClasses->count() }} Kelas Tersedia</span>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @forelse($sourceClasses as $sc)
                    <a href="{{ route('admin.students.promotion.index', [
                            'source_academic_year_id' => $sourceAcademicYear?->id,
                            'target_academic_year_id' => $targetAcademicYear?->id,
                            'source_class_id' => $sc->id
                        ]) }}" 
                       class="neo-btn text-xs font-bold px-3 py-2 shrink-0 flex items-center gap-2 transition-all
                       {{ $selectedSourceClass?->id == $sc->id 
                            ? 'bg-black text-white shadow-[3px_3px_0px_0px_#FFD43B] font-black' 
                            : 'bg-white text-black hover:bg-slate-100 shadow-[2px_2px_0px_#000]' }}">
                        <span>{{ $sc->name }}</span>
                        <span class="px-1.5 py-0.2 text-[10px] rounded-xs font-mono font-bold
                            {{ $selectedSourceClass?->id == $sc->id ? 'bg-[#FFD43B] text-black' : 'bg-slate-200 text-slate-800' }}">
                            {{ $sc->students_count }} Siswa
                        </span>
                    </a>
                @empty
                    <div class="p-3 bg-amber-50 border-2 border-amber-300 text-amber-900 text-xs font-semibold rounded-md">
                        Belum ada kelas yang terdaftar pada Tahun Ajaran Asal ({{ $sourceAcademicYear->name ?? '-' }}). Silakan buat kelas di Master Data Kelas terlebih dahulu.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($selectedSourceClass)
        <!-- ========================================================================= -->
        <!-- FORM EKSEKUSI KENAIKAN KELAS / KELULUSAN SISWA -->
        <!-- ========================================================================= -->
        <form id="promotionForm" method="POST" action="{{ route('admin.students.promotion.process') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="source_class_id" value="{{ $selectedSourceClass->id }}">
            <input type="hidden" name="source_academic_year_id" value="{{ $sourceAcademicYear?->id }}">
            <input type="hidden" name="target_academic_year_id" value="{{ $targetAcademicYear?->id }}">

            <!-- Header Info Kelas Terpilih & Action Bar Atas -->
            <div class="bg-white neo-box p-4 border-3 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-heading font-black text-base text-black uppercase flex items-center gap-2">
                        <span>👥</span> Siswa Kelas: {{ $selectedSourceClass->name }}
                    </h3>
                    <p class="text-xs text-slate-600 font-medium">
                        Total <strong>{{ $students->count() }} siswa</strong> terdaftar pada rombel ini.
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" onclick="selectAllStudents(true)" 
                            class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3 py-1.5 shadow-[1.5px_1.5px_0px_#000]">
                        ✓ Centang Semua
                    </button>
                    <button type="button" onclick="selectAllStudents(false)" 
                            class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3 py-1.5 shadow-[1.5px_1.5px_0px_#000]">
                        ✕ Batal Pilih
                    </button>
                </div>
            </div>

            <!-- Tabel Daftar Siswa dengan Indikator Presensi -->
            <div class="bg-white neo-box border-3 border-black overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                            <tr>
                                <th class="p-3 w-10 text-center border-r-2 border-black">
                                    <input type="checkbox" id="headerCheckbox" onchange="toggleHeaderCheckbox(this)" 
                                           class="w-4 h-4 accent-black cursor-pointer">
                                </th>
                                <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                                <th class="p-3 border-r-2 border-black min-w-[200px]">Identitas Siswa</th>
                                <th class="p-3 w-14 text-center border-r-2 border-black">L/P</th>
                                <th class="p-3 text-center border-r-2 border-black w-20 text-emerald-900 bg-emerald-50">Hadir</th>
                                <th class="p-3 text-center border-r-2 border-black w-16 text-blue-900 bg-blue-50">Telat</th>
                                <th class="p-3 text-center border-r-2 border-black w-16 text-slate-800 bg-slate-50">Izin</th>
                                <th class="p-3 text-center border-r-2 border-black w-16 text-amber-900 bg-amber-50">Sakit</th>
                                <th class="p-3 text-center border-r-2 border-black w-16 text-rose-900 bg-rose-50">Alpa</th>
                                <th class="p-3 text-center min-w-[140px]">Tingkat Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black font-medium">
                            @forelse($students as $idx => $st)
                                @php
                                    $rate = $st->att_rate ?? 0;
                                @endphp
                                <tr class="hover:bg-amber-50/50 transition-colors">
                                    <td class="p-3 text-center border-r-2 border-black">
                                        <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" 
                                               class="student-checkbox w-4 h-4 accent-black cursor-pointer"
                                               onchange="updateSelectedCounter()">
                                    </td>
                                    <td class="p-3 text-center font-bold font-mono border-r-2 border-black">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-sm bg-slate-200 border border-black overflow-hidden shrink-0 flex items-center justify-center font-bold text-xs">
                                                @if($st->photo_url)
                                                    <img src="{{ $st->photo_url }}" alt="" class="w-full h-full object-cover">
                                                @else
                                                    <span>👤</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-heading font-black text-black text-sm">{{ $st->user->name ?? '-' }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono">
                                                    NIS: <strong>{{ $st->nis }}</strong> • NISN: {{ $st->nisn ?: '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center font-bold border-r-2 border-black">
                                        {{ $st->gender }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-emerald-800 bg-emerald-50/40 border-r-2 border-black">
                                        {{ $st->att_hadir ?? 0 }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-blue-800 bg-blue-50/40 border-r-2 border-black">
                                        {{ $st->att_terlambat ?? 0 }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-slate-700 bg-slate-50/40 border-r-2 border-black">
                                        {{ $st->att_izin ?? 0 }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-amber-800 bg-amber-50/40 border-r-2 border-black">
                                        {{ $st->att_sakit ?? 0 }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-rose-800 bg-rose-50/40 border-r-2 border-black">
                                        {{ $st->att_alpa ?? 0 }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="font-mono font-black text-xs {{ $rate >= 85 ? 'text-emerald-700' : ($rate >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                                {{ $rate }}%
                                            </span>
                                            <span class="neo-badge text-[9px] px-1 py-0 border border-black
                                                {{ $rate >= 85 ? 'bg-[#D3F9D8] text-emerald-950' : ($rate >= 70 ? 'bg-[#FFF3BF] text-amber-950' : 'bg-[#FFE3E3] text-rose-950') }}">
                                                {{ $rate >= 85 ? 'Baik' : ($rate >= 70 ? 'Cukup' : 'Perhatian') }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                                        <div class="text-3xl">📭</div>
                                        <div class="font-bold text-slate-800">Belum ada data siswa di kelas ini.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel Konfigurasi Kenaikan / Kelulusan (Floating / Sticky Action Bar) -->
            @if($students->isNotEmpty())
                <div class="bg-white neo-box-lg p-5 sm:p-6 border-4 border-black space-y-4 shadow-[6px_6px_0px_0px_#000]">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-black pb-4">
                        <div>
                            <h4 class="font-heading font-black text-sm uppercase text-black flex items-center gap-2">
                                <span>🎯</span> Konfigurasi Aksi Kenaikan / Kelulusan
                            </h4>
                            <p class="text-xs text-slate-600 font-semibold">
                                Tentukan apakah siswa yang dicentang akan dinaikkan ke kelas berikutnya atau diluluskan.
                            </p>
                        </div>
                        <div class="bg-[#FFF3BF] border-2 border-black px-3.5 py-1.5 rounded-sm font-mono font-black text-xs text-black">
                            <span id="selectedCounter">0</span> dari {{ $students->count() }} Siswa Terpilih
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Pilihan Aksi: Naik Kelas vs Lulus -->
                        <div class="space-y-2">
                            <label class="block text-xs font-black uppercase text-black">1. Pilih Jenis Aksi:</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="border-2 border-black p-3 rounded-sm flex items-center gap-2.5 cursor-pointer has-[:checked]:bg-[#D3F9D8] has-[:checked]:shadow-[2px_2px_0px_#000] bg-white transition-all">
                                    <input type="radio" name="action_type" value="promote" checked onchange="toggleActionType(this.value)" class="w-4 h-4 accent-black">
                                    <div>
                                        <div class="font-heading font-black text-xs text-black uppercase">Naik Kelas</div>
                                        <div class="text-[10px] text-slate-600 font-semibold">Pindah ke rombel baru</div>
                                    </div>
                                </label>

                                <label class="border-2 border-black p-3 rounded-sm flex items-center gap-2.5 cursor-pointer has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[2px_2px_0px_#000] bg-white transition-all">
                                    <input type="radio" name="action_type" value="graduate" onchange="toggleActionType(this.value)" class="w-4 h-4 accent-black">
                                    <div>
                                        <div class="font-heading font-black text-xs text-rose-950 uppercase">Lulus (Alumni)</div>
                                        <div class="text-[10px] text-slate-600 font-semibold">Tingkat akhir / alumni</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Dropdown Kelas Tujuan -->
                        <div id="targetClassContainer" class="space-y-2">
                            <label class="block text-xs font-black uppercase text-black">
                                2. Pilih Kelas Tujuan di T.A. {{ $targetAcademicYear->name ?? '-' }}:
                            </label>
                            <select name="target_class_id" id="targetClassSelect" 
                                    class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                                <option value="">-- Pilih Rombel / Kelas Tujuan --</option>
                                @foreach($targetClasses as $tc)
                                    @php
                                        // Hindari memilih kelas yang sama persis jika tahun ajaran sama
                                        $isSameClass = ($sourceAcademicYear?->id == $targetAcademicYear?->id) && ($selectedSourceClass->id == $tc->id);
                                    @endphp
                                    <option value="{{ $tc->id }}" {{ $isSameClass ? 'disabled' : '' }}>
                                        {{ $tc->name }} (Level: {{ $tc->level }}) — Terisi {{ $tc->students_count }} Siswa {{ $isSameClass ? ' [Kelas Asal]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-500 font-semibold">
                                Siswa yang tidak dicentang akan tetap berada di kelas lama (tinggal kelas).
                            </p>
                        </div>

                        <!-- Info Box jika memilih Lulus -->
                        <div id="graduateInfoBox" class="hidden p-3 bg-rose-50 border-2 border-black rounded-sm text-xs font-medium text-rose-950 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <span>ℹ️</span> Ketentuan Kelulusan:
                            </div>
                            <p class="text-[11px] leading-relaxed">
                                Siswa terpilih akan dipindahkan ke kelas khusus <strong>Alumni</strong>. Akun login siswa akan dinonaktifkan agar tidak masuk dalam presensi harian aktif, namun data riwayat kehadiran dan tabungan tetap tersimpan aman.
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Eksekusi -->
                    <div class="pt-3 border-t-2 border-black/20 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <button type="button" onclick="confirmPromotionExecution()" 
                                class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs sm:text-sm font-black px-6 py-3 w-full sm:w-auto flex items-center justify-center gap-2 shadow-[3px_3px_0px_#000] cursor-pointer">
                            <span>🚀</span>
                            <span id="submitButtonText">Proses Kenaikan Kelas Siswa</span>
                        </button>
                    </div>
                </div>
            @endif
        </form>
    @else
        <!-- Empty State Belum Memilih Kelas -->
        <div class="bg-white neo-box p-12 text-center space-y-3">
            <div class="text-4xl">👆</div>
            <h3 class="font-heading font-black text-lg text-black">Silakan Pilih Kelas Asal Siswa di Atas</h3>
            <p class="text-xs text-slate-600 max-w-md mx-auto">
                Pilih salah satu rombel pada Tahun Ajaran Asal untuk menampilkan daftar siswa beserta data presensi dan menentukan kelas tujuan baru.
            </p>
        </div>
    @endif
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH ROMBEL KELAS CEPAT -->
<!-- ========================================================================= -->
<div id="quickClassModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200 border-4 border-black">
        <div class="bg-[#FFD43B] p-4 text-black border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">➕</span>
                <h3 class="font-heading font-black text-sm uppercase">Tambah Rombel Kelas Baru</h3>
            </div>
            <button type="button" onclick="closeQuickClassModal()" class="font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <form action="{{ route('admin.students.promotion.quick-class') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Tahun Ajaran Target *</label>
                <select name="academic_year_id" required 
                        class="w-full bg-slate-100 border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $targetAcademicYear?->id == $ay->id ? 'selected' : '' }}>
                            {{ $ay->name }} — Semester {{ ucfirst($ay->semester) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Nama Rombel / Kelas *</label>
                <input type="text" name="name" placeholder="Contoh: Kelas 4A, Kelas 8B, X RPL 1..." required
                       class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Tingkat / Jenjang *</label>
                <select name="level" required 
                        class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                    <option value="SD">SD / MI</option>
                    <option value="SMP" selected>SMP / MTs</option>
                    <option value="SMA">SMA / MA / SMK</option>
                </select>
            </div>

            <div class="pt-3 border-t-2 border-black/20 flex justify-end gap-2">
                <button type="button" onclick="closeQuickClassModal()" 
                        class="neo-btn bg-slate-200 text-black text-xs font-bold px-4 py-2 hover:bg-slate-300">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2 hover:bg-emerald-400">
                    💾 Simpan Rombel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function selectAllStudents(checked) {
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = checked;
        });
        const headerCb = document.getElementById('headerCheckbox');
        if (headerCb) headerCb.checked = checked;
        updateSelectedCounter();
    }

    function toggleHeaderCheckbox(headerCb) {
        selectAllStudents(headerCb.checked);
    }

    function updateSelectedCounter() {
        const count = document.querySelectorAll('.student-checkbox:checked').length;
        const counterEl = document.getElementById('selectedCounter');
        if (counterEl) counterEl.innerText = count;

        const total = document.querySelectorAll('.student-checkbox').length;
        const headerCb = document.getElementById('headerCheckbox');
        if (headerCb) {
            headerCb.checked = (count === total && total > 0);
        }
    }

    function toggleActionType(type) {
        const targetContainer = document.getElementById('targetClassContainer');
        const targetSelect = document.getElementById('targetClassSelect');
        const graduateInfo = document.getElementById('graduateInfoBox');
        const submitBtnText = document.getElementById('submitButtonText');

        if (type === 'graduate') {
            if (targetContainer) targetContainer.classList.add('hidden');
            if (targetSelect) targetSelect.required = false;
            if (graduateInfo) graduateInfo.classList.remove('hidden');
            if (submitBtnText) submitBtnText.innerText = 'Luluskan Siswa Terpilih (Alumni)';
        } else {
            if (targetContainer) targetContainer.classList.remove('hidden');
            if (targetSelect) targetSelect.required = true;
            if (graduateInfo) graduateInfo.classList.add('hidden');
            if (submitBtnText) submitBtnText.innerText = 'Proses Kenaikan Kelas Siswa';
        }
    }

    function confirmPromotionExecution() {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        if (checkedCount === 0) {
            Swal.fire({
                title: 'Belum Ada Siswa Dipilih',
                text: 'Silakan centang minimal satu siswa yang akan diproses.',
                icon: 'warning',
                confirmButtonColor: '#000000',
            });
            return;
        }

        const actionType = document.querySelector('input[name="action_type"]:checked')?.value || 'promote';
        const form = document.getElementById('promotionForm');

        if (actionType === 'promote') {
            const targetSelect = document.getElementById('targetClassSelect');
            if (!targetSelect.value) {
                Swal.fire({
                    title: 'Pilih Kelas Tujuan',
                    text: 'Silakan pilih kelas tujuan terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonColor: '#000000',
                });
                return;
            }

            const targetClassName = targetSelect.options[targetSelect.selectedIndex].text;

            Swal.fire({
                title: 'Konfirmasi Kenaikan Kelas',
                html: `Sebanyak <b>${checkedCount} siswa</b> akan dipindahkan ke:<br><span class="badge bg-[#D3F9D8] text-emerald-950 px-2 py-1 mt-2 inline-block font-black border border-black">${targetClassName}</span><br><br>Lanjutkan proses kenaikan kelas?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20C997',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Naikkan Siswa!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            Swal.fire({
                title: 'Konfirmasi Kelulusan Siswa',
                html: `Sebanyak <b>${checkedCount} siswa</b> akan diset sebagai <b>LULUS (ALUMNI)</b>.<br>Akun login siswa akan dinonaktifkan.<br><br>Apakah Anda yakin?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Luluskan Siswa!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    form.submit();
                }
            });
        }
    }

    function openQuickClassModal() {
        document.getElementById('quickClassModal')?.classList.remove('hidden');
    }

    function closeQuickClassModal() {
        document.getElementById('quickClassModal')?.classList.add('hidden');
    }
</script>
@endpush
