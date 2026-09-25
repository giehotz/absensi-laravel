@extends('layouts.guru')

@section('title', 'Buat QR Code Baru')
@section('page-title', 'Buat QR Code')

@section('content')
<div class="space-y-6 pb-12">
    <section class="neo-box-lg relative overflow-hidden bg-[#FFD43B] p-5 sm:p-7">
        <div class="pointer-events-none absolute -right-8 -top-8 hidden h-32 w-32 rotate-12 border-[3px] border-black bg-[#5294FF] shadow-neo sm:block"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <span class="neo-badge bg-black text-white">QR Generator</span>
                <h1 class="font-heading mt-3 text-2xl font-black uppercase tracking-tight text-black sm:text-3xl lg:text-4xl">
                    Buat QR Code Baru
                </h1>
                <p class="mt-3 max-w-xl text-sm font-medium leading-relaxed text-slate-800">
                    Isi tujuan, pilih tampilan, lalu sistem akan membuat QR Code siap simpan dan bagikan dalam format PNG atau SVG.
                </p>
            </div>

            <a href="{{ route('guru.qr-generator.index') }}"
               class="neo-btn w-fit bg-white px-4 py-2.5 text-xs font-black uppercase tracking-wider text-black">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m10 19-7-7m0 0 7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Tahapan pembuatan QR Code">
        <div class="neo-box-sm flex items-center gap-3 bg-white p-4">
            <span class="grid h-9 w-9 shrink-0 place-items-center border-2 border-black bg-[#5294FF] font-heading text-sm font-black text-white shadow-neo-sm">01</span>
            <div>
                <h2 class="font-heading text-xs font-black uppercase text-black">Tentukan Tujuan</h2>
                <p class="mt-0.5 text-[11px] font-medium text-slate-600">Masukkan URL yang akan dipindai.</p>
            </div>
        </div>
        <div class="neo-box-sm flex items-center gap-3 bg-white p-4">
            <span class="grid h-9 w-9 shrink-0 place-items-center border-2 border-black bg-[#FFD43B] font-heading text-sm font-black text-black shadow-neo-sm">02</span>
            <div>
                <h2 class="font-heading text-xs font-black uppercase text-black">Atur Tampilan</h2>
                <p class="mt-0.5 text-[11px] font-medium text-slate-600">Pilih warna, logo, dan gaya frame.</p>
            </div>
        </div>
        <div class="neo-box-sm flex items-center gap-3 bg-white p-4">
            <span class="grid h-9 w-9 shrink-0 place-items-center border-2 border-black bg-[#20C997] font-heading text-sm font-black text-black shadow-neo-sm">03</span>
            <div>
                <h2 class="font-heading text-xs font-black uppercase text-black">Buat & Bagikan</h2>
                <p class="mt-0.5 text-[11px] font-medium text-slate-600">Unduh atau salin tautan QR Code.</p>
            </div>
        </div>
    </section>

    <section aria-label="Formulir pembuatan QR Code">
        @include('partials.qr-generator._form', [
            'actionRoute' => route('guru.qr-generator.store'),
            'schoolSetting' => $schoolSetting,
        ])
    </section>
</div>
@endsection
