@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Master Ruang & Sesi Ujian')

@section('content')
<div class="space-y-6">
    {{-- Header Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 3
                </span>
                <span class="text-xs font-bold text-slate-500">Alokasi Fisik Ruangan & Master Waktu Sesi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Master Ruang & Sesi Ujian
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Kelola data ruang ujian dan standarisasi waktu sesi (Sesi 1, Sesi 2, dst) untuk penjadwalan ujian madrasah.
            </p>
        </div>

        {{-- Navigasi Tab Header --}}
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 border-2 border-black rounded-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
            <button type="button" 
                    id="tabBtnRuang"
                    onclick="switchMainTab('ruang')"
                    class="px-3.5 py-2 rounded-lg text-xs font-black transition-all border-2 border-black bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]">
                🏫 Master Ruang Ujian
            </button>
            <button type="button" 
                    id="tabBtnSesi"
                    onclick="switchMainTab('sesi')"
                    class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-600 hover:bg-slate-200">
                ⏱️ Pengaturan Sesi Ujian
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
    @if(session('error'))
        <div class="p-4 bg-[#FFE3E3] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-rose-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>⚠️</span><span>{{ session('error') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-[#FFE3E3] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-rose-950 font-bold text-xs">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= TAB 1: MASTER RUANG UJIAN ================= --}}
    <div id="tabContentRuang" class="space-y-6">
        <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
            <div class="pb-3 border-b-2 border-black flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
                        <span>🏫</span>
                        <span>Daftar Ruang Ujian Aktif</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 font-semibold mt-0.5">
                        Ruang tipe kelas terikat dengan rombel eksisting; ruang tipe manual fleksibel untuk kebutuhan khusus (UAM/Lab).
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="document.getElementById('modalSyncKelas').classList.remove('hidden')" 
                            class="px-3 py-1.5 bg-[#D3F9D8] hover:bg-emerald-300 text-emerald-950 border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>🔄</span>
                        <span>Sinkronkan dari Rombel</span>
                    </button>
                    <button type="button" onclick="document.getElementById('modalTambahRuang').classList.remove('hidden')" 
                            class="px-3 py-1.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>➕</span>
                        <span>Buat Ruangan Manual</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse border-2 border-black">
                    <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                        <tr>
                            <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                            <th class="p-2.5 border-r border-black w-28">Kode Ruang</th>
                            <th class="p-2.5 border-r border-black">Nama Ruang</th>
                            <th class="p-2.5 border-r border-black w-32 text-center">Tipe Ruangan</th>
                            <th class="p-2.5 border-r border-black text-center w-28">Kapasitas</th>
                            <th class="p-2.5 border-r border-black">Lokasi Gedung</th>
                            <th class="p-2.5 border-r border-black">Keterangan</th>
                            <th class="p-2.5 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black font-medium">
                        @forelse($ruangs as $idx => $r)
                            <tr class="hover:bg-amber-50/50">
                                <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                                <td class="p-2.5 border-r border-black font-mono font-bold text-black">{{ $r->kode_ruang }}</td>
                                <td class="p-2.5 border-r border-black font-bold text-black text-sm">
                                    {{ $r->nama_ruang }}
                                    @if($r->isKelas() && $r->schoolClass)
                                        <div class="text-[10px] text-slate-500 font-normal">
                                            Rombel Asal: Kelas {{ $r->schoolClass->name }} (Tingkat {{ $r->schoolClass->level }})
                                        </div>
                                    @endif
                                </td>
                                <td class="p-2.5 border-r border-black text-center">
                                    @if($r->isKelas())
                                        <span class="px-2 py-0.5 rounded bg-blue-100 border border-blue-600 text-blue-900 font-bold text-[10px] shadow-[1px_1px_0px_0px_#000]">
                                            🏫 Ruang Kelas
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-amber-100 border border-amber-600 text-amber-950 font-bold text-[10px] shadow-[1px_1px_0px_0px_#000]">
                                            📝 Manual (UAM)
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2.5 border-r border-black text-center font-bold">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 border border-black">
                                        {{ $r->kapasitas_maksimal }} Kursi
                                    </span>
                                </td>
                                <td class="p-2.5 border-r border-black">{{ $r->lokasi_gedung ?: '-' }}</td>
                                <td class="p-2.5 border-r border-black text-slate-600">{{ $r->keterangan ?: '-' }}</td>
                                <td class="p-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('ujian.ruang.print-label-pintu', $r) }}" target="_blank" class="px-2 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_0px_#000]" title="Cetak Label Pintu (A4 Landscape)">
                                            🚪 Pintu
                                        </a>
                                        <a href="{{ route('ujian.ruang.print-denah', $r) }}" target="_blank" class="px-2 py-1 bg-[#20C997] hover:bg-emerald-400 text-black border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_0px_#000]" title="Cetak Denah Duduk (A4 Portrait)">
                                            🪑 Denah
                                        </a>

                                        <button type="button" 
                                                onclick="openEditRuangModal({{ $r->id }}, '{{ addslashes($r->kode_ruang) }}', '{{ addslashes($r->nama_ruang) }}', {{ $r->kapasitas_maksimal }}, '{{ addslashes($r->lokasi_gedung ?? '') }}', '{{ addslashes($r->keterangan ?? '') }}')" 
                                                class="px-2 py-1 bg-[#5294FF] hover:bg-blue-400 text-white border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                title="Edit Ruang">
                                            ✏️ Edit
                                        </button>

                                        <form action="{{ route('ujian.ruang.destroy', $r) }}" method="POST" onsubmit="return confirm('Hapus ruangan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 bg-rose-500 hover:bg-rose-600 text-white border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_0px_#000] cursor-pointer" title="Hapus">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-500 font-bold">
                                    Belum ada ruang ujian yang dibuat. Silakan sinkronkan dari rombel kelas atau buat ruangan manual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= TAB 2: PENGATURAN SESI UJIAN ================= --}}
    <div id="tabContentSesi" class="space-y-6 hidden">
        <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
            <div class="pb-3 border-b-2 border-black flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
                        <span>⏱️</span>
                        <span>Master Sesi Ujian (Acuan Jadwal)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 font-semibold mt-0.5">
                        Jam sesi ini otomatis disinkronkan saat memilih "Sesi Ke-" pada form pembuatan jadwal ujian. Bersifat fleksibel dan dapat disesuaikan.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <form action="{{ route('ujian.ruang.sesi.reset-default') }}" method="POST" onsubmit="return confirm('Reset pengaturan sesi ke standar bawaan (Sesi 1: 07:30 - 09:30 & Sesi 2: 10:00 - 12:00)?')" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-black border-2 border-black rounded-lg text-xs font-bold shadow-[2px_2px_0px_0px_#000] flex items-center gap-1 cursor-pointer">
                            <span>🔄</span>
                            <span>Reset Bawaan</span>
                        </button>
                    </form>

                    <button type="button" onclick="document.getElementById('modalTambahSesi').classList.remove('hidden')" 
                            class="px-3.5 py-1.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>➕</span>
                        <span>Tambah Sesi Baru</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse border-2 border-black">
                    <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                        <tr>
                            <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                            <th class="p-2.5 border-r border-black w-24 text-center">Sesi Ke-</th>
                            <th class="p-2.5 border-r border-black">Nama Sesi</th>
                            <th class="p-2.5 border-r border-black text-center w-36">Jam Pelaksanaan</th>
                            <th class="p-2.5 border-r border-black text-center w-28">Durasi Baku</th>
                            <th class="p-2.5 border-r border-black">Keterangan</th>
                            <th class="p-2.5 border-r border-black text-center w-24">Status</th>
                            <th class="p-2.5 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black font-medium">
                        @forelse($sesis as $idx => $s)
                            <tr class="hover:bg-amber-50/50">
                                <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                                <td class="p-2.5 border-r border-black text-center">
                                    <span class="px-2.5 py-1 rounded bg-[#FFD43B] border border-black font-black text-xs shadow-[1px_1px_0px_0px_#000]">
                                        Sesi {{ $s->sesi_ke }}
                                    </span>
                                </td>
                                <td class="p-2.5 border-r border-black font-bold text-black text-sm">
                                    {{ $s->nama_sesi }}
                                </td>
                                <td class="p-2.5 border-r border-black text-center font-mono font-bold text-black whitespace-nowrap bg-slate-50">
                                    {{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }}
                                </td>
                                <td class="p-2.5 border-r border-black text-center font-bold">
                                    <span class="px-2 py-0.5 rounded bg-blue-100 border border-blue-400 text-blue-950">
                                        {{ $s->durasi_menit }} Menit
                                    </span>
                                </td>
                                <td class="p-2.5 border-r border-black text-slate-600">
                                    {{ $s->keterangan ?: '-' }}
                                </td>
                                <td class="p-2.5 border-r border-black text-center">
                                    @if($s->is_active)
                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 border border-emerald-400 text-[10px] font-bold">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-400 text-[10px] font-bold">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" 
                                                onclick="openEditSesiModal({{ $s->id }}, {{ $s->sesi_ke }}, '{{ addslashes($s->nama_sesi) }}', '{{ substr($s->jam_mulai, 0, 5) }}', '{{ substr($s->jam_selesai, 0, 5) }}', {{ $s->durasi_menit }}, '{{ addslashes($s->keterangan ?? '') }}', {{ $s->is_active ? 'true' : 'false' }})" 
                                                class="px-2 py-1 bg-[#5294FF] hover:bg-blue-400 text-white border border-black rounded text-[11px] font-bold shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                                title="Edit Sesi">
                                            ✏️ Edit
                                        </button>

                                        <form action="{{ route('ujian.ruang.sesi.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus sesi {{ $s->nama_sesi }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 bg-rose-500 hover:bg-rose-600 text-white border border-black rounded text-[11px] font-bold shadow-[1px_1px_0px_0px_#000] cursor-pointer" title="Hapus Sesi">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-500 font-bold">
                                    Belum ada sesi ujian yang disetting. Klik "Reset Bawaan" untuk memuat Sesi 1 dan Sesi 2 secara otomatis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sinkronisasi Kelas --}}
