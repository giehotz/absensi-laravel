@php
    $periodeObj = $periode ?? $activePeriode;
    $logoPath = null;
    if (!empty($setting?->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo)) {
        $logoPath = asset('storage/' . $setting->logo);
    }
    $chunkPages = $pesertaList->chunk(10);
@endphp

<x-print-preview-layout 
    :title="'Cetak Label Meja Siswa (Grid 2x5) - ' . ($periodeObj->nama_periode ?? 'Madrasah')"
    orientation="portrait"
    paperSize="A4"
    :backUrl="route('ujian.kartu.index')"
    pageMargin="0.5cm"
    sheetClass="p-2 sm:p-4"
>
    <x-slot:actions>
        <span class="text-xs font-bold font-mono bg-slate-100 border border-black px-2 py-1 rounded">
            Total: <strong>{{ $pesertaList->count() }}</strong> Peserta ({{ $chunkPages->count() }} Lembar)
        </span>
    </x-slot:actions>

    <style>
        .label-meja-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.35cm;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        .label-meja-card {
            border: 2px dashed #000;
            border-radius: 6px;
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            position: relative;
            overflow: hidden;
            min-height: 5.2cm;
        }
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .label-header-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .label-logo {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }
        .label-instansi {
            font-size: 7.5pt;
            font-weight: bold;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .label-ruang-badge {
            background: #FFD43B;
            border: 1px solid #000;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: 900;
            letter-spacing: 0.3px;
        }
        .label-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
        }
        .label-info {
            flex: 1;
            padding-right: 6px;
        }
        .label-nomor-peserta {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13pt;
            font-weight: 900;
            color: #000;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .label-nama {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 195px;
            margin-bottom: 1px;
        }
        .label-kelas {
            font-size: 8pt;
            color: #333;
            font-weight: 600;
        }
        .label-qr {
            width: 44px;
            height: 44px;
            border: 1px solid #000;
            padding: 1px;
            background: white;
            flex-shrink: 0;
        }
        .label-qr img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .label-footer {
            border-top: 1px solid #ccc;
            padding-top: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 6.5pt;
            color: #555;
            text-transform: uppercase;
        }
    </style>

    <div class="sheet-label-meja">
        @forelse($chunkPages as $page)
            <div class="label-meja-grid {{ !$loop->last ? 'page-break' : '' }}">
                @foreach($page as $p)
                    <div class="label-meja-card">
                        <!-- Header Kartu Meja -->
                        <div class="label-header">
                            <div class="label-header-left">
                                @if($logoPath)
                                    <img src="{{ $logoPath }}" alt="Logo" class="label-logo">
                                @endif
                                <div class="label-instansi">
                                    <div>{{ $pengaturan->nama_instansi ?? ($setting->school_name ?? 'MIN 2 TANGGAMUS') }}</div>
                                    <div style="font-size: 6pt; color: #555;">{{ $periodeObj->nama_periode ?? 'ASESMEN SUMATIF' }}</div>
                                </div>
                            </div>
                            <div class="label-ruang-badge">
                                {{ $p->ruang?->nama_ruang ?? 'RUANG' }} • MEJA {{ str_pad($p->nomor_kursi ?: 1, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>

                        <!-- Body Kartu Meja -->
                        <div class="label-body">
                            <div class="label-info">
                                <div class="label-nomor-peserta">{{ $p->nomor_peserta }}</div>
                                <div class="label-nama" title="{{ $p->nama_peserta }}">{{ $p->nama_peserta }}</div>
                                <div class="label-kelas">
                                    NISN: {{ $p->nisn ?: '-' }} &bull; {{ $p->nama_kelas ?? ($p->schoolClass?->name ?? 'Kelas') }}
                                </div>
                            </div>
                            <div class="label-qr">
                                <img src="{{ $p->qr_data_uri }}" alt="QR">
                            </div>
                        </div>

                        <!-- Footer Kartu Meja -->
                        <div class="label-footer">
                            <span>Harap tempel di sudut kanan atas meja</span>
                            <span>TP {{ $periodeObj->tahun_pelajaran ?? '-' }}</span>
                        </div>
                    </div>
                @endforeach

                <!-- Isi grid kosong jika jumlah di halaman terakhir < 10 -->
                @for($empty = $page->count(); $empty < 10; $empty++)
                    <div class="label-meja-card" style="border-style: dotted; opacity: 0.25;">
                        <div class="label-header">
                            <span style="font-size: 8pt; font-weight: bold;">(BLANGKO KOSONG)</span>
                        </div>
                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #777;">
                            Cadangan Meja
                        </div>
                    </div>
                @endfor
            </div>
        @empty
            <div style="text-align: center; padding: 50px;">
                <h3>Tidak ada peserta yang terdaftar untuk dicetak label mejanya.</h3>
            </div>
        @endforelse
    </div>
</x-print-preview-layout>