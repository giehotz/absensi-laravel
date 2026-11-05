@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.guru')

@section('title', 'Master Ruang Ujian & Kapasitas Kursi')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 3 (A)
                </span>
                <span class="text-xs font-bold text-slate-500">Alokasi Fisik & Kursi Peserta</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Master Ruang Ujian
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Sinkronkan ruang dari rombel kelas eksisting atau buat ruangan manual khusus Ujian Akhir Madrasah (UAM).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button type="button" onclick="document.getElementById('modalSyncKelas').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-[#D3F9D8] hover:bg-emerald-300 text-emerald-950 border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                <span>🔄</span>
                <span>Sinkronkan dari Rombel Kelas</span>
            </button>

            <button type="button" onclick="document.getElementById('modalTambahRuang').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                <span>➕</span>
                <span>Buat Ruangan Manual (UAM)</span>
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

    {{-- Tabel Ruang Ujian --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="pb-3 border-b-2 border-black flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
                    <span>🏫</span>
                    <span>Daftar Ruang Ujian Aktif</span>
                </h2>
                <p class="text-[11px] text-slate-500 font-semibold mt-0.5">
                    Ruang tipe kelas terikat dengan rombel eksisting; ruang tipe manual fleksibel untuk kebutuhan khusus (UAM/Lab).
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <span class="px-2 py-0.5 bg-blue-100 border border-black rounded text-blue-900">
                    Kelas: {{ $ruangs->where('tipe_ruang', 'kelas')->count() }}
                </span>
                <span class="px-2 py-0.5 bg-amber-100 border border-black rounded text-amber-900">
                    Manual: {{ $ruangs->where('tipe_ruang', 'manual')->count() }}
                </span>
                <span class="text-slate-500">Total: {{ $ruangs->count() }} Ruang</span>
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
                            <td class="p-2.5 border-r border-black text-center font-mono font-bold">
                                <span class="px-2 py-0.5 rounded bg-blue-50 border border-black text-blue-950">
                                    {{ $r->kapasitas_maksimal }} Kursi
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-slate-700">{{ $r->lokasi_gedung ?: '-' }}</td>
                            <td class="p-2.5 border-r border-black text-slate-500 text-[11px]">{{ $r->keterangan ?: '-' }}</td>
                            <td class="p-2.5 text-center flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openEditRuangModal('{{ $r->id }}', '{{ $r->kode_ruang }}', '{{ $r->nama_ruang }}', '{{ $r->kapasitas_maksimal }}', '{{ addslashes($r->lokasi_gedung ?? '') }}', '{{ addslashes($r->keterangan ?? '') }}')"
                                        class="px-2 py-1 bg-[#5294FF] hover:bg-blue-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('ujian.ruang.destroy', $r) }}" method="POST" onsubmit="return confirm('Hapus ruang ujian ini?')">
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
                            <td colspan="8" class="p-6 text-center text-slate-500 italic">Belum ada ruang ujian yang didaftarkan. Gunakan tombol "Sinkronkan dari Rombel Kelas" atau "Buat Ruangan Manual".</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Sinkronisasi dari Kelas --}}
<div id="modalSyncKelas" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>🔄</span>
                <span>Sinkronisasi Ruang Ujian dari Rombel Kelas</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalSyncKelas').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.ruang.sync') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            
            <div class="p-3 bg-blue-50 border-2 border-blue-400 rounded-lg text-blue-950 text-xs">
                Sistem akan membaca daftar rombel kelas dan membuat data ruang ujian secara otomatis. Kapasitas kursi akan disesuaikan dengan jumlah murid pada masing-masing kelas.
            </div>

            <div class="space-y-3">
                <label class="block text-slate-900 font-black text-xs uppercase tracking-wide">Pilih Opsi Sinkronisasi:</label>

                {{-- Option 1: All Classes --}}
                <label class="flex items-start gap-3 p-3 border-2 border-black rounded-lg cursor-pointer hover:bg-amber-50 has-[:checked]:bg-[#FFF9DB] has-[:checked]:border-black shadow-[2px_2px_0px_0px_#000]">
                    <input type="radio" name="mode" value="all" checked class="mt-0.5 accent-black">
                    <div>
                        <div class="font-black text-black text-xs">
                            Semua Rombel Kelas (Kelas 1 s/d 6)
                        </div>
                        <div class="text-[11px] text-slate-600 font-medium mt-0.5">
                            Cocok untuk Penilaian Akhir Semester (SAS / STS / SAT) di mana seluruh ruang kelas digunakan sebagai ruang ujian. (Total: {{ $totalKelas }} rombel).
                        </div>
                    </div>
                </label>

                {{-- Option 2: Exclude Grade 6 --}}
                <label class="flex items-start gap-3 p-3 border-2 border-black rounded-lg cursor-pointer hover:bg-amber-50 has-[:checked]:bg-[#FFF9DB] has-[:checked]:border-black shadow-[2px_2px_0px_0px_#000]">
                    <input type="radio" name="mode" value="exclude_grade_6" class="mt-0.5 accent-black">
                    <div>
                        <div class="font-black text-black text-xs">
                            Kecualikan Kelas 6 (Khusus UAM / Ujian Akhir Madrasah)
                        </div>
                        <div class="text-[11px] text-slate-600 font-medium mt-0.5">
                            Hanya sinkronkan rombel kelas 1 s/d 5 (Total: {{ $totalKelasNon6 }} rombel). Kelas 6 tidak dijadikan ruang kelas melainkan ruang manual khusus UAM.
                        </div>
                    </div>
                </label>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalSyncKelas').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-emerald-950 bg-[#D3F9D8] hover:bg-emerald-300 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    🚀 Jalankan Sinkronisasi
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
                <span>Buat Ruangan Manual (Khusus UAM)</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahRuang').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.ruang.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <input type="hidden" name="tipe_ruang" value="manual">

            <div class="p-2.5 bg-amber-50 border border-amber-300 rounded text-amber-900 text-[11px]">
                💡 Gunakan formulir ini untuk ruangan khusus seperti Ujian Akhir Madrasah (UAM) kelas 6, Laboratorium, atau Ruang Aula.
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Kode Ruang (Unik)</label>
                <input type="text" name="kode_ruang" required placeholder="Contoh: RUAM-01, LAB-01" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs uppercase font-mono">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Nama Ruang</label>
                <input type="text" name="nama_ruang" required placeholder="Contoh: Ruang UAM 01, Lab Komputer" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Kapasitas Tempat Duduk (Kursi)</label>
                <input type="number" name="kapasitas_maksimal" required min="1" max="100" value="20" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
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

@push('scripts')
<script>
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
</script>
@endpush
@endsection
