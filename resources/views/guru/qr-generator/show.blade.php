@extends('layouts.guru')

@section('title', 'Detail QR Code - ' . ($qrCode->title ?: 'Tanpa Judul'))

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12">
    {{-- Header & Navigasi --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 bg-[#5294FF] text-white font-heading font-black text-[10px] uppercase rounded border-2 border-black shadow-[2px_2px_0px_#000]">
                    QR Code Guru
                </span>
                <span class="text-xs text-slate-500 font-semibold">ID #{{ $qrCode->id }}</span>
            </div>
            <h1 class="font-heading font-black text-2xl lg:text-3xl text-slate-900 tracking-tight mt-1">
                {{ $qrCode->title ?: 'QR Code Tanpa Judul' }}
            </h1>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('guru.qr-generator.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-800 font-heading font-bold text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Daftar QR</span>
            </a>

            <a href="{{ route('guru.qr-generator.create') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black font-heading font-black text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all cursor-pointer">
                <span>+ Buat Baru</span>
            </a>
        </div>
    </div>

    {{-- Alert Notifikasi Sukses --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-xl">🎉</span>
                <span class="font-heading font-bold text-xs text-emerald-950 uppercase tracking-wide">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        {{-- KOLOM KIRI: TAMPILAN QR CODE & TOMBOL AKSI --}}
        <div class="md:col-span-6 flex flex-col items-center">
            <div class="w-full bg-white border-3 border-black rounded-2xl p-6 sm:p-8 shadow-[6px_6px_0px_0px_#000000] flex flex-col items-center">
                {{-- Frame Wrapper --}}
                <div class="flex flex-col items-center w-full max-w-[340px]">
                    @if($qrCode->frame_style === 'scan_me')
                        <div class="w-full bg-[#FFD43B] text-black font-heading font-black text-xs text-center uppercase tracking-wider py-2 px-4 border-3 border-black rounded-t-2xl shadow-[3px_3px_0px_#000] mb-[-3px] z-10">
                            ⚡ SCAN ME / PINDAI SAYA
                        </div>
                    @endif

                    <div class="relative w-full p-4 flex items-center justify-center transition-all
                        {{ $qrCode->frame_style === 'neo_brutalism' ? 'border-4 border-black rounded-2xl shadow-[5px_5px_0px_#000]' : '' }}
                        {{ $qrCode->frame_style === 'scan_me' ? 'border-3 border-black' : '' }}
                        {{ $qrCode->frame_style === 'default' ? 'border-2 border-slate-300 rounded-xl shadow-sm' : '' }}"
                        style="background-color: {{ $qrCode->bg_color ?: '#ffffff' }};">
                        
                        {{-- QR Content (SVG) --}}
                        <div class="w-full max-w-[280px] aspect-square flex items-center justify-center overflow-hidden">
                            @if(!empty($qrCode->svg_content))
                                {!! $qrCode->svg_content !!}
                            @elseif(!empty($qrCode->png_path))
                                <img src="{{ $qrCode->getPngUrl() }}" alt="{{ $qrCode->title }}" class="w-full h-full object-contain">
                            @endif
                        </div>
                    </div>

                    @if($qrCode->frame_style === 'scan_me')
                        <div class="w-full bg-[#1877F2] text-white font-heading font-black text-[11px] text-center uppercase tracking-wider py-1.5 px-4 border-3 border-black rounded-b-2xl shadow-[3px_3px_0px_#000] mt-[-3px]">
                            Absensi & KBM Digital
                        </div>
                    @endif
                </div>

                {{-- Action Buttons Unduh --}}
                <div class="w-full mt-6 space-y-2.5 pt-5 border-t-2 border-slate-200">
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Unduh PNG --}}
                        <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qrCode, 'format' => 'png']) }}" 
                           class="py-3 px-4 bg-[#20C997] hover:bg-[#1dbb8c] text-white font-heading font-black text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[3px_3px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_#000] transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Unduh PNG</span>
                        </a>

                        {{-- Unduh SVG --}}
                        <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qrCode, 'format' => 'svg']) }}" 
                           class="py-3 px-4 bg-[#5294FF] hover:bg-[#4383eb] text-white font-heading font-black text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[3px_3px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_#000] transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Unduh SVG</span>
                        </a>
                    </div>

                    {{-- Salin Link Button --}}
                    <button type="button" 
                            onclick="copyTargetUrl('{{ $qrCode->target_url }}')" 
                            class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-heading font-bold text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[2px_2px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_#000] transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Salin Link URL Tujuan</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: METADATA & INFORMASI DETAIL --}}
        <div class="md:col-span-6 space-y-5">
            <div class="bg-white border-3 border-black rounded-2xl p-6 shadow-[5px_5px_0px_0px_#000000]">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b-2 border-slate-200">
                    <span class="w-3 h-3 rounded-full bg-[#5294FF] border border-black shadow-[1px_1px_0px_#000]"></span>
                    <h3 class="font-heading font-black text-sm uppercase tracking-wider text-slate-900">Informasi QR Code</h3>
                </div>

                <div class="space-y-4 text-xs">
                    {{-- URL Tujuan --}}
                    <div>
                        <div class="font-heading font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1">URL Tujuan</div>
                        <div class="p-3 bg-slate-50 border-2 border-black rounded-xl font-mono text-xs break-all text-blue-700 font-semibold shadow-[2px_2px_0px_#000]">
                            <a href="{{ $qrCode->target_url }}" target="_blank" rel="noopener noreferrer" class="hover:underline flex items-center gap-1.5">
                                <span>{{ $qrCode->target_url }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Nama Label --}}
                    <div>
                        <div class="font-heading font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1">Nama Label</div>
                        <div class="font-bold text-slate-900 text-sm">
                            {{ $qrCode->title ?: '-' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        {{-- Ukuran --}}
                        <div class="p-3 bg-slate-50 border-2 border-black rounded-xl shadow-[2px_2px_0px_#000]">
                            <span class="font-heading font-bold text-slate-500 uppercase text-[10px] block mb-0.5">Ukuran</span>
                            <span class="font-heading font-black text-slate-900 text-sm">{{ $qrCode->size }} px</span>
                        </div>

                        {{-- Gaya Frame --}}
                        <div class="p-3 bg-slate-50 border-2 border-black rounded-xl shadow-[2px_2px_0px_#000]">
                            <span class="font-heading font-bold text-slate-500 uppercase text-[10px] block mb-0.5">Gaya Frame</span>
                            <span class="font-heading font-black text-slate-900 text-xs">
                                @if($qrCode->frame_style === 'neo_brutalism')
                                    Neo-Brutalism
                                @elseif($qrCode->frame_style === 'scan_me')
                                    Scan Me Banner
                                @else
                                    Kotak Default
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Warna --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-slate-50 border-2 border-black rounded-xl shadow-[2px_2px_0px_#000] flex items-center justify-between">
                            <div>
                                <span class="font-heading font-bold text-slate-500 uppercase text-[10px] block mb-0.5">Warna QR</span>
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $qrCode->qr_color }}</span>
                            </div>
                            <span class="w-6 h-6 rounded-md border-2 border-black shadow-[1px_1px_0px_#000]" style="background-color: {{ $qrCode->qr_color }};"></span>
                        </div>

                        <div class="p-3 bg-slate-50 border-2 border-black rounded-xl shadow-[2px_2px_0px_#000] flex items-center justify-between">
                            <div>
                                <span class="font-heading font-bold text-slate-500 uppercase text-[10px] block mb-0.5">Warna Latar</span>
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $qrCode->bg_color }}</span>
                            </div>
                            <span class="w-6 h-6 rounded-md border-2 border-black shadow-[1px_1px_0px_#000]" style="background-color: {{ $qrCode->bg_color }};"></span>
                        </div>
                    </div>

                    {{-- Pembuat & Waktu --}}
                    <div class="pt-3 border-t-2 border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-500">Dibuat Oleh:</span>
                            <span class="font-heading font-bold text-slate-900 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#5294FF]"></span>
                                Anda ({{ Auth::user()->name }})
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-500">Tanggal Pembuatan:</span>
                            <span class="font-medium text-slate-800">{{ $qrCode->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                    </div>

                    {{-- Hapus QR --}}
                    <div class="pt-4 border-t-2 border-slate-200">
                        <form action="{{ route('guru.qr-generator.destroy', $qrCode) }}" method="POST" onsubmit="return confirmDelete(event, this)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2.5 px-4 bg-[#FFE3E3] hover:bg-rose-200 text-rose-900 font-heading font-black text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[2px_2px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_#000] transition-all cursor-pointer">
                                🗑️ Hapus QR Code Ini
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyTargetUrl(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'URL tujuan berhasil disalin ke clipboard.',
            timer: 1500,
            showConfirmButton: false,
            customClass: {
                popup: 'border-3 border-black shadow-[4px_4px_0px_#000] rounded-2xl'
            }
        });
    }).catch(err => {
        alert('Gagal menyalin: ' + err);
    });
}

function confirmDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus QR Code?',
        text: 'QR Code yang dihapus tidak dapat dipulihkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e03131',
        cancelButtonColor: '#495057',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'border-3 border-black shadow-[4px_4px_0px_#000] rounded-2xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
