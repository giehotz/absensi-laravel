@extends('layouts.guru')

@section('title', 'Detail QR Code - ' . ($qrCode->title ?: 'Tanpa Judul'))
@section('page-title', 'Detail QR Code')

@section('content')
<div class="space-y-6 pb-12">
    <section class="neo-box-lg relative overflow-hidden bg-[#FFD43B] p-5 sm:p-7">
        <div class="pointer-events-none absolute -right-8 -top-8 hidden h-32 w-32 rotate-12 border-[3px] border-black bg-[#5294FF] shadow-neo sm:block"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="neo-badge bg-black text-white">QR {{ str_pad((string) $qrCode->id, 4, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-mono text-[10px] font-black uppercase tracking-wider text-slate-700">{{ $qrCode->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>
                <h1 class="font-heading mt-3 break-words text-2xl font-black uppercase tracking-tight text-black sm:text-3xl lg:text-4xl">
                    {{ $qrCode->title ?: 'QR Code Tanpa Judul' }}
                </h1>
                <a href="{{ $qrCode->target_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mt-3 block max-w-2xl truncate font-mono text-xs font-bold text-blue-800 hover:underline"
                   title="{{ $qrCode->target_url }}">
                    {{ $qrCode->target_url }}
                </a>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row lg:shrink-0">
                <a href="{{ route('guru.qr-generator.index') }}"
                   class="neo-btn bg-white px-4 py-2.5 text-xs font-black uppercase tracking-wider text-black">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m10 19-7-7m0 0 7-7m-7 7h18"></path>
                    </svg>
                    Semua QR
                </a>
                <a href="{{ route('guru.qr-generator.create') }}"
                   class="neo-btn bg-[#5294FF] px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Baru
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
        <section class="neo-box-lg bg-white p-5 sm:p-7 lg:col-span-5 lg:sticky lg:top-28" aria-label="Pratinjau QR Code">
            <div class="mb-5 flex items-center justify-between gap-3 border-b-2 border-black pb-4">
                <div>
                    <span class="neo-badge bg-[#20C997] text-black">Siap Dipindai</span>
                    <h2 class="font-heading mt-2 text-lg font-black uppercase text-black">Pratinjau QR</h2>
                </div>
                <span class="font-mono text-xs font-black text-slate-600">{{ $qrCode->size }} PX</span>
            </div>

            <div class="mx-auto w-full max-w-sm">
                @if($qrCode->frame_style === 'scan_me')
                    <div class="border-[3px] border-b-0 border-black bg-[#FFD43B] px-4 py-2 text-center font-heading text-[10px] font-black uppercase tracking-wider text-black shadow-neo-sm">
                        Scan Me / Pindai Saya
                    </div>
                @endif

                <div class="relative flex w-full items-center justify-center p-4
                    {{ $qrCode->frame_style === 'neo_brutalism' ? 'border-4 border-black shadow-neo' : ($qrCode->frame_style === 'scan_me' ? 'border-[3px] border-black' : 'border-2 border-slate-400') }}"
                    style="background-color: {{ $qrCode->bg_color ?: '#ffffff' }};">
                    <div class="aspect-square w-full max-w-[300px] overflow-hidden [&>svg]:h-full [&>svg]:w-full">
                        @if(!empty($qrCode->svg_content))
                            {!! $qrCode->svg_content !!}
                        @elseif($qrCode->getPngUrl())
                            <img src="{{ $qrCode->getPngUrl() }}" alt="{{ $qrCode->title ?: 'QR Code ' . $qrCode->id }}" class="h-full w-full object-contain">
                        @else
                            <div class="grid h-full w-full place-items-center border-2 border-black bg-white font-mono text-xs font-black text-black shadow-neo-sm">
                                QR #{{ $qrCode->id }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($qrCode->frame_style === 'scan_me')
                    <div class="border-[3px] border-t-0 border-black bg-[#5294FF] px-4 py-2 text-center font-heading text-[10px] font-black uppercase tracking-wider text-white shadow-neo-sm">
                        Absensi & KBM Digital
                    </div>
                @endif
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 border-t-2 border-black pt-5">
                <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qrCode, 'format' => 'png']) }}"
                   class="neo-btn bg-[#20C997] px-3 py-3 text-xs font-black uppercase text-black">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"></path>
                    </svg>
                    Unduh PNG
                </a>
                <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qrCode, 'format' => 'svg']) }}"
                   class="neo-btn bg-[#5294FF] px-3 py-3 text-xs font-black uppercase text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"></path>
                    </svg>
                    Unduh SVG
                </a>
            </div>

            <button id="copyTargetUrl"
                    type="button"
                    data-copy-url="{{ $qrCode->target_url }}"
                    class="neo-btn mt-3 w-full bg-white px-4 py-3 text-xs font-black uppercase tracking-wider text-black">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2m-6 12h8a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-8a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2Z"></path>
                </svg>
                Salin URL Tujuan
            </button>
        </section>

        <div class="space-y-6 lg:col-span-7">
            <section class="neo-box bg-white p-5 sm:p-6" aria-labelledby="qrInformationTitle">
                <div class="mb-5 flex items-center justify-between gap-3 border-b-2 border-black pb-4">
                    <div>
                        <span class="neo-badge bg-[#5294FF] text-white">Metadata</span>
                        <h2 id="qrInformationTitle" class="font-heading mt-2 text-lg font-black uppercase text-black">Informasi QR Code</h2>
                    </div>
                    <span class="font-mono text-[10px] font-black uppercase text-slate-500">ID {{ $qrCode->id }}</span>
                </div>

                <div>
                    <h3 class="font-heading text-xs font-black uppercase tracking-wider text-slate-600">URL Tujuan</h3>
                    <a href="{{ $qrCode->target_url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="neo-box-sm mt-2 block break-all bg-[#E7F5FF] p-3 font-mono text-xs font-bold leading-relaxed text-blue-900 transition-transform hover:-translate-y-0.5">
                        {{ $qrCode->target_url }}
                    </a>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="neo-box-sm bg-[#FFF3BF] p-4">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-amber-900">Ukuran</span>
                        <strong class="mt-1 block font-heading text-xl font-black text-black">{{ $qrCode->size }} px</strong>
                    </div>
                    <div class="neo-box-sm bg-[#D3F9D8] p-4">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-900">Gaya Frame</span>
                        <strong class="mt-1 block font-heading text-sm font-black text-black">
                            @if($qrCode->frame_style === 'neo_brutalism')
                                Neo-Brutalism
                            @elseif($qrCode->frame_style === 'scan_me')
                                Scan Me Banner
                            @else
                                Kotak Default
                            @endif
                        </strong>
                    </div>
                    <div class="neo-box-sm bg-white p-4">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Warna QR</span>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <strong class="font-mono text-xs font-black text-black">{{ $qrCode->qr_color }}</strong>
                            <span class="h-8 w-8 shrink-0 border-2 border-black shadow-neo-sm" style="background-color: {{ $qrCode->qr_color }};"></span>
                        </div>
                    </div>
                    <div class="neo-box-sm bg-white p-4">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Warna Latar</span>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <strong class="font-mono text-xs font-black text-black">{{ $qrCode->bg_color }}</strong>
                            <span class="h-8 w-8 shrink-0 border-2 border-black shadow-neo-sm" style="background-color: {{ $qrCode->bg_color }};"></span>
                        </div>
                    </div>
                    <div class="neo-box-sm bg-white p-4">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Label QR</span>
                        <strong class="mt-1 block break-words font-heading text-sm font-black text-black">{{ $qrCode->show_label ? 'Ditampilkan' : 'Tidak ditampilkan' }}</strong>
                    </div>
                    <div class="neo-box-sm bg-white p-4">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Logo</span>
                        <strong class="mt-1 block font-heading text-sm font-black text-black">
                            @if($qrCode->logo_type === 'custom')
                                Kustom
                            @elseif($qrCode->logo_type === 'default')
                                Logo Sekolah
                            @else
                                Tanpa Logo
                            @endif
                        </strong>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 border-t-2 border-black pt-5 sm:grid-cols-2">
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Dibuat Oleh</span>
                        <strong class="mt-1 block text-sm font-black text-black">{{ Auth::user()->name }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Tanggal Pembuatan</span>
                        <strong class="mt-1 block text-sm font-bold text-black">{{ $qrCode->created_at->translatedFormat('d F Y, H:i') }} WIB</strong>
                    </div>
                </div>
            </section>

            <section class="neo-box border-[#FF6B6B] bg-[#FFE3E3] p-5 sm:p-6" aria-labelledby="dangerZoneTitle">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="neo-badge bg-[#FF6B6B] text-white">Zona Berbahaya</span>
                        <h2 id="dangerZoneTitle" class="font-heading mt-2 text-base font-black uppercase text-rose-950">Hapus QR Code</h2>
                        <p class="mt-1 text-xs font-medium text-rose-900">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                    </div>
                    <form action="{{ route('guru.qr-generator.destroy', $qrCode) }}"
                          method="POST"
                          data-confirm="QR Code yang dihapus tidak dapat dipulihkan."
                          class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="neo-btn w-full bg-[#FF6B6B] px-5 py-3 text-xs font-black uppercase text-white sm:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7 18.133 19.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus QR
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButton = document.getElementById('copyTargetUrl');

        if (copyButton) {
            copyButton.addEventListener('click', function () {
                const targetUrl = copyButton.dataset.copyUrl;

                navigator.clipboard.writeText(targetUrl).then(function () {
                    if (typeof Swal === 'undefined') {
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'URL Tersalin',
                        text: 'Tautan tujuan berhasil disalin.',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'neo-box-lg rounded-xl'
                        }
                    });
                }).catch(function () {
                    if (typeof Swal === 'undefined') {
                        window.alert('Gagal menyalin URL tujuan.');
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyalin',
                        text: 'Salin URL tujuan secara manual dari kotak informasi.',
                        confirmButtonColor: '#FF6B6B',
                        customClass: {
                            popup: 'neo-box-lg rounded-xl'
                        }
                    });
                });
            });
        }

        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();

                if (typeof Swal === 'undefined') {
                    if (window.confirm(form.dataset.confirm)) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Hapus QR Code?',
                    text: form.dataset.confirm,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e03131',
                    cancelButtonColor: '#495057',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'neo-box-lg rounded-xl'
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection
