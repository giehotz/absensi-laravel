@extends('layouts.guru')

@section('title', 'QR Generator Saya - Guru')
@section('page-title', 'QR Generator')

@section('content')
<div class="space-y-6 pb-12">
    <section class="neo-box-lg relative overflow-hidden bg-[#5294FF] p-5 text-white sm:p-7">
        <div class="pointer-events-none absolute -right-8 -top-8 hidden h-32 w-32 rotate-12 border-[3px] border-black bg-[#FFD43B] shadow-neo sm:block"></div>
        <div class="pointer-events-none absolute -bottom-10 right-28 hidden h-24 w-24 -rotate-12 border-[3px] border-black bg-white shadow-neo sm:block"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="neo-badge bg-black text-white">Alat Guru</span>
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-white">Koleksi QR pribadi</span>
                </div>
                <h1 class="font-heading text-2xl font-black uppercase tracking-tight text-white sm:text-3xl lg:text-4xl">
                    Buat QR. Bagikan. Arahkan.
                </h1>
                <p class="mt-3 max-w-xl text-sm font-medium leading-relaxed text-white">
                    Kumpulkan semua QR Code untuk materi, tugas, pengumuman, dan aktivitas kelas Anda dalam satu tempat.
                </p>
            </div>

            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center lg:shrink-0">
                <div class="neo-box-sm min-w-28 bg-white px-4 py-3 text-center text-black">
                    <span class="block font-heading text-2xl font-black leading-none">{{ $qrCodes->total() }}</span>
                    <span class="mt-1 block text-[10px] font-black uppercase tracking-wider">QR tersimpan</span>
                </div>
                <a href="{{ route('guru.qr-generator.create') }}"
                   class="neo-btn bg-[#FFD43B] px-4 py-3 text-xs font-black uppercase tracking-wider text-black">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat QR Baru
                </a>
            </div>
        </div>
    </section>

    <section class="neo-box bg-white p-4 sm:p-5" aria-label="Pencarian QR Code">
        <form action="{{ route('guru.qr-generator.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative min-w-0 flex-1">
                <label for="search" class="sr-only">Cari QR Code</label>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"></path>
                </svg>
                <input id="search"
                       type="search"
                       name="search"
                       placeholder="Cari judul atau URL tujuan..."
                       value="{{ request('search') }}"
                       class="neo-input w-full bg-slate-50 pl-10 pr-4 text-sm font-semibold focus:ring-2 focus:ring-[#5294FF]">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="neo-btn flex-1 bg-[#FFD43B] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-black sm:flex-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"></path>
                    </svg>
                    Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('guru.qr-generator.index') }}"
                       class="neo-btn bg-white px-3.5 py-2.5 text-xs font-black uppercase text-black"
                       title="Reset pencarian"
                       aria-label="Reset pencarian">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 6l12 12M18 6 6 18"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </section>

    @if($qrCodes->isEmpty())
        <section class="neo-box-lg bg-[#FFF3BF] p-8 text-center sm:p-12">
            <div class="mx-auto mb-5 grid h-20 w-20 rotate-3 place-items-center border-[3px] border-black bg-white shadow-neo-sm">
                <svg class="h-10 w-10 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3V3Zm11 0h7v7h-7V3ZM3 14h7v7H3v-7Zm13 3v4m-2-2h4"></path>
                </svg>
            </div>
            <span class="neo-badge bg-black text-white">Belum ada QR Code</span>
            <h2 class="font-heading mt-4 text-2xl font-black uppercase tracking-tight text-black">
                @if(request()->filled('search'))
                    Tidak ada hasil
                @else
                    Mulai koleksi QR Anda
                @endif
            </h2>
            <p class="mx-auto mt-2 max-w-lg text-sm font-medium leading-relaxed text-slate-700">
                @if(request()->filled('search'))
                    Coba kata kunci lain atau hapus filter untuk melihat seluruh QR Code Anda.
                @else
                    Buat QR Code pertama untuk tautan materi, formulir kelas, atau aktivitas pembelajaran Anda.
                @endif
            </p>
            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                @if(request()->filled('search'))
                    <a href="{{ route('guru.qr-generator.index') }}" class="neo-btn bg-white px-5 py-2.5 text-xs font-black uppercase text-black">
                        Reset Pencarian
                    </a>
                @endif
                <a href="{{ route('guru.qr-generator.create') }}" class="neo-btn bg-[#5294FF] px-5 py-2.5 text-xs font-black uppercase text-white">
                    Buat QR Code
                </a>
            </div>
        </section>
    @else
        <section class="space-y-4" aria-label="Daftar QR Code">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-heading text-lg font-black uppercase tracking-tight text-black">QR Code Anda</h2>
                    <p class="text-xs font-semibold text-slate-500">Pilih kode untuk melihat detail, menyalin tautan, atau mengunduh.</p>
                </div>
                @if(request()->filled('search'))
                    <span class="neo-badge w-fit bg-[#E7F5FF] text-blue-950">Filter: {{ request('search') }}</span>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($qrCodes as $qr)
                    <article class="neo-box flex flex-col bg-white p-4 transition-transform hover:-translate-y-1 hover:shadow-neo-lg">
                        <div class="mb-3 flex items-start justify-between gap-3 border-b-2 border-black pb-3">
                            <div class="min-w-0">
                                <span class="neo-badge {{ $qr->frame_style === 'neo_brutalism' ? 'bg-[#FFD43B] text-black' : ($qr->frame_style === 'scan_me' ? 'bg-[#5294FF] text-white' : 'bg-slate-100 text-black') }}">
                                    @if($qr->frame_style === 'neo_brutalism')
                                        Neo-Brutalism
                                    @elseif($qr->frame_style === 'scan_me')
                                        Scan Me
                                    @else
                                        Default
                                    @endif
                                </span>
                                <span class="mt-2 block font-mono text-[10px] font-bold uppercase text-slate-500">#{{ str_pad((string) $qr->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <time class="shrink-0 text-right text-[10px] font-bold text-slate-500" datetime="{{ $qr->created_at->toIso8601String() }}">
                                {{ $qr->created_at->translatedFormat('d M Y') }}
                            </time>
                        </div>

                        <div class="relative mb-4 aspect-square overflow-hidden border-2 border-black bg-slate-50 p-4 shadow-neo-sm"
                             style="background-color: {{ $qr->bg_color ?: '#ffffff' }};">
                            <div class="flex h-full w-full items-center justify-center overflow-hidden [&>svg]:h-full [&>svg]:w-full">
                                @if(!empty($qr->svg_content))
                                    {!! $qr->svg_content !!}
                                @elseif($qr->getPngUrl())
                                    <img src="{{ $qr->getPngUrl() }}" alt="{{ $qr->title ?: 'QR Code ' . $qr->id }}" class="h-full w-full object-contain">
                                @else
                                    <span class="border-2 border-black bg-white px-3 py-2 font-mono text-xs font-black shadow-neo-sm">QR #{{ $qr->id }}</span>
                                @endif
                            </div>
                            <span class="absolute bottom-2 right-2 border border-black bg-white px-1.5 py-0.5 font-mono text-[9px] font-black text-black shadow-[1px_1px_0px_#000]">SCAN</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="truncate font-heading text-base font-black text-black" title="{{ $qr->title ?: 'QR Code Tanpa Judul' }}">
                                {{ $qr->title ?: 'QR Code Tanpa Judul' }}
                            </h3>
                            <a href="{{ $qr->target_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mt-1 block truncate font-mono text-[11px] font-bold text-blue-700 hover:underline"
                               title="{{ $qr->target_url }}">
                                {{ $qr->target_url }}
                            </a>
                            <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] font-black uppercase tracking-wide text-slate-600">
                                <span>{{ $qr->size }} px</span>
                                <span aria-hidden="true">/</span>
                                <span>{{ $qr->show_label ? 'Label aktif' : 'Tanpa label' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 border-t-2 border-black pt-4">
                            <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qr, 'format' => 'png']) }}"
                               class="neo-btn bg-[#20C997] px-3 py-2 text-[10px] font-black uppercase text-black"
                               title="Unduh PNG">
                                PNG
                            </a>
                            <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qr, 'format' => 'svg']) }}"
                               class="neo-btn bg-[#5294FF] px-3 py-2 text-[10px] font-black uppercase text-white"
                               title="Unduh SVG">
                                SVG
                            </a>
                        </div>

                        <div class="mt-2 flex gap-2">
                            <a href="{{ route('guru.qr-generator.show', $qr) }}"
                               class="neo-btn flex-1 bg-black px-3 py-2 text-[10px] font-black uppercase tracking-wider text-white">
                                Detail QR
                            </a>
                            <form action="{{ route('guru.qr-generator.destroy', $qr) }}" method="POST" data-confirm="Hapus QR Code secara permanen?">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="neo-btn bg-[#FFE3E3] px-3 py-2 text-[#9F1239] hover:bg-rose-200"
                                        title="Hapus QR Code"
                                        aria-label="Hapus {{ $qr->title ?: 'QR Code ' . $qr->id }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7 18.133 19.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="neo-box overflow-x-auto bg-white p-3" aria-label="Navigasi halaman">
            {{ $qrCodes->onEachSide(1)->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
