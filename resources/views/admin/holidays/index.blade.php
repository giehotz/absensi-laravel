@extends('layouts.admin')

@section('title', 'Kalender & Hari Libur')
@section('page-title', 'Kalender & Hari Libur')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📅</span> Kalender & Hari Libur Nasional
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Data hari libur resmi berdasarkan SKB 3 Menteri via API Kemendesa / upset.dev dan hari libur khusus internal sekolah.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Filter Tahun -->
            <form method="GET" action="{{ route('admin.holidays.index') }}" class="flex items-center gap-2">
                <label for="yearSelect" class="text-xs font-black uppercase font-heading text-slate-700">Tahun:</label>
                <select id="yearSelect" name="year" onchange="this.form.submit()" class="neo-input text-xs font-black py-2 px-3 bg-[#FFF9DB]">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </form>

            <!-- Tombol Buka Modal Sinkronisasi Pintar -->
            <button type="button" onclick="openModal('syncHolidayModal')" class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading font-black shadow-[2px_2px_0px_0px_#000]">
                <span>🔄</span> Sinkron API
            </button>

            <!-- Tombol Tambah Libur Khusus -->
            <button onclick="openModal('createHolidayModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading font-black shadow-[2px_2px_0px_0px_#000]">
                <span>+</span> Tambah Libur Sekolah
            </button>
        </div>
    </div>

    <!-- Alert Success / Error Feedback -->
    @if(session('success'))
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-none shadow-[3px_3px_0px_0px_#000] flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-black text-green-950">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="font-black text-xs text-green-900 hover:text-black">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-[#FFE3E3] border-2 border-black p-4 rounded-none shadow-[3px_3px_0px_0px_#000] flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-black text-red-950">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="font-black text-xs text-red-900 hover:text-black">✕</button>
        </div>
    @endif

    <!-- Statistic Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-slate-500 font-heading">Total Libur ({{ $selectedYear }})</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $stats['total'] }} Hari</div>
            <div class="text-[10px] text-slate-600 font-bold mt-1">{{ $stats['active_holidays'] }} hari berstatus aktif</div>
        </div>
        <div class="bg-[#E7F5FF] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-blue-900 font-heading">Libur Nasional Resmi</div>
            <div class="font-heading font-black text-2xl text-blue-950 mt-1">{{ $stats['national'] }} Hari</div>
            <div class="text-[10px] text-blue-800 font-bold mt-1">Berdasarkan SKB 3 Menteri</div>
        </div>
        <div class="bg-[#FFF4E6] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-amber-900 font-heading">Cuti Bersama</div>
            <div class="font-heading font-black text-2xl text-amber-950 mt-1">{{ $stats['cuti_bersama'] }} Hari</div>
            <div class="text-[10px] text-amber-800 font-bold mt-1">Dapat di-toggle per tanggal</div>
        </div>
        <div class="bg-[#F3F0FF] neo-box p-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-black uppercase text-purple-900 font-heading">Libur Khusus Sekolah</div>
            <div class="font-heading font-black text-2xl text-purple-950 mt-1">{{ $stats['school_specific'] }} Hari</div>
            <div class="text-[10px] text-purple-800 font-bold mt-1">Ditambahkan oleh pihak sekolah</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider font-heading">
                    <tr>
                        <th class="p-3.5 border-r border-black w-12 text-center">No</th>
                        <th class="p-3.5 border-r border-black w-40 text-center">Tanggal & Hari</th>
                        <th class="p-3.5 border-r border-black">Nama Hari Libur</th>
                        <th class="p-3.5 border-r border-black text-center w-40">Kategori & Sumber</th>
                        <th class="p-3.5 border-r border-black text-center w-32">Status Libur</th>
                        <th class="p-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($holidays as $index => $holiday)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($holiday->holiday_date);
                        $dayName = $carbonDate->locale('id')->isoFormat('dddd');
                    @endphp
                    <tr class="hover:bg-slate-50 font-medium {{ ! $holiday->is_active ? 'opacity-60 bg-slate-100' : '' }}">
                        <td class="p-3.5 font-bold text-center border-r border-black text-xs">
                            {{ $holidays->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black text-center">
                            <div class="font-mono font-black text-xs text-black">{{ $carbonDate->format('d/m/Y') }}</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase">{{ $dayName }}</div>
                        </td>
                        <td class="p-3.5 font-bold text-black border-r border-black">
                            <div class="text-xs">{{ $holiday->name }}</div>
                            @if($holiday->description)
                                <div class="text-[10px] text-slate-500 font-normal mt-0.5">{{ $holiday->description }}</div>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black text-center">
                            @if($holiday->is_cuti_bersama)
                                <div class="inline-flex flex-col gap-0.5">
                                    <span class="px-2 py-0.5 bg-[#FFF4E6] text-amber-900 border border-black font-black text-[10px] uppercase rounded">
                                        Cuti Bersama
                                    </span>
                                    <span class="text-[9px] text-slate-500 font-mono">
                                        {{ $holiday->source === 'upset_dev' ? 'upset.dev' : ($holiday->source === 'custom_url' ? 'Custom URL' : 'Kemendesa') }}
                                    </span>
                                </div>
                            @elseif($holiday->is_national)
                                <div class="inline-flex flex-col gap-0.5">
                                    <span class="px-2 py-0.5 bg-[#E7F5FF] text-blue-900 border border-black font-black text-[10px] uppercase rounded">
                                        Nasional
                                    </span>
                                    <span class="text-[9px] text-slate-500 font-mono">
                                        {{ $holiday->source === 'upset_dev' ? 'upset.dev' : ($holiday->source === 'custom_url' ? 'Custom URL' : 'Kemendesa') }}
                                    </span>
                                </div>
                            @else
                                <span class="px-2 py-0.5 bg-[#F3F0FF] text-purple-900 border border-black font-black text-[10px] uppercase rounded">
                                    Internal Sekolah
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black text-center">
                            <form method="POST" action="{{ route('admin.holidays.toggle', $holiday) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Klik untuk mengubah status aktif" class="cursor-pointer text-[10px] font-black uppercase px-2.5 py-1 border border-black transition-all {{ $holiday->is_active ? 'bg-[#D3F9D8] text-green-950 shadow-[1px_1px_0px_0px_#000]' : 'bg-[#FFE3E3] text-red-950' }}">
                                    {{ $holiday->is_active ? 'Libur Aktif' : 'Sekolah Masuk' }}
                                </button>
                            </form>
                        </td>
                        <td class="p-3.5 text-center">
                            @if(! $holiday->is_national || $holiday->source === 'manual')
                                <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}" onsubmit="return confirm('Hapus hari libur {{ $holiday->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-900 px-2 py-1 bg-red-50 border border-red-300 rounded hover:bg-red-100">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 font-mono">API Sync</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500 font-bold">
                            <div class="text-3xl mb-2">🏝️</div>
                            <div class="text-sm">Belum ada data hari libur untuk tahun {{ $selectedYear }}.</div>
                            <div class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Sinkron API"</strong> di atas untuk mengambil data resmi SKB 3 Menteri (Mendukung Kemendesa, upset.dev, atau URL kustom).</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($holidays->hasPages())
        <div class="p-4 border-t-2 border-black bg-[#FFF9DB]">
            {{ $holidays->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Sinkronisasi Pintar (Multi-Source / Custom URL) -->
<div id="syncHolidayModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_#000] w-full max-w-lg p-6 relative">
        <button onclick="closeModal('syncHolidayModal')" class="absolute top-4 right-4 font-black text-lg hover:text-red-600">✕</button>
        <h3 class="font-heading font-black text-lg text-black mb-1 flex items-center gap-2">
            <span>🔄</span> Sinkronkan Data Hari Libur
        </h3>
        <p class="text-xs font-semibold text-slate-600 mb-4">
            Pilih penyedia data libur resmi SKB 3 Menteri atau gunakan URL endpoint manual jika penyedia utama sedang down.
        </p>

        <form method="POST" action="{{ route('admin.holidays.sync') }}" class="space-y-4">
            @csrf
            <div>
                <label for="sync_year" class="block text-xs font-black uppercase font-heading text-slate-800 mb-1">Tahun Kalender *</label>
                <input type="number" id="sync_year" name="year" value="{{ $selectedYear }}" min="2020" max="2099" required class="w-full neo-input text-xs font-bold p-2.5 border-2 border-black bg-[#FFF9DB]">
            </div>

            <div>
                <label class="block text-xs font-black uppercase font-heading text-slate-800 mb-2">Pilih Sumber API *</label>
                <div class="space-y-2">
                    <!-- Option 1: Auto Failover (Recommended) -->
                    <label class="flex items-start gap-3 p-3 border-2 border-black bg-[#F8F9FA] hover:bg-[#FFF9DB] cursor-pointer rounded transition-all">
                        <input type="radio" name="source" value="auto" checked onchange="toggleCustomUrlInput(false)" class="mt-0.5 text-black">
                        <div>
                            <div class="text-xs font-black font-heading text-black flex items-center gap-1.5">
                                <span>⚡ Otomatis Failover (Rekomendasi)</span>
                                <span class="text-[9px] bg-[#20C997] text-white px-1.5 py-0.2 border border-black font-black uppercase shadow-[1px_1px_0px_0px_#000]">Failover</span>
                            </div>
                            <div class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                                Mencoba API Kemendesa terlebih dahulu. Jika gagal / tidak merespons, sistem otomatis beralih mengambil dari TanggalMerah (upset.dev).
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: Kemendesa Only -->
                    <label class="flex items-start gap-3 p-2.5 border-2 border-slate-300 hover:border-black cursor-pointer rounded transition-all">
                        <input type="radio" name="source" value="kemendesa" onchange="toggleCustomUrlInput(false)" class="mt-0.5 text-black">
                        <div>
                            <div class="text-xs font-bold text-slate-900">API Kemendesa Resmi</div>
                            <div class="text-[10px] text-slate-500 font-mono">api.kemendesa.link/libur-nasional</div>
                        </div>
                    </label>

                    <!-- Option 3: TanggalMerah upset.dev Only -->
                    <label class="flex items-start gap-3 p-2.5 border-2 border-slate-300 hover:border-black cursor-pointer rounded transition-all">
                        <input type="radio" name="source" value="upset_dev" onchange="toggleCustomUrlInput(false)" class="mt-0.5 text-black">
                        <div>
                            <div class="text-xs font-bold text-slate-900">API TanggalMerah (upset.dev)</div>
                            <div class="text-[10px] text-slate-500 font-mono">tanggalmerah.upset.dev</div>
                        </div>
                    </label>

                    <!-- Option 4: Custom URL -->
                    <label class="flex items-start gap-3 p-2.5 border-2 border-slate-300 hover:border-black cursor-pointer rounded transition-all">
                        <input type="radio" name="source" value="custom" onchange="toggleCustomUrlInput(true)" class="mt-0.5 text-black">
                        <div>
                            <div class="text-xs font-bold text-slate-900">Input URL Endpoint Kustom (Manual)</div>
                            <div class="text-[10px] text-slate-500">Gunakan jika Anda memiliki URL alternatif atau mirror lain</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Custom URL Input Field (Hidden by default) -->
            <div id="customUrlContainer" class="hidden space-y-1 p-3 bg-[#E7F5FF] border-2 border-black rounded">
                <label for="custom_url" class="block text-xs font-black uppercase font-heading text-blue-950">URL Endpoint Kustom *</label>
                <input type="url" id="custom_url" name="custom_url" placeholder="Contoh: https://tanggalmerah.upset.dev/api/holidays?year=2026" class="w-full neo-input text-xs font-bold p-2.5 border-2 border-black bg-white">
                <p class="text-[10px] text-blue-900 leading-normal">
                    * Catatan: Anda dapat menggunakan placeholder <code>{year}</code> (misal: <code>https://api.domain.com/holidays/{year}.json</code>) atau parameter query <code>?year=</code>. Format respon JSON otomatis disesuaikan.
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t-2 border-slate-200">
                <button type="button" onclick="closeModal('syncHolidayModal')" class="px-4 py-2 text-xs font-bold border-2 border-black bg-slate-200 hover:bg-slate-300">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white px-5 py-2 text-xs uppercase font-heading font-black shadow-[2px_2px_0px_0px_#000]">
                    Mulai Sinkronisasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Libur Sekolah Manual -->
<div id="createHolidayModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_#000] w-full max-w-md p-6 relative">
        <button onclick="closeModal('createHolidayModal')" class="absolute top-4 right-4 font-black text-lg hover:text-red-600">✕</button>
        <h3 class="font-heading font-black text-lg text-black mb-1 flex items-center gap-2">
            <span>➕</span> Tambah Libur Sekolah
        </h3>
        <p class="text-xs font-semibold text-slate-600 mb-4">
            Masukkan tanggal libur khusus internal sekolah (misal: libur semester, dies natalis, dll).
        </p>

        <form method="POST" action="{{ route('admin.holidays.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="holiday_date" class="block text-xs font-black uppercase font-heading text-slate-800 mb-1">Tanggal Libur *</label>
                <input type="date" id="holiday_date" name="holiday_date" required class="w-full neo-input text-xs font-bold p-2.5 border-2 border-black">
            </div>

            <div>
                <label for="name" class="block text-xs font-black uppercase font-heading text-slate-800 mb-1">Nama Hari Libur / Agenda *</label>
                <input type="text" id="name" name="name" placeholder="Contoh: Libur Tengah Semester Ganjil" required class="w-full neo-input text-xs font-bold p-2.5 border-2 border-black">
            </div>

            <div>
                <label for="description" class="block text-xs font-black uppercase font-heading text-slate-800 mb-1">Keterangan Tambahan (Opsional)</label>
                <textarea id="description" name="description" rows="2" placeholder="Catatan atau instruksi untuk siswa dan guru..." class="w-full neo-input text-xs font-bold p-2.5 border-2 border-black"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_cuti_bersama" name="is_cuti_bersama" value="1" class="w-4 h-4 rounded border-2 border-black text-black">
                <label for="is_cuti_bersama" class="text-xs font-bold text-slate-800 cursor-pointer">Tandai sebagai Cuti Bersama</label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('createHolidayModal')" class="px-4 py-2 text-xs font-bold border-2 border-black bg-slate-200 hover:bg-slate-300">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2 text-xs uppercase font-heading font-black shadow-[2px_2px_0px_0px_#000]">
                    Simpan Libur
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id)?.classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id)?.classList.add('hidden');
    }
    function toggleCustomUrlInput(show) {
        const container = document.getElementById('customUrlContainer');
        const input = document.getElementById('custom_url');
        if (show) {
            container?.classList.remove('hidden');
            input?.setAttribute('required', 'required');
            input?.focus();
        } else {
            container?.classList.add('hidden');
            input?.removeAttribute('required');
        }
    }
</script>
@endsection
