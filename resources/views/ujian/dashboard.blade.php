@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.guru')

@section('title', 'Dashboard Manajemen Ujian - ' . ($activePeriode->nama_periode ?? 'Ujian Madrasah'))

@section('content')
<div class="space-y-6">
    {{-- Header & Periode Selector --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Kurikulum Merdeka
                </span>
                @if($activePeriode?->is_active)
                    <span class="text-xs font-black uppercase tracking-wider bg-[#20C997] text-white px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000] flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        Periode Aktif
                    </span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                {{ $activePeriode->nama_periode ?? 'Modul Manajemen Ujian' }}
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Tahun Ajaran: <span class="font-bold text-black">{{ $activePeriode->tahun_pelajaran ?? '-' }}</span> | Semester: <span class="font-bold text-black">{{ $activePeriode->semester ?? '-' }}</span>
            </p>
        </div>

        {{-- Switch Periode Form --}}
        <form method="GET" action="{{ route('ujian.dashboard') }}" class="flex items-center gap-2 shrink-0">
            <select name="periode_id" onchange="this.form.submit()" class="px-3 py-2 text-xs font-bold border-2 border-black rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FFD43B] shadow-[2px_2px_0px_0px_#000]">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                        {{ $p->nama_periode }} {{ $p->is_active ? '★ (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-emerald-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-[#FFE3E3] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-rose-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif

    {{-- Quick Stat KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Peserta Ujian -->
        <a href="{{ route('ujian.kartu.index', ['periode_id' => $activePeriode?->id]) }}" 
           class="p-4 bg-[#E7F5FF] border-3 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_#000] transition-all group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 font-heading uppercase">Peserta Terdaftar</span>
                <span class="text-xl">🎓</span>
            </div>
            <div class="text-2xl font-black font-heading text-black mt-2">
                {{ number_format($totalPeserta) }} <span class="text-xs font-bold text-slate-600">Siswa</span>
            </div>
            <div class="text-[11px] font-bold text-blue-700 mt-2 flex items-center gap-1 group-hover:underline">
                <span>Kelola & Cetak Kartu</span>
                <span>→</span>
            </div>
        </a>

        <!-- Jadwal Ujian -->
        <a href="{{ route('ujian.jadwal.index', ['periode_id' => $activePeriode?->id]) }}" 
           class="p-4 bg-[#FFF3BF] border-3 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_#000] transition-all group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 font-heading uppercase">Slot Jadwal Mapel</span>
                <span class="text-xl">📅</span>
            </div>
            <div class="text-2xl font-black font-heading text-black mt-2">
                {{ number_format($totalJadwal) }} <span class="text-xs font-bold text-slate-600">Jadwal</span>
            </div>
            <div class="text-[11px] font-bold text-amber-800 mt-2 flex items-center gap-1 group-hover:underline">
                <span>Atur Jadwal Ujian</span>
                <span>→</span>
            </div>
        </a>

        <!-- Ruang Ujian -->
        <a href="{{ route('ujian.ruang.index') }}" 
           class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_#000] transition-all group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 font-heading uppercase">Ruang Fisik</span>
                <span class="text-xl">🏫</span>
            </div>
            <div class="text-2xl font-black font-heading text-black mt-2">
                {{ number_format($totalRuang) }} <span class="text-xs font-bold text-slate-600">Ruangan</span>
            </div>
            <div class="text-[11px] font-bold text-emerald-800 mt-2 flex items-center gap-1 group-hover:underline">
                <span>Master Ruang Ujian</span>
                <span>→</span>
            </div>
        </a>

        <!-- Pengawas Terplot -->
        <a href="{{ route('ujian.pengawas.index', ['periode_id' => $activePeriode?->id]) }}" 
           class="p-4 bg-[#F3D9FA] border-3 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_#000] transition-all group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 font-heading uppercase">Pengawas Terplot</span>
                <span class="text-xl">👨‍🏫</span>
            </div>
            <div class="text-2xl font-black font-heading text-black mt-2">
                {{ number_format($totalPengawasTerplot) }} <span class="text-xs font-bold text-slate-600">Ruang/Sesi</span>
            </div>
            <div class="text-[11px] font-bold text-purple-800 mt-2 flex items-center gap-1 group-hover:underline">
                <span>Auto-Plotter Pengawas</span>
                <span>→</span>
            </div>
        </a>
    </div>

    {{-- Bagian Panitia Ujian & Penetapan Akses Guru --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b-2 border-black">
            <div>
                <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
                    <span>👥</span>
                    <span>Struktur Panitia Ujian (Hak Akses Guru)</span>
                </h2>
                <p class="text-xs text-slate-600 font-medium">
                    Guru yang terdaftar di bawah ini secara otomatis memiliki akses penuh mengelola modul ujian ini melalui akun portal guru mereka.
                </p>
            </div>
            @if(auth()->user()->role === 'admin' && $activePeriode)
                <button type="button" onclick="document.getElementById('modalTambahPanitia').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer shrink-0">
                    <span>+</span>
                    <span>Tunjuk Guru Jadi Panitia</span>
                </button>
            @endif
        </div>

        {{-- Daftar Panitia Aktif --}}
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black">Nama Guru</th>
                        <th class="p-2.5 border-r border-black">NIP / NUPTK</th>
                        <th class="p-2.5 border-r border-black text-center">Jabatan Kepanitiaan</th>
                        <th class="p-2.5 border-r border-black">Nomor SK / Keterangan</th>
                        @if(auth()->user()->role === 'admin')
                            <th class="p-2.5 text-center w-24">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($panitias as $idx => $p)
                        <tr class="hover:bg-amber-50/50">
                            <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-bold text-black">
                                {{ $p->teacher?->user?->name ?? 'Guru' }}
                            </td>
                            <td class="p-2.5 border-r border-black font-mono text-[11px]">
                                {{ $p->teacher?->nip ?: ($p->teacher?->nuptk ?: '-') }}
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                @php
                                    $badgeColor = match($p->jabatan) {
                                        'ketua' => 'bg-[#FFD43B] text-black',
                                        'sekretaris' => 'bg-[#5294FF] text-white',
                                        'bendahara' => 'bg-[#20C997] text-white',
                                        default => 'bg-slate-200 text-black',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[11px] font-black border border-black shadow-[1px_1px_0px_0px_#000] {{ $badgeColor }}">
                                    {{ $p->jabatan_label }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-slate-700">
                                <div>{{ $p->sk_nomor ?: '-' }}</div>
                                @if($p->keterangan)
                                    <div class="text-[10px] text-slate-500 italic">{{ $p->keterangan }}</div>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'admin')
                                <td class="p-2.5 text-center">
                                    <form action="{{ route('ujian.panitia.destroy', $p) }}" method="POST" onsubmit="return confirm('Cabut status kepanitiaan guru ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-[#FF6B6B] hover:bg-red-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                            Cabut
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? 6 : 5 }}" class="p-6 text-center text-slate-500 italic">
                                Belum ada guru yang ditetapkan sebagai Panitia Ujian untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Panitia Ujian (Admin) --}}
@if(auth()->user()->role === 'admin' && $activePeriode)
<div id="modalTambahPanitia" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>➕</span>
                <span>Penetapan Panitia Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahPanitia').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.panitia.store') }}" method="POST" class="space-y-3.5 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Guru</label>
                <select name="teacher_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 focus:bg-white text-xs">
                    <option value="">-- Pilih Guru Eksisting --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->user?->name }} (NIP: {{ $t->nip ?: '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Jabatan Kepanitiaan</label>
                <select name="jabatan" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 focus:bg-white text-xs">
                    <option value="ketua">Ketua Pelaksana</option>
                    <option value="sekretaris">Sekretaris</option>
                    <option value="bendahara">Bendahara</option>
                    <option value="anggota">Anggota Panitia</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nomor SK Kepanitiaan (Opsional)</label>
                <input type="text" name="sk_nomor" placeholder="Contoh: MIN.02/PP.00.4/01/2026" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 focus:bg-white text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Keterangan Tugas (Opsional)</label>
                <input type="text" name="keterangan" placeholder="Contoh: Koordinator Distribusi Soal" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 focus:bg-white text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahPanitia').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Penetapan
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
