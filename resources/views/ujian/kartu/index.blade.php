@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Manajemen Peserta & Cetak Kartu Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-6 transition-all duration-200" id="kartuUjianContainer">
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
                <button type="button" onclick="openModalTambahPeserta()" 
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
                    {{-- Tombol Buka Modal Siswa Terpilih (Badge Counter) --}}
                    <button type="button" onclick="bukaModalSiswaTerpilih()" 
                            class="px-3.5 py-1.5 bg-[#5294FF] hover:bg-blue-600 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>📋</span>
                        <span>Daftar Terpilih (<span class="jumlahTerpilihBadge">0</span>)</span>
                    </button>

                    {{-- Tombol Cetak Terpilih (Checkbox) --}}
                    <button type="button" onclick="cetakKartuTerpilih()" 
                            class="px-3.5 py-1.5 bg-[#FF6B6B] hover:bg-red-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🖨️</span>
                        <span>Cetak Terpilih (<span class="jumlahTerpilihBadge">0</span>)</span>
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
                        <th class="p-2.5 border-r border-black w-12 text-center whitespace-nowrap">No</th>
                        <th class="p-2.5 border-r border-black whitespace-nowrap min-w-[140px]">No. Peserta</th>
                        <th class="p-2.5 border-r border-black min-w-[180px]">Nama Siswa</th>
                        <th class="p-2.5 border-r border-black whitespace-nowrap min-w-[110px]">NIS / NISN</th>
                        <th class="p-2.5 border-r border-black text-center whitespace-nowrap min-w-[90px]">Kelas</th>
                        <th class="p-2.5 border-r border-black min-w-[160px]">Tempat, Tanggal Lahir</th>
                        <th class="p-2.5 border-r border-black text-center whitespace-nowrap min-w-[120px]">Ruang / Sesi</th>
                        <th class="p-2.5 text-center whitespace-nowrap w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($pesertas as $idx => $p)
                        <tr class="hover:bg-amber-50/50" id="row_peserta_{{ $p->id }}">
                            <td class="p-2.5 border-r border-black text-center">
                                <input type="checkbox" 
                                       class="peserta-checkbox rounded border-black cursor-pointer" 
                                       value="{{ $p->id }}"
                                       data-id="{{ $p->id }}"
                                       data-nama="{{ $p->nama_peserta }}"
                                       data-nomor="{{ $p->nomor_peserta }}"
                                       data-kelas="{{ $p->nama_kelas }}"
                                       data-ruang="{{ $p->ruang?->nama_ruang ?? '-' }}"
                                       data-kursi="{{ $p->nomor_kursi ?: '-' }}"
                                       onchange="handleCheckboxChange(this)">
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-bold whitespace-nowrap">
                                {{ $pesertas->firstItem() + $idx }}
                            </td>
                            <td class="p-2.5 border-r border-black font-mono font-black text-black whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded bg-amber-100 border border-black whitespace-nowrap">
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
                            <td class="p-2.5 border-r border-black font-mono text-[11px] text-slate-700 whitespace-nowrap">
                                <div>{{ $p->nisn ?: '-' }}</div>
                                <div class="text-[10px] text-slate-500">NIS: {{ $p->nis_lokal ?: '-' }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-black whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded bg-blue-50 text-blue-950 border border-black whitespace-nowrap">
                                    {{ $p->nama_kelas }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-slate-700">
                                {{ $p->tempat_tanggal_lahir }}
                            </td>
                            <td class="p-2.5 border-r border-black text-center whitespace-nowrap">
                                @if($p->ruang)
                                    <div class="font-bold text-black">{{ $p->ruang->nama_ruang }}</div>
                                    <div class="text-[10px] text-slate-500">Kursi: {{ $p->nomor_kursi ?: '-' }}</div>
                                @else
                                    <span class="text-slate-400 italic">Belum diatur</span>
                                @endif
                            </td>
                            <td class="p-2.5 text-center whitespace-nowrap flex items-center justify-center gap-1">
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
<div id="modalTambahPeserta" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150 space-y-3.5">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <div>
                <h3 class="text-sm font-black font-heading text-black flex items-center gap-1.5">
                    <span>➕</span>
                    <span>Tambah Peserta Ujian Manual</span>
                </h3>
                <p class="text-[11px] text-slate-600 font-medium mt-0.5">Pilih siswa aktif dari Master Data Siswa untuk dimasukkan ke ujian.</p>
            </div>
            <button type="button" onclick="closeModalTambahPeserta()" class="text-black font-black text-lg p-1 hover:bg-slate-100 rounded border border-black cursor-pointer leading-none">✕</button>
        </div>

        {{-- Banner Peringatan & Tautan ke Master Siswa --}}
        <div class="p-3 bg-amber-50 border-2 border-black rounded-lg flex items-center justify-between gap-3">
            <div class="text-[11px] text-slate-800 leading-snug">
                <span class="font-bold text-amber-950">ℹ️ Info:</span> Siswa yang dapat ditambahkan harus sudah terdaftar aktif di menu Master Data Siswa.
            </div>
            <a href="{{ route('admin.students.index') }}" target="_blank" 
               class="px-2.5 py-1.5 bg-[#20C997] hover:bg-emerald-400 text-black border border-black rounded text-[11px] font-black shrink-0 flex items-center gap-1 shadow-[1px_1px_0px_#000]" title="Buka menu data siswa di tab baru">
                <span>➕</span> Data Siswa ↗
            </a>
        </div>

        <form action="{{ route('ujian.kartu.store') }}" method="POST" id="formTambahPeserta" class="space-y-3 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            {{-- 1. Pilihan Kelas --}}
            <div>
                <label class="block text-slate-800 font-bold mb-1">1. Pilih Rombel / Kelas Target *</label>
                <select id="modal_tambah_class_id" onchange="onClassChangeTambahPeserta(this.value)" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 font-bold text-xs" required>
                    <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">Kelas {{ $c->name }} (Tingkat {{ $c->level }})</option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Pilihan Siswa --}}
            <div>
                <label class="block text-slate-800 font-bold mb-1">2. Pilih Siswa (Belum Terdaftar Ujian) *</label>
                <select name="student_id" id="modal_tambah_student_id" onchange="onStudentSelectChange(this)" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 font-bold text-xs" required disabled>
                    <option value="">-- Pilih Kelas di atas terlebih dahulu --</option>
                </select>
                <p id="modal_tambah_student_loading" class="text-[11px] text-blue-600 font-semibold mt-1 hidden">⏳ Sedang memuat daftar siswa kelas...</p>
                <p id="modal_tambah_student_empty" class="text-[11px] text-amber-700 font-bold mt-1 hidden">Semua siswa di kelas ini sudah terdaftar sebagai peserta ujian.</p>
            </div>

            {{-- Preview Biodata Terverifikasi --}}
            <div id="modal_tambah_preview_card" class="p-3 bg-blue-50/70 border-2 border-dashed border-blue-900 rounded-lg hidden space-y-1.5 text-[11px]">
                <div class="flex items-center justify-between border-b border-blue-200 pb-1">
                    <span id="prev_siswa_nama" class="font-heading font-black text-xs text-blue-950">Nama Siswa</span>
                    <span id="prev_siswa_gender" class="px-1.5 py-0.2 bg-white border border-blue-900 rounded font-mono font-bold text-[10px] text-black">L</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-slate-700">
                    <div>NISN: <span id="prev_siswa_nisn" class="font-mono font-bold text-black">-</span></div>
                    <div>NIS Lokal: <span id="prev_siswa_nis" class="font-mono font-bold text-black">-</span></div>
                    <div class="col-span-2">TTL: <span id="prev_siswa_ttl" class="font-bold text-black">-</span></div>
                </div>
            </div>

            {{-- Detail Ujian Tambahan (Opsional) --}}
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-200">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nomor Peserta Ujian (Opsional)</label>
                    <input type="text" name="nomor_peserta" placeholder="Kosongkan jika auto-generate" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Ruang Ujian (Opsional)</label>
                    <select name="ujian_ruang_id" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                        <option value="">-- Otomatis Ruang Kelas --</option>
                        @foreach($ruangs as $r)
                            <option value="{{ $r->id }}">{{ $r->nama_ruang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-black">
                <button type="button" onclick="closeModalTambahPeserta()" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitTambahPeserta" disabled class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    Simpan Peserta Ujian
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

            {{-- 5. Mode Penomoran (Append-only vs Reset) --}}
            <div class="p-2.5 bg-slate-50 border-2 border-black rounded-lg space-y-1.5">
                <label class="block text-slate-800 font-black">Mode Penomoran</label>
                <div class="space-y-1.5">
                    <label class="flex items-start gap-2 cursor-pointer font-bold text-slate-900">
                        <input type="radio" name="reset_existing" value="0" checked class="mt-0.5 text-black focus:ring-0">
                        <span>
                            <span class="block">Teruskan nomor & kursi yang ada (Append-Only)</span>
                            <span class="block text-[10px] text-slate-500 font-normal">Siswa yang sudah punya nomor/kursi tidak diubah. Siswa baru meneruskan nomor urut terakhir (Rekomendasi).</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer font-bold text-rose-700">
                        <input type="radio" name="reset_existing" value="1" class="mt-0.5 text-rose-600 focus:ring-0">
                        <span>
                            <span class="block">Reset ulang seluruh nomor & kursi</span>
                            <span class="block text-[10px] text-rose-500 font-normal">Semua siswa diurutkan ulang dari 1 berdasarkan abjad nama A-Z.</span>
                        </span>
                    </label>
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

{{-- Bilah Mengambang Siswa Terpilih (Floating Selection Bar) --}}
<div id="floatingSelectionBar" class="hidden fixed bottom-4 inset-x-4 max-w-4xl mx-auto z-40 bg-[#FFD43B] border-3 border-black p-3 sm:p-4 rounded-xl shadow-[4px_4px_0px_0px_#000] flex flex-col sm:flex-row items-center justify-between gap-3 animate-in fade-in slide-in-from-bottom-4 duration-200">
    <div class="flex items-center gap-2.5 w-full sm:w-auto">
        <span class="w-8 h-8 rounded-lg bg-black text-[#FFD43B] font-black text-sm flex items-center justify-center shrink-0">
            📌
        </span>
        <div class="min-w-0">
            <div class="font-heading font-black text-xs sm:text-sm text-black uppercase tracking-tight">
                <span id="floatingCount">0</span> Siswa Terpilih (Lintas Kelas)
            </div>
            <p class="text-[11px] font-semibold text-slate-800 truncate">
                Pilihan tetap tersimpan saat berganti filter rombel atau melakukan pencarian.
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap justify-end">
        <button type="button" onclick="bukaModalSiswaTerpilih()" 
                class="px-3 py-1.5 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1 cursor-pointer">
            <span>👁️</span> Lihat Daftar (<span class="jumlahTerpilihBadge">0</span>)
        </button>
        <button type="button" onclick="cetakKartuTerpilih()" 
                class="px-3 py-1.5 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1 cursor-pointer">
            <span>🖨️</span> Cetak Kartu
        </button>
        <button type="button" onclick="cetakLabelMejaTerpilih()" 
                class="px-3 py-1.5 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1 cursor-pointer">
            <span>🏷️</span> Cetak Label
        </button>
        <button type="button" onclick="konfirmasiKosongkanPilihan()" 
                class="px-2.5 py-1.5 bg-[#FF6B6B] hover:bg-red-500 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer" title="Kosongkan Semua Pilihan">
            ✕
        </button>
    </div>
</div>

{{-- Modal Dialog: Daftar Siswa Terpilih (Native Dialog) --}}
<dialog id="modalSiswaTerpilih" aria-labelledby="modalSiswaTerpilihTitle" class="bg-transparent p-0 m-auto w-[calc(100%-1.5rem)] sm:w-full max-w-3xl border-0 shadow-none backdrop:bg-black/75">
    <div class="bg-white border-4 border-black w-full max-w-3xl shadow-[6px_6px_0px_0px_#000] flex flex-col max-h-[85vh] sm:max-h-[90vh] overflow-hidden rounded-xl">
        <!-- Header Modal -->
        <div class="bg-[#FFD43B] border-b-3 border-black p-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-black text-[#FFD43B] font-black text-sm flex items-center justify-center shrink-0">
                    📋
                </span>
                <div>
                    <h3 id="modalSiswaTerpilihTitle" class="font-heading font-black text-base sm:text-lg text-black uppercase">
                        Daftar Siswa Terpilih Untuk Dicetak
                    </h3>
                    <p class="text-[11px] font-semibold text-slate-800">
                        Total <span id="modalSelectedCountText">0</span> siswa terpilih lintas rombel/pencarian
                    </p>
                </div>
            </div>
            <button type="button" onclick="tutupModalSiswaTerpilih()" aria-label="Tutup modal" class="w-9 h-9 bg-white hover:bg-slate-100 text-black border-2 border-black font-black flex items-center justify-center shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Toolbar di Modal & Tombol Kosongkan -->
        <div class="p-3 border-b-2 border-black bg-slate-50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 shrink-0">
            <div class="text-xs font-bold text-slate-700">
                Siswa di bawah ini adalah siswa yang akan dicetak Kartu Ujian atau Label Meja.
            </div>
            <button type="button" onclick="konfirmasiKosongkanPilihan()" class="px-3 py-1 bg-[#FFE3E3] hover:bg-red-200 text-rose-950 border-2 border-black rounded text-xs font-black shadow-[1px_1px_0px_0px_#000] flex items-center justify-center gap-1 cursor-pointer">
                <span>🗑️</span> Kosongkan Semua Pilihan
            </button>
        </div>

        <!-- Body Modal: Tabel Siswa Terpilih -->
        <div class="p-4 overflow-y-auto flex-1 space-y-3" id="modalSiswaTerpilihBody">
            <div class="border-2 border-black rounded-lg overflow-x-auto" tabindex="0" role="region" aria-label="Tabel rincian siswa terpilih">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-[#FFD43B] font-heading font-black uppercase text-[10px] border-b-2 border-black">
                            <th class="p-2.5 text-center w-12 border-r border-slate-700 whitespace-nowrap">No</th>
                            <th class="p-2.5 border-r border-slate-700 whitespace-nowrap min-w-[130px]">No. Peserta</th>
                            <th class="p-2.5 border-r border-slate-700 min-w-[170px]">Nama Lengkap Siswa</th>
                            <th class="p-2.5 text-center border-r border-slate-700 whitespace-nowrap min-w-[85px]">Kelas</th>
                            <th class="p-2.5 border-r border-slate-700 whitespace-nowrap min-w-[120px]">Ruang & Kursi</th>
                            <th class="p-2.5 text-center whitespace-nowrap w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableSiswaTerpilihBody" class="divide-y divide-black font-semibold">
                        <!-- Dynamic rows built via safe textContent DOM methods -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Modal: Aksi Cetak & Tutup -->
        <div class="bg-slate-100 border-t-2 border-black p-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 shrink-0">
            <div class="text-xs font-bold text-slate-600 text-center sm:text-left">
                Pencetakan akan membuka halaman cetak A4 di tab baru.
            </div>
            <div class="flex items-center gap-2 justify-end flex-wrap">
                <button type="button" onclick="tutupModalSiswaTerpilih()" 
                        class="px-4 py-2 bg-white hover:bg-slate-200 text-black border-2 border-black rounded-lg text-xs font-heading font-black uppercase shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Tutup
                </button>
                <button type="button" onclick="cetakKartuTerpilih()" 
                        class="px-4 py-2 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-heading font-black uppercase shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🖨️</span> Cetak Kartu (<span class="jumlahTerpilihBadge">0</span>)
                </button>
                <button type="button" onclick="cetakLabelMejaTerpilih()" 
                        class="px-4 py-2 bg-[#5294FF] hover:bg-blue-500 text-white border-2 border-black rounded-lg text-xs font-heading font-black uppercase shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🏷️</span> Cetak Label Meja (<span class="jumlahTerpilihBadge">0</span>)
                </button>
            </div>
        </div>
    </div>
</dialog>

@push('scripts')
<style>
    dialog#modalSiswaTerpilih:modal {
        overscroll-behavior: contain;
    }
    dialog#modalSiswaTerpilih::backdrop {
        background-color: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(2px);
        overflow: hidden;
        overscroll-behavior: contain;
    }
</style>
<script>
    const activePeriodeId = '{{ $activePeriode->id ?? "" }}';
    const storageKey = 'ujian_kartu_selected_periode_' + activePeriodeId;

    function getSelectedMap() {
        if (!activePeriodeId) return {};
        try {
            const raw = sessionStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            console.error('Gagal membaca data siswa terpilih dari sessionStorage:', e);
            return {};
        }
    }

    function saveSelectedMap(map) {
        if (!activePeriodeId) return;
        try {
            sessionStorage.setItem(storageKey, JSON.stringify(map));
        } catch (e) {
            console.error('Gagal menyimpan data siswa terpilih ke sessionStorage:', e);
        }
    }

    function initSelectionState() {
        const map = getSelectedMap();
        let mapUpdated = false;
        const checkboxes = document.querySelectorAll('.peserta-checkbox');
        let visibleCheckedCount = 0;

        checkboxes.forEach(cb => {
            const id = cb.value;
            if (map[id]) {
                cb.checked = true;
                visibleCheckedCount++;

                // Sinkronkan / refresh metadata DOM terbaru (jika ada pembaruan nomor/kursi/ruang)
                map[id] = {
                    id: id,
                    nama: cb.getAttribute('data-nama') || map[id].nama || '',
                    nomor: cb.getAttribute('data-nomor') || map[id].nomor || '',
                    kelas: cb.getAttribute('data-kelas') || map[id].kelas || '',
                    ruang: cb.getAttribute('data-ruang') || map[id].ruang || '-',
                    kursi: cb.getAttribute('data-kursi') || map[id].kursi || '-'
                };
                mapUpdated = true;
            } else {
                cb.checked = false;
            }
        });

        if (mapUpdated) {
            saveSelectedMap(map);
        }

        // Sinkronkan status checkAll (checked vs indeterminate)
        const checkAll = document.getElementById('checkAll');
        if (checkAll && checkboxes.length > 0) {
            if (visibleCheckedCount === checkboxes.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else if (visibleCheckedCount > 0) {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
        }

        updateUIState();
    }

    function handleCheckboxChange(cb) {
        const map = getSelectedMap();
        const id = cb.value;

        if (cb.checked) {
            map[id] = {
                id: id,
                nama: cb.getAttribute('data-nama') || '',
                nomor: cb.getAttribute('data-nomor') || '',
                kelas: cb.getAttribute('data-kelas') || '',
                ruang: cb.getAttribute('data-ruang') || '-',
                kursi: cb.getAttribute('data-kursi') || '-'
            };
        } else {
            delete map[id];
        }

        saveSelectedMap(map);

        // Update checkAll status
        const checkboxes = document.querySelectorAll('.peserta-checkbox');
        const checkedCount = document.querySelectorAll('.peserta-checkbox:checked').length;
        const checkAll = document.getElementById('checkAll');
        if (checkAll && checkboxes.length > 0) {
            if (checkedCount === checkboxes.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else if (checkedCount > 0) {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
        }

        updateUIState();

        // Jika modal sedang terbuka, render ulang tabelnya
        const modal = document.getElementById('modalSiswaTerpilih');
        if (modal && modal.open) {
            renderModalSiswaTerpilih();
        }
    }

    function toggleCheckAll(source) {
        const checkboxes = document.querySelectorAll('.peserta-checkbox');
        const map = getSelectedMap();

        checkboxes.forEach(cb => {
            cb.checked = source.checked;
            const id = cb.value;
            if (source.checked) {
                map[id] = {
                    id: id,
                    nama: cb.getAttribute('data-nama') || '',
                    nomor: cb.getAttribute('data-nomor') || '',
                    kelas: cb.getAttribute('data-kelas') || '',
                    ruang: cb.getAttribute('data-ruang') || '-',
                    kursi: cb.getAttribute('data-kursi') || '-'
                };
            } else {
                delete map[id];
            }
        });

        source.indeterminate = false;
        saveSelectedMap(map);
        updateUIState();

        const modal = document.getElementById('modalSiswaTerpilih');
        if (modal && modal.open) {
            renderModalSiswaTerpilih();
        }
    }

    function updateUIState() {
        const map = getSelectedMap();
        const items = Object.values(map);
        const count = items.length;

        // Update semua elemen badge
        document.querySelectorAll('.jumlahTerpilihBadge').forEach(el => {
            el.textContent = count;
        });

        const floatingCount = document.getElementById('floatingCount');
        if (floatingCount) floatingCount.textContent = count;

        const modalCount = document.getElementById('modalSelectedCountText');
        if (modalCount) modalCount.textContent = count;

        // Toggle Floating Bar & Container Bottom Padding (pb-24)
        const floatingBar = document.getElementById('floatingSelectionBar');
        const container = document.getElementById('kartuUjianContainer');

        if (count > 0) {
            if (floatingBar) floatingBar.classList.remove('hidden');
            if (container) container.classList.add('pb-24');
        } else {
            if (floatingBar) floatingBar.classList.add('hidden');
            if (container) container.classList.remove('pb-24');
        }
    }

    function bukaModalSiswaTerpilih() {
        renderModalSiswaTerpilih();
        const modal = document.getElementById('modalSiswaTerpilih');
        if (modal) {
            if (typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                modal.classList.remove('hidden');
            }
        }
    }

    function tutupModalSiswaTerpilih() {
        const modal = document.getElementById('modalSiswaTerpilih');
        if (modal) {
            if (typeof modal.close === 'function') {
                modal.close();
            } else {
                modal.classList.add('hidden');
            }
        }
    }

    function renderModalSiswaTerpilih() {
        const tbody = document.getElementById('tableSiswaTerpilihBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        const items = Object.values(getSelectedMap());

        if (items.length === 0) {
            const emptyTr = document.createElement('tr');
            const emptyTd = document.createElement('td');
            emptyTd.colSpan = 6;
            emptyTd.className = 'p-6 text-center text-slate-500 italic';
            emptyTd.textContent = 'Belum ada siswa yang dicentang. Silakan centang siswa pada tabel di halaman.';
            emptyTr.appendChild(emptyTd);
            tbody.appendChild(emptyTr);
            return;
        }

        items.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-amber-50/50 transition-colors';

            // Col 1: No
            const tdNo = document.createElement('td');
            tdNo.className = 'p-2.5 text-center font-bold border-r border-slate-300';
            tdNo.textContent = index + 1;
            tr.appendChild(tdNo);

            // Col 2: No Peserta
            const tdNomor = document.createElement('td');
            tdNomor.className = 'p-2.5 border-r border-slate-300 font-mono font-bold text-black whitespace-nowrap';
            const badgeNomor = document.createElement('span');
            badgeNomor.className = 'inline-block px-2 py-0.5 rounded bg-amber-100 border border-black whitespace-nowrap';
            badgeNomor.textContent = item.nomor || '-';
            tdNomor.appendChild(badgeNomor);
            tr.appendChild(tdNomor);

            // Col 3: Nama Lengkap Siswa (Aman dari nama bertanda petik seperti Syafi'i)
            const tdNama = document.createElement('td');
            tdNama.className = 'p-2.5 border-r border-slate-300 font-bold text-black';
            tdNama.textContent = item.nama || '-';
            tr.appendChild(tdNama);

            // Col 4: Kelas
            const tdKelas = document.createElement('td');
            tdKelas.className = 'p-2.5 border-r border-slate-300 text-center font-black whitespace-nowrap';
            const badgeKelas = document.createElement('span');
            badgeKelas.className = 'inline-block px-2.5 py-0.5 rounded bg-blue-50 text-blue-950 border border-black text-[11px] whitespace-nowrap';
            badgeKelas.textContent = item.kelas || '-';
            tdKelas.appendChild(badgeKelas);
            tr.appendChild(tdKelas);

            // Col 5: Ruang & Kursi
            const tdRuang = document.createElement('td');
            tdRuang.className = 'p-2.5 border-r border-slate-300 text-slate-700 whitespace-nowrap';
            tdRuang.textContent = `${item.ruang || '-'} (Kursi: ${item.kursi || '-'})`;
            tr.appendChild(tdRuang);

            // Col 6: Tombol Hapus Satuan
            const tdAksi = document.createElement('td');
            tdAksi.className = 'p-2.5 text-center';
            const btnHapus = document.createElement('button');
            btnHapus.type = 'button';
            btnHapus.className = 'px-2 py-1 bg-rose-100 hover:bg-rose-200 text-rose-800 border border-black rounded text-[10px] font-black cursor-pointer shadow-[1px_1px_0px_0px_#000]';
            btnHapus.textContent = '✕ Hapus';
            btnHapus.onclick = () => removeSingleSelected(item.id);
            tdAksi.appendChild(btnHapus);
            tr.appendChild(tdAksi);

            tbody.appendChild(tr);
        });
    }

    function removeSingleSelected(id) {
        const map = getSelectedMap();
        delete map[id];
        saveSelectedMap(map);

        // Uncheck di DOM jika siswa sedang terlihat
        const cb = document.querySelector(`.peserta-checkbox[value="${id}"]`);
        if (cb) {
            cb.checked = false;
        }

        // Update checkAll
        const checkboxes = document.querySelectorAll('.peserta-checkbox');
        const checkedCount = document.querySelectorAll('.peserta-checkbox:checked').length;
        const checkAll = document.getElementById('checkAll');
        if (checkAll && checkboxes.length > 0) {
            if (checkedCount === checkboxes.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else if (checkedCount > 0) {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
        }

        updateUIState();
        renderModalSiswaTerpilih();
    }

    function konfirmasiKosongkanPilihan() {
        const count = Object.keys(getSelectedMap()).length;
        if (count === 0) return;

        if (confirm(`Apakah Anda yakin ingin mengosongkan seluruh ${count} siswa yang telah dicentang?`)) {
            sessionStorage.removeItem(storageKey);

            document.querySelectorAll('.peserta-checkbox').forEach(cb => cb.checked = false);
            const checkAll = document.getElementById('checkAll');
            if (checkAll) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }

            updateUIState();
            renderModalSiswaTerpilih();
            tutupModalSiswaTerpilih();
        }
    }

    function submitPrintForm(actionUrl, ids) {
        if (!ids || ids.length === 0) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = actionUrl;
        form.target = '_blank';

        // CSRF Token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Periode ID (terkunci sesuai periode aktif)
        const periodeInput = document.createElement('input');
        periodeInput.type = 'hidden';
        periodeInput.name = 'periode_id';
        periodeInput.value = activePeriodeId;
        form.appendChild(periodeInput);

        // Selected IDs dikirim sebagai array input
        ids.forEach(id => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'selected_ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });

        document.body.appendChild(form);
        form.submit();
        setTimeout(() => form.remove(), 1000);
    }

    function cetakKartuTerpilih() {
        const map = getSelectedMap();
        const ids = Object.keys(map);

        if (ids.length === 0) {
            alert('Silakan centang minimal satu peserta yang ingin dicetak.');
            return;
        }

        submitPrintForm("{{ route('ujian.kartu.print') }}", ids);
    }

    function cetakLabelMejaTerpilih() {
        const map = getSelectedMap();
        const ids = Object.keys(map);

        if (ids.length === 0) {
            alert('Silakan centang minimal satu peserta yang ingin dicetak label mejanya.');
            return;
        }

        submitPrintForm("{{ route('ujian.kartu.print-label-meja') }}", ids);
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

    function onClassFilterChange(selectElem) {
        const classId = selectElem.value;
        const ruangSelect = document.getElementById('selectRuangId');
        if (ruangSelect) {
            if (classId === 'SEMUA' || !classId) {
                ruangSelect.value = '';
            } else {
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
            classSelect.value = classId;
        }
        selectElem.form.submit();
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

    // Modal Tambah Peserta Manual Terikat Master Siswa
    let cachedAvailableStudents = {};

    function openModalTambahPeserta() {
        const modal = document.getElementById('modalTambahPeserta');
        if (modal) modal.classList.remove('hidden');
    }

    function closeModalTambahPeserta() {
        const modal = document.getElementById('modalTambahPeserta');
        if (modal) {
            modal.classList.add('hidden');
            const classSelect = document.getElementById('modal_tambah_class_id');
            if (classSelect) classSelect.value = '';
            const studentSelect = document.getElementById('modal_tambah_student_id');
            if (studentSelect) {
                studentSelect.innerHTML = '<option value="">-- Pilih Kelas di atas terlebih dahulu --</option>';
                studentSelect.disabled = true;
            }
            const previewCard = document.getElementById('modal_tambah_preview_card');
            if (previewCard) previewCard.classList.add('hidden');
            const btnSubmit = document.getElementById('btnSubmitTambahPeserta');
            if (btnSubmit) btnSubmit.disabled = true;
            const emptyNotice = document.getElementById('modal_tambah_student_empty');
            if (emptyNotice) emptyNotice.classList.add('hidden');
            const loadingNotice = document.getElementById('modal_tambah_student_loading');
            if (loadingNotice) loadingNotice.classList.add('hidden');
        }
    }

    function onClassChangeTambahPeserta(classId) {
        const studentSelect = document.getElementById('modal_tambah_student_id');
        const previewCard = document.getElementById('modal_tambah_preview_card');
        const btnSubmit = document.getElementById('btnSubmitTambahPeserta');
        const emptyNotice = document.getElementById('modal_tambah_student_empty');
        const loadingNotice = document.getElementById('modal_tambah_student_loading');

        if (previewCard) previewCard.classList.add('hidden');
        if (btnSubmit) btnSubmit.disabled = true;
        if (emptyNotice) emptyNotice.classList.add('hidden');

        if (!classId) {
            studentSelect.innerHTML = '<option value="">-- Pilih Kelas di atas terlebih dahulu --</option>';
            studentSelect.disabled = true;
            return;
        }

        studentSelect.disabled = true;
        if (loadingNotice) loadingNotice.classList.remove('hidden');

        const url = "{{ route('ujian.kartu.available-students') }}?school_class_id=" + encodeURIComponent(classId) + "&ujian_periode_id={{ $activePeriode->id }}";

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (loadingNotice) loadingNotice.classList.add('hidden');
                cachedAvailableStudents = {};
                studentSelect.innerHTML = '';

                const students = data.students || [];
                if (students.length === 0) {
                    studentSelect.innerHTML = '<option value="">-- Tidak ada siswa yang tersedia --</option>';
                    studentSelect.disabled = true;
                    if (emptyNotice) emptyNotice.classList.remove('hidden');
                } else {
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = `-- Pilih Siswa (${students.length} Siswa Tersedia) --`;
                    studentSelect.appendChild(defaultOpt);

                    students.forEach(s => {
                        cachedAvailableStudents[s.id] = s;
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = `${s.name} (NISN: ${s.nisn || '-'})`;
                        studentSelect.appendChild(opt);
                    });

                    studentSelect.disabled = false;
                }
            })
            .catch(err => {
                if (loadingNotice) loadingNotice.classList.add('hidden');
                studentSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
                console.error(err);
            });
    }

    function onStudentSelectChange(selectElem) {
        const studentId = selectElem.value;
        const previewCard = document.getElementById('modal_tambah_preview_card');
        const btnSubmit = document.getElementById('btnSubmitTambahPeserta');

        if (!studentId || !cachedAvailableStudents[studentId]) {
            if (previewCard) previewCard.classList.add('hidden');
            if (btnSubmit) btnSubmit.disabled = true;
            return;
        }

        const s = cachedAvailableStudents[studentId];
        document.getElementById('prev_siswa_nama').textContent = s.name;
        document.getElementById('prev_siswa_gender').textContent = s.gender === 'P' ? 'Perempuan (P)' : 'Laki-laki (L)';
        document.getElementById('prev_siswa_nisn').textContent = s.nisn || '-';
        document.getElementById('prev_siswa_nis').textContent = s.nis || '-';
        
        let ttl = '-';
        if (s.birth_place !== '-' || s.birth_date_formatted !== '-') {
            ttl = `${s.birth_place !== '-' ? s.birth_place : ''}${s.birth_place !== '-' && s.birth_date_formatted !== '-' ? ', ' : ''}${s.birth_date_formatted !== '-' ? s.birth_date_formatted : ''}`;
        }
        document.getElementById('prev_siswa_ttl').textContent = ttl;

        if (previewCard) previewCard.classList.remove('hidden');
        if (btnSubmit) btnSubmit.disabled = false;
    }

    // Modal backdrop click listener
    const modalSiswaTerpilih = document.getElementById('modalSiswaTerpilih');
    if (modalSiswaTerpilih) {
        modalSiswaTerpilih.addEventListener('click', function(e) {
            if (e.target === this) {
                tutupModalSiswaTerpilih();
            }
        });
    }

    // Inisialisasi status seleksi saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        initSelectionState();
    });
</script>
@endpush
@endif
@endsection
