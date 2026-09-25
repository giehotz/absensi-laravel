<!-- ========================================================================= -->
<!-- TAB 6: BUKU TABUNGAN DIGITAL SISWA -->
<!-- ========================================================================= -->
<div id="tabContent-tabungan" class="tab-pane hidden space-y-4">
@if($savingsAccount)
    <!-- Kartu Buku Tabungan (ATM/Passbook Style) -->
    <div class="bg-gradient-to-br from-[#1E293B] to-[#0F172A] border-4 border-black p-5 text-white shadow-[4px_4px_0px_0px_#000] relative overflow-hidden">
        <!-- Background Pattern Decor -->
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-[#5294FF]/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute right-4 top-4 text-4xl opacity-20">💰</div>

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🏦</span>
                <div>
                    <h3 class="font-heading font-black text-sm tracking-wider uppercase text-[#FFD43B]">Buku Tabungan Siswa</h3>
                    <p class="text-[10px] text-slate-400 font-mono">SIMPANAN PELAJAR (SIMPEL)</p>
                </div>
            </div>
            <span class="neo-badge bg-[#20C997] text-white text-[10px] px-2.5 py-0.5 font-bold shadow-[2px_2px_0px_0px_#000]">
                ● {{ strtoupper($savingsAccount->status) }}
            </span>
        </div>

        <div class="space-y-1 mb-4">
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
                <div class="font-bold text-white truncate max-w-[150px]">{{ $student->user->name ?? 'Siswa' }}</div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Akumulasi Tabungan -->
    @php
        $txList = $savingsTransactions ?? collect();
        $totalSetor = $txList->where('type', 'deposit')->sum('amount');
        $totalTarik = $txList->where('type', 'withdrawal')->sum('amount');
    @endphp
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-[#EBFBEE] border-3 border-black p-3 shadow-[3px_3px_0px_0px_#000]">
            <div class="flex items-center gap-1.5 text-xs font-black text-emerald-950 uppercase mb-1">
                <span>📥</span> Total Setor
            </div>
            <div class="font-mono font-black text-sm sm:text-base text-emerald-700">
                +Rp {{ number_format($totalSetor, 0, ',', '.') }}
            </div>
        </div>
        <div class="bg-[#FFF5F5] border-3 border-black p-3 shadow-[3px_3px_0px_0px_#000]">
            <div class="flex items-center gap-1.5 text-xs font-black text-rose-950 uppercase mb-1">
                <span>📤</span> Total Tarik
            </div>
            <div class="font-mono font-black text-sm sm:text-base text-rose-700">
                -Rp {{ number_format($totalTarik, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Informasi Prosedur -->
    <div class="bg-[#FFF9DB] border-3 border-black p-3 neo-box text-amber-950 text-xs font-medium space-y-1">
        <div class="font-bold flex items-center gap-1.5 text-black">
            <span>ℹ</span> Ketentuan Tabungan Sekolah:
        </div>
        <p class="text-[11px] leading-relaxed text-amber-900">
            Setoran dan penarikan tabungan dapat dilakukan secara langsung di sekolah melalui <strong>Guru Pengelola Tabungan (Bendahara)</strong>. Harap selalu memeriksa mutasi setelah melakukan transaksi.
        </p>
    </div>

    <!-- Daftar Riwayat Mutasi Tabungan -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="font-heading font-black text-sm text-black uppercase flex items-center gap-1.5">
                <span>📜</span> Riwayat Mutasi Transaksi
            </h4>
            <span class="text-[11px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 border border-slate-300 rounded">
                {{ count($txList) }} Transaksi Terakhir
            </span>
        </div>

        <div class="space-y-2.5">
            @forelse($txList as $tx)
                <div class="bg-white neo-box p-3.5 flex items-start justify-between gap-3 hover:translate-x-0.5 transition-transform">
                    <div class="min-w-0 space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-heading font-black text-xs text-black">
                                {{ $tx->created_at->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                            <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.2 border border-slate-300 rounded">
                                {{ $tx->transaction_code }}
                            </span>
                            @if($tx->is_corrected)
                                <span class="neo-badge bg-[#FFE066] text-[#664D03] text-[10px] px-2 py-0.5 font-bold border border-black inline-flex items-center gap-1 shadow-[1px_1px_0px_0px_#000]">
                                    <span>⚠️</span> Dikoreksi
                                </span>
                            @endif
                        </div>

                        <div class="text-xs font-medium text-slate-700">
                            {{ $tx->description ?: ($tx->isDeposit() ? 'Setoran tunai' : 'Penarikan tunai') }}
                        </div>

                        @if($tx->is_corrected)
                            <div class="bg-amber-50 border-l-2 border-amber-500 p-2 text-[11px] text-amber-900 rounded-r space-y-0.5 my-1">
                                <div class="font-semibold flex items-baseline gap-1">
                                    <span class="shrink-0 font-bold text-amber-950">Alasan:</span>
                                    <span class="italic text-slate-800">"{{ $tx->correction_reason }}"</span>
                                </div>
                                <div class="text-[10px] text-amber-800 flex items-center gap-1 flex-wrap">
                                    <span>Semula: <span class="line-through font-semibold text-rose-700">{{ $tx->formatted_original_amount }}</span></span>
                                    <span>•</span>
                                    <span>Oleh: <strong>{{ $tx->corrector?->name ?? 'Petugas' }}</strong> ({{ $tx->corrected_at?->translatedFormat('d M Y, H:i') }} WIB)</span>
                                </div>
                            </div>
                        @endif

                        <div class="text-[11px] font-mono text-slate-500 flex items-center gap-1">
                            <span>Sisa Saldo:</span>
                            <span class="font-bold text-black">{{ $tx->formatted_balance_after }}</span>
                            @if($tx->handler)
                                <span class="text-slate-400">• Petugas: {{ $tx->handler->name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        @if($tx->isDeposit())
                            <span class="neo-badge bg-[#20C997] text-white text-xs font-mono font-black px-2.5 py-1 inline-block">
                                +{{ $tx->formatted_amount }}
                            </span>
                        @else
                            <span class="neo-badge bg-[#FF6B6B] text-white text-xs font-mono font-black px-2.5 py-1 inline-block">
                                -{{ $tx->formatted_amount }}
                            </span>
                        @endif
                        <div class="text-[10px] font-bold text-slate-500 mt-1 uppercase">
                            {{ $tx->isDeposit() ? 'Setor Tunai' : 'Penarikan' }}
                        </div>
                        @if($tx->is_corrected)
                            <div class="text-[10px] text-slate-400 font-mono line-through mt-0.5" title="Nominal sebelum koreksi">
                                {{ $tx->formatted_original_amount }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white neo-box p-8 text-center text-slate-500 space-y-2">
                    <div class="text-4xl">🪙</div>
                    <div class="font-heading font-bold text-sm text-black">Belum Ada Transaksi</div>
                    <p class="text-xs text-slate-600 max-w-sm mx-auto">
                        Anda belum memiliki mutasi setoran atau penarikan tabungan. Mari mulai menabung sejak dini bersama Guru Pengelola Tabungan!
                    </p>
                </div>
            @endforelse
        </div>
    </div>
@else
    <div class="bg-white neo-box p-8 text-center text-slate-500 space-y-3">
        <div class="text-4xl">🏦</div>
        <div class="font-heading font-black text-base text-black">Buku Tabungan Belum Aktif</div>
        <p class="text-xs text-slate-600 max-w-sm mx-auto">
            Akun Anda belum memiliki rekening tabungan aktif. Silakan hubungi Guru Pengelola Tabungan di sekolah untuk membuka tabungan siswa.
        </p>
        <a href="{{ route('siswa.savings.index') }}" class="neo-btn bg-[#FFD43B] text-black px-4 py-2 text-xs font-black inline-block uppercase">
            Buka Halaman Tabungan →
        </a>
    </div>
@endif
</div>
