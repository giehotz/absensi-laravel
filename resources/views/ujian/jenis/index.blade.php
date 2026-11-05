@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.guru')

@section('title', 'Jenis Asesmen & Periode Ujian Kurikulum Merdeka')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 1
                </span>
                <span class="text-xs font-bold text-slate-500">Standar Asesmen Nasional & Kemenag</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Jenis Asesmen & Periode Ujian
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Mengelola master jenis asesmen Kurikulum Merdeka (SLM, STS, SAS, SAT, UM) serta penetapan kalender periode ujian aktif.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="document.getElementById('modalTambahPeriode').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                <span>➕</span>
                <span>Buat Periode Baru</span>
            </button>
            <button type="button" onclick="document.getElementById('modalTambahJenis').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                <span>➕</span>
                <span>Tambah Jenis Asesmen</span>
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-emerald-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>✅</span><span>{{ session('success') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif

    {{-- 1. Daftar Periode Pelaksanaan Ujian --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="pb-3 border-b-2 border-black flex items-center justify-between">
            <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
                <span>🗓️</span>
                <span>Daftar Periode Pelaksanaan Ujian</span>
            </h2>
            <span class="text-xs font-bold text-slate-500">Total: {{ $periodes->count() }} Periode</span>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black">Nama Periode Ujian</th>
                        <th class="p-2.5 border-r border-black">Jenis Asesmen</th>
                        <th class="p-2.5 border-r border-black text-center">Tahun Ajaran / Sem</th>
                        <th class="p-2.5 border-r border-black text-center">Rentang Tanggal</th>
                        <th class="p-2.5 border-r border-black text-center">Status</th>
                        <th class="p-2.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($periodes as $idx => $p)
                        <tr class="hover:bg-amber-50/50 {{ $p->is_active ? 'bg-emerald-50/60 font-semibold' : '' }}">
                            <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-bold text-black">
                                {{ $p->nama_periode }}
                            </td>
                            <td class="p-2.5 border-r border-black">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 border border-black">
                                    {{ $p->jenis?->nama_jenis }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-mono">
                                {{ $p->tahun_pelajaran }} ({{ $p->semester }})
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-mono text-[11px]">
                                {{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d/m/Y') : '-' }} s.d. {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d/m/Y') : '-' }}
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                @if($p->is_active)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-[#20C997] text-white border border-black shadow-[1px_1px_0px_0px_#000]">
                                        AKTIF
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 border border-slate-400">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="p-2.5 text-center flex items-center justify-center gap-1.5">
                                @if(! $p->is_active)
                                    <form action="{{ route('ujian.periode.activate', $p) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-[#20C997] hover:bg-emerald-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('ujian.kartu.index', ['periode_id' => $p->id]) }}" class="px-2 py-1 bg-[#FFD43B] text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000]">
                                    Peserta
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500 italic">Belum ada periode ujian yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Master Jenis Asesmen Kurikulum Merdeka --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="pb-3 border-b-2 border-black flex items-center justify-between">
            <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
                <span>📚</span>
                <span>Daftar Master Jenis Asesmen (Kurikulum Merdeka)</span>
            </h2>
            <span class="text-xs font-bold text-slate-500">Total: {{ $jenisList->count() }} Jenis</span>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black w-24">Kode</th>
                        <th class="p-2.5 border-r border-black">Nama Jenis Asesmen</th>
                        <th class="p-2.5 border-r border-black">Kategori Kurikulum</th>
                        <th class="p-2.5 border-r border-black">Fase Sasaran</th>
                        <th class="p-2.5 border-r border-black">Teks Kop Kartu (Kop 2)</th>
                        <th class="p-2.5 border-r border-black text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @foreach($jenisList as $idx => $j)
                        <tr class="hover:bg-amber-50/50">
                            <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-mono font-bold">{{ $j->kode_jenis }}</td>
                            <td class="p-2.5 border-r border-black font-bold text-black">{{ $j->nama_jenis }}</td>
                            <td class="p-2.5 border-r border-black">{{ $j->kategori_kurikulum }}</td>
                            <td class="p-2.5 border-r border-black font-semibold text-slate-700">{{ $j->fase_sasaran }}</td>
                            <td class="p-2.5 border-r border-black text-[11px] font-bold text-slate-800">{{ $j->judul_kop_kartu ?: '-' }}</td>
                            <td class="p-2.5 border-r border-black text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $j->status_aktif ? 'bg-emerald-100 text-emerald-950 border border-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $j->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Periode --}}
<div id="modalTambahPeriode" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>🗓️</span>
                <span>Buat Periode Pelaksanaan Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahPeriode').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.periode.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Jenis Asesmen</label>
                <select name="ujian_jenis_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                    @foreach($jenisList as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jenis }} ({{ $j->kode_jenis }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Periode Ujian</label>
                <input type="text" name="nama_periode" required placeholder="Contoh: Asesmen Sumatif Akhir Semester Ganjil 2026/2027" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tahun Pelajaran</label>
                    <input type="text" name="tahun_pelajaran" required value="{{ $academicYears->first()?->name ?? '2026/2027' }}" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Semester</label>
                    <select name="semester" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tempat Cetak Kartu</label>
                    <input type="text" name="tempat_cetak" value="Tanggamus" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Cetak Kartu</label>
                    <input type="date" name="tanggal_cetak_kartu" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahPeriode').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#20C997] hover:bg-emerald-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Periode
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Jenis Asesmen --}}
<div id="modalTambahJenis" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>📚</span>
                <span>Tambah Jenis Asesmen Baru</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahJenis').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.jenis.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1">Kode Jenis (Singkatan Unik)</label>
                <input type="text" name="kode_jenis" required placeholder="Contoh: SAS-2, SAT, ANBK" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs uppercase font-mono">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Jenis Asesmen</label>
                <input type="text" name="nama_jenis" required placeholder="Contoh: Asesmen Sumatif Akhir Tahun (SAT)" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Kategori Kurikulum</label>
                    <select name="kategori_kurikulum" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="Formatif">Formatif</option>
                        <option value="Sumatif Lingkup Materi">Sumatif Lingkup Materi</option>
                        <option value="Sumatif Tengah Semester">Sumatif Tengah Semester</option>
                        <option value="Sumatif Akhir Semester (SAS)">Sumatif Akhir Semester (SAS)</option>
                        <option value="Sumatif Akhir Tahun (SAT)">Sumatif Akhir Tahun (SAT)</option>
                        <option value="Sumatif Akhir Jenjang">Sumatif Akhir Jenjang</option>
                        <option value="Ujian Praktik / Kinerja">Ujian Praktik / Kinerja</option>
                        <option value="P5-PPRA">P5-PPRA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Fase Sasaran</label>
                    <select name="fase_sasaran" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="Semua Fase">Semua Fase (Kelas 1-6)</option>
                        <option value="Fase A">Fase A (Kelas 1-2)</option>
                        <option value="Fase B">Fase B (Kelas 3-4)</option>
                        <option value="Fase C">Fase C (Kelas 5-6)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Judul Baris Kop Kartu (Kop 2)</label>
                <input type="text" name="judul_kop_kartu" placeholder="Contoh: ASESMEN SUMATIF AKHIR TAHUN GENAP" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs uppercase">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahJenis').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Jenis
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