<div id="modalSyncKelas" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>🔄</span>
                <span>Sinkronisasi Ruang dari Rombel Kelas</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalSyncKelas').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.ruang.sync') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-2">Pilih Mode Sinkronisasi Ruangan:</label>
                <div class="space-y-2">
                    <label class="flex items-start gap-2.5 p-3 border-2 border-black rounded-lg bg-slate-50 cursor-pointer hover:bg-amber-50">
                        <input type="radio" name="mode" value="all" checked class="mt-0.5 accent-black">
                        <div>
                            <div class="font-bold text-black">Semua Rombel (Kelas 1 s/d 6)</div>
                            <div class="text-[11px] text-slate-500 font-normal">Membuat ruang ujian untuk seluruh rombel aktif (Total: {{ $totalKelas }} Kelas).</div>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 p-3 border-2 border-black rounded-lg bg-slate-50 cursor-pointer hover:bg-amber-50">
                        <input type="radio" name="mode" value="exclude_grade_6" class="mt-0.5 accent-black">
                        <div>
                            <div class="font-bold text-black">Kecuali Kelas 6 (Kelas 1 s/d 5 Saja)</div>
                            <div class="text-[11px] text-slate-500 font-normal">Khusus persiapan Ujian Akhir Madrasah (UAM) di mana ruang Kelas 6 dibuatkan manual (Total: {{ $totalKelasNon6 }} Kelas).</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalSyncKelas').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#D3F9D8] hover:bg-emerald-300 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Jalankan Sinkronisasi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Ruang Manual --}}
