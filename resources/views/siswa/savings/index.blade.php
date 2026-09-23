@extends('layouts.neobrutalism')

@section('title', 'Tabungan Siswa - ' . ($student->user->name ?? 'Buku Tabungan'))

@section('content')
<div class="max-w-2xl mx-auto pb-28 space-y-4">
    <!-- Header Navigasi -->
    <div class="bg-white neo-box p-4 flex items-center justify-between gap-3">
        <a href="{{ route('siswa.dashboard') }}" 
           class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 transition-all">
            <span>←</span> Dashboard
        </a>
        <div class="text-right">
            <span class="neo-badge bg-[#FFD43B] text-black text-[10px] font-black uppercase">
                Simpanan Pelajar
            </span>
        </div>
    </div>

    @if(! $isEnrolled)
        <!-- ========================================================================= -->
        <!-- STATE 1: SISWA BELUM TERDAFTAR / BELUM MEMILIKI TABUNGAN -->
        <!-- ========================================================================= -->
        <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-6 text-center border-4 border-black shadow-[6px_6px_0px_0px_#000]">
            <!-- Icon Hero Gembok & Tabungan -->
            <div class="relative w-24 h-24 mx-auto">
                <div class="w-24 h-24 bg-[#FFF3BF] border-3 border-black rounded-2xl flex items-center justify-center text-5xl shadow-[4px_4px_0px_0px_#000]">
                    🏦
                </div>
                <div class="absolute -bottom-2 -right-2 w-9 h-9 bg-[#FF6B6B] border-2 border-black rounded-full flex items-center justify-center text-lg text-white shadow-[2px_2px_0px_0px_#000]">
                    🔒
                </div>
            </div>

            <!-- Pesan Utama Peringatan -->
            <div class="space-y-2.5">
                <span class="neo-badge bg-[#FFE3E3] text-rose-950 text-xs px-3 py-1 font-black inline-flex items-center gap-1.5 border border-black shadow-[2px_2px_0px_0px_#000]">
                    <span>🔒</span> AKSES DIBATASI
                </span>
                <h2 class="font-heading font-black text-xl sm:text-2xl text-black uppercase tracking-tight">
                    Anda Belum Memiliki Tabungan
                </h2>
                <div class="bg-[#FFF9DB] border-2 border-black p-4 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 max-w-lg mx-auto leading-relaxed shadow-[2px_2px_0px_0px_#000] text-left sm:text-center space-y-2">
                    <p>
                        Akun Anda saat ini <strong>belum terdaftar</strong> dalam sistem tabungan sekolah dan belum didaftarkan oleh <strong>Pengelola Tabungan</strong>, sehingga halaman buku tabungan dan grafik mutasi ini tidak dapat diakses.
                    </p>
                    <p class="text-amber-950 font-bold">
                        👉 Jika ingin membuka tabungan, silakan hubungi pengelola tabungan sekolah melalui kontak di bawah ini.
                    </p>
                </div>
            </div>

            <!-- Kartu Kontak Pengelola Tabungan -->
            <div class="bg-slate-50 border-2 border-black p-4 rounded-xl text-left space-y-1.5 shadow-[3px_3px_0px_0px_#000] max-w-lg mx-auto">
                <div class="flex items-center justify-between text-[11px] font-black uppercase text-slate-600">
                    <span>Pengelola Tabungan Sekolah:</span>
                    <span>👨‍🏫</span>
                </div>
                <div class="font-heading font-black text-sm text-black">
                    {{ $officerName }}
                </div>
                <div class="text-xs text-slate-600 font-mono font-bold">
                    {{ $officerPhone ? 'No. HP / WA: ' . $officerPhone : 'Hubungi Bagian Tata Usaha / Guru Piket Sekolah' }}
                </div>
            </div>

            <!-- Tombol Aksi WhatsApp Langsung -->
            <div class="max-w-lg mx-auto space-y-2">
                @if($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" 
                       class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs sm:text-sm font-black px-6 py-3.5 w-full flex items-center justify-center gap-2.5 shadow-[4px_4px_0px_0px_#000] hover:scale-[1.01] transition-transform cursor-pointer">
                        <span class="text-xl">💬</span>
                        <span>Hubungi Pengelola Tabungan via WhatsApp</span>
                    </a>
                @else
                    <div class="p-3 bg-amber-100 border-2 border-black text-amber-950 text-xs font-bold rounded-lg text-center">
                        Silakan hubungi Pengelola Tabungan secara langsung di ruang guru untuk pendaftaran buku tabungan.
                    </div>
                @endif

                <a href="{{ route('siswa.dashboard') }}" 
                   class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-4 py-2 w-full flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] transition-all">
                    <span>←</span> Kembali ke Dashboard
                </a>
            </div>

            <!-- Manfaat Menabung -->
            <div class="pt-4 border-t-2 border-black/10">
                <div class="text-[11px] font-black uppercase text-slate-600 mb-3 tracking-wider text-left sm:text-center">
                    ⭐ Keuntungan Menabung di Sekolah:
                </div>
                <div class="grid grid-cols-2 gap-2.5 text-left">
                    <div class="p-2.5 bg-slate-50 border border-black rounded-lg">
                        <div class="text-xs font-black text-black">🛡️ Aman & Terdata</div>
                        <div class="text-[10px] text-slate-600 mt-0.5">Semua setoran tercatat digital dan transparan.</div>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-black rounded-lg">
                        <div class="text-xs font-black text-black">📈 Pantau Real-Time</div>
                        <div class="text-[10px] text-slate-600 mt-0.5">Cek grafik dan mutasi langsung lewat HP.</div>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-black rounded-lg">
                        <div class="text-xs font-black text-black">🆓 Tanpa Biaya Admin</div>
                        <div class="text-[10px] text-slate-600 mt-0.5">Saldo utuh tanpa potongan biaya bulanan.</div>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-black rounded-lg">
                        <div class="text-xs font-black text-black">🎯 Latih Disiplin</div>
                        <div class="text-[10px] text-slate-600 mt-0.5">Belajar menyisihkan uang saku sejak dini.</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- ========================================================================= -->
        <!-- STATE 2: SISWA TERDAFTAR (BUKU TABUNGAN DIGITAL AKTIF) -->
        <!-- ========================================================================= -->

        <!-- Kartu Buku Tabungan (ATM/Passbook Style) -->
        <div class="bg-gradient-to-br from-[#1E293B] to-[#0F172A] border-4 border-black p-5 sm:p-6 text-white shadow-[5px_5px_0px_0px_#000] relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#5294FF]/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute right-4 top-4 text-5xl opacity-15">💰</div>

            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🏦</span>
                    <div>
                        <h3 class="font-heading font-black text-sm tracking-wider uppercase text-[#FFD43B]">Buku Tabungan Siswa</h3>
                        <p class="text-[10px] text-slate-400 font-mono">SIMPANAN PELAJAR (SIMPEL)</p>
                    </div>
                </div>
                <span class="neo-badge bg-[#20C997] text-white text-[10px] px-2.5 py-0.5 font-bold shadow-[2px_2px_0px_0px_#000]">
                    ● AKTIF
                </span>
            </div>

            <div class="space-y-1 mb-5">
                <div class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Saldo Tabungan Saat Ini</div>
                <div class="font-mono font-black text-3xl sm:text-4xl text-white tracking-tight">
                    {{ $savingsAccount->formatted_balance }}
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-700/80 flex items-center justify-between text-xs">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Nomor Rekening</div>
                    <div class="font-mono font-bold text-[#FFD43B] tracking-wider">{{ $savingsAccount->account_number }}</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Pemilik Rekening</div>
                    <div class="font-bold text-white truncate max-w-[160px]">{{ $student->user->name ?? 'Siswa' }}</div>
                </div>
            </div>
        </div>

        <!-- Banner Performa Mingguan (Weekly Performance) -->
        <div class="bg-white neo-box p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📊</span>
                    <div>
                        <h4 class="font-heading font-black text-xs sm:text-sm text-black uppercase">Performa Menabung Mingguan</h4>
                        <p class="text-[10px] text-slate-500 font-semibold">Komparasi setoran minggu ini vs minggu lalu</p>
                    </div>
                </div>
                <!-- Status Badge -->
                @if($isMore)
                    <span class="neo-badge bg-[#D3F9D8] text-emerald-950 text-[10px] px-2.5 py-1 font-black flex items-center gap-1">
                        <span>🚀</span> Lebih Banyak (+{{ $growthPercent }}%)
                    </span>
                @elseif($diffDeposit < 0)
                    <span class="neo-badge bg-[#FFE3E3] text-rose-950 text-[10px] px-2.5 py-1 font-black flex items-center gap-1">
                        <span>📉</span> Menurun (-{{ $growthPercent }}%)
                    </span>
                @elseif($thisWeekDeposit > 0)
                    <span class="neo-badge bg-[#D0EBFF] text-blue-950 text-[10px] px-2.5 py-1 font-black flex items-center gap-1">
                        <span>🎯</span> Konsisten Sama
                    </span>
                @else
                    <span class="neo-badge bg-slate-100 text-slate-700 text-[10px] px-2 py-0.5 font-bold">
                        Belum Menabung
                    </span>
                @endif
            </div>

            <!-- Komparasi Angka -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div class="p-3 bg-[#EBFBEE] border-2 border-black rounded-lg">
                    <div class="text-[10px] font-black uppercase text-emerald-900">Minggu Ini</div>
                    <div class="font-mono font-black text-sm sm:text-base text-emerald-700 mt-0.5">
                        Rp {{ number_format($thisWeekDeposit, 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 bg-slate-50 border-2 border-black rounded-lg">
                    <div class="text-[10px] font-black uppercase text-slate-600">Minggu Lalu</div>
                    <div class="font-mono font-black text-sm sm:text-base text-slate-700 mt-0.5">
                        Rp {{ number_format($lastWeekDeposit, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Feedback Motivasi -->
            <div class="text-[11px] font-medium text-slate-700 bg-slate-50 border border-black/20 p-2.5 rounded-lg flex items-center gap-2">
                @if($isMore)
                    <span>⭐</span>
                    <span><strong>Luar biasa!</strong> Setoran tabunganmu minggu ini meningkat <strong>Rp {{ number_format(abs($diffDeposit), 0, ',', '.') }}</strong> dibanding minggu lalu. Pertahankan kebiasaan hemat ini!</span>
                @elseif($diffDeposit < 0)
                    <span>💡</span>
                    <span>Setoran minggu ini lebih sedikit <strong>Rp {{ number_format(abs($diffDeposit), 0, ',', '.') }}</strong> dibanding minggu lalu. Yuk sisihkan sisa uang sakumu sebelum akhir pekan!</span>
                @elseif($thisWeekDeposit > 0)
                    <span>👍</span>
                    <span>Konsistensi yang hebat! Kamu berhasil mempertahankan nominal menabung yang sama dengan minggu lalu.</span>
                @else
                    <span>🌱</span>
                    <span>Minggu ini belum ada setoran yang tercatat. Kunjungi Guru Pengelola Tabungan di sekolah untuk mulai menabung minggu ini!</span>
                @endif
            </div>
        </div>

        <!-- Grafik Tren Mutasi Tabungan (4 Minggu Terakhir) -->
        <div class="bg-white neo-box p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-heading font-black text-xs sm:text-sm text-black uppercase flex items-center gap-1.5">
                        <span>📈</span> Tren Mutasi Tabungan
                    </h4>
                    <p class="text-[10px] text-slate-500 font-semibold">Aktivitas setoran & penarikan 4 minggu terakhir</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-bold">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 bg-[#20C997] border border-black rounded-xs"></span> Setor
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 bg-[#FF6B6B] border border-black rounded-xs"></span> Tarik
                    </span>
                </div>
            </div>

            <!-- Canvas Chart.js -->
            <div class="relative h-56 sm:h-64 w-full pt-2">
                <canvas id="savingsTrendChart"></canvas>
            </div>
        </div>

        <!-- Total Akumulasi Mutasi -->
        <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <div class="bg-[#EBFBEE] border-3 border-black p-3 shadow-[3px_3px_0px_0px_#000]">
                <div class="text-[10px] font-black text-emerald-950 uppercase mb-0.5">Total Setor</div>
                <div class="font-mono font-black text-xs sm:text-sm text-emerald-700">
                    +Rp {{ number_format($totalDeposit, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-[#FFF5F5] border-3 border-black p-3 shadow-[3px_3px_0px_0px_#000]">
                <div class="text-[10px] font-black text-rose-950 uppercase mb-0.5">Total Tarik</div>
                <div class="font-mono font-black text-xs sm:text-sm text-rose-700">
                    -Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white border-3 border-black p-3 shadow-[3px_3px_0px_0px_#000]">
                <div class="text-[10px] font-black text-slate-900 uppercase mb-0.5">Frekuensi</div>
                <div class="font-mono font-black text-xs sm:text-sm text-black">
                    {{ $depositCount }}× Setor
                </div>
            </div>
        </div>

        <!-- Riwayat Mutasi Transaksi -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="font-heading font-black text-xs sm:text-sm text-black uppercase flex items-center gap-1.5">
                    <span>📜</span> Riwayat Mutasi Transaksi
                </h4>
                <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 border border-slate-300 rounded">
                    {{ count($transactions) }} Transaksi Terakhir
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($transactions as $tx)
                    <div class="bg-white neo-box p-3.5 flex items-start justify-between gap-3 hover:translate-x-0.5 transition-transform">
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-heading font-black text-xs text-black">
                                    {{ $tx->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                                <span class="text-[9px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.2 border border-slate-300 rounded">
                                    {{ $tx->transaction_code }}
                                </span>
                                @if($tx->is_corrected)
                                    <span class="neo-badge bg-[#FFE066] text-[#664D03] text-[9px] px-1.5 py-0.2 font-bold border border-black inline-flex items-center gap-0.5">
                                        <span>✏</span> Koreksi
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-slate-700 font-medium">
                                {{ $tx->description ?: ($tx->type === 'deposit' ? 'Setoran tunai tabungan' : 'Penarikan tunai tabungan') }}
                            </div>

                            <div class="text-[10px] text-slate-400 font-mono">
                                Saldo: Rp {{ number_format($tx->balance_before, 0, ',', '.') }} → <strong class="text-black">Rp {{ number_format($tx->balance_after, 0, ',', '.') }}</strong>
                            </div>

                            @if($tx->handler)
                                <div class="text-[10px] text-slate-500 font-semibold flex items-center gap-1 pt-0.5">
                                    <span>👤</span> Dicatat oleh: {{ $tx->handler->name }}
                                </div>
                            @endif
                        </div>

                        <!-- Nominal Badge -->
                        <div class="text-right shrink-0">
                            @if($tx->type === 'deposit')
                                <div class="font-mono font-black text-xs sm:text-sm text-emerald-700">
                                    +Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </div>
                                <span class="neo-badge bg-[#D3F9D8] text-emerald-950 text-[9px] px-1.5 py-0.2 font-bold mt-1 inline-block">
                                    SETORAN
                                </span>
                            @else
                                <div class="font-mono font-black text-xs sm:text-sm text-rose-700">
                                    -Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </div>
                                <span class="neo-badge bg-[#FFE3E3] text-rose-950 text-[9px] px-1.5 py-0.2 font-bold mt-1 inline-block">
                                    PENARIKAN
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white neo-box p-8 text-center text-slate-500 font-semibold space-y-2">
                        <div class="text-3xl">📭</div>
                        <div class="text-xs">Belum ada riwayat mutasi transaksi pada rekening ini.</div>
                        <div class="text-[11px] text-slate-400">Setoran pertama Anda akan langsung tercatat dan diakumulasikan di sini.</div>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>

<!-- Fixed Bottom Navigation Bar -->
@include('siswa._bottom-nav')
@endsection

@push('scripts')
@if($isEnrolled)
<!-- Chart.js Library via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('savingsTrendChart');
        if (!ctx) return;

        const labels = @json($chartLabels);
        const deposits = @json($chartDeposits);
        const withdrawals = @json($chartWithdrawals);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Setoran (Rp)',
                        data: deposits,
                        backgroundColor: '#20C997',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 4,
                    },
                    {
                        label: 'Penarikan (Rp)',
                        data: withdrawals,
                        backgroundColor: '#FF6B6B',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#000000',
                        titleFont: { weight: 'bold', family: 'monospace' },
                        bodyFont: { family: 'monospace' },
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { weight: 'bold', size: 10 },
                            color: '#000000'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#E2E8F0',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 9, family: 'monospace' },
                            color: '#64748B',
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + ' Jt';
                                } else if (value >= 1000) {
                                    return (value / 1000) + ' Rb';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
@endpush
