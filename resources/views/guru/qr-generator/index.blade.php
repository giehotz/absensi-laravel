@extends('layouts.guru')

@section('title', 'QR Generator Saya - Guru')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 bg-[#5294FF] text-white font-heading font-black text-[10px] uppercase rounded border-2 border-black shadow-[2px_2px_0px_#000]">
                    Alat Guru
                </span>
                <span class="text-xs text-slate-500 font-semibold">{{ $qrCodes->total() }} QR Code Anda</span>
            </div>
            <h1 class="font-heading font-black text-2xl lg:text-3xl text-slate-900 tracking-tight mt-1">
                QR Generator Saya
            </h1>
            <p class="text-xs font-semibold text-slate-500 mt-1">
                Buat dan kelola QR Code untuk keperluan KBM, modul pelajaran, atau formulir kelas Anda.
            </p>
        </div>

        <div>
            <a href="{{ route('guru.qr-generator.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1877F2] hover:bg-[#166fe5] text-white font-heading font-black text-xs uppercase tracking-wider rounded-xl border-3 border-black shadow-[4px_4px_0px_0px_#000000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_#000] active:translate-x-1 active:translate-y-1 active:shadow-none transition-all cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Generate QR Code Baru</span>
            </a>
        </div>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span class="font-heading font-bold text-xs text-emerald-950 uppercase tracking-wide">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white border-3 border-black rounded-2xl p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000000]">
        <form action="{{ route('guru.qr-generator.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            {{-- Input Pencarian --}}
            <div class="flex-1 w-full">
                <input type="text" 
                       name="search" 
                       placeholder="Cari berdasarkan judul atau link tujuan..." 
                       value="{{ request('search') }}"
                       class="w-full px-3.5 py-2 bg-slate-50 border-2 border-black rounded-xl text-xs font-semibold text-slate-800 shadow-[2px_2px_0px_#000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5294FF]">
            </div>

            {{-- Tombol Cari & Reset --}}
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" 
                        class="flex-1 sm:flex-initial py-2 px-4 bg-[#FFD43B] hover:bg-yellow-400 text-black font-heading font-black text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[2px_2px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all cursor-pointer text-center">
                    Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('guru.qr-generator.index') }}" 
                       class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-heading font-bold text-xs uppercase rounded-xl border-2 border-black shadow-[2px_2px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all cursor-pointer"
                       title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar QR Code Grid Card --}}
    @if($qrCodes->isEmpty())
        <div class="bg-white border-3 border-black rounded-2xl p-12 text-center shadow-[5px_5px_0px_0px_#000000]">
            <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 border-2 border-black rounded-2xl flex items-center justify-center text-3xl shadow-[3px_3px_0px_#000]">
                📱
            </div>
            <h3 class="font-heading font-black text-lg text-slate-900 mb-1">Belum Ada QR Code</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-5 font-medium">
                @if(request()->filled('search'))
                    Tidak ada QR Code yang cocok dengan kata kunci pencarian Anda.
                @else
                    Anda belum membuat QR Code kustom. Klik tombol di bawah untuk membuat QR Code pertama untuk kelas atau materi Anda.
                @endif
            </p>
            <a href="{{ route('guru.qr-generator.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1877F2] text-white font-heading font-black text-xs uppercase tracking-wider rounded-xl border-2 border-black shadow-[3px_3px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all">
                <span>+ Buat QR Code Sekarang</span>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($qrCodes as $qr)
                <div class="bg-white border-3 border-black rounded-2xl p-4 shadow-[4px_4px_0px_0px_#000000] hover:shadow-[6px_6px_0px_0px_#000000] hover:-translate-y-1 transition-all flex flex-col justify-between group">
                    <div>
                        {{-- Header Card: Badge Frame & Tanggal --}}
                        <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b-2 border-slate-100">
                            <span class="px-2 py-0.5 bg-slate-100 border border-black rounded text-[9px] font-heading font-black uppercase text-slate-800 shadow-[1px_1px_0px_#000]">
                                @if($qr->frame_style === 'neo_brutalism')
                                    Neo-Brutalist
                                @elseif($qr->frame_style === 'scan_me')
                                    Scan Me
                                @else
                                    Kotak
                                @endif
                            </span>
                            <span class="text-[10px] text-slate-400 font-semibold">
                                {{ $qr->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Preview Box QR --}}
                        <div class="w-full aspect-square bg-slate-50 border-2 border-black rounded-xl p-3 flex items-center justify-center shadow-[2px_2px_0px_#000] mb-3 group-hover:bg-yellow-50 transition-colors"
                             style="background-color: {{ $qr->bg_color ?: '#ffffff' }};">
                            <div class="w-full h-full flex items-center justify-center overflow-hidden">
                                @if(!empty($qr->svg_content))
                                    <div class="w-full h-full flex items-center justify-center">
                                        {!! $qr->svg_content !!}
                                    </div>
                                @elseif(!empty($qr->png_path))
                                    <img src="{{ $qr->getPngUrl() }}" alt="{{ $qr->title }}" class="w-full h-full object-contain">
                                @else
                                    <span class="text-xs text-slate-400 font-bold">QR #{{ $qr->id }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Judul & URL --}}
                        <div class="space-y-1">
                            <h3 class="font-heading font-black text-sm text-slate-900 truncate" title="{{ $qr->title ?: 'Tanpa Judul' }}">
                                {{ $qr->title ?: 'QR Code Tanpa Judul' }}
                            </h3>
                            <a href="{{ $qr->target_url }}" target="_blank" rel="noopener noreferrer" class="text-[11px] font-mono text-blue-600 hover:underline truncate block" title="{{ $qr->target_url }}">
                                {{ $qr->target_url }}
                            </a>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 pt-3 border-t-2 border-slate-100 space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qr, 'format' => 'png']) }}" 
                               class="py-1.5 px-2 bg-[#20C997] hover:bg-[#1dbb8c] text-white font-heading font-black text-[10px] uppercase rounded-lg border border-black shadow-[1px_1px_0px_#000] text-center transition-all"
                               title="Unduh PNG">
                                PNG
                            </a>
                            <a href="{{ route('guru.qr-generator.download', ['qrGenerator' => $qr, 'format' => 'svg']) }}" 
                               class="py-1.5 px-2 bg-[#5294FF] hover:bg-[#4383eb] text-white font-heading font-black text-[10px] uppercase rounded-lg border border-black shadow-[1px_1px_0px_#000] text-center transition-all"
                               title="Unduh SVG">
                                SVG
                            </a>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('guru.qr-generator.show', $qr) }}" 
                               class="flex-1 py-1.5 px-3 bg-slate-900 hover:bg-black text-white font-heading font-bold text-xs uppercase tracking-wider rounded-lg border border-black shadow-[2px_2px_0px_#000] text-center hover:translate-x-0.5 hover:translate-y-0.5 transition-all">
                                Detail & Cetak
                            </a>

                            <form action="{{ route('guru.qr-generator.destroy', $qr) }}" method="POST" onsubmit="return confirmDelete(event, this)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-1.5 bg-[#FFE3E3] hover:bg-rose-200 text-rose-800 rounded-lg border border-black shadow-[1px_1px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 transition-all cursor-pointer"
                                        title="Hapus QR Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $qrCodes->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function confirmDelete(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus QR Code?',
        text: 'QR Code ini akan dihapus secara permanen.',
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