<div id="modalTambahRuang" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>➕</span>
                <span>Buat Ruang Ujian Manual (UAM)</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahRuang').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.ruang.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1">Kode Ruang (Unik)</label>
                <input type="text" name="kode_ruang" placeholder="Contoh: RU-01, LAB-KOMP" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs uppercase font-mono">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Ruang</label>
                <input type="text" name="nama_ruang" placeholder="Contoh: Ruang UAM 01" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Kapasitas Tempat Duduk (Kursi)</label>
                <input type="number" name="kapasitas_maksimal" value="20" required min="1" max="100" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Lokasi Gedung (Opsional)</label>
                <input type="text" name="lokasi_gedung" placeholder="Contoh: Gedung Timur Lantai 1" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" placeholder="Contoh: Khusus Ujian Akhir Madrasah Kelas 6" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahRuang').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Ruang Manual
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Ruang --}}
<div id="modalEditRuang" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>✏️</span>
                <span>Edit Ruang Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalEditRuang').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form id="formEditRuang" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-700 font-bold mb-1">Kode Ruang</label>
                <input type="text" id="edit_kode_ruang" name="kode_ruang" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs uppercase font-mono">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Ruang</label>
                <input type="text" id="edit_nama_ruang" name="nama_ruang" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Kapasitas Tempat Duduk (Kursi)</label>
                <input type="number" id="edit_kapasitas" name="kapasitas_maksimal" required min="1" max="100" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Lokasi Gedung</label>
                <input type="text" id="edit_lokasi" name="lokasi_gedung" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Keterangan</label>
                <input type="text" id="edit_keterangan" name="keterangan" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalEditRuang').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Sesi Ujian --}}
<div id="modalTambahSesi" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>➕</span>
                <span>Tambah Master Sesi Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahSesi').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.ruang.sesi.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke- (Angka)</label>
                    <input type="number" name="sesi_ke" min="1" max="10" value="{{ ($sesis->max('sesi_ke') ?? 0) + 1 }}" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nama Sesi</label>
                    <input type="text" name="nama_sesi" placeholder="Contoh: Sesi 3 (Siang)" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" id="add_jam_mulai" name="jam_mulai" required value="07:30" onchange="autoCalcDuration('add')" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold font-mono">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" id="add_jam_selesai" name="jam_selesai" required value="09:30" onchange="autoCalcDuration('add')" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi Baku (Menit)</label>
                    <input type="number" id="add_durasi_menit" name="durasi_menit" min="15" value="120" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Status Sesi</label>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" id="add_is_active" name="is_active" value="1" checked class="rounded border-2 border-black text-amber-500 focus:ring-0">
                        <label for="add_is_active" class="text-xs font-bold text-slate-700">Aktif Digunakan</label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" placeholder="Contoh: Sesi Pagi Utama" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahSesi').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Sesi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Sesi Ujian --}}
