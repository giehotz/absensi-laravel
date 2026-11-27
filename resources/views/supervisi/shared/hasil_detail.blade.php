@extends('layouts.supervisi')
@section('title', 'Detail Hasil Supervisi')
@section('page_title', 'Detail Hasil & Pengesahan Supervisi')

@section('content')
@php
    $sigService = app(\App\Services\Supervisi\SupervisiSignatureService::class);
    $canSignCheck = $sigService->canSign($jadwal, $portalPrefix);
@endphp

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-4">
    <div>
        <div class="text-xs font-bold text-slate-500">{{ $jadwal->academicYear?->name }} ({{ ucfirst($jadwal->academicYear?->semester ?? '') }}) · {{ $jadwal->tanggal?->format('d/m/Y') }}</div>
        <h2 class="font-heading font-black text-lg">{{ $jadwal->teacher?->user?->name }}</h2>
        <p class="text-xs font-semibold text-slate-600">Supervisor: {{ $jadwal->supervisor_display_name }} · Kelas: {{ $jadwal->schoolClass?->name ?? '-' }} · Mapel: {{ $jadwal->subject?->name ?? '-' }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="px-3 py-1.5 rounded-lg border-2 border-black bg-[#FFD43B] text-sm font-black tabular-nums">
            {{ $jadwal->nilai_akhir !== null ? number_format((float) $jadwal->nilai_akhir, 2) : '-' }} ({{ $jadwal->ketercapaian ?? $jadwal->statusEnum()->label() }})
        </span>
        <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'detail']) }}" target="_blank" class="px-3 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Hasil</a>
        <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'detail', 'polos' => 1]) }}" target="_blank" class="px-3 py-2 bg-white text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Polos</a>
        @if($jadwal->isKurmer())
            <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'kurmer']) }}" target="_blank" class="px-3 py-2 bg-sky-100 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Form A-D PMM</a>
        @endif
        <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'coaching']) }}" target="_blank" class="px-3 py-2 bg-amber-100 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Coaching TIRTA</a>
    </div>
</div>

