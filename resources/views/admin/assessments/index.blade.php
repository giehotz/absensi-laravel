@extends('layouts.admin')

@section('title', 'Monitoring Penilaian Sumatif Siswa')
@section('page-title', 'Penilaian Sumatif Siswa')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>Akademik & Guru</span>
                <span>/</span>
                <span class="text-black">Penilaian Sumatif</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Monitoring Penilaian Sumatif Siswa</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Pusat pengawasan capaian nilai sumatif sekolah, kontrol penguncian nilai, serta rekapitulasi akademik per rombel.
            </p>
        </div>
    </div>

    <!-- 3 Kartu Metrik KPI Admin -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Total Paket Nilai</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($totalPackages) }}</div>
            <div class="text-[11px] text-slate-600 mt-0.5">Kombinasi Rombel & Mapel</div>
        </div>
        <div class="bg-[#FFF9DB] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-amber-900">Status Draft</div>
            <div class="font-mono font-black text-2xl text-amber-950 mt-1">{{ number_format($draftPackages) }}</div>
            <div class="text-[11px] text-amber-800 mt-0.5">Masih dalam pengisian guru</div>
        </div>
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-900">Terkunci (Final)</div>
            <div class="font-mono font-black text-2xl text-emerald-950 mt-1">{{ number_format($lockedPackages) }}</div>
            <div class="text-[11px] text-emerald-800 mt-0.5">Telah difinalisasi & terpublikasi</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
        <form action="{{ route('admin.penilaian.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Jenis Asesmen</label>
                <select name="type" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Jenis --</option>
                    <option value="materi" {{ request('type') === 'materi' ? 'selected' : '' }}>Lingkup Materi</option>
                    <option value="sts" {{ request('type') === 'sts' ? 'selected' : '' }}>Sumatif STS</option>
                    <option value="sas" {{ request('type') === 'sas' ? 'selected' : '' }}>Sumatif SAS</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Tahun --</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>
                            {{ $y->name }} ({{ ucfirst($y->semester) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Kelas / Rombel</label>
                <select name="school_class_id" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('school_class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
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
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Status Kunci</label>
                <select name="status" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Terkunci</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-black hover:bg-slate-800 text-white font-black text-xs py-2 rounded border-2 border-black shadow-[2px_2px_0px_0px_#FFD43B] cursor-pointer">
                    Filter
                </button>
                @if(request()->hasAny(['type', 'academic_year_id', 'school_class_id', 'subject_id', 'status']))
                    <a href="{{ route('admin.penilaian.index') }}" class="bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs p-2 rounded border-2 border-black">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Packages -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-[11px] font-black uppercase text-black">
                        <th class="py-3 px-3 border-r border-slate-300 text-center w-12">No</th>
                        <th class="py-3 px-3 border-r border-slate-300">Rombel & Mapel</th>
                        <th class="py-3 px-3 border-r border-slate-300">Guru Pengampu</th>
                        <th class="py-3 px-3 border-r border-slate-300 text-center w-24">KKTP</th>
                        <th class="py-3 px-3 border-r border-slate-300 text-center w-28">Status</th>
                        <th class="py-3 px-3 border-r border-slate-300 text-center w-28">Tahun Ajaran</th>
                        <th class="py-3 px-3 text-center w-48">Aksi & Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($packages as $idx => $pkg)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-3 border-r border-slate-200 text-center font-bold text-slate-500">
                                {{ $packages->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded border border-black {{ $pkg->type_badge_bg }}">
                                        {{ $pkg->type_short_label }}
                                    </span>
                                    <a href="{{ route('admin.penilaian.show', $pkg) }}" class="font-black text-black hover:text-[#5294FF] block">
                                        {{ $pkg->title }}
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-slate-700">{{ $pkg->schoolClass->name }}</span> • {{ $pkg->subject->name }}
                                </div>
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200">
                                <div class="font-bold text-slate-900">{{ $pkg->teacher->user?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NIP: {{ $pkg->teacher->nip ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-center font-mono font-black text-sm">
                                {{ $pkg->kktp_default }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-center">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded border {{ $pkg->isLocked() ? 'bg-emerald-100 text-emerald-950 border-emerald-400' : 'bg-amber-100 text-amber-950 border-amber-400' }}">
                                    {{ $pkg->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-center text-slate-700">
                                {{ $pkg->academicYear->name }} ({{ ucfirst($pkg->academicYear->semester) }})
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.penilaian.show', $pkg) }}" 
                                       class="p-1.5 bg-[#FFD43B] hover:bg-[#fcc419] text-black rounded border border-black shadow-[1px_1px_0px_0px_#000]"
                                       title="Lihat Rekap Nilai">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if($pkg->isLocked())
                                        <!-- Tombol Unlock Khusus Admin -->
                                        <form action="{{ route('admin.penilaian.unlock', $pkg) }}" method="POST"
                                              onsubmit="return confirm('Buka kunci paket nilai ini? Status akan kembali menjadi Draft sehingga guru pengampu dapat memperbarui nilai.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="p-1.5 bg-[#5294FF] hover:bg-blue-600 text-white rounded border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                    title="Buka Kunci Paket">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Cetak PDF -->
                                    <a href="{{ route('admin.penilaian.print', $pkg) }}" target="_blank"
                                       class="p-1.5 bg-white hover:bg-slate-100 text-black rounded border border-black shadow-[1px_1px_0px_0px_#000]"
                                       title="Cetak PDF Ber-Kop">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>

                                    <!-- Hapus Paket -->
                                    <form action="{{ route('admin.penilaian.destroy', $pkg) }}" method="POST"
                                          onsubmit="return confirm('Hapus paket penilaian ini beserta seluruh nilainya? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 bg-[#FF6B6B] hover:bg-red-600 text-white rounded border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                title="Hapus Paket">
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
                            <td colspan="7" class="py-8 text-center text-slate-500 font-bold">
                                Belum ada paket penilaian sumatif yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t-2 border-black">
            {{ $packages->links() }}
        </div>
    </div>
</div>
@endsection