<div id="modalEditSesi" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>✏️</span>
                <span>Edit Master Sesi Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalEditSesi').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form id="formEditSesi" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke- (Angka)</label>
                    <input type="number" id="edit_sesi_ke" name="sesi_ke" min="1" max="10" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nama Sesi</label>
                    <input type="text" id="edit_nama_sesi" name="nama_sesi" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" id="edit_jam_mulai" name="jam_mulai" required onchange="autoCalcDuration('edit')" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold font-mono">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" id="edit_jam_selesai" name="jam_selesai" required onchange="autoCalcDuration('edit')" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi Baku (Menit)</label>
                    <input type="number" id="edit_durasi_menit" name="durasi_menit" min="15" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Status Sesi</label>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-2 border-black text-amber-500 focus:ring-0">
                        <label for="edit_is_active" class="text-xs font-bold text-slate-700">Aktif Digunakan</label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Keterangan (Opsional)</label>
                <input type="text" id="edit_keterangan_sesi" name="keterangan" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalEditSesi').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchMainTab(tab) {
        const btnRuang = document.getElementById('tabBtnRuang');
        const btnSesi = document.getElementById('tabBtnSesi');
        const contentRuang = document.getElementById('tabContentRuang');
        const contentSesi = document.getElementById('tabContentSesi');

        if (tab === 'sesi') {
            btnSesi.className = "px-3.5 py-2 rounded-lg text-xs font-black transition-all border-2 border-black bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]";
            btnRuang.className = "px-3.5 py-2 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-600 hover:bg-slate-200";
            contentSesi.classList.remove('hidden');
            contentRuang.classList.add('hidden');
            window.location.hash = 'sesi';
        } else {
            btnRuang.className = "px-3.5 py-2 rounded-lg text-xs font-black transition-all border-2 border-black bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]";
            btnSesi.className = "px-3.5 py-2 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-600 hover:bg-slate-200";
            contentRuang.classList.remove('hidden');
            contentSesi.classList.add('hidden');
            window.location.hash = 'ruang';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#sesi') {
            switchMainTab('sesi');
        }
    });

    function openEditRuangModal(id, kode, nama, kapasitas, lokasi, keterangan) {
        const form = document.getElementById('formEditRuang');
        form.action = '/ujian/ruang-ujian/' + id;
        document.getElementById('edit_kode_ruang').value = kode;
        document.getElementById('edit_nama_ruang').value = nama;
        document.getElementById('edit_kapasitas').value = kapasitas;
        document.getElementById('edit_lokasi').value = lokasi;
        document.getElementById('edit_keterangan').value = keterangan;
        document.getElementById('modalEditRuang').classList.remove('hidden');
    }

    function openEditSesiModal(id, sesiKe, nama, jamMulai, jamSelesai, durasi, keterangan, isActive) {
        const form = document.getElementById('formEditSesi');
        form.action = '/ujian/ruang-ujian/sesi/' + id;
        document.getElementById('edit_sesi_ke').value = sesiKe;
        document.getElementById('edit_nama_sesi').value = nama;
        document.getElementById('edit_jam_mulai').value = jamMulai;
        document.getElementById('edit_jam_selesai').value = jamSelesai;
        document.getElementById('edit_durasi_menit').value = durasi;
        document.getElementById('edit_keterangan_sesi').value = keterangan;
        document.getElementById('edit_is_active').checked = !!isActive;
        document.getElementById('modalEditSesi').classList.remove('hidden');
    }

    function autoCalcDuration(prefix) {
        const mulaiEl = document.getElementById(prefix + '_jam_mulai');
        const selesaiEl = document.getElementById(prefix + '_jam_selesai');
        const durasiEl = document.getElementById(prefix + '_durasi_menit');

        if (mulaiEl && selesaiEl && durasiEl && mulaiEl.value && selesaiEl.value) {
            const [mH, mM] = mulaiEl.value.split(':').map(Number);
            const [sH, sM] = selesaiEl.value.split(':').map(Number);
            const totalMulai = mH * 60 + mM;
            const totalSelesai = sH * 60 + sM;
            if (totalSelesai > totalMulai) {
                durasiEl.value = totalSelesai - totalMulai;
            }
        }
    }
</script>
@endpush
@endsection
