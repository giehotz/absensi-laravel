@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Penugasan Pengawas Ruang Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 3 (B)
                </span>
                <span class="text-xs font-bold text-slate-500">Auto-Plotter Anti-Bentrok</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Jadwal Pengawas Ruang
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Menugaskan guru madrasah sebagai pengawas ujian di setiap ruang secara manual atau otomatis (Auto-Plotter round-robin).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($activePeriode)
                <a href="{{ route('ujian.pengawas.print-daftar-hadir') }}" target="_blank"
                   class="px-3.5 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🖨️</span>
                    <span>Cetak Daftar Hadir Pengawas</span>
                </a>
                <form action="{{ route('ujian.pengawas.auto-plot') }}" method="POST" onsubmit="return confirm('Jalankan Auto-Plotter? Pengawas akan didistribusikan secara seimbang ke seluruh jadwal dan ruang.')">
                    @csrf
                    <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
                    <button type="submit" class="px-3.5 py-2 bg-[#F3D9FA] hover:bg-purple-300 text-purple-950 border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                        <span>⚡</span>
                        <span>Auto-Plotter Otomatis</span>
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('modalTambahPengawas').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>➕</span>
                    <span>Tugaskan Manual</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Switch Periode & Alerts --}}
    <div class="flex items-center justify-between gap-4 p-4 bg-slate-100 border-2 border-black rounded-xl">
        <form method="GET" action="{{ route('ujian.pengawas.index') }}" class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-700">Pilih Periode:</span>
            <select name="periode_id" onchange="this.form.submit()" class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[1px_1px_0px_0px_#000]">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                        {{ $p->nama_periode }} {{ $p->is_active ? '★' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        <span class="text-xs font-bold text-slate-600">Total Terplot: <strong class="text-black">{{ $pengawasList->count() }}</strong></span>
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

    {{-- Matriks Penugasan Pengawas --}}
    <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black">Jadwal & Mapel</th>
                        <th class="p-2.5 border-r border-black text-center">Ruang Ujian</th>
                        <th class="p-2.5 border-r border-black">Pengawas 1 (Utama)</th>
                        <th class="p-2.5 border-r border-black">Pengawas 2 (Pendamping)</th>
                        <th class="p-2.5 border-r border-black text-center">Kehadiran</th>
                        <th class="p-2.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($pengawasList as $idx => $pw)
                        <tr class="hover:bg-amber-50/50">
                            <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black">
                                <div class="font-bold text-black">{{ $pw->jadwal?->nama_mapel }}</div>
                                <div class="text-[11px] font-mono text-slate-600">
                                    {{ $pw->jadwal?->tanggal_ujian?->format('d/m/Y') }} | {{ substr($pw->jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($pw->jadwal?->jam_selesai ?? '', 0, 5) }}
                                </div>
                                <div class="text-[10px] text-blue-700 font-bold">{{ $pw->jadwal?->target_kelas }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-bold">
                                <span class="px-2 py-0.5 rounded bg-amber-100 border border-black font-mono">
                                    {{ $pw->ruang?->nama_ruang }} ({{ $pw->ruang?->kode_ruang }})
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black font-bold text-black">
                                <div>{{ $pw->pengawas1?->user?->name ?: '-' }}</div>
                                <div class="text-[10px] font-mono font-normal text-slate-500">NIP: {{ $pw->pengawas1?->nip ?: '-' }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black font-semibold text-slate-800">
                                <div>{{ $pw->pengawas2?->user?->name ?: '-' }}</div>
                                @if($pw->pengawas2)
                                    <div class="text-[10px] font-mono font-normal text-slate-500">NIP: {{ $pw->pengawas2?->nip ?: '-' }}</div>
                                @endif
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pw->status_kehadiran === 'Hadir' ? 'bg-emerald-100 text-emerald-950 border border-emerald-600' : 'bg-slate-100 text-slate-700 border border-slate-400' }}">
                                    {{ $pw->status_kehadiran }}
                                </span>
                            </td>
                            <td class="p-2.5 text-center">
                                <button type="button" onclick="openEditPengawasModal('{{ $pw->ujian_jadwal_id }}', '{{ $pw->ujian_ruang_id }}', '{{ $pw->pengawas_1_id }}', '{{ $pw->pengawas_2_id }}', '{{ $pw->status_kehadiran }}')"
                                        class="px-2 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                    Ubah
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500 italic">
                                Belum ada penugasan pengawas. Gunakan tombol <strong>⚡ Auto-Plotter Otomatis</strong> untuk alokasi cepat atau <strong>+ Tugaskan Manual</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tugaskan Pengawas --}}
@if($activePeriode)
<div id="modalTambahPengawas" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>👨‍🏫</span>
                <span>Penugasan Pengawas Ruang</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahPengawas').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.pengawas.store') }}" method="POST" class="space-y-3.5 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Slot Jadwal Ujian</label>
                <select id="modal_jadwal_id" name="ujian_jadwal_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih Jadwal --</option>
                    @foreach($jadwals as $j)
                        <option value="{{ $j->id }}">
                            {{ $j->tanggal_ujian->format('d/m/Y') }} ({{ substr($j->jam_mulai, 0, 5) }}-{{ substr($j->jam_selesai, 0, 5) }}) - {{ $j->nama_mapel }} [{{ $j->target_kelas }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Ruang Ujian</label>
                <select id="modal_ruang_id" name="ujian_ruang_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih Ruang --</option>
                    @foreach($ruangs as $r)
                        <option value="{{ $r->id }}">{{ $r->nama_ruang }} ({{ $r->kode_ruang }}) - Kapasitas: {{ $r->kapasitas_maksimal }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Pengawas 1 (Utama)</label>
                    <select id="modal_pengawas_1" name="pengawas_1_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->user?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Pengawas 2 (Opsional)</label>
                    <select id="modal_pengawas_2" name="pengawas_2_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->user?->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Status Kehadiran</label>
                <select id="modal_kehadiran" name="status_kehadiran" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                    <option value="Terjadwal">Terjadwal</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Digantikan">Digantikan</option>
                    <option value="Izin">Izin</option>
                </select>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahPengawas').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditPengawasModal(jadwalId, ruangId, p1Id, p2Id, kehadiran) {
        document.getElementById('modal_jadwal_id').value = jadwalId;
        document.getElementById('modal_ruang_id').value = ruangId;
        document.getElementById('modal_pengawas_1').value = p1Id || '';
        document.getElementById('modal_pengawas_2').value = p2Id || '';
        document.getElementById('modal_kehadiran').value = kehadiran || 'Terjadwal';
        document.getElementById('modalTambahPengawas').classList.remove('hidden');
    }
</script>
@endpush
@endif
@endsection
