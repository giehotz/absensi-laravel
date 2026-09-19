@extends('layouts.neobrutalism')

@section('title', 'Dashboard Orang Tua')

@section('content')
<div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-[#FFF9DB] neo-box-lg p-6 sm:p-8 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#FFD43B] text-black">PORTAL WALI MURID</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    No. Kontak: {{ $parent->phone }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black text-black tracking-tight">
                SELAMAT DATANG, BAPAK/IBU {{ Auth::user()->name }}
            </h1>
            <p class="text-sm font-semibold text-slate-800 max-w-xl">
                Pantau kehadiran putra/putri Anda di sekolah secara langsung (real-time) dan terima notifikasi presensi otomatis.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 z-10">
            <button class="neo-btn bg-[#20C997] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer">
                <span>💬</span> Hubungi Wali Kelas
            </button>
        </div>
    </div>

    <!-- Monitoring Anak -->
    <div class="space-y-4">
        <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
            <span>🧒</span> Status Kehadiran Ananda Hari Ini
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($children as $child)
            @php
                $att = $childrenAttendances->get($child->id);
            @endphp
            <div class="bg-white neo-box-lg p-6 space-y-5">
                <div class="flex items-start justify-between border-b-2 border-black pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-[#5294FF] text-white border-2 border-black font-heading font-black text-xl flex items-center justify-center neo-box-sm">
                            {{ substr($child->user->name ?? 'A', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-lg text-black">{{ $child->user->name ?? 'Nama Siswa' }}</h3>
                            <p class="text-xs font-bold text-slate-600">{{ $child->schoolClass->name ?? 'Kelas' }} • NIS: {{ $child->nis }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px]">
                            {{ $child->schoolClass->homeroomTeacher->user->name ?? 'Wali Kelas' }}
                        </span>
                    </div>
                </div>

                <!-- Status Presensi Box -->
                <div class="p-4 border-2 border-black rounded-sm 
                    @if($att && $att->status === 'hadir') bg-[#D3F9D8]
                    @elseif($att && $att->status === 'terlambat') bg-[#FFF3BF]
                    @else bg-[#FFE3E3] @endif">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Status Hari Ini</div>
                            <div class="text-xl font-black font-heading uppercase text-black mt-0.5">
                                {{ $att->status ?? 'Belum Hadir di Sekolah' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Waktu Masuk</div>
                            <div class="text-base font-black font-mono text-black">
                                {{ $att && $att->check_in_time ? $att->check_in_time->format('H:i') . ' WIB' : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t border-black/20 text-xs font-medium text-slate-800">
                        {{ $att->notes ?? 'Belum ada catatan presensi hari ini.' }}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs font-semibold text-slate-600">Butuh mengajukan surat izin sakit?</span>
                    <button class="neo-btn bg-[#FFD43B] text-black text-xs px-3 py-1.5 cursor-pointer">
                        Ajukan Izin →
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white neo-box p-8 text-center text-slate-500 font-semibold">
                Belum ada profil siswa yang terhubung dengan akun Anda.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Riwayat Notifikasi WhatsApp -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📲</span> Log Notifikasi WhatsApp Terkirim
            </h2>
            <span class="text-xs font-bold font-mono bg-white border border-black px-2 py-0.5">
                Gateway Aktif
            </span>
        </div>

        <div class="bg-white neo-box p-5 space-y-3">
            @forelse($notifications as $notif)
            <div class="p-3.5 bg-[#F4FBF7] border-2 border-black rounded-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center font-bold text-white text-xs shrink-0 mt-0.5">
                        WA
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-900 leading-snug">
                            {{ $notif->message }}
                        </div>
                        <div class="text-[11px] font-mono text-slate-500 mt-1">
                            Dikirim: {{ $notif->sent_at ? $notif->sent_at->format('d/m/Y H:i:s') : '-' }} WIB
                        </div>
                    </div>
                </div>
                <div class="shrink-0">
                    <span class="neo-badge bg-[#20C997] text-white">
                        {{ $notif->status }}
                    </span>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-slate-500 font-semibold">
                Belum ada notifikasi yang terkirim ke WhatsApp Anda.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
