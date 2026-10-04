@extends('layouts.guru')

@section('title', 'Penilaian Sumatif Siswa')
@section('page-title', 'Penilaian Sumatif Siswa')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>Akademik</span>
                <span>/</span>
                <span class="text-black">Penilaian Sumatif</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Penilaian Sumatif Siswa</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Pengelolaan paket nilai sumatif (SUM 1 s.d. SUM 15), template Excel multi-sheet, kalkulasi ketuntasan, dan rekap resmi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- CTA Buat Paket Baru -->
            <a href="{{ route('guru.penilaian.create') }}" 
               class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs font-black flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat Paket Penilaian</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Paket</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $totalPackages }}</div>
            <div class="text-[10px] text-slate-600 mt-0.5">Seluruh paket dibuat</div>
        </div>
        <div class="bg-[#FFF9DB] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Status Draft</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $draftPackages }}</div>
            <div class="text-[10px] text-amber-800 mt-0.5">Bebas diisi & diimpor</div>
        </div>
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Terkunci (Final)</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $lockedPackages }}</div>
            <div class="text-[10px] text-emerald-900 mt-0.5">Tampil di portal siswa</div>
        </div>
        <div class="bg-[#E7F5FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Kelas Binaan</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $homeroomCount }}</div>
            <div class="text-[10px] text-blue-900 mt-0.5">Sebagai Wali Kelas</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
        <form action="{{ route('guru.penilaian.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Kelas / Rombel</label>
                <select name="school_class_id" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('school_class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} (Tk. {{ $c->level }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Mapel --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Status Nilai</label>
                <select name="status" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Aktif Diisi)</option>
                    <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Terkunci (Final)</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-black hover:bg-slate-800 text-white font-black text-xs py-2.5 rounded border-2 border-black shadow-[2px_2px_0px_0px_#FFD43B] cursor-pointer">
                    Filter Data
                </button>
                @if(request()->hasAny(['school_class_id', 'subject_id', 'status']))
                    <a href="{{ route('guru.penilaian.index') }}" class="bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs p-2.5 rounded border-2 border-black">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Package Cards Grid -->
    @if($packages->isEmpty())
        <div class="bg-white border-2 border-black p-12 text-center rounded-lg shadow-[4px_4px_0px_0px_#000]">
            <div class="w-16 h-16 bg-[#FFF9DB] border-2 border-black rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="font-heading font-black text-lg text-black">Belum Ada Paket Penilaian</h3>
            <p class="text-xs text-slate-600 max-w-md mx-auto mt-1 mb-4">
                Mulai kelola nilai siswa dengan membuat paket penilaian baru untuk kelas dan mata pelajaran yang Anda ampu.
            </p>
            <a href="{{ route('guru.penilaian.create') }}" class="neo-btn bg-[#FFD43B] text-black px-4 py-2 text-xs font-black inline-flex items-center gap-1.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                + Buat Paket Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($packages as $pkg)
                @php
                    $isOwner = $pkg->teacher_id === $teacher->id;
                    $isHomeroom = $pkg->schoolClass->homeroom_teacher_id === $teacher->id;
                @endphp
                <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded border border-black {{ $pkg->isLocked() ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-[#FFF9DB] text-amber-950' }}">
                                {{ $pkg->isLocked() ? 'TERKUNCI (FINAL)' : 'DRAFT' }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-500">
                                KKTP: <strong class="text-black">{{ $pkg->kktp_default }}</strong>
                            </span>
                        </div>

                        <h3 class="font-heading font-black text-base text-black mb-1 hover:text-[#5294FF] transition-colors">
                            <a href="{{ route('guru.penilaian.show', $pkg) }}">
                                {{ $pkg->title }}
                            </a>
                        </h3>

                        <div class="space-y-1.5 text-xs text-slate-700 mt-3 pt-3 border-t-2 border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Kelas / Rombel:</span>
                                <span class="font-black text-black bg-slate-100 px-1.5 py-0.5 rounded border border-slate-300">{{ $pkg->schoolClass->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Mata Pelajaran:</span>
                                <span class="font-bold text-black">{{ $pkg->subject->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Tahun Ajaran:</span>
                                <span class="text-slate-800">{{ $pkg->academicYear->name }} ({{ ucfirst($pkg->academicYear->semester) }})</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-medium">Guru Pengampu:</span>
                                <span class="font-bold text-slate-900">{{ $pkg->teacher->user?->name ?? 'Guru' }}</span>
                            </div>
                        </div>

                        @if($isHomeroom && ! $isOwner)
                            <div class="mt-3 p-2 bg-[#E7F5FF] border border-blue-400 rounded text-[11px] font-bold text-blue-950 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Akses Wali Kelas (Mode Pantau & Rekap)</span>
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 border-t-2 border-black p-3.5 flex items-center justify-between gap-2">
                        <!-- Unduh Template -->
                        <a href="{{ route('guru.penilaian.template', $pkg) }}" 
                           class="text-xs font-bold text-slate-700 hover:text-black flex items-center gap-1 py-1 px-2 rounded hover:bg-slate-200 transition-colors"
                           title="Unduh Template Excel 15 Sheet">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Template</span>
                        </a>

                        <div class="flex items-center gap-1.5">
                            @if($isOwner)
                                @if(! $pkg->isLocked())
                                    <!-- Tombol Hapus Paket (Draft) -->
                                    <form action="{{ route('guru.penilaian.destroy', $pkg) }}" method="POST"
                                          onsubmit="return confirm('Hapus paket penilaian \'{{ addslashes($pkg->title) }}\'?\n\nSeluruh data nilai sumatif (SUM 1 s.d. SUM 15) di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="neo-btn bg-[#FF6B6B] hover:bg-red-600 text-white p-1.5 text-xs font-bold flex items-center justify-center rounded border border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-colors"
                                                title="Hapus Paket Penilaian">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <!-- Tombol Terkunci (Beri Pesan Buka Kunci) -->
                                    <button type="button" 
                                            onclick="showLockedModal('{{ addslashes($pkg->title) }}')"
                                            class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-500 hover:text-black rounded border border-slate-400 shadow-[1px_1px_0px_0px_#94a3b8] cursor-pointer flex items-center justify-center transition-colors"
                                            title="Paket Terkunci (Klik untuk info cara menghapus)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </button>
                                @endif
                            @endif

                            <!-- Buka Matriks Nilai -->
                            <a href="{{ route('guru.penilaian.show', $pkg) }}" 
                               class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-1.5 text-xs font-black flex items-center gap-1 rounded border border-black shadow-[2px_2px_0px_0px_#000]">
                                <span>Buka Nilai</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $packages->links() }}
        </div>
    @endif
</div>

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

<script>
    function showLockedModal(title) {
        document.getElementById('lockedModalPackageTitle').textContent = title;
        document.getElementById('lockedModal').classList.remove('hidden');
    }

    function closeLockedModal() {
        document.getElementById('lockedModal').classList.add('hidden');
    }

    // Close on background click
    document.getElementById('lockedModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeLockedModal();
        }
    });
</script>
@endsection
