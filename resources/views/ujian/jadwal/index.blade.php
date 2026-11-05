@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.guru')

@section('title', 'Jadwal Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 2
                </span>
                <span class="text-xs font-bold text-slate-500">Anti-Bentrok & Duplikasi Cepat</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Jadwal Mata Pelajaran Ujian
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Mengatur slot waktu ujian per rombel/tingkat menggunakan daftar 16 mata pelajaran madrasah yang telah terdaftar.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($activePeriode)
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id]) }}" target="_blank"
                   class="px-3.5 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🖨️</span>
                    <span>Cetak Lembar Jadwal</span>
                </a>
                <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>📑</span>
                    <span>Salin Antar Rombel</span>
                </button>
                <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>➕</span>
                    <span>Tambah Slot Jadwal</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Switch Periode & Alerts --}}
    <div class="flex items-center justify-between gap-4 p-4 bg-slate-100 border-2 border-black rounded-xl">
        <form method="GET" action="{{ route('ujian.jadwal.index') }}" class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-700">Pilih Periode:</span>
            <select name="periode_id" onchange="this.form.submit()" class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[1px_1px_0px_0px_#000]">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                        {{ $p->nama_periode }} {{ $p->is_active ? '★' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        <span class="text-xs font-bold text-slate-600">Total Slot: <strong class="text-black">{{ $jadwals->count() }}</strong></span>
    </div>

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

    {{-- Tabel Jadwal Ujian --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black">Hari / Tanggal</th>
                        <th class="p-2.5 border-r border-black text-center">Waktu (Jam)</th>
                        <th class="p-2.5 border-r border-black text-center">Sesi</th>
                        <th class="p-2.5 border-r border-black">Mata Pelajaran</th>
                        <th class="p-2.5 border-r border-black">Rombel / Tingkat</th>
                        <th class="p-2.5 border-r border-black text-center">Jenis Soal</th>
                        <th class="p-2.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($jadwals as $idx => $j)
                        <tr class="hover:bg-amber-50/50">
                            <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-bold text-black whitespace-nowrap">
                                <div>{{ $j->tanggal_ujian->locale('id')->translatedFormat('l') }}</div>
                                <div class="text-[11px] font-mono font-normal text-slate-600">{{ $j->tanggal_ujian->format('d/m/Y') }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-mono font-bold whitespace-nowrap">
                                {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                <div class="text-[10px] font-normal text-slate-500">({{ $j->durasi_menit }} menit)</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-mono">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-black font-bold text-[10px]">
                                    Sesi {{ $j->sesi_ke }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black font-bold text-black text-sm">
                                {{ $j->nama_mapel }}
                            </td>
                            <td class="p-2.5 border-r border-black">
                                <span class="px-2 py-0.5 rounded font-black text-[11px] bg-[#E7F5FF] text-blue-950 border border-black">
                                    {{ $j->target_kelas }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 border border-slate-400">
                                    {{ $j->jenis_soal }}
                                </span>
                            </td>
                            <td class="p-2.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick="openEditJadwalModal({{ json_encode($j) }})" 
                                            class="px-2 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                        Edit
                                    </button>
                                    <form action="{{ route('ujian.jadwal.destroy', $j) }}" method="POST" onsubmit="return confirm('Hapus slot jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-[#FF6B6B] hover:bg-red-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-500 italic">
                                Belum ada slot jadwal ujian untuk periode ini. Klik <strong>+ Tambah Slot Jadwal</strong> untuk mulai menyusun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Jadwal --}}
@if($activePeriode)
<div id="modalTambahJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>➕</span>
                <span>Tambah Slot Jadwal Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.jadwal.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih dari 16 Mapel Madrasah --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code ?: '-' }})</option>
                    @endforeach
                </select>
                <div class="mt-1">
                    <input type="text" name="nama_mapel_kustom" placeholder="Atau ketik nama mapel kustom (jika tidak ada di daftar)" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white text-[11px]">
                </div>
            </div>

            <div class="space-y-2.5">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Target Rombel Spesifik (Opsional jika berlaku per tingkat)</label>
                    <select name="school_class_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Semua Kelas / Sesuai Pilihan Tingkat --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Ditetapkan di Tingkat Ini (Bisa Pilih > 1 Tingkat)</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 p-2 bg-slate-50 border-2 border-black rounded-lg">
                        @for($t = 1; $t <= 6; $t++)
                            <label class="flex items-center justify-center gap-1.5 px-2 py-1.5 bg-white border border-black rounded cursor-pointer hover:bg-amber-100 text-[11px] font-bold">
                                <input type="checkbox" name="tingkat[]" value="{{ $t }}" class="rounded border-black accent-black">
                                <span>Kls {{ $t }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal</label>
                    <input type="date" name="tanggal_ujian" required value="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" required value="07:30" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" required value="09:00" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke-</label>
                    <input type="number" name="sesi_ke" min="1" value="1" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi (Menit)</label>
                    <input type="number" name="durasi_menit" min="15" value="90" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Bentuk Ujian</label>
                    <select name="jenis_soal" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="Tertulis">Tertulis</option>
                        <option value="CBT / Digital">CBT / Digital</option>
                        <option value="Praktik">Praktik</option>
                        <option value="Portofolio">Portofolio</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Catatan (Opsional)</label>
                <input type="text" name="catatan" placeholder="Catatan khusus slot jadwal..." class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Jadwal --}}
<div id="modalEditJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>✏️</span>
                <span>Edit Slot Jadwal Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalEditJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form id="formEditJadwal" method="POST" action="" class="space-y-3 text-xs font-semibold">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-slate-700 font-bold mb-1">Mata Pelajaran</label>
                <select id="edit_subject_id" name="subject_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih dari 16 Mapel Madrasah --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code ?: '-' }})</option>
                    @endforeach
                </select>
                <div class="mt-1">
                    <input type="text" id="edit_nama_mapel_kustom" name="nama_mapel_kustom" placeholder="Atau ketik nama mapel kustom (jika tidak ada di daftar)" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white text-[11px]">
                </div>
            </div>

            <div class="space-y-2.5">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Target Rombel Spesifik (Opsional jika berlaku per tingkat)</label>
                    <select id="edit_school_class_id" name="school_class_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Semua Kelas / Sesuai Pilihan Tingkat --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Ditetapkan di Tingkat Ini (Bisa Pilih > 1 Tingkat)</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 p-2 bg-slate-50 border-2 border-black rounded-lg">
                        @for($t = 1; $t <= 6; $t++)
                            <label class="flex items-center justify-center gap-1.5 px-2 py-1.5 bg-white border border-black rounded cursor-pointer hover:bg-amber-100 text-[11px] font-bold">
                                <input type="checkbox" name="tingkat[]" value="{{ $t }}" class="edit-tingkat-cb rounded border-black accent-black">
                                <span>Kls {{ $t }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal</label>
                    <input type="date" id="edit_tanggal_ujian" name="tanggal_ujian" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" id="edit_jam_mulai" name="jam_mulai" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" id="edit_jam_selesai" name="jam_selesai" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke-</label>
                    <input type="number" id="edit_sesi_ke" name="sesi_ke" min="1" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi (Menit)</label>
                    <input type="number" id="edit_durasi_menit" name="durasi_menit" min="15" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Bentuk Ujian</label>
                    <select id="edit_jenis_soal" name="jenis_soal" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="Tertulis">Tertulis</option>
                        <option value="CBT / Digital">CBT / Digital</option>
                        <option value="Praktik">Praktik</option>
                        <option value="Portofolio">Portofolio</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Catatan (Opsional)</label>
                <input type="text" id="edit_catatan" name="catatan" placeholder="Catatan khusus slot jadwal..." class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalEditJadwal').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Salin Jadwal Antar Rombel --}}
<div id="modalSalinJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>📑</span>
                <span>Salin Jadwal Antar Rombel Paralel</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.jadwal.copy') }}" method="POST" class="space-y-3.5 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Rombel Sumber (Yang Sudah Memiliki Jadwal)</label>
                <select name="source_class_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih Rombel Sumber --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->level }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Rombel Tujuan (Bisa Lebih Dari Satu)</label>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2.5 border-2 border-black rounded-lg bg-slate-50">
                    @foreach($classes as $c)
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-800 p-1 hover:bg-slate-200 rounded cursor-pointer">
                            <input type="checkbox" name="target_class_ids[]" value="{{ $c->id }}" class="rounded border-black text-[#5294FF]">
                            <span>{{ $c->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Duplikasi Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    function openEditJadwalModal(j) {
        const form = document.getElementById('formEditJadwal');
        form.action = `/ujian/jadwal-ujian/${j.id}`;

        document.getElementById('edit_subject_id').value = j.subject_id || '';
        document.getElementById('edit_nama_mapel_kustom').value = j.nama_mapel_kustom || '';
        document.getElementById('edit_school_class_id').value = j.school_class_id || '';

        document.getElementById('edit_tanggal_ujian').value = j.tanggal_ujian ? j.tanggal_ujian.substring(0, 10) : '';
        document.getElementById('edit_jam_mulai').value = j.jam_mulai ? j.jam_mulai.substring(0, 5) : '';
        document.getElementById('edit_jam_selesai').value = j.jam_selesai ? j.jam_selesai.substring(0, 5) : '';
        document.getElementById('edit_sesi_ke').value = j.sesi_ke || 1;
        document.getElementById('edit_durasi_menit').value = j.durasi_menit || 90;
        document.getElementById('edit_jenis_soal').value = j.jenis_soal || 'Tertulis';
        document.getElementById('edit_catatan').value = j.catatan || '';

        const selectedLevels = (j.tingkat || '').split(',').map(s => s.trim()).filter(Boolean);
        document.querySelectorAll('.edit-tingkat-cb').forEach(cb => {
            cb.checked = selectedLevels.includes(cb.value);
        });

        document.getElementById('modalEditJadwal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