<!-- Box Tanda Tangan Digital -->
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="font-heading font-black text-sm">✍️ Tanda Tangan Digital Sekuensial (QR Code)</h3>
            <p class="text-xs text-slate-600">Urutan wajib: 1. Supervisor → 2. Guru → 3. Kepala Madrasah</p>
        </div>
        <span class="px-3 py-1 rounded-full border-2 border-black text-xs font-black {{ $isFullySigned ? 'bg-emerald-200' : 'bg-amber-100' }}">
            {{ $isFullySigned ? 'Disahkan Lengkap' : 'Menunggu Pengesahan' }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach(['supervisor' => '1. Supervisor', 'guru' => '2. Guru', 'kepala' => '3. Kepala Madrasah'] as $rKey => $rLabel)
            @php $sig = $signatures[$rKey] ?? null; @endphp
            <div class="p-3.5 rounded-lg border-2 border-black {{ $sig ? 'bg-emerald-50' : 'bg-slate-50' }}">
                <div class="text-[11px] font-black uppercase text-slate-600">{{ $rLabel }}</div>
                @if($sig)
                    <div class="font-black text-sm mt-1">{{ $sig->signer_nama }}</div>
                    <div class="text-xs text-slate-600">NIP: {{ $sig->signer_nip ?: '-' }}</div>
                    <div class="text-[11px] font-bold text-emerald-800 mt-1">TTD: {{ $sig->tanggal_ttd->format('d/m/Y') }}</div>
                    <a href="{{ route('supervisi.verifikasi', $sig->token_verifikasi) }}" target="_blank" class="text-[11px] font-black text-blue-700 underline">Lihat Verifikasi QR</a>
                @else
                    <div class="text-xs font-bold text-slate-400 italic mt-2">Belum ditandatangani</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
        <p class="text-xs font-semibold text-slate-600">
            @if(!empty($signatures[$portalPrefix]))
                Anda sudah menandatangani dokumen ini.
            @elseif($canSignCheck['allowed'])
                Giliran Anda menandatangani dokumen supervisi ini.
            @else
                {{ $canSignCheck['message'] }}
            @endif
        </p>

        <div class="flex flex-wrap gap-2">
            @if($canSignCheck['allowed'] && in_array($portalPrefix, ['guru', 'supervisor', 'kepala'], true))
                <button type="button" onclick="document.getElementById('dialogSignDigital').showModal()" class="px-4 py-2 bg-[#20C997] text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ✍️ Tanda Tangani Digital
                </button>
            @endif

            @if($portalPrefix === 'admin' && !$isFullySigned && $jadwal->status === 'selesai')
                <button type="button" onclick="document.getElementById('dialogSignAllAdmin').showModal()" class="px-4 py-2 bg-[#5294FF] text-white font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ✅ Sahkan Semua Sekaligus
                </button>
            @endif

            @if($portalPrefix === 'admin' && $hasAnySignature)
                <button type="button" onclick="document.getElementById('dialogResetSignature').showModal()" class="px-4 py-2 bg-rose-200 text-rose-950 font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ↺ Reset Tanda Tangan
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Rincian Nilai Komponen -->
@foreach($jadwal->hasils as $hasil)
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-heading font-black text-sm">{{ $hasil->jenisPenilaian?->nama }}</h3>
            <span class="px-2.5 py-1 rounded border-2 border-black bg-amber-100 text-xs font-black tabular-nums">
                {{ $hasil->total_skor }} / {{ $hasil->skor_maksimal }} ({{ number_format((float) $hasil->nilai_akhir, 2) }}% · {{ $hasil->ketercapaian }})
            </span>
        </div>
        <div tabindex="0" role="region" aria-label="Rincian aspek {{ $hasil->jenisPenilaian?->nama }}" class="overflow-x-auto">
            <table class="w-full text-xs border-collapse border-2 border-black">
                <caption class="sr-only">Rincian aspek {{ $hasil->jenisPenilaian?->nama }}</caption>
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="p-2 border border-black text-left w-10">No</th>
                        <th class="p-2 border border-black text-left">Aspek / Fokus Perilaku</th>
                        <th class="p-2 border border-black text-center w-20">Skor</th>
                        <th class="p-2 border border-black text-left">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hasil->detailHasils as $idx => $detail)
                        <tr class="odd:bg-white even:bg-slate-50">
                            <td class="p-2 border border-black">{{ $idx + 1 }}</td>
                            <td class="p-2 border border-black font-semibold">{{ $detail->aspek?->nama_aspek }}</td>
                            <td class="p-2 border border-black text-center font-black tabular-nums">{{ $detail->skor }}</td>
                            <td class="p-2 border border-black">{{ $detail->catatan ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($hasil->rekomendasi)
            <div class="p-3 rounded-lg bg-amber-50 border-2 border-black text-xs"><strong>Rekomendasi:</strong> {{ $hasil->rekomendasi }}</div>
        @endif
    </div>
@endforeach

@if($jadwal->fotoBuktis->isNotEmpty())
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
        <h3 class="font-heading font-black text-sm">📸 Foto Bukti Supervisi</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($jadwal->fotoBuktis as $foto)
                <div class="border-2 border-black rounded-lg overflow-hidden">
                    <img src="{{ $foto->url }}" alt="{{ $foto->keterangan ?? 'Foto bukti' }}" class="w-full h-32 object-cover">
                    @if($foto->keterangan)
                        <div class="p-2 text-[11px] font-semibold">{{ $foto->keterangan }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Native Dialog Modals -->
<dialog id="dialogSignDigital" aria-labelledby="dialogSignDigitalTitle" closedby="any" class="p-0 rounded-xl border-4 border-black shadow-[6px_6px_0px_0px_#000] w-full max-w-md m-auto">
    <form action="{{ route('supervisi.signature.sign', $jadwal) }}" method="POST" class="bg-white p-5 space-y-4">
        @csrf
        <input type="hidden" name="signer_role" value="{{ $portalPrefix }}">
        <h4 id="dialogSignDigitalTitle" class="font-heading font-black text-base">Konfirmasi Tanda Tangan Digital</h4>
        <p class="text-xs text-slate-600">Anda akan menandatangani dokumen supervisi ini sebagai <strong>{{ strtoupper($portalPrefix) }}</strong>.</p>
        <div>
            <label for="tanggal_ttd" class="block text-xs font-black mb-1">Tanggal Tanda Tangan</label>
            <input id="tanggal_ttd" type="date" name="tanggal_ttd" value="{{ $jadwal->tanggal?->toDateString() ?? now()->toDateString() }}" required autofocus class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('dialogSignDigital').close()" class="px-3 py-2 bg-slate-100 text-xs font-bold rounded-lg border-2 border-black cursor-pointer">Batal</button>
            <button type="submit" class="px-4 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Ya, Tandatangani</button>
        </div>
    </form>
</dialog>

@if($portalPrefix === 'admin')
<dialog id="dialogSignAllAdmin" aria-labelledby="dialogSignAllAdminTitle" closedby="any" class="p-0 rounded-xl border-4 border-black shadow-[6px_6px_0px_0px_#000] w-full max-w-md m-auto">
    <form action="{{ route('supervisi.admin.signature.sign-all', $jadwal) }}" method="POST" class="bg-white p-5 space-y-4">
        @csrf
        <h4 id="dialogSignAllAdminTitle" class="font-heading font-black text-base">Sahkan Semua Tanda Tangan</h4>
        <p class="text-xs text-slate-600">Sistem akan menerbitkan QR verifikasi untuk seluruh peran yang belum bertanda tangan (Guru, Supervisor, Kepala Madrasah).</p>
        <div>
            <label for="tanggal_ttd_all" class="block text-xs font-black mb-1">Tanggal Penandatanganan</label>
            <input id="tanggal_ttd_all" type="date" name="tanggal_ttd" value="{{ $jadwal->tanggal?->toDateString() ?? now()->toDateString() }}" required autofocus class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('dialogSignAllAdmin').close()" class="px-3 py-2 bg-slate-100 text-xs font-bold rounded-lg border-2 border-black cursor-pointer">Batal</button>
            <button type="submit" class="px-4 py-2 bg-[#5294FF] text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Sahkan Semua</button>
        </div>
    </form>
</dialog>

<dialog id="dialogResetSignature" aria-labelledby="dialogResetSignatureTitle" closedby="closerequest" class="p-0 rounded-xl border-4 border-black shadow-[6px_6px_0px_0px_#000] w-full max-w-md m-auto">
    <form action="{{ route('supervisi.admin.signature.reset', $jadwal) }}" method="POST" class="bg-white p-5 space-y-4">
        @csrf
        <h4 id="dialogResetSignatureTitle" class="font-heading font-black text-base text-rose-800">Reset Tanda Tangan Digital</h4>
        <p class="text-xs text-slate-600">Seluruh tanda tangan digital pada jadwal ini akan dihapus agar penilaian dapat direvisi kembali.</p>
        <div>
            <label for="alasan_reset" class="block text-xs font-black mb-1">Alasan Reset (Wajib)</label>
            <textarea id="alasan_reset" name="alasan" rows="3" required placeholder="Contoh: Revisi skor aspek observasi kelas" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg"></textarea>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" autofocus onclick="document.getElementById('dialogResetSignature').close()" class="px-3 py-2 bg-slate-100 text-xs font-bold rounded-lg border-2 border-black cursor-pointer">Batal</button>
            <button type="submit" class="px-4 py-2 bg-rose-600 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Reset Sekarang</button>
        </div>
    </form>
</dialog>
@endif
@endsection
