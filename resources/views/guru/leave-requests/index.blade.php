@extends('layouts.guru')

@section('title', 'Perizinan Siswa')
@section('page-title', 'Pengelolaan Izin & Sakit Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header Title Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#5294FF] text-white">PORTAL GURU</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    NIP: {{ $teacher->nip ?? '-' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
                PERIZINAN SISWA (IZIN & SAKIT)
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Tinjau dan verifikasi permohonan izin atau sakit dari siswa dan orang tua, atau catat izin manual dengan sinkronisasi presensi otomatis.
            </p>
        </div>

        <div class="z-10 flex flex-wrap gap-2">
            <button type="button" onclick="openCreateLeaveModal()" 
                    class="neo-btn bg-[#20C997] text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 hover:bg-emerald-400 cursor-pointer shadow-sm">
                <span>➕</span> Catat Izin Manual
            </button>
            <a href="{{ route('guru.dashboard') }}" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2.5 flex items-center gap-1.5 cursor-pointer hover:bg-slate-100">
                ← Dashboard
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards: Statistik Perizinan -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Menunggu Verifikasi (Pending) -->
        <div class="bg-[#FFE3E3] neo-box p-4 flex flex-col justify-between relative overflow-hidden">
            @if($counts['pending'] > 0)
                <span class="absolute top-2 right-2 flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-600"></span>
                </span>
            @endif
            <div class="text-[11px] font-black uppercase text-rose-950 flex items-center justify-between">
                <span>Menunggu Verifikasi</span>
                <span>⏳</span>
            </div>
            <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-rose-950">
                {{ $counts['pending'] }}
            </div>
            <div class="text-[10px] font-bold text-rose-800 mt-1">Perlu Tindakan Guru</div>
        </div>

        <!-- Disetujui -->
        <div class="bg-[#D3F9D8] neo-box p-4 flex flex-col justify-between">
            <div class="text-[11px] font-black uppercase text-emerald-950 flex items-center justify-between">
                <span>Disetujui</span>
                <span>✅</span>
            </div>
            <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-emerald-950">
                {{ $counts['approved'] }}
            </div>
            <div class="text-[10px] font-bold text-emerald-800 mt-1">Presensi Tercatat Izin/Sakit</div>
        </div>

        <!-- Ditolak -->
        <div class="bg-slate-100 neo-box p-4 flex flex-col justify-between">
            <div class="text-[11px] font-black uppercase text-slate-800 flex items-center justify-between">
                <span>Ditolak</span>
                <span>❌</span>
            </div>
            <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-slate-800">
                {{ $counts['rejected'] }}
            </div>
            <div class="text-[10px] font-bold text-slate-600 mt-1">Pengajuan Tidak Valid</div>
        </div>

        <!-- Total Seluruh Pengajuan -->
        <div class="bg-white neo-box p-4 flex flex-col justify-between">
            <div class="text-[11px] font-black uppercase text-slate-600 flex items-center justify-between">
                <span>Total Pengajuan</span>
                <span>📋</span>
            </div>
            <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-black">
                {{ $counts['all'] }}
            </div>
            <div class="text-[10px] font-semibold text-slate-500 mt-1">Di Seluruh Kelas Binaan/Ajar</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white neo-box p-4 sm:p-5">
        <form action="{{ route('guru.leave-requests.index') }}" method="GET" class="space-y-3">
            <input type="hidden" name="status" value="{{ $status }}">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Filter Kelas -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-black mb-1">Kelas:</label>
                    <select name="school_class_id" 
                            class="w-full bg-white border-2 border-black px-2.5 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                        <option value="">Semua Kelas Binaan/Ajar</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Jenis -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-black mb-1">Jenis Izin:</label>
                    <select name="type" 
                            class="w-full bg-white border-2 border-black px-2.5 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                        <option value="all" {{ $type == 'all' ? 'selected' : '' }}>Semua Jenis (Izin & Sakit)</option>
                        <option value="izin" {{ $type == 'izin' ? 'selected' : '' }}>Izin Saja</option>
                        <option value="sakit" {{ $type == 'sakit' ? 'selected' : '' }}>Sakit Saja</option>
                    </select>
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-black mb-1">Dari Tanggal:</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full bg-white border-2 border-black px-2.5 py-1.5 text-xs font-bold font-mono focus:outline-hidden">
                </div>

                <!-- Tanggal Sampai -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-black mb-1">Sampai Tanggal:</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full bg-white border-2 border-black px-2.5 py-1.5 text-xs font-bold font-mono focus:outline-hidden">
                </div>

                <!-- Cari Nama / NIS -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-black mb-1">Cari Siswa:</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIS..."
                           class="w-full bg-white border-2 border-black px-2.5 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200">
                <div class="flex items-center gap-2">
                    <button type="submit" class="neo-btn bg-black text-white text-xs px-4 py-1.5 font-bold cursor-pointer hover:bg-slate-800">
                        🔍 Terapkan Filter
                    </button>
                    @if($classId || $type !== 'all' || $startDate || $endDate || $search)
                        <a href="{{ route('guru.leave-requests.index', ['status' => $status]) }}" 
                           class="neo-btn bg-slate-200 text-black text-xs px-3 py-1.5 font-bold hover:bg-slate-300">
                            Reset Filter
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('guru.leave-requests.export', request()->query()) }}" 
                       class="neo-btn bg-emerald-100 text-emerald-950 text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-emerald-200">
                        <span>📥</span> Unduh Excel (.xlsx)
                    </a>
                    <button type="button" onclick="window.print()" 
                            class="neo-btn bg-white text-black text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-slate-100">
                        <span>🖨️</span> Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 4 Status Tabs -->
    <div class="border-b-2 border-black flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('guru.leave-requests.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
           class="px-4 py-2.5 text-xs uppercase transition-all -mb-[2px] flex items-center gap-1.5
           {{ $status === 'pending' 
                ? 'font-black border-t-2 border-l-2 border-r-2 border-black bg-white text-black' 
                : 'font-bold text-slate-600 hover:text-black border-t-2 border-l-2 border-r-2 border-transparent' }}">
            <span>⏳</span> Menunggu Verifikasi ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('guru.leave-requests.index', array_merge(request()->query(), ['status' => 'approved'])) }}" 
           class="px-4 py-2.5 text-xs uppercase transition-all -mb-[2px] flex items-center gap-1.5
           {{ $status === 'approved' 
                ? 'font-black border-t-2 border-l-2 border-r-2 border-black bg-white text-black' 
                : 'font-bold text-slate-600 hover:text-black border-t-2 border-l-2 border-r-2 border-transparent' }}">
            <span>✅</span> Disetujui ({{ $counts['approved'] }})
        </a>
        <a href="{{ route('guru.leave-requests.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" 
           class="px-4 py-2.5 text-xs uppercase transition-all -mb-[2px] flex items-center gap-1.5
           {{ $status === 'rejected' 
                ? 'font-black border-t-2 border-l-2 border-r-2 border-black bg-white text-black' 
                : 'font-bold text-slate-600 hover:text-black border-t-2 border-l-2 border-r-2 border-transparent' }}">
            <span>❌</span> Ditolak ({{ $counts['rejected'] }})
        </a>
        <a href="{{ route('guru.leave-requests.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
           class="px-4 py-2.5 text-xs uppercase transition-all -mb-[2px] flex items-center gap-1.5
           {{ $status === 'all' 
                ? 'font-black border-t-2 border-l-2 border-r-2 border-black bg-white text-black' 
                : 'font-bold text-slate-600 hover:text-black border-t-2 border-l-2 border-r-2 border-transparent' }}">
            <span>📋</span> Semua Riwayat ({{ $counts['all'] }})
        </a>
    </div>

    <!-- Tabel Daftar Permohonan Izin -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black min-w-[200px]">Siswa & Kelas</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Jenis</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Periode Izin</th>
                        <th class="p-3 border-r-2 border-black min-w-[240px]">Alasan & Lampiran Bukti</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Status & Verifikator</th>
                        <th class="p-3 text-center min-w-[150px]">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($leaveRequests as $idx => $lr)
                        @php
                            $from = \Carbon\Carbon::parse($lr->date_from);
                            $to = \Carbon\Carbon::parse($lr->date_to);
                            $days = $from->diffInDays($to) + 1;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors text-xs">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                {{ $leaveRequests->firstItem() + $idx }}
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#5294FF] text-white font-black text-xs flex items-center justify-center border-2 border-black shrink-0">
                                        {{ strtoupper(substr($lr->student->user->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-black text-sm">{{ $lr->student->user->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            <span>{{ $lr->student->schoolClass->name ?? '-' }}</span> • <span>NIS: {{ $lr->student->nis }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            Diajukan: {{ $lr->created_at->translatedFormat('d M Y, H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px] uppercase font-bold
                                    {{ $lr->type === 'sakit' ? 'bg-[#FFD43B] text-black' : 'bg-[#868E96] text-white' }}">
                                    {{ $lr->type }}
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black font-mono">
                                <div class="font-bold text-black">{{ $from->translatedFormat('d M Y') }}</div>
                                @if($lr->date_from !== $lr->date_to)
                                    <div class="text-[11px] text-slate-500">s/d {{ $to->translatedFormat('d M Y') }}</div>
                                @endif
                                <div class="text-[10px] font-bold text-blue-700 mt-0.5">Durasi: {{ $days }} Hari</div>
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div class="text-slate-800 font-medium whitespace-pre-line">{{ $lr->reason }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">
                                    Pemohon: <b>{{ $lr->requester->name ?? 'Orang Tua' }}</b>
                                </div>
                                @if($lr->attachment_path)
                                    <div class="mt-2">
                                        <button type="button" 
                                                onclick="previewAttachment('{{ asset('storage/' . $lr->attachment_path) }}', '{{ strtolower(pathinfo($lr->attachment_path, PATHINFO_EXTENSION)) }}')"
                                                class="neo-btn bg-white text-blue-700 text-[10px] font-bold px-2 py-0.5 flex items-center gap-1 hover:bg-slate-100 cursor-pointer">
                                            <span>📎</span> Lihat Lampiran Surat
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div>
                                    <span class="neo-badge text-[10px]
                                        @if($lr->status === 'approved') bg-[#20C997] text-white 
                                        @elseif($lr->status === 'rejected') bg-[#FF6B6B] text-white 
                                        @else bg-amber-400 text-black @endif">
                                        {{ strtoupper($lr->status) }}
                                    </span>
                                </div>
                                @if($lr->status !== 'pending')
                                    <div class="text-[10px] text-slate-500 mt-1 space-y-0.5">
                                        <div>Oleh: <b>{{ $lr->reviewer->name ?? 'Wali Kelas' }}</b></div>
                                        <div>Tgl: {{ $lr->reviewed_at ? \Carbon\Carbon::parse($lr->reviewed_at)->translatedFormat('d/m/y H:i') : '-' }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($lr->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('guru.leave-requests.approve', $lr->id) }}" method="POST"
                                              onsubmit="return confirmAction(event, 'Setujui Pengajuan?', 'Kehadiran siswa akan otomatis dicatat sebagai {{ strtoupper($lr->type) }} pada rentang tanggal tersebut.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="neo-btn bg-[#20C997] text-white text-[11px] font-bold px-2.5 py-1.5 hover:bg-emerald-500 cursor-pointer">
                                                ✓ Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('guru.leave-requests.reject', $lr->id) }}" method="POST"
                                              onsubmit="return confirmAction(event, 'Tolak Pengajuan?', 'Pengajuan ini akan ditandai ditolak.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="neo-btn bg-[#FF6B6B] text-white text-[11px] font-bold px-2.5 py-1.5 hover:bg-rose-600 cursor-pointer">
                                                ✕ Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic font-semibold">Telah Diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-500 font-semibold">
                                <div class="text-3xl mb-2">📬</div>
                                <div>Tidak ada data permohonan izin pada kategori filter ini.</div>
                                <div class="text-xs text-slate-400 mt-1">Coba sesuaikan filter kelas atau tanggal di bagian atas.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaveRequests->hasPages())
            <div class="p-4 border-t-2 border-black bg-slate-50">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Input Izin Siswa Manual oleh Guru -->
<div id="createLeaveModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-[#20C997] p-4 text-black border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">📝</span>
                <h3 class="font-heading font-black text-base uppercase">Catat Izin Siswa Manual</h3>
            </div>
            <button type="button" onclick="closeCreateLeaveModal()" class="font-black text-lg hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('guru.leave-requests.store') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Pilih Siswa *</label>
                <select name="student_id" required
                        class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($students as $st)
                        <option value="{{ $st->id }}">
                            {{ $st->schoolClass->name ?? '-' }} — {{ $st->nis }} — {{ $st->user->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Jenis Perizinan *</label>
                    <select name="type" required
                            class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                        <option value="izin">📝 Izin (Kepentingan Keluarga / Lainnya)</option>
                        <option value="sakit">🩺 Sakit (Dengan/Tanpa Surat Dokter)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Dari Tanggal *</label>
                    <input type="date" name="date_from" value="{{ now()->toDateString() }}" required
                           class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold font-mono focus:outline-hidden">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Sampai Tanggal *</label>
                <input type="date" name="date_to" value="{{ now()->toDateString() }}" required
                       class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold font-mono focus:outline-hidden">
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Alasan / Keterangan *</label>
                <textarea name="reason" rows="3" required placeholder="Contoh: Sakit demam tinggi, Menghadiri pernikahan keluarga, dsb..."
                          class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-medium text-black focus:outline-hidden"></textarea>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Unggah Surat / Foto Bukti (Opsional)</label>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full bg-slate-50 border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                <p class="text-[10px] text-slate-500 mt-0.5">Format: JPG, PNG, PDF (Maksimal 2 MB)</p>
            </div>

            <div class="p-3 bg-[#FFF9DB] border-2 border-black rounded-sm text-[11px] font-semibold text-slate-800">
                ⚡ <b>Catatan:</b> Perizinan yang dicatat langsung oleh guru/wali kelas akan <b>otomatis disetujui</b> dan kehadiran siswa langsung disinkronkan ke database.
            </div>

            <div class="pt-3 border-t-2 border-black/20 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCreateLeaveModal()" class="neo-btn bg-slate-200 text-black text-xs font-bold px-4 py-2 hover:bg-slate-300">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2 hover:bg-emerald-400">
                    💾 Simpan & Setujui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pratinjau Lampiran Bukti Surat -->
<div id="previewModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-black p-4 text-white flex items-center justify-between">
            <h3 class="font-heading font-black text-sm uppercase flex items-center gap-2">
                <span>📎</span> Pratinjau Lampiran Dokumen
            </h3>
            <button type="button" onclick="closePreviewModal()" class="font-black text-lg hover:opacity-75">✕</button>
        </div>
        <div class="p-4 flex items-center justify-center min-h-[300px] max-h-[70vh] overflow-auto bg-slate-100" id="previewContainer">
            <!-- Konten Pratinjau Gambar atau Iframe PDF -->
        </div>
        <div class="p-3 bg-white border-t-2 border-black flex justify-between items-center">
            <a id="downloadAttachmentBtn" href="#" target="_blank" class="neo-btn bg-[#5294FF] text-white text-xs font-bold px-4 py-1.5">
                ⬇ Unduh File
            </a>
            <button type="button" onclick="closePreviewModal()" class="neo-btn bg-black text-white text-xs font-bold px-4 py-1.5">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateLeaveModal() {
        document.getElementById('createLeaveModal').classList.remove('hidden');
    }

    function closeCreateLeaveModal() {
        document.getElementById('createLeaveModal').classList.add('hidden');
    }

    function previewAttachment(url, ext) {
        const container = document.getElementById('previewContainer');
        const dlBtn = document.getElementById('downloadAttachmentBtn');
        dlBtn.href = url;

        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            container.innerHTML = `<img src="${url}" class="max-h-[60vh] max-w-full object-contain border-2 border-black neo-box-sm">`;
        } else if (ext === 'pdf') {
            container.innerHTML = `<iframe src="${url}" class="w-full h-[60vh] border-2 border-black"></iframe>`;
        } else {
            container.innerHTML = `<div class="text-center p-8 space-y-2"><div class="text-3xl">📄</div><div class="text-sm font-bold">File Lampiran Dokumen</div><a href="${url}" target="_blank" class="neo-btn bg-black text-white text-xs px-4 py-2 mt-2">Buka Dokumen</a></div>`;
        }

        document.getElementById('previewModal').classList.remove('hidden');
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.add('hidden');
    }

    function confirmAction(e, title, text) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>
@endpush
