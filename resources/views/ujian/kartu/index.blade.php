@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Manajemen Peserta & Cetak Kartu Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 4
                </span>
                <span class="text-xs font-bold text-slate-500">Presisi Cetak A4 (Grid 2x4)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Cetak Kartu Ujian & Manajemen Peserta
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Sinkronisasi data siswa dari database utama, generate nomor peserta terstruktur, alokasi kursi, dan cetak kartu presisi tinggi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($activePeriode)
                {{-- Tombol Sinkronkan Data Siswa --}}
                <form action="{{ route('ujian.kartu.sync') }}" method="POST" onsubmit="return confirm('Tarik seluruh data siswa aktif dari sistem absensi ke periode ujian ini?')">
                    @csrf
                    <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
                    <button type="submit" class="px-3.5 py-2 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🔄</span>
                        <span>Sinkronkan Siswa</span>
                    </button>
                </form>

                {{-- Tombol Buka Modal Generate Nomor Peserta --}}
                <button type="button" onclick="openModalGenerateNomor()" class="px-3.5 py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🔢</span>
                    <span>Generate Nomor</span>
                </button>

                {{-- Tombol Bagi Ruang --}}
                <form action="{{ route('ujian.kartu.allocate-rooms') }}" method="POST" onsubmit="return confirm('Distribusikan peserta ke ruang ujian secara otomatis?')">
                    @csrf
                    <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
                    <button type="submit" class="px-3.5 py-2 bg-[#F3D9FA] hover:bg-purple-300 text-purple-950 border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🏫</span>
                        <span>Bagi Ruang</span>
                    </button>
                </form>

                {{-- Tombol Tambah Peserta Manual --}}
                <button type="button" onclick="document.getElementById('modalTambahPeserta').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>➕</span>
                    <span>Tambah Siswa</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Filter Bar & Tombol Cetak Kartu --}}
    <div class="p-4 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] space-y-3">
        <form method="GET" action="{{ route('ujian.kartu.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="periode_id" value="{{ $activePeriode?->id }}">

            <div>
                <label class="block text-[11px] font-black text-slate-700 uppercase mb-1">Filter Rombel / Kelas</label>
                <select name="class_id" id="selectClassId" onchange="onClassFilterChange(this)" class="w-full px-3 py-2 text-xs font-bold border-2 border-black rounded-lg bg-slate-50">
                    <option value="SEMUA" {{ $selectedClassId === 'SEMUA' || empty($selectedClassId) ? 'selected' : '' }}>-- Semua Rombel --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} (Tingkat {{ $c->level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-black text-slate-700 uppercase mb-1">Filter Ruang Ujian</label>
                <select name="ruang_id" id="selectRuangId" onchange="onRuangFilterChange(this)" class="w-full px-3 py-2 text-xs font-bold border-2 border-black rounded-lg bg-slate-50">
                    <option value="">-- Semua Ruangan --</option>
                    @foreach($ruangs as $r)
                        <option value="{{ $r->id }}" data-class-id="{{ $r->school_class_id }}" {{ $selectedRuangId == $r->id ? 'selected' : '' }}>
                            {{ $r->nama_ruang }} ({{ $r->kode_ruang }}){{ $r->isKelas() && $r->schoolClass ? ' - [Rombel ' . $r->schoolClass->name . ']' : ($r->isManual() ? ' - [Manual / UAM]' : '') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-black text-slate-700 uppercase mb-1">Cari Nama / NISN / No Peserta</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..." class="w-full px-3 py-2 text-xs font-semibold border-2 border-black rounded-lg bg-slate-50">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white border-2 border-black rounded-lg text-xs font-bold">
                    Terapkan Filter
                </button>
                @if($search || $selectedClassId || $selectedRuangId)
                    <a href="{{ route('ujian.kartu.index', ['periode_id' => $activePeriode?->id]) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-black border-2 border-black rounded-lg text-xs font-bold">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Action Bar: Tombol Cetak Kartu Berdasarkan Filter & Checkbox --}}
        @if($activePeriode)
            <div class="pt-3 border-t-2 border-black flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Total Peserta: <strong class="text-black">{{ $pesertas->total() }}</strong> Siswa</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- Tombol Cetak Terpilih (Checkbox) --}}
                    <button type="button" onclick="cetakKartuTerpilih()" 
                            class="px-3.5 py-1.5 bg-[#FF6B6B] hover:bg-red-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🖨️</span>
                        <span>Cetak Terpilih (<span id="jumlahTerpilih">0</span>)</span>
                    </button>

                    {{-- Tombol Cetak Label Meja Grid A4 --}}
                    <a href="{{ route('ujian.kartu.print-label-meja', ['periode_id' => $activePeriode->id, 'class_id' => $selectedClassId, 'ruang_id' => $selectedRuangId]) }}" target="_blank"
                       class="px-3.5 py-1.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🏷️</span>
                        <span>Cetak Label Meja</span>
                    </a>

                    {{-- Tombol Cetak Filter (Semua / Kelas Terpilih) --}}
                    <button type="button" onclick="cetakKartuFilter()"
                            class="px-4 py-1.5 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🖨️</span>
                        <span>Cetak Kartu (Filter)</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-emerald-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>✅</span><span>{{ session('success') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-[#FFE3E3] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-rose-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>⚠️</span><span>{{ session('error') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif

    {{-- Tabel Peserta Ujian --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-8 text-center">
                            <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)" class="rounded border-black">
                        </th>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black w-28">No. Peserta</th>
                        <th class="p-2.5 border-r border-black">Nama Siswa</th>
                        <th class="p-2.5 border-r border-black w-24">NIS / NISN</th>
                        <th class="p-2.5 border-r border-black text-center">Kelas</th>
                        <th class="p-2.5 border-r border-black">Tempat, Tanggal Lahir</th>
                        <th class="p-2.5 border-r border-black text-center">Ruang / Sesi</th>
                        <th class="p-2.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($pesertas as $idx => $p)
                        <tr class="hover:bg-amber-50/50">
                            <td class="p-2.5 border-r border-black text-center">
                                <input type="checkbox" class="peserta-checkbox rounded border-black" value="{{ $p->id }}" onchange="updateSelectedCount()">
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-bold">
                                {{ $pesertas->firstItem() + $idx }}
                            </td>
                            <td class="p-2.5 border-r border-black font-mono font-black text-black">
                                <span class="px-1.5 py-0.5 rounded bg-amber-100 border border-black">
                                    {{ $p->nomor_peserta }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black font-bold text-black flex items-center gap-2">
                                @if($p->foto_url)
                                    <img src="{{ $p->foto_url }}" alt="Foto" class="w-6 h-7 object-cover rounded border border-black shrink-0">
                                @else
                                    <span class="w-6 h-7 rounded border border-dashed border-slate-400 bg-slate-100 text-[9px] flex items-center justify-center shrink-0">3x4</span>
                                @endif
                                <span>{{ $p->nama_peserta }}</span>
                            </td>
                            <td class="p-2.5 border-r border-black font-mono text-[11px] text-slate-700">
                                <div>{{ $p->nisn ?: '-' }}</div>
                                <div class="text-[10px] text-slate-500">NIS: {{ $p->nis_lokal ?: '-' }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-black">
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-950 border border-black">
                                    {{ $p->nama_kelas }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-slate-700">
                                {{ $p->tempat_tanggal_lahir }}
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                @if($p->ruang)
                                    <div class="font-bold text-black">{{ $p->ruang->nama_ruang }}</div>
                                    <div class="text-[10px] text-slate-500">Kursi: {{ $p->nomor_kursi ?: '-' }}</div>
                                @else
                                    <span class="text-slate-400 italic">Belum diatur</span>
                                @endif
                            </td>
                            <td class="p-2.5 text-center flex items-center justify-center gap-1">
                                <a href="{{ route('ujian.kartu.print', ['periode_id' => $activePeriode->id, 'selected_ids' => $p->id]) }}" target="_blank"
                                   class="px-2 py-1 bg-[#20C997] hover:bg-emerald-400 text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000]" title="Cetak Satuan">
                                    Cetak
                                </a>
                                <form action="{{ route('ujian.kartu.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus peserta ujian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-[#FF6B6B] hover:bg-red-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-500 italic">
                                Belum ada data peserta ujian. Silakan klik tombol <strong>🔄 Sinkronkan Siswa</strong> untuk menarik data dari basis data utama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pesertas instanceof \Illuminate\Pagination\LengthAwarePaginator && $pesertas->hasPages())
            <div class="mt-4 pt-4 border-t-2 border-black">
                {{ $pesertas->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Tambah Peserta Manual --}}
@if($activePeriode)
<div id="modalTambahPeserta" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>➕</span>
                <span>Tambah Peserta Ujian Manual</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahPeserta').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.kartu.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Lengkap Peserta</label>
                <input type="text" name="nama_peserta" required placeholder="Contoh: AHMAD FAUZI" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs uppercase">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nama Kelas / Rombel</label>
                    <input type="text" name="nama_kelas" required placeholder="Contoh: I.A, VI.B" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nomor Peserta Ujian</label>
                    <input type="text" name="nomor_peserta" required placeholder="Contoh: 26-01-01-001" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">NISN</label>
                    <input type="text" name="nisn" placeholder="Nomor NISN" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">NIS Lokal</label>
                    <input type="text" name="nis_lokal" placeholder="Nomor Induk Siswa" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" placeholder="Kota Lahir" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahPeserta').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Peserta
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Generate Nomor Peserta Terstruktur --}}
<div id="modalGenerateNomor" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl p-5 w-full max-w-lg shadow-[6px_6px_0px_0px_#000] space-y-4">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <div>
                <h3 class="font-heading font-black text-base text-black flex items-center gap-1.5">
                    <span>🔢</span>
                    <span>Generate Nomor Peserta Ujian</span>
                </h3>
                <p class="text-xs text-slate-600 font-medium">Atur format nomor peserta secara fleksibel atau gunakan format default.</p>
            </div>
            <button type="button" onclick="closeModalGenerateNomor()" class="text-black font-black text-lg p-1 hover:bg-slate-100 rounded border border-black cursor-pointer">✕</button>
        </div>

        <form action="{{ route('ujian.kartu.generate-nomor') }}" method="POST" id="formGenerateNomor" class="space-y-3.5 text-xs">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            {{-- 1. Pilihan Target Rombel --}}
            <div>
                <label class="block text-slate-800 font-black mb-1">Target Rombel / Kelas</label>
                <select name="school_class_id" id="gen_school_class_id" onchange="updateGenerateNomorPreview()" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 font-bold">
                    <option value="">-- Seluruh Rombel / Semua Siswa ({{ $pesertas->total() }} Siswa) --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" data-level="{{ $c->level }}" data-name="{{ $c->name }}">
                            Kelas {{ $c->name }} (Tingkat {{ $c->level }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-500 mt-0.5">Pilih rombel tertentu atau biarkan kosong untuk men-generate seluruh siswa.</p>
            </div>

            {{-- 2. Format Komponen Nomor --}}
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-slate-800 font-black mb-1">Tahun Ajaran (YY-YY)</label>
                    @php
                        $rawTahun = $activePeriode->tahun_pelajaran ?? date('Y');
                        $defaultTahunPrefix = '26-27';
                        if (preg_match('/\b(?:\d{2})?(\d{2})\s*[\/\-]\s*(?:\d{2})?(\d{2})\b/', $rawTahun, $m)) {
                            $defaultTahunPrefix = $m[1] . '-' . $m[2];
                        }
                    @endphp
                    <input type="text" name="tahun_prefix" id="gen_tahun_prefix" value="{{ $defaultTahunPrefix }}" oninput="updateGenerateNomorPreview()" class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 font-bold font-mono text-center">
                </div>

                <div>
                    <label class="block text-slate-800 font-black mb-1">Semester</label>
                    @php
                        $defaultSemester = str_contains(strtolower($activePeriode->semester ?? ''), 'genap') ? '2' : '1';
                    @endphp
                    <select name="semester" id="gen_semester" onchange="updateGenerateNomorPreview()" class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 font-bold">
                        <option value="1" {{ $defaultSemester === '1' ? 'selected' : '' }}>1 (Semester Ganjil)</option>
                        <option value="2" {{ $defaultSemester === '2' ? 'selected' : '' }}>2 (Semester Genap)</option>
                    </select>
                </div>
            </div>

            {{-- 3. Format Bagian Kelas & Jumlah Digit Urut --}}
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-slate-800 font-black mb-1">Format Identitas Kelas</label>
                    <select name="format_kelas" id="gen_format_kelas" onchange="updateGenerateNomorPreview()" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 font-bold">
                        <option value="tingkat" selected>Tingkat Kelas Saja: 1 (Default)</option>
                        <option value="nama_rombel_lower">Nama Rombel Kecil: 1a</option>
                        <option value="nama_rombel_upper">Nama Rombel Besar: 1A</option>
                        <option value="kode_rombel">Kode Numerik 2-Digit: 01</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-800 font-black mb-1">Digit Nomor Urut Absen</label>
                    <select name="digit_urut" id="gen_digit_urut" onchange="updateGenerateNomorPreview()" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 font-bold">
                        <option value="3" selected>3 Digit: 001 (Default)</option>
                        <option value="2">2 Digit: 01</option>
                        <option value="4">4 Digit: 0001</option>
                    </select>
                </div>
            </div>

            {{-- 4. Pengaturan Tanda Pemisah (Separators) --}}
            <div class="p-2.5 bg-slate-50 border-2 border-slate-300 rounded-lg space-y-1.5">
                <span class="block text-[11px] font-black text-slate-700">Tanda Pemisah (Separator)</span>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] text-slate-600 font-bold">Thn . Smt</label>
                        <input type="text" name="separator_1" id="gen_sep_1" value="." maxlength="2" oninput="updateGenerateNomorPreview()" class="w-full px-2 py-1 border border-black rounded text-center font-mono font-bold bg-white text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-600 font-bold">Smt - Kelas</label>
                        <input type="text" name="separator_2" id="gen_sep_2" value="-" maxlength="2" oninput="updateGenerateNomorPreview()" class="w-full px-2 py-1 border border-black rounded text-center font-mono font-bold bg-white text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-600 font-bold">Kelas . Urut</label>
                        <input type="text" name="separator_3" id="gen_sep_3" value="." maxlength="2" oninput="updateGenerateNomorPreview()" class="w-full px-2 py-1 border border-black rounded text-center font-mono font-bold bg-white text-xs">
                    </div>
                </div>
            </div>

            {{-- 5. Box Live Preview --}}
            <div class="p-3 bg-amber-50 border-2 border-black rounded-xl shadow-[2px_2px_0px_0px_#000] flex items-center justify-between gap-3">
                <div>
                    <div class="text-[10px] font-black uppercase text-amber-900 tracking-wider">Live Contoh Nomor Peserta</div>
                    <div class="text-base font-black font-mono text-black mt-0.5 tracking-wide" id="liveNomorPreview">
                        26-27.1-1.001
                    </div>
                    <div class="text-[10px] text-slate-600 font-medium" id="liveNomorDesc">
                        Contoh untuk Kelas 1 a (Siswa urutan absen ke-1)
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="px-2 py-1 bg-[#FFD43B] border border-black rounded text-[10px] font-black shadow-[1px_1px_0px_0px_#000]">
                        Urut Abjad A-Z
                    </span>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t-2 border-black">
                <button type="button" onclick="closeModalGenerateNomor()" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-600 font-black text-xs shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    ⚡ Generate Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function onClassFilterChange(selectElem) {
        const classId = selectElem.value;
        const ruangSelect = document.getElementById('selectRuangId');
        if (ruangSelect) {
            if (classId === 'SEMUA' || !classId) {
                ruangSelect.value = '';
            } else {
                // Temukan ruangan yang terikat langsung dengan rombel terpilih
                const matchingOption = ruangSelect.querySelector(`option[data-class-id="${classId}"]`);
                if (matchingOption) {
                    ruangSelect.value = matchingOption.value;
                } else {
                    ruangSelect.value = '';
                }
            }
        }
        selectElem.form.submit();
    }

    function onRuangFilterChange(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const classId = selectedOption ? selectedOption.getAttribute('data-class-id') : null;
        const classSelect = document.getElementById('selectClassId');
        if (classSelect && classId) {
            // Otomatis sinkronkan filter rombel ke rombel milik ruang ini
            classSelect.value = classId;
        }
        selectElem.form.submit();
    }

    function toggleCheckAll(source) {
        const checkboxes = document.querySelectorAll('.peserta-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.peserta-checkbox:checked');
        const countSpan = document.getElementById('jumlahTerpilih');
        if (countSpan) {
            countSpan.innerText = selected.length;
        }
    }

    function cetakKartuFilter() {
        const classSelect = document.querySelector('select[name="class_id"]');
        const classId = classSelect ? classSelect.value : 'SEMUA';
        const className = classSelect && classSelect.selectedIndex > 0 ? classSelect.options[classSelect.selectedIndex].text : 'SEMUA ROMBEL';
        const ruangSelect = document.querySelector('select[name="ruang_id"]');
        const ruangId = ruangSelect ? ruangSelect.value : '';
        const searchInput = document.querySelector('input[name="search"]');
        const search = searchInput ? searchInput.value : '';

        const teksInfo = (classId === 'SEMUA' || !classId) ? 'SEMUA KELAS' : className;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Menyiapkan Kartu...',
                text: `Mencetak kartu untuk ${teksInfo}`,
                allowOutsideClick: false,
                timer: 1500,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        const params = new URLSearchParams({
            periode_id: '{{ $activePeriode->id }}',
            class_id: classId,
            ruang_id: ruangId,
            search: search
        });

        const printUrl = "{{ route('ujian.kartu.print') }}?" + params.toString();
        window.open(printUrl, '_blank');
    }

    function cetakKartuTerpilih() {
        const selected = document.querySelectorAll('.peserta-checkbox:checked');
        if (selected.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Silakan centang minimal satu peserta yang ingin dicetak.',
                    icon: 'warning'
                });
            } else {
                alert('Silakan centang minimal satu peserta yang ingin dicetak.');
            }
            return;
        }

        const ids = Array.from(selected).map(cb => cb.value).join(',');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Menyiapkan Kartu...',
                text: `Mencetak ${selected.length} kartu peserta terpilih`,
                allowOutsideClick: false,
                timer: 1500,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        const printUrl = "{{ route('ujian.kartu.print') }}?periode_id={{ $activePeriode->id }}&selected_ids=" + ids;
        window.open(printUrl, '_blank');
    }

    function openModalGenerateNomor() {
        const modal = document.getElementById('modalGenerateNomor');
        if (modal) {
            modal.classList.remove('hidden');
            updateGenerateNomorPreview();
        }
    }

    function closeModalGenerateNomor() {
        const modal = document.getElementById('modalGenerateNomor');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function updateGenerateNomorPreview() {
        const tahun = document.getElementById('gen_tahun_prefix')?.value || '26-27';
        const semester = document.getElementById('gen_semester')?.value || '1';
        const formatKelas = document.getElementById('gen_format_kelas')?.value || 'tingkat';
        const digitUrut = parseInt(document.getElementById('gen_digit_urut')?.value || '3', 10);
        const sep1 = document.getElementById('gen_sep_1')?.value ?? '.';
        const sep2 = document.getElementById('gen_sep_2')?.value ?? '-';
        const sep3 = document.getElementById('gen_sep_3')?.value ?? '.';

        let classPart = '1';
        let sampleClassDesc = 'Kelas 1 a';
        const classSelect = document.getElementById('gen_school_class_id');
        if (classSelect && classSelect.value) {
            const opt = classSelect.options[classSelect.selectedIndex];
            const level = opt.getAttribute('data-level') || '1';
            const name = opt.getAttribute('data-name') || '1 a';
            sampleClassDesc = 'Kelas ' + name;

            if (formatKelas === 'tingkat') {
                classPart = level;
            } else if (formatKelas === 'nama_rombel_lower') {
                classPart = name.toLowerCase().replace(/kelas/gi, '').replace(/\s+/g, '') || level;
            } else if (formatKelas === 'nama_rombel_upper') {
                classPart = name.toUpperCase().replace(/kelas/gi, '').replace(/\s+/g, '') || level;
            } else if (formatKelas === 'kode_rombel') {
                classPart = '01';
            }
        } else {
            if (formatKelas === 'nama_rombel_lower') {
                classPart = '1a';
            } else if (formatKelas === 'nama_rombel_upper') {
                classPart = '1A';
            } else if (formatKelas === 'kode_rombel') {
                classPart = '01';
            } else {
                classPart = '1';
            }
        }

        const urutSample = '1'.padStart(digitUrut, '0');
        const result = `${tahun}${sep1}${semester}${sep2}${classPart}${sep3}${urutSample}`;

        const previewEl = document.getElementById('liveNomorPreview');
        if (previewEl) previewEl.innerText = result;

        const descEl = document.getElementById('liveNomorDesc');
        if (descEl) descEl.innerText = `Contoh untuk ${sampleClassDesc} (Siswa urutan absen ke-1)`;
    }
</script>
@endpush
@endif
@endsection
