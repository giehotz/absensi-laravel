@extends('layouts.guru')

@section('title', 'Dashboard Guru')
@section('page-title', 'Portal & Dashboard Tenaga Pengajar')

@section('content')
<div class="space-y-8">
    {{-- 1. Welcome Banner & Quick Actions --}}
    @include('guru._welcome-banner')

    {{-- 2. 4 Stats Cards --}}
    @include('guru._stats-cards')

    {{-- 3. Two-Column Layout: Left = Jadwal & Ringkasan, Right = Log Presensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-7 space-y-8">
            <div id="jadwal">
                @include('guru._jadwal-hari-ini')
            </div>
            <div id="rekap">
                @include('guru._ringkasan-mingguan')
            </div>
        </div>

        <div class="lg:col-span-5 space-y-8">
            @include('guru._log-presensi')
        </div>
    </div>

    {{-- 4. Permohonan Izin / Sakit Pending --}}
    @include('guru._leave-requests')

    {{-- 5. Siswa Kelas Binaan (Wali Kelas) --}}
    @include('guru._kelas-binaan')
</div>
@endsection

@push('scripts')
<script>
    function featurePlaceholder(name) {
        Swal.fire({
            icon: 'info',
            title: name,
            text: 'Fitur ' + name + ' sedang disiapkan dan akan segera aktif pada pembaruan berikutnya.',
            confirmButtonColor: '#5294FF',
            confirmButtonText: 'Mengerti'
        });
    }

    function confirmApproveLeave(id, studentName, type) {
        Swal.fire({
            title: 'Setujui ' + type + '?',
            html: 'Apakah Anda yakin ingin menyetujui pengajuan <b>' + type + '</b> untuk siswa <b>' + studentName + '</b>?<br><small class="text-slate-500">Presensi siswa akan otomatis dicatat sebagai ' + type.toLowerCase() + ' untuk tanggal yang diajukan.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#6c757d',
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
            html: 'Apakah Anda yakin ingin menolak pengajuan <b>' + type + '</b> untuk siswa <b>' + studentName + '</b>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FF6B6B',
            cancelButtonColor: '#6c757d',
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
