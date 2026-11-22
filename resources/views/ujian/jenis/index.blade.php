@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Jenis Asesmen & Periode Ujian Kurikulum Merdeka')

@section('content')
<div class="space-y-6">
    {{-- Header Card --}}
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
                Mengelola master jenis asesmen Kurikulum Merdeka (SLM, STS, SAS, SAT, UM), penetapan periode aktif, dan pemetaan kalender ujian per kelompok.
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

    {{-- Flash Alerts --}}
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

    {{-- Navigasi Tab Neo-Brutalism --}}
    @php
        $tabs = [
            'periode' => ['label' => 'Periode Pelaksanaan', 'icon' => '🗓️'],
            'jenis' => ['label' => 'Master Jenis Asesmen', 'icon' => '📚'],
            'pemetaan-hari' => ['label' => 'Pemetaan Hari & Sesi Ujian', 'icon' => '⏱️'],
        ];
    @endphp

    <div class="flex items-center gap-2 border-b-3 border-black pb-0 overflow-x-auto">
        @foreach($tabs as $key => $tab)
            @php $isActive = ($activeTab === $key); @endphp
            <a href="{{ route('ujian.jenis.index', ['tab' => $key, 'periode_id' => $selectedPeriodeId]) }}"
               class="px-4 py-2.5 rounded-t-xl border-t-3 border-x-3 border-black font-black text-xs flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap {{ $isActive ? 'bg-white text-black shadow-[2px_-2px_0px_0px_#000] -mb-[3px] border-b-3 border-b-white z-10' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                <span>{{ $tab['icon'] }}</span>
                <span>{{ $tab['label'] }}</span>
                @if($key === 'pemetaan-hari' && $configs->isNotEmpty())
                    <span class="w-2 h-2 rounded-full bg-[#20C997] border border-black"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Konten Tab Aktif --}}
    @if($activeTab === 'periode')
        @include('ujian.jenis.partials._tab-periode')
    @elseif($activeTab === 'jenis')
        @include('ujian.jenis.partials._tab-jenis')
    @elseif($activeTab === 'pemetaan-hari')
        @include('ujian.jenis.partials._tab-pemetaan-hari')
    @endif
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

{{-- Modal Edit Periode --}}
<div id="modalEditPeriode" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>✏️</span>
                <span>Edit Periode Pelaksanaan Ujian</span>
            </h3>
            <button type="button" onclick="closeEditPeriodeModal()" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form id="formEditPeriode" action="" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Jenis Asesmen</label>
                <select name="ujian_jenis_id" id="edit_ujian_jenis_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                    @foreach($jenisList as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jenis }} ({{ $j->kode_jenis }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Periode Ujian</label>
                <input type="text" name="nama_periode" id="edit_nama_periode" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tahun Pelajaran</label>
                    <input type="text" name="tahun_pelajaran" id="edit_tahun_pelajaran" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Semester</label>
                    <select name="semester" id="edit_semester" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tempat Cetak Kartu</label>
                    <input type="text" name="tempat_cetak" id="edit_tempat_cetak" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Cetak Kartu</label>
                    <input type="date" name="tanggal_cetak_kartu" id="edit_tanggal_cetak_kartu" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="closeEditPeriodeModal()" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#4DABF7] hover:bg-blue-500 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Perbarui Periode
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

@push('scripts')
    <script>
        function openEditPeriodeModal(data) {
            const modal = document.getElementById('modalEditPeriode');
            const form = document.getElementById('formEditPeriode');
            if (!modal || !form) return;

            form.action = data.update_url;
            document.getElementById('edit_ujian_jenis_id').value = data.ujian_jenis_id || '';
            document.getElementById('edit_nama_periode').value = data.nama_periode || '';
            document.getElementById('edit_tahun_pelajaran').value = data.tahun_pelajaran || '';
            document.getElementById('edit_semester').value = data.semester || 'Ganjil';
            document.getElementById('edit_tanggal_mulai').value = data.tanggal_mulai || '';
            document.getElementById('edit_tanggal_selesai').value = data.tanggal_selesai || '';
            document.getElementById('edit_tempat_cetak').value = data.tempat_cetak || 'Tanggamus';
            document.getElementById('edit_tanggal_cetak_kartu').value = data.tanggal_cetak_kartu || '';

            modal.classList.remove('hidden');
        }

        function closeEditPeriodeModal() {
            const modal = document.getElementById('modalEditPeriode');
            if (modal) modal.classList.add('hidden');
        }
    </script>
    @include('ujian.jenis.partials._pemetaan-scripts')
@endpush
@endsection
