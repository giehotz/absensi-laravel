@extends('layouts.supervisi')
@section('title', 'Kelola Perangkat Ajar')
@section('page_title', 'Unggah / Tautkan Perangkat Ajar')

@section('content')
<div class="flex justify-end">
    <a href="{{ route('supervisi.guru.siklus.show', ['jadwal' => $jadwal, 'step' => 1]) }}" class="px-3.5 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
        📋 Buka Mode Step Form Lengkap
    </a>
</div>

@include('supervisi.guru.partials.step_perangkat')
@endsection
