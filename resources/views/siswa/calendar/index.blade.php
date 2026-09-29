@extends('layouts.neobrutalism')

@section('title', 'Kalender Pendidikan & Hari Libur - ' . ($student->user->name ?? 'Portal Siswa'))

@section('content')
<div class="max-w-3xl mx-auto pb-16 sm:pb-20 space-y-4 px-2 sm:px-0">
    <!-- Header Navigasi -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 flex items-center justify-between gap-3 shadow-[4px_4px_0px_0px_#000]">
        <a href="{{ route('siswa.dashboard') }}" 
           class="bg-slate-100 hover:bg-slate-200 text-black text-xs font-black px-4 py-2 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] flex items-center gap-2 transition-all">
            <span>←</span> Kembali ke Dashboard
        </a>
        <div class="text-right">
            <span class="bg-[#FFD43B] text-black text-[10px] font-black uppercase px-3 py-1 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                Akademik & Libur
            </span>
        </div>
    </div>

    <!-- Konten Kalender Read-Only -->
    @include('partials._calendar-readonly')
</div>

<!-- Fixed Bottom Navigation Bar -->
@include('siswa._bottom-nav')

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
</script>
@endsection
