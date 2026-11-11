@extends('layouts.admin')

@section('title', 'Pengecekan Duplikat NIS & Verifikasi Manual')
@section('page-title', 'Pengecekan Duplikat NIS Siswa')

@section('content')
<div class="space-y-6">
    <!-- Tab Navigasi Master Siswa -->
    @include('admin.students.partials._tabs')

    <!-- Header & Panduan Verifikasi Manual -->
    <div class="bg-white neo-box p-6 border-2 border-black shadow-[4px_4px_0px_#000]">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div class="space-y-3 max-w-4xl">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 bg-[#FF6B6B] text-white border-2 border-black shadow-[2px_2px_0px_#000] text-xl">⚠️</span>
                    <div>
                        <h2 class="font-heading font-black text-xl text-black">
                            Verifikasi Manual & Resolusi Duplikasi NIS
                        </h2>
                        <p class="text-xs font-semibold text-slate-600">
                            Sistem pendeteksi inkonsistensi nomor induk lokal (NIS) antara API SIS-EMIS dengan data madrasah.
                        </p>
                    </div>
                </div>

                <!-- Banner Saran & Petunjuk Admin -->
                <div class="bg-[#FFF9DB] border-2 border-black p-4 text-xs space-y-2 shadow-[2px_2px_0px_#000]">
                    <div class="font-black text-black flex items-center gap-1.5 uppercase tracking-wide">
                        <span>💡</span>
                        <span>Saran & Petunjuk Verifikasi Manual untuk Admin:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 font-medium leading-relaxed pl-1">
                        <li>
                            <strong class="text-black">Akar Masalah:</strong> Terdapat beberapa siswa di SIS-EMIS yang memiliki nomor <span class="font-bold underline">NIS lokal yang sama persis</span> (kembar), padahal nomor <span class="font-bold text-blue-700">NISN</span> dan <span class="font-bold text-slate-900">NIK</span> mereka 100% berbeda dan unik.
                        </li>
                        <li>
                            <strong class="text-black">Perlakuan Sistem:</strong> Seluruh siswa tetap ditarik 100% lengkap tanpa ada yang tertimpa atau hilang. Siswa kedua/ketiga diberikan <span class="bg-amber-200 px-1 border border-black font-bold">NIS Sementara</span> (berupa nomor NISN unik mereka) agar dapat tersimpan di database.
                        </li>
                        <li>
                            <strong class="text-black">Langkah Tindakan Admin:</strong> Silakan verifikasi nomor NIS resmi melalui <strong>Buku Induk Madrasah</strong> atau cek kesesuaian data di <strong>VervalPD Kemendikbudristek / Portal EMIS Kemenag</strong> menggunakan nomor <span class="font-black text-blue-700">NISN</span> masing-masing siswa di bawah ini.
                        </li>
                        <li>
                            Klik tombol <strong class="bg-[#20C997] px-1.5 py-0.5 border border-black text-black font-black">Verifikasi & Tetapkan NIS</strong> untuk memasukkan nomor NIS resmi yang benar bagi siswa terkait.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tombol Aksi Pindai & Navigasi -->
            <div class="flex flex-col sm:flex-row lg:flex-col gap-2 shrink-0">
                <form action="{{ route('admin.students.cek-duplikat.scan') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full neo-btn bg-[#A5D8FF] hover:bg-[#74c0fc] text-black px-4 py-2.5 text-xs font-heading font-black flex items-center justify-center gap-2 cursor-pointer shadow-[2px_2px_0px_#000]">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Pindai Ulang Konflik NIS</span>
                    </button>
                </form>

                <a href="{{ route('admin.students.index') }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2.5 text-xs font-heading font-black flex items-center justify-center gap-2 cursor-pointer shadow-[2px_2px_0px_#000]">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Data Siswa</span>
                </a>
            </div>
        </div>

        <!-- Kartu Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-[#FFF9DB] border-2 border-black p-4 shadow-[3px_3px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-slate-700 tracking-wider">Kelompok Nomor Kembar</div>
                <div class="text-2xl font-black font-heading text-black mt-1">{{ $stats['total_cases'] }}</div>
                <div class="text-[10px] text-slate-500 font-semibold mt-0.5">Nomor NIS yang terduplikasi</div>
            </div>

            <div class="bg-white border-2 border-black p-4 shadow-[3px_3px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-slate-700 tracking-wider">Siswa Terdampak</div>
                <div class="text-2xl font-black font-heading text-black mt-1">{{ $stats['total_students'] }}</div>
                <div class="text-[10px] text-slate-500 font-semibold mt-0.5">Total profil siswa dalam log</div>
            </div>

            <div class="bg-[#FFE3E3] border-2 border-black p-4 shadow-[3px_3px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-red-900 tracking-wider">Perlu Verifikasi Manual</div>
                <div class="text-2xl font-black font-heading text-red-600 mt-1 flex items-center gap-2">
                    <span>{{ $stats['pending'] }}</span>
                    @if($stats['pending'] > 0)
                        <span class="text-xs bg-red-600 text-white px-2 py-0.5 rounded-full font-bold animate-pulse">Pending</span>
                    @endif
                </div>
                <div class="text-[10px] text-red-700 font-semibold mt-0.5">Menunggu penetapan NIS resmi</div>
            </div>

            <div class="bg-[#D3F9D8] border-2 border-black p-4 shadow-[3px_3px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-green-900 tracking-wider">Telah Diselesaikan</div>
                <div class="text-2xl font-black font-heading text-green-700 mt-1">{{ $stats['resolved'] }}</div>
                <div class="text-[10px] text-green-700 font-semibold mt-0.5">NIS valid & terverifikasi</div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white neo-box p-4 border-2 border-black shadow-[4px_4px_0px_#000] flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Status Tab Filter -->
        <div class="flex items-center gap-1.5 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('admin.students.cek-duplikat.index', ['status' => 'all', 'search' => $search]) }}"
               class="px-3.5 py-1.5 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000] transition-all
               {{ $status === 'all' ? 'bg-[#FFD43B] text-black' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                Semua ({{ $stats['total_cases'] }})
            </a>
            <a href="{{ route('admin.students.cek-duplikat.index', ['status' => 'pending', 'search' => $search]) }}"
               class="px-3.5 py-1.5 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000] transition-all
               {{ $status === 'pending' ? 'bg-[#FF6B6B] text-white' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                Perlu Verifikasi ({{ $stats['pending'] }})
            </a>
            <a href="{{ route('admin.students.cek-duplikat.index', ['status' => 'resolved', 'search' => $search]) }}"
               class="px-3.5 py-1.5 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000] transition-all
               {{ $status === 'resolved' ? 'bg-[#20C997] text-black' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                Selesai ({{ $stats['resolved'] }})
            </a>
        </div>

        <!-- Form Pencarian -->
        <form action="{{ route('admin.students.cek-duplikat.index') }}" method="GET" class="w-full md:w-auto flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative w-full md:w-72">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari nama, NIS, NISN, NIK..." 
                       class="w-full pl-8 pr-3 py-1.5 text-xs neo-input border-2 border-black font-semibold shadow-[2px_2px_0px_#000]" />
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            @if($search)
                <a href="{{ route('admin.students.cek-duplikat.index', ['status' => $status]) }}" 
                   class="p-2 neo-btn bg-slate-200 text-black border-2 border-black text-xs font-black shadow-[2px_2px_0px_#000]" 
                   title="Reset Pencarian">
                    ✕
                </a>
            @endif
            <button type="submit" class="px-3 py-1.5 neo-btn bg-[#FFD43B] text-black border-2 border-black text-xs font-heading font-black shadow-[2px_2px_0px_#000]">
                Cari
            </button>
        </form>
    </div>

    <!-- Daftar Kelompok Duplikat -->
    @if($groupedConflicts->isEmpty())
        <div class="bg-white neo-box p-12 border-2 border-black shadow-[4px_4px_0px_#000] text-center space-y-4">
            <div class="inline-block p-4 bg-[#D3F9D8] border-2 border-black shadow-[3px_3px_0px_#000] rounded-full text-4xl">
                ✅
            </div>
            <div class="space-y-1">
                <h3 class="font-heading font-black text-lg text-black">
                    Tidak Ditemukan Kasus Duplikasi NIS
                </h3>
                <p class="text-xs text-slate-600 max-w-md mx-auto">
                    Seluruh siswa di database memiliki nomor NIS yang unik atau semua konflik duplikasi telah selesai diverifikasi.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('admin.students.index') }}" class="neo-btn bg-[#FFD43B] text-black px-4 py-2 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000]">
                    Buka Data Siswa & Kartu QR
                </a>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($groupedConflicts as $dupNis => $groupItems)
                @php
                    $isGroupPending = $groupItems->contains('status', 'pending');
                @endphp
                <div class="bg-white neo-box border-2 border-black shadow-[4px_4px_0px_#000] overflow-hidden">
                    <!-- Header Kelompok Konflik -->
                    <div class="p-4 border-b-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $isGroupPending ? 'bg-[#FFF9DB]' : 'bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 text-xs font-black uppercase border-2 border-black shadow-[2px_2px_0px_#000] {{ $isGroupPending ? 'bg-[#FF6B6B] text-white' : 'bg-[#20C997] text-black' }}">
                                {{ $isGroupPending ? '⚠️ Perlu Verifikasi' : '✓ Selesai' }}
                            </span>
                            <div>
                                <div class="font-heading font-black text-sm text-black flex items-center gap-2">
                                    <span>Nomor NIS Kembar di SIS-EMIS:</span>
                                    <span class="font-mono bg-white px-2 py-0.5 border border-black shadow-[1px_1px_0px_#000] text-blue-900 font-bold tracking-wider">
                                        {{ $dupNis }}
                                    </span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-600 mt-0.5">
                                    Melibatkan {{ $groupItems->count() }} profil siswa di madrasah.
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[11px] font-bold text-slate-500">
                                Kasus tercatat: {{ $groupItems->first()->created_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    </div>

                    <!-- Tabel Perbandingan Siswa dalam Kelompok -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100 border-b-2 border-black font-heading font-black uppercase text-[10px] tracking-wider text-black">
                                    <th class="p-3 border-r-2 border-black">Profil Siswa</th>
                                    <th class="p-3 border-r-2 border-black">Kelas / Rombel</th>
                                    <th class="p-3 border-r-2 border-black">NIS Saat Ini di Absensi</th>
                                    <th class="p-3 border-r-2 border-black">NISN (Unik Nasional)</th>
                                    <th class="p-3 border-r-2 border-black">NIK</th>
                                    <th class="p-3 border-r-2 border-black">Status & Catatan</th>
                                    <th class="p-3 text-center">Aksi Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-black">
                                @foreach($groupItems as $conflict)
                                    @php
                                        $st = $conflict->student;
                                        $isTempNis = $st && $st->nis !== $conflict->duplicate_nis;
                                        $isResolved = $conflict->status === 'resolved';
                                    @endphp
                                    <tr class="hover:bg-amber-50/50 transition-colors {{ $isResolved ? 'bg-green-50/20' : '' }}">
                                        <!-- Profil Siswa -->
                                        <td class="p-3 border-r-2 border-black font-semibold">
                                            @if($st)
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full border border-black bg-white flex items-center justify-center font-heading font-black text-xs shrink-0 shadow-[1px_1px_0px_#000]">
                                                        {{ strtoupper(substr($st->user->name ?? 'S', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-black text-black text-xs">
                                                            {{ $st->user->name ?? '-' }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-500 font-semibold flex items-center gap-1 mt-0.5">
                                                            <span>{{ $st->gender == 'L' ? '👦 Laki-laki' : '👧 Perempuan' }}</span>
                                                            @if($st->birth_date)
                                                                <span>• {{ \Carbon\Carbon::parse($st->birth_date)->format('d/m/Y') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic">Data Siswa Terhapus</span>
                                            @endif
                                        </td>

                                        <!-- Kelas / Rombel -->
                                        <td class="p-3 border-r-2 border-black font-bold">
                                            <span class="px-2 py-0.5 bg-[#FFF9DB] border border-black shadow-[1px_1px_0px_#000] text-[11px]">
                                                {{ $st->schoolClass->name ?? '-' }}
                                            </span>
                                        </td>

                                        <!-- NIS Saat Ini di Database -->
                                        <td class="p-3 border-r-2 border-black font-mono">
                                            @if($st)
                                                <div class="font-black text-black">
                                                    {{ $st->nis }}
                                                </div>
                                                @if($isTempNis)
                                                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-amber-100 text-amber-900 border border-amber-800 text-[9px] font-bold rounded">
                                                        ⚡ NIS Sementara (dari NISN)
                                                    </span>
                                                @else
                                                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-blue-100 text-blue-900 border border-blue-800 text-[9px] font-bold rounded">
                                                        📌 NIS Asli SIS-EMIS
                                                    </span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- NISN (Unik) -->
                                        <td class="p-3 border-r-2 border-black font-mono font-bold text-blue-800">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ $st->nisn ?? ($conflict->nisn ?? '-') }}</span>
                                                @if($st && filled($st->nisn))
                                                    <button type="button" 
                                                            onclick="copyText('{{ $st->nisn }}', 'NISN')"
                                                            class="text-slate-400 hover:text-black p-0.5 cursor-pointer"
                                                            title="Salin NISN untuk dicek di VervalPD">
                                                        📋
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="text-[9px] text-green-700 font-sans font-semibold mt-0.5">
                                                ✓ Terverifikasi Unik
                                            </div>
                                        </td>

                                        <!-- NIK -->
                                        <td class="p-3 border-r-2 border-black font-mono text-slate-700">
                                            {{ $st->nik ?? ($conflict->nik ?? '-') }}
                                        </td>

                                        <!-- Status & Catatan -->
                                        <td class="p-3 border-r-2 border-black">
                                            @if($isResolved)
                                                <span class="inline-block px-2 py-0.5 bg-[#D3F9D8] text-green-900 border border-black font-black text-[10px] shadow-[1px_1px_0px_#000]">
                                                    ✓ Terverifikasi
                                                </span>
                                                <p class="text-[10px] text-slate-600 mt-1 font-medium leading-snug">
                                                    {{ $conflict->resolution_notes }}
                                                </p>
                                                @if($conflict->resolvedBy)
                                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                                        Oleh: {{ $conflict->resolvedBy->name }} ({{ $conflict->resolved_at?->format('d/m/y H:i') }})
                                                    </div>
                                                @endif
                                            @else
                                                <span class="inline-block px-2 py-0.5 bg-[#FFE3E3] text-red-900 border border-black font-black text-[10px] shadow-[1px_1px_0px_#000]">
                                                    ⏳ Menunggu Tindakan
                                                </span>
                                                <p class="text-[10px] text-slate-600 mt-1 font-medium leading-snug">
                                                    {{ Str::limit($conflict->resolution_notes, 90) }}
                                                </p>
                                            @endif
                                        </td>

                                        <!-- Aksi -->
                                        <td class="p-3 text-center">
                                            @if($st)
                                                <button type="button" 
                                                        onclick="openResolveModal('{{ $conflict->id }}', '{{ addslashes($st->user->name) }}', '{{ $st->nisn }}', '{{ $st->nis }}', '{{ $conflict->duplicate_nis }}')"
                                                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-3 py-1.5 text-xs font-heading font-black shadow-[2px_2px_0px_#000] cursor-pointer inline-flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>{{ $isResolved ? 'Ubah NIS' : 'Verifikasi & Ganti NIS' }}</span>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.students.cek-duplikat.destroy', $conflict->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat konflik ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline text-[10px] font-bold">
                                                        Hapus Log
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $conflicts->links() }}
        </div>
    @endif
</div>

<!-- Modal Verifikasi & Ganti NIS -->
<div id="resolveConflictModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden p-4">
    <div class="bg-white neo-box border-4 border-black shadow-[8px_8px_0px_#000] w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header Modal -->
        <div class="bg-[#FFD43B] p-4 border-b-2 border-black flex items-center justify-between">
            <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                <span>📝</span> Verifikasi & Tetapkan NIS Siswa
            </h3>
            <button type="button" onclick="closeResolveModal()" class="text-black hover:bg-black hover:text-white p-1 font-black text-base border border-black shadow-[1px_1px_0px_#000] leading-none">
                ✕
            </button>
        </div>

        <!-- Form Update NIS -->
        <form id="resolveForm" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div class="p-5 space-y-4 text-xs">
                <!-- Info Siswa -->
                <div class="bg-[#FFF9DB] border-2 border-black p-3 space-y-1.5 shadow-[2px_2px_0px_#000]">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-bold">Nama Siswa:</span>
                        <span id="modalStudentName" class="font-black text-black text-sm">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-bold">NISN Siswa:</span>
                        <span id="modalStudentNisn" class="font-mono font-bold text-blue-900 bg-blue-50 px-1.5 py-0.5 border border-blue-800">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-bold">Nomor Duplikat Asal:</span>
                        <span id="modalDuplicateNis" class="font-mono text-red-900 font-bold">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-bold">NIS Saat Ini:</span>
                        <span id="modalCurrentNis" class="font-mono text-slate-800 font-bold">-</span>
                    </div>
                </div>

                <!-- Input NIS Baru -->
                <div class="space-y-1">
                    <label for="inputNis" class="block font-heading font-black text-black uppercase tracking-wider text-[11px]">
                        Nomor NIS Resmi Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="inputNis" 
                           name="nis" 
                           required 
                           class="w-full neo-input border-2 border-black p-2.5 font-mono font-black text-sm text-black shadow-[2px_2px_0px_#000] focus:bg-[#FFF9DB]" 
                           placeholder="Contoh: 111118060002240072" />
                    <p class="text-[10px] text-slate-500 font-medium">
                        Nomor NIS resmi madrasah dari buku induk atau penetapan kepala madrasah. Wajib unik di database.
                    </p>
                </div>

                <!-- Input Catatan Verifikasi -->
                <div class="space-y-1">
                    <label for="inputNotes" class="block font-heading font-black text-black uppercase tracking-wider text-[11px]">
                        Catatan Verifikasi / Referensi Dokumen <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                    </label>
                    <textarea id="inputNotes" 
                              name="notes" 
                              rows="3" 
                              class="w-full neo-input border-2 border-black p-2.5 text-xs text-black shadow-[2px_2px_0px_#000]" 
                              placeholder="Contoh: Sesuai Buku Induk Siswa Reg. No 072/MIN2/2024 setelah verifikasi NISN di VervalPD."></textarea>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="bg-slate-100 p-4 border-t-2 border-black flex items-center justify-end gap-2">
                <button type="button" 
                        onclick="closeResolveModal()" 
                        class="neo-btn bg-white hover:bg-slate-200 text-black px-4 py-2 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000]">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000]">
                    ✓ Simpan & Selesaikan Konflik
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openResolveModal(conflictId, studentName, nisn, currentNis, duplicateNis) {
        document.getElementById('modalStudentName').textContent = studentName || '-';
        document.getElementById('modalStudentNisn').textContent = nisn || '-';
        document.getElementById('modalDuplicateNis').textContent = duplicateNis || '-';
        document.getElementById('modalCurrentNis').textContent = currentNis || '-';
        
        // Default saran nomor NIS: jika NIS saat ini berbeda dari duplicate NIS (misal NISN), biarkan atau sarankan
        const inputNis = document.getElementById('inputNis');
        inputNis.value = currentNis || duplicateNis || '';
        
        // Action route
        const form = document.getElementById('resolveForm');
        form.action = `/admin/students/cek-duplikat/${conflictId}/resolve`;
        
        document.getElementById('resolveConflictModal').classList.remove('hidden');
        setTimeout(() => inputNis.focus(), 100);
    }

    function closeResolveModal() {
        document.getElementById('resolveConflictModal').classList.add('hidden');
    }

    function copyText(text, label) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${label} disalin ke clipboard!`,
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                alert(`${label} ${text} disalin!`);
            }
        });
    }

    // Tutup modal jika klik di luar box
    document.getElementById('resolveConflictModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeResolveModal();
        }
    });
</script>
@endpush
@endsection
