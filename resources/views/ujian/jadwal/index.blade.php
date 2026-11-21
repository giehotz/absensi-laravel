@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Jadwal Ujian - ' . ($activePeriode->nama_periode ?? ''))

@section('content')
<div class="space-y-6">
    {{-- Header Card Neo-Brutalism Terpadu --}}
    @include('ujian.jadwal.partials._header')

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-emerald-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>✅</span><span>{{ session('success') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-[#FFE3E3] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-rose-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>⚠️</span><span>{{ session('error') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif

    {{-- Tabel Jadwal Ujian & Filter Tingkat --}}
    @include('ujian.jadwal.partials._table')

    {{-- Peta Kesiapan Slot per Rombel/Tingkat --}}
    @include('ujian.jadwal.partials._kelola-slot')
</div>

{{-- Modals Popup --}}
@include('ujian.jadwal.partials._modals')

{{-- Scripts Logika Interaktif --}}
@include('ujian.jadwal.partials._scripts')
@endsection
