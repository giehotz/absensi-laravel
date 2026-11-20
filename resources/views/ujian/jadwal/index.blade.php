@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

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
                <div class="inline-flex rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] overflow-hidden">
                    <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'standar']) }}" target="_blank"
                       class="px-3.5 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black flex items-center gap-1.5 cursor-pointer border-r border-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                        </svg>
                        <span>Cetak Lembar Jadwal</span>
                    </a>
                    <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap']) }}" target="_blank"
                       title="Cetak Format Rekap Matriks 1 Lembar Landscape (Seperti jadwal.pdf)"
                       class="px-3 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black flex items-center gap-1 cursor-pointer">
                        <span>📊 Rekap 1 Lembar</span>
                    </a>
                </div>
                <button type="button" onclick="document.getElementById('modalImportJadwal').classList.remove('hidden')"
                        title="Upload Template Excel atau PDF Matriks Jadwal Ujian"
                        class="px-3.5 py-2 bg-[#51CF66] hover:bg-green-500 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span>Upload Template</span>
                </button>
                <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                    </svg>
                    <span>Salin Antar Rombel</span>
                </button>
                <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.remove('hidden')" 
                        class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
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
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="search" id="search_jadwal" oninput="filterJadwalTable()" placeholder="Cari mapel atau kelas…"
                           class="w-full sm:w-60 px-3 py-1.5 pl-8 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[2px_2px_0px_0px_#000] focus:outline-none focus:bg-yellow-50/50">
                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                    </svg>
                </div>
                <select id="filter_bentuk_ujian" onchange="filterJadwalTable()"
                        class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[2px_2px_0px_0px_#000] focus:outline-none cursor-pointer">
                    <option value="">Semua Bentuk Ujian</option>
                    @foreach($bentukUjianList ?? [] as $bu)
                        <option value="{{ strtolower($bu) }}">{{ $bu }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <span id="jadwal_count_badge" class="px-2.5 py-1 text-[11px] font-black border-2 border-black rounded-lg bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    {{ $jadwals->count() }} Slot Jadwal
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border-2 border-black">
                <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                    <tr>
                        <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-2.5 border-r border-black">Tanggal</th>
                        <th class="p-2.5 border-r border-black text-center w-20">Sesi Ke-</th>
                        <th class="p-2.5 border-r border-black text-center w-24">Jam Mulai</th>
                        <th class="p-2.5 border-r border-black text-center w-24">Jam Selesai</th>
                        <th class="p-2.5 border-r border-black text-center w-28">Durasi (Menit)</th>
                        <th class="p-2.5 border-r border-black text-center w-28">Bentuk Ujian</th>
                        <th class="p-2.5 border-r border-black">Mata Pelajaran</th>
                        <th class="p-2.5 border-r border-black">Rombel / Tingkat</th>
                        <th class="p-2.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black font-medium">
                    @forelse($jadwals as $idx => $j)
                        <tr class="jadwal-row hover:bg-amber-50/50"
                            data-jenis-soal="{{ strtolower($j->jenis_soal) }}"
                            data-search="{{ strtolower($j->nama_mapel . ' ' . $j->target_kelas . ' ' . $j->jenis_soal . ' ' . $j->tanggal_ujian->format('d/m/Y')) }}">
                            <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2.5 border-r border-black font-bold text-black whitespace-nowrap">
                                <div>{{ $j->tanggal_ujian->locale('id')->translatedFormat('l') }}</div>
                                <div class="text-[11px] font-mono font-normal text-slate-600">{{ $j->tanggal_ujian->format('d/m/Y') }}</div>
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                <span class="px-2 py-0.5 rounded bg-[#FFD43B] border border-black font-black text-[11px] shadow-[1px_1px_0px_0px_#000]">
                                    Sesi {{ $j->sesi_ke }}
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-mono font-bold text-black">
                                {{ substr($j->jam_mulai, 0, 5) }}
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-mono font-bold text-black">
                                {{ substr($j->jam_selesai, 0, 5) }}
                            </td>
                            <td class="p-2.5 border-r border-black text-center font-bold">
                                <span class="px-2 py-0.5 rounded bg-blue-100 border border-blue-400 text-blue-950 text-[11px]">
                                    {{ $j->durasi_menit }} Menit
                                </span>
                            </td>
                            <td class="p-2.5 border-r border-black text-center">
                                @php
                                    $js = strtolower($j->jenis_soal);
                                    $badgeClass = 'bg-slate-100 text-slate-900 border-slate-900';
                                    if (str_contains($js, 'cbt') || str_contains($js, 'digital')) {
                                        $badgeClass = 'bg-[#D0EBFF] text-blue-950 border-blue-900';
                                    } elseif (str_contains($js, 'tulis')) {
                                        $badgeClass = 'bg-[#FFF3BF] text-amber-950 border-amber-900';
                                    } elseif (str_contains($js, 'praktik')) {
                                        $badgeClass = 'bg-[#D3F9D8] text-emerald-950 border-emerald-900';
                                    } elseif (str_contains($js, 'portofolio') || str_contains($js, 'lisan')) {
                                        $badgeClass = 'bg-[#F3D9FA] text-purple-950 border-purple-900';
                                    }
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded border-2 {{ $badgeClass }} text-[10px] font-black shadow-[1px_1px_0px_0px_#000] whitespace-nowrap">
                                    {{ $j->jenis_soal }}
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
                            <td colspan="10" class="p-6 text-center text-slate-500 italic">
                                Belum ada slot jadwal ujian untuk periode ini. Klik <strong>+ Tambah Slot Jadwal</strong> untuk mulai menyusun.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="empty_search_row" style="display: none;">
                        <td colspan="10" class="p-6 text-center text-slate-500 font-bold italic">
                            Tidak ada jadwal ujian yang cocok dengan pencarian atau filter bentuk ujian.
                        </td>
                    </tr>
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
                    <select name="school_class_id" id="add_school_class_id" onchange="updateBentukUjianAddForm()" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
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
                                <input type="checkbox" name="tingkat[]" value="{{ $t }}" onchange="updateBentukUjianAddForm()" class="rounded border-black accent-black">
                                <span>Kls {{ $t }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal</label>
                    <input type="date" name="tanggal_ujian" required value="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke-</label>
                    <select name="sesi_ke" id="add_sesi_ke" onchange="applySesiTime('add', this)" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                        @forelse($sesis as $s)
                            <option value="{{ $s->sesi_ke }}"
                                    data-mulai="{{ substr($s->jam_mulai, 0, 5) }}"
                                    data-selesai="{{ substr($s->jam_selesai, 0, 5) }}"
                                    data-durasi="{{ $s->durasi_menit }}"
                                    {{ $loop->first ? 'selected' : '' }}>
                                Sesi {{ $s->sesi_ke }} ({{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }})
                            </option>
                        @empty
                            <option value="1" data-mulai="07:30" data-selesai="09:30" data-durasi="120" selected>Sesi 1 (07:30 - 09:30)</option>
                            <option value="2" data-mulai="10:00" data-selesai="12:00" data-durasi="120">Sesi 2 (10:00 - 12:00)</option>
                        @endforelse
                        @for($extra = 1; $extra <= 6; $extra++)
                            @if(!$sesis->contains('sesi_ke', $extra))
                                <option value="{{ $extra }}">Sesi {{ $extra }} (Manual)</option>
                            @endif
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="add_jam_mulai" required value="{{ $sesis->first()?->jam_mulai ? substr($sesis->first()->jam_mulai, 0, 5) : '07:30' }}" onchange="autoCalcJadwalDuration('add')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="add_jam_selesai" required value="{{ $sesis->first()?->jam_selesai ? substr($sesis->first()->jam_selesai, 0, 5) : '09:30' }}" onchange="autoCalcJadwalDuration('add')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi (Menit)</label>
                    <input type="number" name="durasi_menit" id="add_durasi_menit" min="15" value="{{ $sesis->first()?->durasi_menit ?? 120 }}" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
            </div>

            {{-- Bagian Bentuk Ujian (Single / Split Bawah & Atas) --}}
            <div id="wrapper_bentuk_ujian_single" class="space-y-1">
                <label class="block text-slate-700 font-bold mb-1" id="label_bentuk_ujian_single">Bentuk Ujian</label>
                <select name="jenis_soal" id="add_jenis_soal_single" onchange="toggleCustomBentukUjian(this, 'add_jenis_soal_kustom_single_wrapper')" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="Tertulis" selected>Tertulis</option>
                    <option value="CBT / Digital">CBT / Digital</option>
                    <option value="Praktik">Praktik</option>
                    <option value="Portofolio">Portofolio</option>
                    <option value="Lisan">Lisan</option>
                    <option value="Lainnya">Lainnya...</option>
                </select>
                <div id="add_jenis_soal_kustom_single_wrapper" class="hidden mt-1">
                    <input type="text" name="jenis_soal_kustom" id="add_jenis_soal_kustom_single" placeholder="Ketik bentuk ujian kustom (misal: Proyek, Observasi)..." class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-semibold">
                </div>
            </div>

            <div id="wrapper_bentuk_ujian_split" class="hidden space-y-2 p-3 bg-amber-50 border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 border-b border-black pb-1.5">
                    <span>⚡</span>
                    <span>Pilihan Bentuk Ujian Kelas Bawah &amp; Kelas Atas</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-slate-800 font-black text-[11px] mb-1">
                            📘 Kelas Bawah (Tingkat 1–3)
                        </label>
                        <select name="jenis_soal_bawah" id="add_jenis_soal_bawah" onchange="toggleCustomBentukUjian(this, 'add_jenis_soal_bawah_kustom_wrapper')" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-bold">
                            <option value="Tertulis" selected>Tertulis</option>
                            <option value="CBT / Digital">CBT / Digital</option>
                            <option value="Praktik">Praktik</option>
                            <option value="Portofolio">Portofolio</option>
                            <option value="Lisan">Lisan</option>
                            <option value="Lainnya">Lainnya...</option>
                        </select>
                        <div id="add_jenis_soal_bawah_kustom_wrapper" class="hidden mt-1">
                            <input type="text" name="jenis_soal_bawah_kustom" id="add_jenis_soal_bawah_kustom" placeholder="Bentuk kustom kelas bawah..." class="w-full px-2 py-1 border border-black rounded text-[11px]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-800 font-black text-[11px] mb-1">
                            💻 Kelas Atas (Tingkat 4–6)
                        </label>
                        <select name="jenis_soal_atas" id="add_jenis_soal_atas" onchange="toggleCustomBentukUjian(this, 'add_jenis_soal_atas_kustom_wrapper')" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-bold">
                            <option value="CBT / Digital" selected>CBT / Digital</option>
                            <option value="Tertulis">Tertulis</option>
                            <option value="Praktik">Praktik</option>
                            <option value="Portofolio">Portofolio</option>
                            <option value="Lisan">Lisan</option>
                            <option value="Lainnya">Lainnya...</option>
                        </select>
                        <div id="add_jenis_soal_atas_kustom_wrapper" class="hidden mt-1">
                            <input type="text" name="jenis_soal_atas_kustom" id="add_jenis_soal_atas_kustom" placeholder="Bentuk kustom kelas atas..." class="w-full px-2 py-1 border border-black rounded text-[11px]">
                        </div>
                    </div>
                </div>
                <p class="text-[10px] font-semibold text-slate-600 italic">
                    * Jika bentuk ujian kelas bawah dan atas berbeda, sistem akan otomatis membuat 2 slot jadwal terpisah. Jika sama, akan digabung menjadi 1 slot.
                </p>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal</label>
                    <input type="date" id="edit_tanggal_ujian" name="tanggal_ujian" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke-</label>
                    <select name="sesi_ke" id="edit_sesi_ke" onchange="applySesiTime('edit', this)" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                        @forelse($sesis as $s)
                            <option value="{{ $s->sesi_ke }}"
                                    data-mulai="{{ substr($s->jam_mulai, 0, 5) }}"
                                    data-selesai="{{ substr($s->jam_selesai, 0, 5) }}"
                                    data-durasi="{{ $s->durasi_menit }}">
                                Sesi {{ $s->sesi_ke }} ({{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }})
                            </option>
                        @empty
                            <option value="1" data-mulai="07:30" data-selesai="09:30" data-durasi="120">Sesi 1 (07:30 - 09:30)</option>
                            <option value="2" data-mulai="10:00" data-selesai="12:00" data-durasi="120">Sesi 2 (10:00 - 12:00)</option>
                        @endforelse
                        @for($extra = 1; $extra <= 6; $extra++)
                            @if(!$sesis->contains('sesi_ke', $extra))
                                <option value="{{ $extra }}">Sesi {{ $extra }} (Manual)</option>
                            @endif
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" id="edit_jam_mulai" name="jam_mulai" required onchange="autoCalcJadwalDuration('edit')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" id="edit_jam_selesai" name="jam_selesai" required onchange="autoCalcJadwalDuration('edit')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi (Menit)</label>
                    <input type="number" id="edit_durasi_menit" name="durasi_menit" min="15" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Bentuk Ujian</label>
                <select id="edit_jenis_soal" name="jenis_soal" onchange="toggleCustomBentukUjian(this, 'edit_jenis_soal_kustom_wrapper')" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="Tertulis">Tertulis</option>
                    <option value="CBT / Digital">CBT / Digital</option>
                    <option value="Praktik">Praktik</option>
                    <option value="Portofolio">Portofolio</option>
                    <option value="Lisan">Lisan</option>
                    <option value="Lainnya">Lainnya...</option>
                </select>
                <div id="edit_jenis_soal_kustom_wrapper" class="hidden mt-1">
                    <input type="text" name="jenis_soal_kustom" id="edit_jenis_soal_kustom" placeholder="Ketik bentuk ujian kustom..." class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-semibold">
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

{{-- Modal Upload / Import Template Jadwal Ujian (Excel & PDF) --}}
<div id="modalImportJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <span>Upload & Import Jadwal Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalImportJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        {{-- Tab Switcher: Excel vs PDF --}}
        <div class="flex gap-2 mb-4 border-b-2 border-black pb-2">
            <button type="button" id="tabBtnExcel" onclick="switchImportJadwalTab('excel')"
                    class="px-3 py-1.5 rounded-lg border-2 border-black font-black text-xs bg-[#FFD43B] text-black shadow-[1px_1px_0px_#000] cursor-pointer">
                📗 File Excel (.xlsx / .csv)
            </button>
            <button type="button" id="tabBtnPdf" onclick="switchImportJadwalTab('pdf')"
                    class="px-3 py-1.5 rounded-lg border-2 border-black font-bold text-xs bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">
                📕 File PDF Matriks (jadwal.pdf)
            </button>
        </div>

        {{-- Tab 1: Form Excel --}}
        <div id="tabContentExcel">
            <div class="p-3 bg-amber-50 border-2 border-black rounded-lg mb-3.5 text-xs">
                <div class="font-black text-black flex items-center justify-between mb-1">
                    <span>Unduh Format Template Excel:</span>
                    <a href="{{ route('ujian.jadwal.template') }}" class="px-2.5 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black border border-black rounded font-black text-[11px] shadow-[1px_1px_0px_0px_#000] inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        <span>Download Template</span>
                    </a>
                </div>
                <p class="text-[11px] text-slate-700 font-medium leading-relaxed">
                    Format: <strong class="font-bold">No | Matapelajaran | tanggal(dd-mm-yyyy) | sesi | Kelas</strong>.
                    Tingkat kelas dapat diisi <code class="bg-amber-100 px-1 rounded font-bold">123</code>, <code class="bg-amber-100 px-1 rounded font-bold">456</code>, atau <code class="bg-amber-100 px-1 rounded font-bold">123456</code>.
                </p>
            </div>

            <form action="{{ route('ujian.jadwal.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                @csrf
                <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

                <div>
                    <label class="block text-slate-800 font-bold mb-1">Pilih File Spreadsheet (Excel / CSV) <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                           class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold file:mr-3 file:py-1 file:px-2.5 file:rounded file:border file:border-black file:text-xs file:font-black file:bg-[#FFD43B] file:hover:bg-yellow-400 file:cursor-pointer cursor-pointer">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                    <button type="button" onclick="document.getElementById('modalImportJadwal').classList.add('hidden')" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#51CF66] hover:bg-green-500 font-black text-xs shadow-[2px_2px_0px_0px_#000] inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span>Import Spreadsheet</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Tab 2: Form PDF Matriks --}}
        <div id="tabContentPdf" class="hidden">
            <div class="p-3 bg-blue-50 border-2 border-black rounded-lg mb-3.5 text-xs">
                <div class="font-black text-blue-950 flex items-center gap-1.5 mb-1">
                    <span>📄</span>
                    <span>Format Matriks PDF Resmi (seperti jadwal.pdf)</span>
                </div>
                <p class="text-[11px] text-blue-900 font-medium leading-relaxed">
                    Sistem akan membaca matriks tanggal, jam ujian, mata pelajaran, serta menelusuri nomor pengawas di 16 ruangan lalu memetakannya langsung ke ID User guru madrasah.
                </p>
            </div>

            <form action="{{ route('ujian.jadwal.import-pdf') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                @csrf
                <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

                <div>
                    <label class="block text-slate-800 font-bold mb-1">Pilih File PDF Matriks Jadwal <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".pdf"
                           class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold file:mr-3 file:py-1 file:px-2.5 file:rounded file:border file:border-black file:text-xs file:font-black file:bg-[#FFD43B] file:hover:bg-yellow-400 file:cursor-pointer cursor-pointer">
                    <p class="text-[10px] text-slate-500 mt-1 font-normal">Format file .pdf matriks jadwal (Maksimal 5MB).</p>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                    <button type="button" onclick="document.getElementById('modalImportJadwal').classList.add('hidden')" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-600 font-black text-xs shadow-[2px_2px_0px_0px_#000] inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span>Import PDF Matriks</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function switchImportJadwalTab(tab) {
        const btnExcel = document.getElementById('tabBtnExcel');
        const btnPdf = document.getElementById('tabBtnPdf');
        const contentExcel = document.getElementById('tabContentExcel');
        const contentPdf = document.getElementById('tabContentPdf');

        if (tab === 'excel') {
            btnExcel.classList.add('bg-[#FFD43B]', 'text-black', 'font-black');
            btnExcel.classList.remove('bg-slate-100', 'text-slate-700', 'font-bold');
            btnPdf.classList.remove('bg-[#FFD43B]', 'text-black', 'font-black');
            btnPdf.classList.add('bg-slate-100', 'text-slate-700', 'font-bold');
            contentExcel.classList.remove('hidden');
            contentPdf.classList.add('hidden');
        } else {
            btnPdf.classList.add('bg-[#FFD43B]', 'text-black', 'font-black');
            btnPdf.classList.remove('bg-slate-100', 'text-slate-700', 'font-bold');
            btnExcel.classList.remove('bg-[#FFD43B]', 'text-black', 'font-black');
            btnExcel.classList.add('bg-slate-100', 'text-slate-700', 'font-bold');
            contentPdf.classList.remove('hidden');
            contentExcel.classList.add('hidden');
        }
    }

    function applySesiTime(prefix, selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt) return;

        const mulai = opt.getAttribute('data-mulai');
        const selesai = opt.getAttribute('data-selesai');
        const durasi = opt.getAttribute('data-durasi');

        if (mulai) {
            const elMulai = document.getElementById(`${prefix}_jam_mulai`);
            if (elMulai) elMulai.value = mulai;
        }
        if (selesai) {
            const elSelesai = document.getElementById(`${prefix}_jam_selesai`);
            if (elSelesai) elSelesai.value = selesai;
        }
        if (durasi) {
            const elDurasi = document.getElementById(`${prefix}_durasi_menit`);
            if (elDurasi) elDurasi.value = durasi;
        }
    }

    function autoCalcJadwalDuration(prefix) {
        const elMulai = document.getElementById(`${prefix}_jam_mulai`);
        const elSelesai = document.getElementById(`${prefix}_jam_selesai`);
        const elDurasi = document.getElementById(`${prefix}_durasi_menit`);
        if (!elMulai || !elSelesai || !elDurasi) return;

        const mulai = elMulai.value;
        const selesai = elSelesai.value;
        if (!mulai || !selesai) return;

        const [h1, m1] = mulai.split(':').map(Number);
        const [h2, m2] = selesai.split(':').map(Number);
        const diffMinutes = (h2 * 60 + m2) - (h1 * 60 + m1);
        if (diffMinutes > 0) {
            elDurasi.value = diffMinutes;
        }
    }

    function filterJadwalTable() {
        const query = (document.getElementById('search_jadwal')?.value || '').trim().toLowerCase();
        const jenisFilter = (document.getElementById('filter_bentuk_ujian')?.value || '').trim().toLowerCase();
        const rows = document.querySelectorAll('.jadwal-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.dataset.search || '';
            const rowJenis = row.dataset.jenisSoal || '';
            const matchesQuery = query === '' || rowSearch.includes(query);
            const matchesJenis = jenisFilter === '' || rowJenis === jenisFilter;

            if (matchesQuery && matchesJenis) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptySearchRow = document.getElementById('empty_search_row');
        if (emptySearchRow) {
            emptySearchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }

        const countBadge = document.getElementById('jadwal_count_badge');
        if (countBadge) {
            countBadge.textContent = `${visibleCount} Slot Jadwal`;
        }
    }

    function toggleCustomBentukUjian(selectEl, wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        if (selectEl.value === 'Lainnya') {
            wrapper.classList.remove('hidden');
            wrapper.querySelector('input')?.focus();
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function updateBentukUjianAddForm() {
        const rombelSelect = document.getElementById('add_school_class_id');
        const checkedBoxes = Array.from(document.querySelectorAll('#modalTambahJadwal input[name="tingkat[]"]:checked'));
        const levels = checkedBoxes.map(cb => cb.value);

        const hasBawah = levels.some(l => ['1', '2', '3'].includes(l));
        const hasAtas = levels.some(l => ['4', '5', '6'].includes(l));
        const isMixed = hasBawah && hasAtas && (!rombelSelect || !rombelSelect.value);

        const singleWrapper = document.getElementById('wrapper_bentuk_ujian_single');
        const splitWrapper = document.getElementById('wrapper_bentuk_ujian_split');
        const singleSelect = document.getElementById('add_jenis_soal_single');
        const labelSingle = document.getElementById('label_bentuk_ujian_single');

        if (!singleWrapper || !splitWrapper) return;

        if (isMixed) {
            singleWrapper.classList.add('hidden');
            splitWrapper.classList.remove('hidden');
            singleSelect.removeAttribute('required');
        } else {
            singleWrapper.classList.remove('hidden');
            splitWrapper.classList.add('hidden');
            singleSelect.setAttribute('required', 'required');

            if (hasBawah && !hasAtas) {
                labelSingle.textContent = 'Bentuk Ujian (Kelas Bawah 1–3)';
            } else if (hasAtas && !hasBawah) {
                labelSingle.textContent = 'Bentuk Ujian (Kelas Atas 4–6)';
            } else {
                labelSingle.textContent = 'Bentuk Ujian';
            }
        }
    }

    function openEditJadwalModal(j) {
        const form = document.getElementById('formEditJadwal');
        form.action = `/ujian/jadwal-ujian/${j.id}`;

        document.getElementById('edit_subject_id').value = j.subject_id || '';
        document.getElementById('edit_nama_mapel_kustom').value = j.nama_mapel_kustom || '';
        document.getElementById('edit_school_class_id').value = j.school_class_id || '';

        document.getElementById('edit_tanggal_ujian').value = j.tanggal_ujian ? j.tanggal_ujian.substring(0, 10) : '';

        // Sesi Ke-
        const sesiSelect = document.getElementById('edit_sesi_ke');
        if (sesiSelect) {
            const sesiVal = (j.sesi_ke || 1).toString();
            let hasOption = Array.from(sesiSelect.options).some(o => o.value === sesiVal);
            if (!hasOption) {
                const opt = document.createElement('option');
                opt.value = sesiVal;
                opt.textContent = `Sesi ${sesiVal}`;
                sesiSelect.appendChild(opt);
            }
            sesiSelect.value = sesiVal;
        }

        document.getElementById('edit_jam_mulai').value = j.jam_mulai ? j.jam_mulai.substring(0, 5) : '';
        document.getElementById('edit_jam_selesai').value = j.jam_selesai ? j.jam_selesai.substring(0, 5) : '';
        document.getElementById('edit_durasi_menit').value = j.durasi_menit || 90;

        // Bentuk Ujian Fleksibel
        const standardOptions = ['Tertulis', 'CBT / Digital', 'Praktik', 'Portofolio', 'Lisan'];
        const editSelect = document.getElementById('edit_jenis_soal');
        const customWrapper = document.getElementById('edit_jenis_soal_kustom_wrapper');
        const customInput = document.getElementById('edit_jenis_soal_kustom');

        if (standardOptions.includes(j.jenis_soal)) {
            editSelect.value = j.jenis_soal;
            customWrapper.classList.add('hidden');
            customInput.value = '';
        } else if (j.jenis_soal) {
            editSelect.value = 'Lainnya';
            customWrapper.classList.remove('hidden');
            customInput.value = j.jenis_soal;
        } else {
            editSelect.value = 'Tertulis';
            customWrapper.classList.add('hidden');
            customInput.value = '';
        }

        document.getElementById('edit_catatan').value = j.catatan || '';

        const selectedLevels = (j.tingkat || '').split(',').map(s => s.trim()).filter(Boolean);
        document.querySelectorAll('.edit-tingkat-cb').forEach(cb => {
            cb.checked = selectedLevels.includes(cb.value);
        });

        document.getElementById('modalEditJadwal').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateBentukUjianAddForm();
    });
</script>
@endpush
@endsection
