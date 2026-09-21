@extends('layouts.guru')

@section('title', 'Buku Mutasi Kas Tabungan Siswa')
@section('page-title', 'Buku Mutasi Kas Tabungan Siswa')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="space-y-0.5">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📜</span> Buku Mutasi & Riwayat Transaksi
            </h2>
            <p class="text-xs text-slate-500 font-semibold">Seluruh rekam jejak setoran dan penarikan tabungan siswa</p>
        </div>
            <a href="{{ route('guru.savings.index') }}" 
               onclick="try { const sc = localStorage.getItem('guru_tabungan_selected_class_{{ auth()->id() }}'); if (sc) { this.href = '{{ route('guru.savings.index') }}?class_id=' + encodeURIComponent(sc); } } catch(e) {}"
               class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2 flex items-center gap-1.5">
                <span>←</span> Form Transaksi
            </a>
            <a href="{{ route('guru.savings.export', request()->query()) }}" class="neo-btn bg-[#20C997] hover:bg-emerald-600 text-white text-xs font-bold px-3.5 py-2 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Sesuai Filter</span>
            </a>
        </div>
    </div>

    <!-- Filter Form Box -->
    <div class="bg-white neo-box p-4 border-3 border-black">
        <form action="{{ route('guru.savings.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Search Keyword -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Pencarian</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NISN / Kode TRX..." 
                    class="w-full neo-input py-1.5 text-xs">
            </div>

            <!-- Transaction Type -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Jenis Transaksi</label>
                <select name="type" class="w-full neo-input py-1.5 text-xs">
                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Setoran Tunai (+)</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai (-)</option>
                </select>
            </div>

            <!-- Class Filter -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Rombel / Kelas</label>
                <select name="class_id" class="w-full neo-input py-1.5 text-xs">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string) $classId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div class="space-y-1">
                <label class="font-bold text-slate-700">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full neo-input py-1.5 text-xs">
            </div>

            <!-- End Date & Buttons -->
            <div class="space-y-1 flex flex-col justify-end">
                <label class="font-bold text-slate-700">Sampai Tanggal</label>
                <div class="flex items-center gap-1.5">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full neo-input py-1.5 text-xs">
                    <button type="submit" class="neo-btn bg-[#FFD43B] text-black font-black px-3 py-1.5 hover:bg-yellow-400" title="Terapkan Filter">
                        Filter
                    </button>
                    @if(!empty($search) || $type !== 'all' || !empty($classId) || !empty($startDate) || !empty($endDate))
                        <a href="{{ route('guru.savings.transactions') }}" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-2.5 py-1.5" title="Reset Filter">
                            ↺
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table of Transactions -->
    <div class="bg-white neo-box p-4 border-3 border-black space-y-4">
        <div class="flex items-center justify-between text-xs font-bold text-slate-600">
            <span>Ditemukan: <strong class="text-black">{{ $transactions->total() }}</strong> catatan transaksi</span>
        </div>

        <div class="overflow-x-auto border-2 border-black">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FFD43B] text-black uppercase font-black border-b-2 border-black">
                    <tr>
                        <th class="p-3 border-r border-black text-center w-12">No</th>
                        <th class="p-3 border-r border-black">Kode Transaksi</th>
                        <th class="p-3 border-r border-black">Tanggal & Waktu</th>
                        <th class="p-3 border-r border-black">Nama Siswa</th>
                        <th class="p-3 border-r border-black text-center">Kelas</th>
                        <th class="p-3 border-r border-black text-center">Jenis</th>
                        <th class="p-3 border-r border-black text-right">Nominal</th>
                        <th class="p-3 border-r border-black text-right">Saldo Sesudah</th>
                        <th class="p-3 border-r border-black">Keterangan</th>
                        <th class="p-3 border-r border-black">Petugas</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @forelse($transactions as $idx => $t)
                        @php
                            $tStudent = $t->savingsAccount?->student;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center font-bold border-r border-black">
                                {{ $transactions->firstItem() + $idx }}
                            </td>
                            <td class="p-3 border-r border-black font-mono font-bold text-black whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $t->transaction_code }}</span>
                                    @if($t->is_corrected)
                                        <span class="neo-badge bg-[#FFE066] text-[#664D03] text-[9px] px-1.5 py-0.2 font-black border border-black inline-flex items-center gap-0.5 cursor-help shadow-[1px_1px_0px_0px_#000]"
                                            title="Telah dikoreksi. Alasan: {{ $t->correction_reason }} (Oleh: {{ $t->corrector?->name }})">
                                            <span>⚠️</span> Dikoreksi
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3 border-r border-black font-mono text-slate-700">
                                {{ $t->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="p-3 border-r border-black">
                                <div class="font-bold text-black">{{ $tStudent?->user?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NISN: {{ $tStudent?->nisn ?: ($tStudent?->nis ?: '-') }}</div>
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                <span class="neo-badge bg-[#E7F5FF] text-blue-950 text-[10px] px-2 py-0.5 font-bold">
                                    {{ $tStudent?->schoolClass?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                @if($t->isDeposit())
                                    <span class="neo-badge bg-[#20C997] text-white text-[10px] px-2 py-0.5 font-bold">SETOR</span>
                                @else
                                    <span class="neo-badge bg-[#FF6B6B] text-white text-[10px] px-2 py-0.5 font-bold">TARIK</span>
                                @endif
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-black {{ $t->isDeposit() ? 'text-emerald-700' : 'text-rose-700' }}">
                                <div>{{ $t->isDeposit() ? '+' : '-' }}{{ $t->formatted_amount }}</div>
                                @if($t->is_corrected)
                                    <div class="text-[10px] text-slate-400 font-mono line-through font-normal" title="Nominal sebelum koreksi">
                                        {{ $t->formatted_original_amount }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 border-r border-black text-right font-mono font-bold text-slate-900">
                                {{ $t->formatted_balance_after }}
                            </td>
                            <td class="p-3 border-r border-black text-slate-600 text-[11px]">
                                <div>{{ $t->description ?: '-' }}</div>
                                @if($t->is_corrected)
                                    <div class="text-[10px] text-amber-900 font-semibold mt-0.5 italic">
                                        "{{ $t->correction_reason }}"
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 border-r border-black text-slate-700 text-xs">
                                {{ $t->handler?->name ?? '-' }}
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('guru.savings.receipt', $t) }}" target="_blank" 
                                        class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1 shadow-[1px_1px_0px_0px_#000]"
                                        title="Cetak Kuitansi Transaksi">
                                        <span>🖨️</span> Slip
                                    </a>
                                    <button type="button"
                                        onclick="openCorrectModal({
                                            code: '{{ $t->transaction_code }}',
                                            student: '{{ addslashes($tStudent?->user?->name ?? 'Siswa') }}',
                                            accountNumber: '{{ $t->savingsAccount?->account_number ?? '-' }}',
                                            type: '{{ $t->type }}',
                                            typeName: '{{ $t->type === 'deposit' ? 'Setoran (+)' : 'Penarikan (-)' }}',
                                            amount: {{ (float) $t->amount }},
                                            currentBalance: {{ (float) ($t->savingsAccount?->balance ?? 0) }},
                                            actionUrl: '{{ route('guru.savings.transactions.correct', $t) }}'
                                        })"
                                        class="neo-btn bg-[#FFE066] hover:bg-yellow-400 text-black text-[10px] font-bold px-2 py-1 inline-flex items-center gap-1 shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                        title="Koreksi nominal transaksi">
                                        <span>✏️</span> Koreksi
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                                <div class="text-3xl">📭</div>
                                <div class="text-sm font-bold text-slate-800">Tidak ada data transaksi yang sesuai filter</div>
                                <p class="text-xs text-slate-500">Coba ubah filter atau rentang tanggal pencarian Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL POPUP: KOREKSI TRANSAKSI TABUNGAN (GURU) -->
<!-- ========================================================================= -->
<div id="modalCorrectTransaction" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-lg w-full p-6 my-8 flex flex-col border-4 border-black shadow-[6px_6px_0px_0px_#000] relative">
        <button type="button" onclick="closeModal('modalCorrectTransaction')" class="absolute right-4 top-4 text-black hover:text-rose-600 font-black text-xl p-1 cursor-pointer">
            ✕
        </button>

        <div class="border-b-2 border-black pb-3 mb-4">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Koreksi Nominal Transaksi
            </h3>
            <p class="text-xs text-slate-600 mt-0.5">
                Perbaikan kesalahan input nominal transaksi tabungan santri/siswa
            </p>
        </div>

        <div class="bg-amber-50 border-2 border-black p-3 text-xs text-amber-950 mb-4 space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <span>⚠️</span> Ketentuan Audit Koreksi:
            </div>
            <p class="text-[11px] text-slate-700 leading-relaxed">
                Perubahan nominal akan otomatis menyesuaikan saldo rekening siswa, meninggalkan tanda audit koreksi di mutasi siswa, dan mencatat nama Anda beserta alasan perbaikan.
            </p>
        </div>

        <form id="correctTransactionForm" method="POST" action="" class="space-y-4">
            @csrf

            <!-- Info Transaksi Terpilih -->
            <div class="bg-slate-100 border-2 border-black p-3 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Kode Transaksi:</span>
                    <span id="correctModalCode" class="font-mono font-bold text-black">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Nama Siswa:</span>
                    <strong id="correctModalStudent" class="text-black">-</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">No. Rekening:</span>
                    <span id="correctModalAccount" class="font-mono text-slate-700">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Jenis Transaksi:</span>
                    <span id="correctModalType" class="font-black">-</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-300 pt-1.5">
                    <span class="text-slate-700 font-bold">Nominal Tercatat Saat Ini:</span>
                    <span id="correctModalCurrentAmount" class="font-mono font-black text-slate-900">Rp 0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-700 font-bold">Saldo Siswa Saat Ini:</span>
                    <span id="correctModalCurrentBalance" class="font-mono font-black text-emerald-800">Rp 0</span>
                </div>
            </div>

            <!-- Input Nominal Baru -->
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Nominal Sebenarnya (Baru) <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 font-mono font-bold text-sm text-black">Rp</span>
                    <input type="number" name="new_amount" id="correctModalNewAmount" min="0" step="500" required
                        placeholder="0"
                        class="w-full neo-input text-base font-mono font-black pl-10 pr-4 py-2 bg-yellow-50/50">
                </div>
                <!-- Quick Amount Buttons -->
                <div class="flex flex-wrap gap-1.5 pt-0.5">
                    <button type="button" onclick="setModalCorrectAmount(1000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">1.000</button>
                    <button type="button" onclick="setModalCorrectAmount(2000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">2.000</button>
                    <button type="button" onclick="setModalCorrectAmount(5000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">5.000</button>
                    <button type="button" onclick="setModalCorrectAmount(10000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">10.000</button>
                    <button type="button" onclick="setModalCorrectAmount(20000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">20.000</button>
                    <button type="button" onclick="setModalCorrectAmount(50000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">50.000</button>
                    <button type="button" onclick="setModalCorrectAmount(100000)" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-[10px] font-mono font-bold px-1.5 py-0.5 cursor-pointer">100.000</button>
                </div>
                
                <!-- Perhitungan Dampak Saldo -->
                <div id="correctModalCalculation" class="bg-blue-50 border border-blue-300 p-2 text-xs space-y-0.5 text-blue-950">
                    <div class="flex justify-between items-center text-[11px]">
                        <span>Selisih Penyesuaian:</span>
                        <span id="correctModalDeltaText" class="font-mono font-bold">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center font-bold">
                        <span>Estimasi Saldo Baru Siswa:</span>
                        <span id="correctModalNewBalanceText" class="font-mono font-black text-blue-900">Rp 0</span>
                    </div>
                </div>

                <div id="correctModalWarningNegative" class="hidden bg-rose-100 border-2 border-rose-600 p-2.5 text-xs text-rose-950 font-bold">
                    🚫 Peringatan: Perubahan ini menyebabkan saldo akhir siswa menjadi minus (&lt; Rp 0). Koreksi nominal tidak dapat diproses!
                </div>
            </div>

            <!-- Input Alasan Koreksi -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-black">
                    Alasan Perubahan / Koreksi <span class="text-rose-600">*</span>
                </label>
                <input type="text" name="reason" id="correctModalReason" required minlength="5" maxlength="255"
                    placeholder="Contoh: Salah ketik kelebihan nol saat melayani setoran santri"
                    class="w-full neo-input text-xs font-medium py-2 bg-white">
                <p class="text-[10px] text-slate-500">Minimal 5 karakter. Alasan ini akan tampil pada buku tabungan digital siswa.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeModal('modalCorrectTransaction')" class="w-1/3 neo-btn bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs py-2.5 cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="correctModalSubmitBtn" class="w-2/3 neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black font-black text-xs py-2.5 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>Simpan Koreksi</span>
                    <span>✓</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeCorrectTx = null;

    function openCorrectModal(data) {
        activeCorrectTx = data;
        
        document.getElementById('correctTransactionForm').action = data.actionUrl;
        document.getElementById('correctModalCode').textContent = data.code;
        document.getElementById('correctModalStudent').textContent = data.student;
        document.getElementById('correctModalAccount').textContent = data.accountNumber;
        document.getElementById('correctModalType').textContent = data.typeName;
        document.getElementById('correctModalCurrentAmount').textContent = 'Rp ' + Number(data.amount).toLocaleString('id-ID');
        document.getElementById('correctModalCurrentBalance').textContent = 'Rp ' + Number(data.currentBalance).toLocaleString('id-ID');
        
        const inputNewAmount = document.getElementById('correctModalNewAmount');
        inputNewAmount.value = data.amount;
        document.getElementById('correctModalReason').value = '';
        
        updateCorrectCalculation();
        openModal('modalCorrectTransaction');
    }

    function updateCorrectCalculation() {
        if (!activeCorrectTx) return;
        
        const newAmount = parseFloat(document.getElementById('correctModalNewAmount').value) || 0;
        const oldAmount = parseFloat(activeCorrectTx.amount) || 0;
        const currentBalance = parseFloat(activeCorrectTx.currentBalance) || 0;
        
        let delta = 0;
        if (activeCorrectTx.type === 'deposit') {
            delta = newAmount - oldAmount;
        } else {
            delta = oldAmount - newAmount;
        }
        
        const newBalance = currentBalance + delta;
        
        const deltaEl = document.getElementById('correctModalDeltaText');
        const newBalanceEl = document.getElementById('correctModalNewBalanceText');
        const warningEl = document.getElementById('correctModalWarningNegative');
        const submitBtn = document.getElementById('correctModalSubmitBtn');
        
        deltaEl.textContent = (delta >= 0 ? '+' : '') + 'Rp ' + Number(delta).toLocaleString('id-ID');
        deltaEl.className = 'font-mono font-bold ' + (delta >= 0 ? 'text-emerald-700' : 'text-rose-700');
        
        newBalanceEl.textContent = 'Rp ' + Number(newBalance).toLocaleString('id-ID');
        
        if (newBalance < 0) {
            warningEl.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warningEl.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function setModalCorrectAmount(val) {
        document.getElementById('correctModalNewAmount').value = val;
        updateCorrectCalculation();
    }

    document.getElementById('correctModalNewAmount')?.addEventListener('input', updateCorrectCalculation);
</script>
@endpush
