@extends('layouts.guru')

@section('title', 'Generate QR Code Baru')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-10">
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading font-black text-2xl lg:text-3xl text-slate-900 tracking-tight">
                Generate QR Code Baru
            </h1>
            <p class="text-xs font-semibold text-slate-500 mt-1">
                Buat dan bagikan QR Code untuk materi KBM, tugas, atau presensi kelas binaan Anda.
            </p>
        </div>

        <div>
            <a href="{{ route('guru.qr-generator.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-800 font-heading font-bold text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- Form Partial --}}
    @include('partials.qr-generator._form', [
        'actionRoute' => route('guru.qr-generator.store'),
        'schoolSetting' => $schoolSetting,
    ])
</div>
@endsection
