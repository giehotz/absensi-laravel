@extends('layouts.guru')

@section('title', 'Dashboard Guru')
@section('page-title', 'Portal & Dashboard Tenaga Pengajar')

@section('content')
<div class="space-y-5 sm:space-y-6 lg:space-y-8">
    {{-- 1. Welcome Banner & Quick Actions --}}
    @include('guru._welcome-banner')

    {{-- Mobile Quick Jump Strip (md:hidden) --}}
    <div class="md:hidden flex items-center gap-2 overflow-x-auto pb-1 text-xs font-heading font-black no-scrollbar">
        <a href="#jadwal" class="px-3 py-1.5 bg-white text-black border-2 border-black rounded-xs shadow-[2px_2px_0px_#000] shrink-0 active:translate-x-0.5">
            📚 Jadwal Hari Ini ({{ $todaySchedules->count() }})
        </a>
        <a href="#rekap" class="px-3 py-1.5 bg-white text-black border-2 border-black rounded-xs shadow-[2px_2px_0px_#000] shrink-0 active:translate-x-0.5">
            📊 Tren 7 Hari
        </a>
        <a href="#leave-requests" class="px-3 py-1.5 bg-white text-black border-2 border-black rounded-xs shadow-[2px_2px_0px_#000] shrink-0 active:translate-x-0.5 flex items-center gap-1.5">
            <span>📝 Izin Siswa</span>
            @if($pendingLeaveRequests->isNotEmpty())
                <span class="px-1.5 py-0.2 bg-[#FF6B6B] text-white text-[9px] rounded-xs font-mono">
                    {{ $pendingLeaveRequests->count() }}
                </span>
            @endif
        </a>
    </div>

    {{-- 2. 4 Stats Cards --}}
    @include('guru._stats-cards')

    {{-- 3. Two-Column Layout: Left = Jadwal Mengajar, Right = Ringkasan Mingguan --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 lg:gap-8 items-start">
        <div class="lg:col-span-7 space-y-5 sm:space-y-6 lg:space-y-8" id="jadwal">
            @include('guru._jadwal-hari-ini')
        </div>

        <div class="lg:col-span-5 space-y-5 sm:space-y-6 lg:space-y-8" id="rekap">
            @include('guru._ringkasan-mingguan')
        </div>
    </div>

    {{-- 4. Permohonan Izin / Sakit Pending --}}
    @include('guru._leave-requests')
</div>
@endsection

@push('scripts')
<script>
    function featurePlaceholder(name) {
        Swal.fire({
            icon: 'info',
            title: name,
            text: 'Fitur ' + name + ' sedang disiapkan dan akan segera aktif pada pembaruan berikutnya.',
            confirmButtonColor: '#000000',
            confirmButtonText: 'Mengerti'
        });
    }

    function confirmApproveLeave(id, studentName, type) {
        Swal.fire({
            title: 'Setujui ' + type + '?',
            html: 'Apakah Anda yakin ingin menyetujui pengajuan <b>' + type + '</b> untuk siswa <b>' + studentName + '</b>?<br><small class="text-slate-500 font-bold">Presensi siswa akan otomatis dicatat sebagai ' + type.toLowerCase() + ' untuk tanggal yang diajukan.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#000000',
            confirmButtonText: '✓ Ya, Setujui',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-approve-' + id);
                if (form) form.submit();
            }
        });
    }

    function confirmRejectLeave(id, studentName, type) {
        Swal.fire({
            title: 'Tolak ' + type + '?',
            html: 'Apakah Anda yakin ingin menolak permohonan <b>' + type + '</b> untuk siswa <b>' + studentName + '</b>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FF6B6B',
            cancelButtonColor: '#000000',
            confirmButtonText: '✕ Ya, Tolak',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-reject-' + id);
                if (form) form.submit();
            }
        });
    }
</script>
@endpush
