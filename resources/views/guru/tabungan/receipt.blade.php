<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Transaksi Tabungan - {{ $transaction->transaction_code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #f1f5f9;
        }
        .font-mono {
            font-family: 'JetBrains+Mono', monospace;
        }
        @media print {
            body {
                background-color: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border: 2px solid #000 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 flex flex-col items-center justify-center min-h-screen">

    <!-- Action Bar (Hidden when printed) -->
    <div class="no-print mb-6 flex items-center gap-3">
        <button onclick="window.print()" class="bg-[#20C997] hover:bg-emerald-600 text-white font-bold text-xs px-5 py-2.5 rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Bukti Transaksi</span>
        </button>
        <button onclick="window.close()" class="bg-white hover:bg-slate-100 text-black font-bold text-xs px-4 py-2.5 rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
            Tutup
        </button>
    </div>

    <!-- Kuitansi Container (Neobrutalism Print-friendly) -->
    <div class="w-full max-w-md bg-white border-4 border-black p-6 shadow-[6px_6px_0px_0px_#000] print-border relative">
        <!-- Stamp Type -->
        <div class="absolute right-6 top-6">
            <span class="inline-block px-3 py-1 font-black text-xs uppercase border-2 border-black tracking-wider {{ $transaction->isDeposit() ? 'bg-[#D3F9D8] text-emerald-950 shadow-[2px_2px_0px_0px_#000]' : 'bg-[#FFE3E3] text-rose-950 shadow-[2px_2px_0px_0px_#000]' }}">
                {{ $transaction->type_label }}
            </span>
        </div>

        <!-- Header -->
        <div class="border-b-3 border-black pb-4 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🏦</span>
                <div>
                    <h1 class="font-black text-base uppercase text-black leading-tight">BUKTI TRANSAKSI TABUNGAN</h1>
                    <p class="text-[11px] font-mono text-slate-600">SISTEM KAS TABUNGAN SISWA</p>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="space-y-3 text-xs mb-5">
            <div class="flex items-center justify-between border-b border-dashed border-slate-300 pb-2">
                <span class="text-slate-500 font-bold">No. Transaksi</span>
                <span class="font-mono font-black text-black">{{ $transaction->transaction_code }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-300 pb-2">
                <span class="text-slate-500 font-bold">Tanggal & Jam</span>
                <span class="font-mono text-slate-800">{{ $transaction->created_at->format('d/m/Y H:i:s') }} WIB</span>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-300 pb-2">
                <span class="text-slate-500 font-bold">No. Rekening</span>
                <span class="font-mono font-bold text-black">{{ $transaction->savingsAccount?->account_number }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-300 pb-2">
                <span class="text-slate-500 font-bold">Nama Siswa</span>
                <span class="font-black text-black text-sm">{{ $transaction->savingsAccount?->student?->user?->name ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-dashed border-slate-300 pb-2">
                <span class="text-slate-500 font-bold">Kelas / NISN</span>
                <span class="font-bold text-slate-800">{{ $transaction->savingsAccount?->student?->schoolClass?->name ?? '-' }} ({{ $transaction->savingsAccount?->student?->nisn ?: $transaction->savingsAccount?->student?->nis }})</span>
            </div>
        </div>

        <!-- Amount Box -->
        <div class="bg-slate-50 border-2 border-black p-4 mb-4 text-center space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-600 tracking-wider">
                Jumlah {{ $transaction->type_label }}
            </div>
            <div class="font-mono font-black text-2xl text-black">
                {{ $transaction->formatted_amount }}
            </div>
            @if($transaction->description)
                <div class="text-[11px] text-slate-600 italic mt-1">
                    "{{ $transaction->description }}"
                </div>
            @endif
        </div>

        <!-- Saldo Summary -->
        <div class="bg-yellow-50 border-2 border-black p-3 mb-6 space-y-1.5 text-xs font-mono">
            <div class="flex items-center justify-between text-slate-600">
                <span>Saldo Sebelumnya:</span>
                <span>Rp {{ number_format((float) $transaction->balance_before, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between font-black text-black border-t border-black pt-1">
                <span>Saldo Sesudah Transaksi:</span>
                <span>{{ $transaction->formatted_balance_after }}</span>
            </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-4 text-center text-xs pt-2">
            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-600 uppercase">Siswa / Penyetor</div>
                <div class="border-b border-black font-bold text-black mx-2 pb-1">
                    {{ $transaction->savingsAccount?->student?->user?->name ?? 'Siswa' }}
                </div>
            </div>
            <div class="space-y-12">
                <div class="text-[11px] font-bold text-slate-600 uppercase">Petugas Tabungan</div>
                <div class="border-b border-black font-bold text-black mx-2 pb-1">
                    {{ $transaction->handler?->name ?? 'Pengelola Tabungan' }}
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-[10px] text-slate-400 font-mono">
            Simpan kuitansi ini sebagai bukti transaksi tabungan yang sah.
        </div>
    </div>

</body>
</html>
