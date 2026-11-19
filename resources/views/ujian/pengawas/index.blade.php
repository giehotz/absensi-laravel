@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Manajemen Pengawas Ruang Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-6">
    {{-- Header Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 3 (B)
                </span>
                <span class="text-xs font-bold text-slate-500">Logika Pemetaan & Penugasan Pengawas</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Jadwal Pengawas Ruang Ujian
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Pengaturan berbasis jadwal mapel, pemetaan kelas rendah/atas, pengecualian panitia, dan sistem pertukaran jadwal (swap).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($activePeriode)
                <button type="button" onclick="openModalCetakDaftarHadir()"
                   class="px-3.5 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🖨️</span>
                    <span>Cetak Daftar Hadir</span>
                </button>
                <a href="{{ route('ujian.pengawas.print-jadwal-guru', ['periode_id' => $activePeriode->id]) }}" target="_blank"
                   class="px-3.5 py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>👨‍🏫</span>
                    <span>Cetak Tugas per Guru</span>
                </a>
                <form action="{{ route('ujian.pengawas.auto-plot') }}" method="POST" onsubmit="return confirm('Jalankan Auto-Plotter? Sistem hanya akan memilih guru yang memenuhi syarat pemetaan kelas dan mengecualikan panitia ujian.')">
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
        <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
            <span>Terplot: <strong class="text-black">{{ $pengawasList->count() }}</strong></span>
            <span>•</span>
            <span>Terpetakan: <strong class="text-emerald-700">{{ $mappedCount }}</strong> / {{ $totalTeachersCount }} Guru</span>
        </div>
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

    {{-- Navigasi Tab Neo-Brutalist --}}
    <div class="flex flex-wrap gap-2 border-b-2 border-black pb-2">
        <button type="button" onclick="switchTab('penugasan')" id="tab-btn-penugasan"
                class="tab-btn px-4 py-2 text-xs font-black rounded-t-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-2 cursor-pointer bg-[#FFD43B] text-black">
            <span>📋</span>
            <span>Penugasan Jadwal Pengawas</span>
            <span class="px-2 py-0.2 bg-black text-white text-[10px] rounded-full">{{ $pengawasList->count() }}</span>
        </button>
        <button type="button" onclick="switchTab('pemetaan')" id="tab-btn-pemetaan"
                class="tab-btn px-4 py-2 text-xs font-black rounded-t-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-2 cursor-pointer bg-slate-100 text-slate-600 hover:bg-slate-200">
            <span>👥</span>
            <span>Pemetaan Calon Pengawas (Kelas Rendah / Atas)</span>
            <span class="px-2 py-0.2 bg-blue-600 text-white text-[10px] rounded-full">{{ $mappedCount }}/{{ $totalTeachersCount }}</span>
        </button>
        <button type="button" onclick="switchTab('riwayat')" id="tab-btn-riwayat"
                class="tab-btn px-4 py-2 text-xs font-black rounded-t-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-2 cursor-pointer bg-slate-100 text-slate-600 hover:bg-slate-200">
            <span>🔄</span>
            <span>Riwayat Pertukaran Pengawas</span>
            <span class="px-2 py-0.2 bg-purple-700 text-white text-[10px] rounded-full">{{ $swapHistories->count() }}</span>
        </button>
    </div>

    {{-- TAB 1: PENUGASAN JADWAL PENGAWAS --}}
    <div id="content-penugasan" class="tab-content space-y-4">
        <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-black font-heading text-black uppercase tracking-wide">
                        Matriks Penugasan Pengawas Ruang Ujian
                    </h2>
                    <p class="text-[11px] text-slate-600 font-medium">
                        Penetapan berbasis mata pelajaran dan waktu pelaksanaan. Gunakan tombol <strong>🔄 Tukar</strong> untuk menukar jadwal antarguru secara aman.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse border-2 border-black">
                    <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                        <tr>
                            <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                            <th class="p-2.5 border-r border-black">Jadwal & Mata Pelajaran</th>
                            <th class="p-2.5 border-r border-black text-center">Ruang Ujian</th>
                            <th class="p-2.5 border-r border-black">Pengawas 1 (Utama)</th>
                            <th class="p-2.5 border-r border-black">Pengawas 2 (Pendamping)</th>
                            <th class="p-2.5 border-r border-black text-center">Kehadiran</th>
                            <th class="p-2.5 text-center w-36">Aksi</th>
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
                                    <div class="text-[10px] text-blue-700 font-bold flex items-center gap-1 mt-0.5">
                                        <span>🏷️</span>
                                        <span>{{ $pw->jadwal?->target_kelas }}</span>
                                    </div>
                                </td>
                                <td class="p-2.5 border-r border-black text-center font-bold">
                                    <span class="px-2 py-0.5 rounded bg-amber-100 border border-black font-mono">
                                        {{ $pw->ruang?->nama_ruang }} ({{ $pw->ruang?->kode_ruang }})
                                    </span>
                                </td>
                                <td class="p-2.5 border-r border-black text-black">
                                    <div class="font-bold flex items-center justify-between">
                                        <span>{{ $pw->pengawas1?->user?->name ?: '-' }}</span>
                                        @if($pw->pengawas_1_id)
                                            <button type="button" onclick="openModalTukar('{{ $pw->id }}', 'pengawas_1', '{{ addslashes($pw->pengawas1?->user?->name) }}', '{{ addslashes($pw->jadwal?->nama_mapel) }}', '{{ $pw->jadwal?->tanggal_ujian?->format('d/m/Y') }}', '{{ substr($pw->jadwal?->jam_mulai ?? '', 0, 5) }}-{{ substr($pw->jadwal?->jam_selesai ?? '', 0, 5) }}', '{{ addslashes($pw->ruang?->nama_ruang) }}', '{{ addslashes($pw->jadwal?->target_kelas) }}')"
                                                    title="Tukar jadwal pengawas 1"
                                                    class="px-1.5 py-0.5 bg-purple-100 hover:bg-purple-200 text-purple-900 border border-purple-800 rounded text-[9px] font-black cursor-pointer">
                                                🔄 Tukar
                                            </button>
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-mono text-slate-500">NIP: {{ $pw->pengawas1?->nip ?: '-' }}</div>
                                </td>
                                <td class="p-2.5 border-r border-black text-slate-800">
                                    <div class="font-semibold flex items-center justify-between">
                                        <span>{{ $pw->pengawas2?->user?->name ?: '-' }}</span>
                                        @if($pw->pengawas_2_id)
                                            <button type="button" onclick="openModalTukar('{{ $pw->id }}', 'pengawas_2', '{{ addslashes($pw->pengawas2?->user?->name) }}', '{{ addslashes($pw->jadwal?->nama_mapel) }}', '{{ $pw->jadwal?->tanggal_ujian?->format('d/m/Y') }}', '{{ substr($pw->jadwal?->jam_mulai ?? '', 0, 5) }}-{{ substr($pw->jadwal?->jam_selesai ?? '', 0, 5) }}', '{{ addslashes($pw->ruang?->nama_ruang) }}', '{{ addslashes($pw->jadwal?->target_kelas) }}')"
                                                    title="Tukar jadwal pengawas 2"
                                                    class="px-1.5 py-0.5 bg-purple-100 hover:bg-purple-200 text-purple-900 border border-purple-800 rounded text-[9px] font-black cursor-pointer">
                                                🔄 Tukar
                                            </button>
                                        @endif
                                    </div>
                                    @if($pw->pengawas2)
                                        <div class="text-[10px] font-mono text-slate-500">NIP: {{ $pw->pengawas2?->nip ?: '-' }}</div>
                                    @endif
                                </td>
                                <td class="p-2.5 border-r border-black text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pw->status_kehadiran === 'Hadir' ? 'bg-emerald-100 text-emerald-950 border border-emerald-600' : 'bg-slate-100 text-slate-700 border border-slate-400' }}">
                                        {{ $pw->status_kehadiran }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openEditPengawasModal('{{ $pw->ujian_jadwal_id }}', '{{ $pw->ujian_ruang_id }}', '{{ $pw->pengawas_1_id }}', '{{ $pw->pengawas_2_id }}', '{{ $pw->status_kehadiran }}')"
                                                class="px-2 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                            ✏️ Ubah
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500 italic">
                                    Belum ada penugasan pengawas untuk periode ini. Gunakan tombol <strong>⚡ Auto-Plotter Otomatis</strong> untuk alokasi cepat atau <strong>+ Tugaskan Manual</strong>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB 2: PEMETAAN CALON PENGAWAS (KELAS RENDAH / ATAS) --}}
    <div id="content-pemetaan" class="tab-content space-y-4 hidden">
        {{-- Ringkasan Statistik Pemetaan --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 bg-white border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Total Guru Aktif</div>
                <div class="text-xl font-black text-black mt-0.5">{{ $totalTeachersCount }}</div>
                <div class="text-[10px] text-slate-500">Guru madrasah</div>
            </div>
            <div class="p-3 bg-[#F3D9FA] border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold text-purple-900 uppercase">Panitia Ujian (Dikecualikan)</div>
                <div class="text-xl font-black text-purple-950 mt-0.5">{{ $panitiaCount }}</div>
                <div class="text-[10px] text-purple-800">Tidak boleh mengawas</div>
            </div>
            <div class="p-3 bg-[#D3F9D8] border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold text-emerald-900 uppercase">Guru Terpetakan</div>
                <div class="text-xl font-black text-emerald-950 mt-0.5">{{ $mappedCount }}</div>
                <div class="text-[10px] text-emerald-800">Siap dialokasikan</div>
            </div>
            <div class="p-3 bg-[#FFE3E3] border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold text-rose-900 uppercase">Belum Dipetakan</div>
                <div class="text-xl font-black text-rose-950 mt-0.5">{{ $unmappedCount }}</div>
                <div class="text-[10px] text-rose-800">Perlu pengaturan kelompok</div>
            </div>
        </div>

        {{-- Panel Pemetaan Bulk & Tabel Guru --}}
        <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
            <form id="formBulkMapping" method="POST" action="{{ route('ujian.pengawas.mapping.bulk') }}">
                @csrf
                <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
                <input type="hidden" id="bulk_levels_input" name="assigned_levels[]" value="">

                {{-- Toolbar Bulk Action --}}
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-4 mb-4 border-b-2 border-black">
                    <div>
                        <h2 class="text-sm font-black font-heading text-black uppercase tracking-wide">
                            Daftar Guru & Pemetaan Kelompok Kelas Calon Pengawas
                        </h2>
                        <p class="text-[11px] text-slate-600 font-medium">
                            Pilih guru dengan checkbox untuk penetapan massal (bulk), atau sesuaikan tingkat kelas per guru secara individual.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-slate-700 mr-1">Tindakan Massal:</span>
                        <button type="button" onclick="submitBulkLevels(['1', '2', '3'])"
                                class="px-2.5 py-1.5 bg-[#5294FF] hover:bg-blue-600 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                            📘 Kelas Rendah (1, 2, 3)
                        </button>
                        <button type="button" onclick="submitBulkLevels(['4', '5', '6'])"
                                class="px-2.5 py-1.5 bg-[#FF922B] hover:bg-orange-500 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                            📙 Kelas Atas (4, 5, 6)
                        </button>
                        <button type="button" onclick="submitBulkLevels(['1', '2', '3', '4', '5', '6'])"
                                class="px-2.5 py-1.5 bg-[#51CF66] hover:bg-green-600 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                            📗 Semua Kelas (1–6)
                        </button>
                        <button type="button" onclick="submitBulkDelete()"
                                class="px-2.5 py-1.5 bg-[#FF6B6B] hover:bg-red-600 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                            🗑️ Hapus Pemetaan
                        </button>
                    </div>
                </div>

                {{-- Tabel Pemetaan Guru --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse border-2 border-black">
                        <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                            <tr>
                                <th class="p-2.5 border-r border-black w-10 text-center">
                                    <input type="checkbox" id="checkAllTeachers" onclick="toggleSelectAllTeachers(this)" class="w-4 h-4 rounded border-2 border-black cursor-pointer">
                                </th>
                                <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                                <th class="p-2.5 border-r border-black">Nama Guru & NIP</th>
                                <th class="p-2.5 border-r border-black text-center">Status Kepanitiaan</th>
                                <th class="p-2.5 border-r border-black">Pemetaan Tingkat Kelas Saat Ini</th>
                                <th class="p-2.5 border-r border-black text-center">Cakupan Tingkat (1–6)</th>
                                <th class="p-2.5 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black font-medium">
                            @forelse($teachers as $idx => $t)
                                @php
                                    $isPanitia = in_array($t->id, $panitiaTeacherIds, true);
                                    $panitiaRole = $panitiaRoles[$t->id] ?? null;
                                    $mapping = $mappings[$t->id] ?? null;
                                    $assignedLevels = $mapping?->assigned_levels ?? [];
                                @endphp
                                <tr class="hover:bg-amber-50/40 {{ $isPanitia ? 'bg-purple-50/40' : '' }}">
                                    <td class="p-2.5 border-r border-black text-center">
                                        @if($isPanitia)
                                            <input type="checkbox" disabled title="Guru adalah Panitia Ujian" class="w-4 h-4 rounded opacity-40 cursor-not-allowed">
                                        @else
                                            <input type="checkbox" name="teacher_ids[]" value="{{ $t->id }}" class="teacher-check w-4 h-4 rounded border-2 border-black cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                                    <td class="p-2.5 border-r border-black">
                                        <div class="font-bold text-black">{{ $t->user?->name }}</div>
                                        <div class="text-[10px] font-mono text-slate-500">NIP: {{ $t->nip ?: '-' }} | NUPTK: {{ $t->nuptk ?: '-' }}</div>
                                    </td>
                                    <td class="p-2.5 border-r border-black text-center">
                                        @if($isPanitia)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-200 text-purple-950 border border-purple-800 flex items-center justify-center gap-1">
                                                <span>🛡️</span>
                                                <span>{{ $panitiaRole ?: 'Panitia Ujian' }}</span>
                                            </span>
                                            <div class="text-[9px] text-purple-700 font-semibold mt-0.5">Dikecualikan otomatis</div>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                                Bukan Panitia
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 border-r border-black">
                                        @if($isPanitia)
                                            <span class="text-slate-400 italic text-[11px]">— Tidak Berhak Mengawas —</span>
                                        @elseif($mapping && !empty($assignedLevels))
                                            <span class="font-bold text-black">{{ $mapping->formatted_levels }}</span>
                                            @if($mapping->keterangan)
                                                <div class="text-[10px] text-slate-500 mt-0.5">Catatan: {{ $mapping->keterangan }}</div>
                                            @endif
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-400">
                                                ⚠️ Belum Dipetakan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 border-r border-black text-center">
                                        @if(!$isPanitia)
                                            <div class="inline-flex items-center gap-1">
                                                @foreach(['1', '2', '3', '4', '5', '6'] as $lvl)
                                                    @php $active = in_array($lvl, array_map('strval', $assignedLevels), true); @endphp
                                                    <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-black border {{ $active ? ($lvl <= 3 ? 'bg-blue-600 text-white border-black' : 'bg-orange-500 text-white border-black') : 'bg-slate-100 text-slate-400 border-slate-300' }}">
                                                        {{ $lvl }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-center">
                                        @if(!$isPanitia)
                                            <button type="button" onclick="openModalEditMapping('{{ $t->id }}', '{{ addslashes($t->user?->name) }}', '{{ $t->nip }}', {{ json_encode($assignedLevels) }}, '{{ addslashes($mapping?->keterangan ?? '') }}')"
                                                    class="px-2.5 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black border border-black rounded text-[10px] font-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                                ⚙️ Atur Kelas
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-[10px] italic">Terkunci</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-500 italic">
                                        Tidak ada data guru aktif yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    {{-- TAB 3: RIWAYAT PERTUKARAN PENGAWAS --}}
    <div id="content-riwayat" class="tab-content space-y-4 hidden">
        <div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-black font-heading text-black uppercase tracking-wide">
                        Riwayat Audit Pertukaran Jadwal Pengawas (Swap Audit Trail)
                    </h2>
                    <p class="text-[11px] text-slate-600 font-medium">
                        Catatan audit transaksional seluruh pertukaran tugas mengawas ujian antarguru untuk pemeriksaan administrasi madrasah.
                    </p>
                </div>
                <div class="text-xs font-bold text-slate-600">
                    Total Pertukaran: <strong class="text-black">{{ $swapHistories->count() }}</strong>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse border-2 border-black">
                    <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                        <tr>
                            <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                            <th class="p-2.5 border-r border-black w-32">Waktu Pertukaran</th>
                            <th class="p-2.5 border-r border-black">Pengawas Asal & Jadwal Semula</th>
                            <th class="p-2.5 border-r border-black text-center w-12">Swap</th>
                            <th class="p-2.5 border-r border-black">Bertukar Dengan (Pengawas & Jadwal)</th>
                            <th class="p-2.5 border-r border-black">Alasan / Catatan</th>
                            <th class="p-2.5 border-r border-black">Diproses Oleh</th>
                            <th class="p-2.5 text-center w-24">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black font-medium">
                        @forelse($swapHistories as $idx => $sw)
                            <tr class="hover:bg-purple-50/40">
                                <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                                <td class="p-2.5 border-r border-black font-mono text-[11px]">
                                    <div>{{ $sw->created_at->format('d/m/Y') }}</div>
                                    <div class="text-slate-500 text-[10px]">{{ $sw->created_at->format('H:i:s') }} WIB</div>
                                </td>
                                <td class="p-2.5 border-r border-black">
                                    <div class="font-bold text-black">{{ $sw->teacher1?->user?->name ?? 'Guru 1' }}</div>
                                    <div class="text-[11px] text-blue-700 font-semibold">{{ $sw->jadwal1?->nama_mapel }} [{{ $sw->jadwal1?->target_kelas }}]</div>
                                    <div class="text-[10px] font-mono text-slate-500">
                                        {{ $sw->jadwal1?->tanggal_ujian?->format('d/m/Y') }} ({{ substr($sw->jadwal1?->jam_mulai ?? '', 0, 5) }}-{{ substr($sw->jadwal1?->jam_selesai ?? '', 0, 5) }}) | {{ $sw->sourcePengawas?->ruang?->nama_ruang ?? 'Ruang' }}
                                    </div>
                                </td>
                                <td class="p-2.5 border-r border-black text-center text-base">
                                    ⇄
                                </td>
                                <td class="p-2.5 border-r border-black">
                                    <div class="font-bold text-black">{{ $sw->teacher2?->user?->name ?? 'Guru 2' }}</div>
                                    <div class="text-[11px] text-blue-700 font-semibold">{{ $sw->jadwal2?->nama_mapel }} [{{ $sw->jadwal2?->target_kelas }}]</div>
                                    <div class="text-[10px] font-mono text-slate-500">
                                        {{ $sw->jadwal2?->tanggal_ujian?->format('d/m/Y') }} ({{ substr($sw->jadwal2?->jam_mulai ?? '', 0, 5) }}-{{ substr($sw->jadwal2?->jam_selesai ?? '', 0, 5) }}) | {{ $sw->targetPengawas?->ruang?->nama_ruang ?? 'Ruang' }}
                                    </div>
                                </td>
                                <td class="p-2.5 border-r border-black">
                                    <span class="italic text-slate-700">{{ $sw->alasan ?: 'Pertukaran jadwal tugas pengawas.' }}</span>
                                </td>
                                <td class="p-2.5 border-r border-black">
                                    <div class="font-bold">{{ $sw->processedBy?->name ?? 'Administrator' }}</div>
                                    <div class="text-[10px] text-slate-500">Admin</div>
                                </td>
                                <td class="p-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-950 border border-emerald-600">
                                        {{ $sw->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-500 italic">
                                    Belum ada catatan riwayat pertukaran tugas pengawas untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: TUGASKAN MANUAL / UBAH PENGAWAS --}}
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
                <label class="block text-slate-700 font-bold mb-1">Pilih Slot Jadwal Ujian <span class="text-red-500">*</span></label>
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
                <label class="block text-slate-700 font-bold mb-1">Pilih Ruang Ujian <span class="text-red-500">*</span></label>
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
                            @php
                                $isPanitia = in_array($t->id, $panitiaTeacherIds, true);
                                $mapping = $mappings[$t->id] ?? null;
                            @endphp
                            <option value="{{ $t->id }}" {{ $isPanitia ? 'disabled class=text-slate-400' : '' }}>
                                {{ $t->user?->name }} {{ $isPanitia ? '⛔ (Panitia Ujian)' : ($mapping ? '['.$mapping->formatted_levels.']' : '[Belum Dipetakan]') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Pengawas 2 (Pendamping)</label>
                    <select id="modal_pengawas_2" name="pengawas_2_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            @php
                                $isPanitia = in_array($t->id, $panitiaTeacherIds, true);
                                $mapping = $mappings[$t->id] ?? null;
                            @endphp
                            <option value="{{ $t->id }}" {{ $isPanitia ? 'disabled class=text-slate-400' : '' }}>
                                {{ $t->user?->name }} {{ $isPanitia ? '⛔ (Panitia Ujian)' : ($mapping ? '['.$mapping->formatted_levels.']' : '[Belum Dipetakan]') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Status Kehadiran <span class="text-red-500">*</span></label>
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

{{-- MODAL 2: EDIT PEMETAAN KELAS INDIVIDUAL PER GURU --}}
<div id="modalEditMapping" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>⚙️</span>
                <span>Atur Pemetaan Kelas Guru</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalEditMapping').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.pengawas.mapping.store') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
            <input type="hidden" id="edit_map_teacher_id" name="teacher_id" value="">

            <div class="p-3 bg-slate-50 border-2 border-black rounded-lg">
                <div class="text-slate-500 text-[10px] uppercase font-bold">Guru Yang Diatur:</div>
                <div id="edit_map_teacher_name" class="text-sm font-black text-black"></div>
                <div id="edit_map_teacher_nip" class="text-[10px] font-mono text-slate-600"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-slate-800 font-bold">Pilih Tingkat Kelas yang Boleh Diawasi <span class="text-red-500">*</span></label>
                    <div class="flex gap-1 text-[10px]">
                        <button type="button" onclick="setEditLevels(['1', '2', '3'])" class="px-1.5 py-0.5 bg-blue-100 hover:bg-blue-200 text-blue-900 border border-blue-800 rounded font-bold">Rendah</button>
                        <button type="button" onclick="setEditLevels(['4', '5', '6'])" class="px-1.5 py-0.5 bg-orange-100 hover:bg-orange-200 text-orange-900 border border-orange-800 rounded font-bold">Atas</button>
                        <button type="button" onclick="setEditLevels(['1', '2', '3', '4', '5', '6'])" class="px-1.5 py-0.5 bg-green-100 hover:bg-green-200 text-green-900 border border-green-800 rounded font-bold">Semua</button>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    @foreach(['1' => 'Kelas 1', '2' => 'Kelas 2', '3' => 'Kelas 3', '4' => 'Kelas 4', '5' => 'Kelas 5', '6' => 'Kelas 6'] as $num => $lbl)
                        <label class="flex items-center gap-2 p-2 border-2 border-black rounded-lg cursor-pointer hover:bg-slate-50 font-bold">
                            <input type="checkbox" name="assigned_levels[]" value="{{ $num }}" class="edit-level-check w-4 h-4 rounded border-2 border-black">
                            <span>{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Catatan Pemetaan (Opsional)</label>
                <input type="text" id="edit_map_keterangan" name="keterangan" placeholder="Contoh: Fokus kelas bawah" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalEditMapping').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Pemetaan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: PERTUKARAN JADWAL PENGAWAS (SWAP) --}}
<div id="modalTukarPengawas" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>🔄</span>
                <span>Pertukaran Jadwal Pengawas (Swap)</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTukarPengawas').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.pengawas.swap.execute') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" id="swap_source_assignment_id" name="source_assignment_id" value="">
            <input type="hidden" id="swap_source_slot" name="source_slot" value="pengawas_1">

            {{-- Kartu Data Tugas Asal --}}
            <div class="p-3 bg-purple-50 border-2 border-purple-800 rounded-lg">
                <div class="text-purple-900 text-[10px] uppercase font-black mb-1">Tugas Pengawasan Yang Hendak Ditukar:</div>
                <div id="swap_info_teacher" class="text-sm font-black text-black"></div>
                <div id="swap_info_mapel" class="text-xs font-bold text-blue-700 mt-0.5"></div>
                <div id="swap_info_datetime" class="text-[11px] font-mono text-slate-600 mt-0.5"></div>
            </div>

            {{-- Pilihan Lawan Tukar --}}
            <div>
                <label class="block text-slate-800 font-bold mb-1">
                    Pilih Penugasan Lawan Tukar <span class="text-red-500">*</span>
                </label>
                <div id="swap_loading_box" class="p-4 text-center text-slate-500 italic bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg hidden">
                    Sedang memverifikasi kelayakan dua arah dan bentrok jadwal...
                </div>
                <select id="swap_target_select" onchange="onSelectSwapTarget(this)" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 font-bold text-xs">
                    <option value="">-- Pilih Penugasan Lawan Tukar --</option>
                </select>
                <input type="hidden" id="swap_target_assignment_id" name="target_assignment_id" value="">
                <input type="hidden" id="swap_target_slot" name="target_slot" value="pengawas_1">
                <p class="text-[10px] text-slate-500 mt-1">
                    Hanya menampilkan jadwal yang memenuhi syarat pemetaan kelas kedua belah pihak dan bebas dari bentrok waktu.
                </p>
            </div>

            {{-- Alasan Pertukaran --}}
            <div>
                <label class="block text-slate-700 font-bold mb-1">Alasan Pertukaran (Opsional)</label>
                <textarea name="alasan" rows="2" placeholder="Contoh: Ada keperluan dinas luar / rapat dinas pada jam tersebut." class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-medium"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTukarPengawas').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" id="btnConfirmSwap" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Konfirmasi Pertukaran
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: CETAK DAFTAR HADIR PENGAWAS --}}
<div id="modalCetakDaftarHadir" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden p-4">
    <div class="bg-white border-4 border-black rounded-xl max-w-md w-full p-6 shadow-[8px_8px_0px_0px_#000] space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="text-base font-black font-heading text-black flex items-center gap-2">
                <span>🖨️</span> Cetak Daftar Hadir Pengawas
            </h3>
            <button type="button" onclick="closeModalCetakDaftarHadir()" class="text-black hover:bg-black hover:text-white p-1 font-black text-sm border border-black shadow-[1px_1px_0px_#000] leading-none">
                ✕
            </button>
        </div>

        <form method="GET" action="{{ route('ujian.pengawas.print-daftar-hadir') }}" target="_blank" onsubmit="closeModalCetakDaftarHadir()">
            <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-800 font-bold mb-1">
                        Pilih Hari / Tanggal Ujian <span class="text-red-500">*</span>
                    </label>
                    <select name="tanggal" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 font-bold text-xs">
                        <option value="all">Semua Hari Ujian (Otomatis Pisah Halaman per Hari)</option>
                        @foreach($examDates as $date)
                            <option value="{{ $date->format('Y-m-d') }}">
                                {{ $date->locale('id')->translatedFormat('l, d F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-800 font-bold mb-1">
                        Tanggal Cetak Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_cetak" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 font-bold text-xs">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t-2 border-black mt-5">
                <button type="button" onclick="closeModalCetakDaftarHadir()" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🖨️</span>
                    <span>Cetak Dokumen (Landscape)</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Tab Navigation Logic
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('bg-[#FFD43B]', 'text-black');
            el.classList.add('bg-slate-100', 'text-slate-600');
        });

        const activeContent = document.getElementById('content-' + tabName);
        const activeBtn = document.getElementById('tab-btn-' + tabName);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('bg-slate-100', 'text-slate-600');
            activeBtn.classList.add('bg-[#FFD43B]', 'text-black');
        }

        window.location.hash = tabName;
    }

    // Auto open tab based on URL hash
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.replace('#', '');
        if (['penugasan', 'pemetaan', 'riwayat'].includes(hash)) {
            switchTab(hash);
        }
    });

    // Checkbox Select All Teachers
    function toggleSelectAllTeachers(master) {
        document.querySelectorAll('.teacher-check').forEach(cb => {
            cb.checked = master.checked;
        });
    }

    // Bulk Level Assignment
    function submitBulkLevels(levels) {
        const checked = document.querySelectorAll('.teacher-check:checked');
        if (checked.length === 0) {
            alert('Pilih setidaknya satu guru dengan mencentang checkbox terlebih dahulu.');
            return;
        }

        const form = document.getElementById('formBulkMapping');
        form.action = "{{ route('ujian.pengawas.mapping.bulk') }}";

        // Remove old level inputs
        form.querySelectorAll('input[name="assigned_levels[]"]').forEach(el => el.remove());

        // Append selected levels
        levels.forEach(lvl => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'assigned_levels[]';
            input.value = lvl;
            form.appendChild(input);
        });

        form.submit();
    }

    function submitBulkDelete() {
        const checked = document.querySelectorAll('.teacher-check:checked');
        if (checked.length === 0) {
            alert('Pilih setidaknya satu guru yang pemetaannya hendak dihapus.');
            return;
        }

        if (!confirm('Hapus pemetaan kelompok kelas untuk guru-guru terpilih?')) {
            return;
        }

        const form = document.getElementById('formBulkMapping');
        form.action = "{{ route('ujian.pengawas.mapping.destroy') }}";
        
        let methodField = form.querySelector('input[name="_method"]');
        if (!methodField) {
            methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            form.appendChild(methodField);
        }
        methodField.value = 'DELETE';

        form.submit();
    }

    // Modal Edit Mapping Individual
    function openModalEditMapping(teacherId, teacherName, teacherNip, assignedLevels, keterangan) {
        document.getElementById('edit_map_teacher_id').value = teacherId;
        document.getElementById('edit_map_teacher_name').textContent = teacherName;
        document.getElementById('edit_map_teacher_nip').textContent = 'NIP: ' + (teacherNip || '-');
        document.getElementById('edit_map_keterangan').value = keterangan || '';

        const levelsArray = (assignedLevels || []).map(String);
        document.querySelectorAll('.edit-level-check').forEach(cb => {
            cb.checked = levelsArray.includes(cb.value);
        });

        document.getElementById('modalEditMapping').classList.remove('hidden');
    }

    function setEditLevels(levels) {
        document.querySelectorAll('.edit-level-check').forEach(cb => {
            cb.checked = levels.includes(cb.value);
        });
    }

    // Modal Edit Penugasan Pengawas
    function openEditPengawasModal(jadwalId, ruangId, p1Id, p2Id, kehadiran) {
        document.getElementById('modal_jadwal_id').value = jadwalId;
        document.getElementById('modal_ruang_id').value = ruangId;
        document.getElementById('modal_pengawas_1').value = p1Id || '';
        document.getElementById('modal_pengawas_2').value = p2Id || '';
        document.getElementById('modal_kehadiran').value = kehadiran || 'Terjadwal';
        document.getElementById('modalTambahPengawas').classList.remove('hidden');
    }

    // Modal Pertukaran Jadwal (Swap)
    function openModalTukar(assignmentId, slot, teacherName, mapel, tanggal, jam, ruang, targetKelas) {
        document.getElementById('swap_source_assignment_id').value = assignmentId;
        document.getElementById('swap_source_slot').value = slot;

        document.getElementById('swap_info_teacher').textContent = teacherName + ' (' + (slot === 'pengawas_2' ? 'Pengawas 2' : 'Pengawas 1') + ')';
        document.getElementById('swap_info_mapel').textContent = 'Mata Pelajaran: ' + mapel + ' [' + targetKelas + ']';
        document.getElementById('swap_info_datetime').textContent = 'Waktu: ' + tanggal + ' (' + jam + ') | ' + ruang;

        const select = document.getElementById('swap_target_select');
        select.innerHTML = '<option value="">-- Memuat kandidat pertukaran yang valid... --</option>';
        select.disabled = true;

        const loadingBox = document.getElementById('swap_loading_box');
        loadingBox.classList.remove('hidden');

        fetch(`{{ route('ujian.pengawas.swap.candidates') }}?assignment_id=${assignmentId}&slot=${slot}`)
            .then(res => res.json())
            .then(candidates => {
                loadingBox.classList.add('hidden');
                select.disabled = false;
                select.innerHTML = '<option value="">-- Pilih Penugasan Lawan Tukar --</option>';

                if (candidates.length === 0) {
                    select.innerHTML = '<option value="">-- Tidak ada jadwal yang memenuhi syarat pertukaran --</option>';
                    return;
                }

                candidates.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = `${c.assignment_id}|${c.slot}`;
                    opt.textContent = `[${c.teacher_name}] - ${c.mapel} | ${c.ruang} | ${c.tanggal} (${c.jam}) [${c.target_kelas}]`;
                    select.appendChild(opt);
                });
            })
            .catch(err => {
                loadingBox.classList.add('hidden');
                select.disabled = false;
                select.innerHTML = '<option value="">Gagal memuat kandidat pertukaran.</option>';
            });

        document.getElementById('modalTukarPengawas').classList.remove('hidden');
    }

    function onSelectSwapTarget(select) {
        if (!select.value) {
            document.getElementById('swap_target_assignment_id').value = '';
            document.getElementById('swap_target_slot').value = '';
            return;
        }

        const parts = select.value.split('|');
        document.getElementById('swap_target_assignment_id').value = parts[0];
        document.getElementById('swap_target_slot').value = parts[1];
    }

    // Modal Cetak
    function openModalCetakDaftarHadir() {
        document.getElementById('modalCetakDaftarHadir').classList.remove('hidden');
    }

    function closeModalCetakDaftarHadir() {
        document.getElementById('modalCetakDaftarHadir').classList.add('hidden');
    }
</script>
@endpush
@endif
@endsection
