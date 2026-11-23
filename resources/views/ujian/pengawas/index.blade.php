@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Manajemen Pengawas Ruang Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-5">
    {{-- Header Utama Neo-Brutalism --}}
    <div class="overflow-hidden rounded-xl border-2 border-slate-900 bg-white shadow-[4px_4px_0px_0px_#0f172a]">
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="mb-1.5 flex flex-wrap items-center gap-2">
                    <span class="rounded-md border-2 border-slate-900 bg-[#FFD43B] px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-900 shadow-[1px_1px_0px_0px_#0f172a]">
                        Sub-Menu 3 (B)
                    </span>
                    <span class="text-[11px] font-black text-slate-600">Logika Pemetaan &amp; Penugasan Pengawas</span>
                </div>
                <h1 class="font-heading text-xl font-black tracking-tight text-slate-900 sm:text-2xl">
                    Jadwal Pengawas Ruang Ujian
                </h1>
                <p class="mt-1 max-w-2xl text-xs font-semibold leading-relaxed text-slate-600">
                    Pengaturan berbasis jadwal mapel, pemetaan kelas rendah/atas, pengecualian panitia, dan sistem pertukaran jadwal (swap).
                </p>
            </div>

            @if($activePeriode)
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" onclick="document.getElementById('modalAutoPlotter').classList.remove('hidden')"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#F3D9FA] px-3.5 py-2 text-xs font-black text-purple-950 shadow-[2px_2px_0px_0px_#0f172a] transition-all hover:bg-purple-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                        </svg>
                        <span>Auto-Plot</span>
                    </button>
                    <button type="button" onclick="document.getElementById('modalTambahPengawas').classList.remove('hidden')"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#FFD43B] px-3.5 py-2 text-xs font-black text-slate-900 shadow-[2px_2px_0px_0px_#0f172a] transition-all hover:bg-yellow-400 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Tugaskan Manual</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-3 border-t-2 border-slate-900 bg-slate-50 px-5 py-3 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('ujian.pengawas.index') }}" class="flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <label for="periode_id" class="text-[11px] font-black uppercase tracking-wide text-slate-700">Periode</label>
                <select id="periode_id" name="periode_id" onchange="this.form.submit()"
                        class="cursor-pointer rounded-lg border-2 border-slate-900 bg-white px-3 py-1.5 text-xs font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] focus:outline-none">
                    @forelse($periodes as $p)
                        <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}{{ $p->is_active ? ' (Aktif)' : '' }}
                        </option>
                    @empty
                        <option value="">Belum ada periode</option>
                    @endforelse
                </select>
            </form>

            @if($activePeriode)
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="openModalCetakDaftarHadir()"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-white px-3 py-1.5 text-[11px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-yellow-50 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                        </svg>
                        <span>Daftar Hadir</span>
                    </button>
                    <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => '3-6', 'show_mapel' => 1]) }}" target="_blank"
                       title="Cetak Jadwal Pengawas + Mapel + Kode 1..N (Sheet 3: Kelas 3–6)"
                       class="inline-flex items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#FFD43B] px-3 py-1.5 text-[11px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-yellow-400 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <span>📊 Sheet 3 (3–6)</span>
                    </a>
                    <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => '1-2', 'show_mapel' => 0]) }}" target="_blank"
                       title="Cetak Jadwal Pengawas Kode 1..N tanpa Mapel (Sheet 4: Kelas 1–2)"
                       class="inline-flex items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#74C0FC] px-3 py-1.5 text-[11px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-blue-300 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <span>📋 Sheet 4 (1–2)</span>
                    </a>
                    <a href="{{ route('ujian.pengawas.print-jadwal-guru', ['periode_id' => $activePeriode->id]) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-white px-3 py-1.5 text-[11px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-yellow-50 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <span>Tugas per Guru</span>
                    </a>
                    <a href="{{ route('ujian.pengawas.presensi.index', ['periode_id' => $activePeriode->id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#20C997] px-3 py-1.5 text-[11px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-emerald-400 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <span>📋 Presensi Pengawas</span>
                    </a>
                    <button type="button" onclick="document.getElementById('modalImportPdfMatriks').classList.remove('hidden')"
                            title="Import penugasan & jadwal dari file PDF matriks"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#51CF66] px-3 py-1.5 text-[11px] font-black text-slate-900 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-green-500 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span>Import PDF</span>
                    </button>
                    @if($pengawasList->isNotEmpty())
                        <form action="{{ route('ujian.pengawas.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS / MERESET seluruh ({{ $pengawasList->count() }}) data penugasan pengawas pada periode ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
                            <button type="submit" title="Hapus / kosongkan seluruh penugasan pengawas periode ini"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-900 bg-[#FFE3E3] px-3 py-1.5 text-[11px] font-black text-rose-950 shadow-[1.5px_1.5px_0px_0px_#0f172a] hover:bg-rose-200 active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                                <span>Reset ({{ $pengawasList->count() }})</span>
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Toggle Pengaturan Akses Presensi & Berita Acara Sebelum Hari H --}}
    @if($activePeriode)
        <div class="rounded-xl border-2 border-slate-900 p-4 shadow-[4px_4px_0px_0px_#0f172a] flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#D3F9D8]' : 'bg-white' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg border-2 border-slate-900 flex items-center justify-center text-lg shadow-[2px_2px_0px_0px_#0f172a] shrink-0 {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#20C997]' : 'bg-slate-100' }}">
                    {{ $activePeriode->izinkan_pengisian_awal ? '🔓' : '🔒' }}
                </div>
                <div>
                    <div class="font-heading font-black text-xs sm:text-sm text-slate-900 uppercase flex items-center gap-2">
                        <span>Akses Pengisian Sebelum Hari H Pelaksanaan Ujian</span>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded border border-slate-900 {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#20C997] text-black' : 'bg-slate-200 text-slate-700' }}">
                            {{ $activePeriode->izinkan_pengisian_awal ? 'AKTIF (DIIZINKAN)' : 'NONAKTIF (TERKUNCI)' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-700 font-semibold mt-0.5">
                        {{ $activePeriode->izinkan_pengisian_awal 
                            ? 'Pengawas ruangan diizinkan untuk melakukan check-in kehadiran dan mengisi serta mengirim Berita Acara lebih awal sebelum tanggal pelaksanaan ujian.' 
                            : 'Pengawas hanya dapat melakukan check-in dan mengisi Berita Acara tepat pada hari H pelaksanaan masing-masing sesi ujian.' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('ujian.pengawas.presensi.toggle-pengisian-awal') }}" method="POST" class="shrink-0">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 rounded-lg border-2 border-slate-900 text-xs font-heading font-black flex items-center justify-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#0f172a] active:translate-x-0.5 active:translate-y-0.5 transition-all {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#FF6B6B] text-white hover:bg-rose-600' : 'bg-[#FFD43B] text-black hover:bg-yellow-400' }}">
                    @if($activePeriode->izinkan_pengisian_awal)
                        <span>🔒</span> Nonaktifkan Pengisian Awal
                    @else
                        <span>⚡</span> Aktifkan Pengisian Sebelum Hari H
                    @endif
                </button>
            </form>
        </div>
    @endif

    {{-- Flash Alerts Neo-Brutalism --}}
    @if(session('success'))
        <div class="flex items-center justify-between gap-3 rounded-xl border-2 border-slate-900 bg-emerald-100 px-4 py-3 text-xs font-black text-emerald-950 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="flex items-center gap-2">
                <span class="grid h-5 w-5 shrink-0 place-items-center rounded-md border border-slate-900 bg-emerald-600 text-[10px] text-white font-black">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black text-slate-900 hover:text-rose-700">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center justify-between gap-3 rounded-xl border-2 border-slate-900 bg-rose-100 px-4 py-3 text-xs font-black text-rose-950 shadow-[3px_3px_0px_0px_#0f172a]">
            <div class="flex items-center gap-2">
                <span class="grid h-5 w-5 shrink-0 place-items-center rounded-md border border-slate-900 bg-rose-600 text-[10px] text-white font-black">!</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black text-slate-900 hover:text-black">✕</button>
        </div>
    @endif

    {{-- Filter Kelompok Ujian (1-2 vs 3-6) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-white border-2 border-slate-900 rounded-xl shadow-[3px_3px_0px_0px_#0f172a]">
        <div class="flex items-center gap-2">
            <span class="text-xs font-black uppercase text-slate-700">Pilih Kelompok Ujian:</span>
            <div class="inline-flex rounded-lg border-2 border-slate-900 p-0.5 bg-slate-100">
                <a href="{{ route('ujian.pengawas.index', array_merge(request()->query(), ['kelompok' => '3-6'])) }}"
                   class="px-3 py-1 rounded-md text-xs font-black transition-all cursor-pointer {{ ($kelompok ?? '3-6') === '3-6' ? 'bg-[#FFD43B] text-slate-900 shadow-[1px_1px_0px_0px_#0f172a]' : 'text-slate-600 hover:text-black' }}">
                    🌟 Kelompok Kelas 3–6 (Utama)
                </a>
                <a href="{{ route('ujian.pengawas.index', array_merge(request()->query(), ['kelompok' => '1-2'])) }}"
                   class="px-3 py-1 rounded-md text-xs font-black transition-all cursor-pointer {{ ($kelompok ?? '3-6') === '1-2' ? 'bg-[#74C0FC] text-slate-900 shadow-[1px_1px_0px_0px_#0f172a]' : 'text-slate-600 hover:text-black' }}">
                    👶 Kelompok Kelas 1–2 (Bawah)
                </a>
                <a href="{{ route('ujian.pengawas.index', array_merge(request()->query(), ['kelompok' => 'all'])) }}"
                   class="px-3 py-1 rounded-md text-xs font-black transition-all cursor-pointer {{ ($kelompok ?? '3-6') === 'all' ? 'bg-slate-900 text-white shadow-[1px_1px_0px_0px_#0f172a]' : 'text-slate-600 hover:text-black' }}">
                    Semua Kelompok
                </a>
            </div>
        </div>
        <div class="text-[11px] font-bold text-slate-600">
            Terfilter: <strong class="text-slate-900">{{ $pengawasList->count() }}</strong> penugasan pengawas
        </div>
    </div>

    {{-- Navigasi Tab Neo-Brutalism --}}
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" onclick="switchTab('penugasan')" id="tab-btn-penugasan" class="tab-btn is-active">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            <span>Penugasan Jadwal Pengawas</span>
            <span class="tab-count">{{ $pengawasList->count() }}</span>
        </button>
        <button type="button" onclick="switchTab('pemetaan')" id="tab-btn-pemetaan" class="tab-btn">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
            <span>Pemetaan Calon Pengawas (Kelas Rendah / Atas)</span>
            <span class="tab-count">{{ $mappedCount }}/{{ $totalTeachersCount }}</span>
        </button>
        <button type="button" onclick="switchTab('riwayat')" id="tab-btn-riwayat" class="tab-btn">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
            </svg>
            <span>Riwayat Pertukaran Pengawas</span>
            <span class="tab-count">{{ $swapHistories->count() }}</span>
        </button>
    </div>

    {{-- TAB 1: PENUGASAN JADWAL PENGAWAS --}}
    @include('ujian.pengawas.partials._tab-penugasan')

    {{-- TAB 2: PEMETAAN CALON PENGAWAS (KELAS RENDAH / ATAS) --}}
    @include('ujian.pengawas.partials._tab-pemetaan')

    {{-- TAB 3: RIWAYAT PERTUKARAN PENGAWAS --}}
    @include('ujian.pengawas.partials._tab-riwayat')
</div>

{{-- MODALS POPUP --}}
@include('ujian.pengawas.partials._modals')

{{-- STYLES KHUSUS --}}
@include('ujian.pengawas.partials._styles')

{{-- SCRIPTS LOGIKA INTERAKTIF --}}
@include('ujian.pengawas.partials._scripts')
@endsection
