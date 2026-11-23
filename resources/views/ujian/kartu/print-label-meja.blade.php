@php
    $periodeObj = $periode ?? $activePeriode;
    $logoPath = null;
    if (!empty($setting?->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo)) {
        $logoPath = asset('storage/' . $setting->logo);
    }
    $chunkPages = $pesertaList->chunk(10);
    $defaultAddress = $setting->school_address ?? ($pengaturan->alamat_instansi ?? 'Jl. A. Yani No. 01 Tanggamus');
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
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .label-meja-card {
            border: 1.5px solid #000;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            height: 5.3cm;
            page-break-inside: avoid;
            break-inside: avoid;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .label-card-content {
            padding: 8px 10px 4px 10px;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
        }

        .label-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 2px;
        }

        .label-header-left {
            display: flex;
            align-items: center;
            gap: 7px;
            flex: 1;
            min-width: 0;
        }

        .label-logo {
            width: 28px;
            height: 28px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .label-instansi {
            min-width: 0;
        }

        .label-nama-instansi {
            font-size: 8.5pt;
            font-weight: 900;
            line-height: 1.15;
            text-transform: uppercase;
            color: #000;
            letter-spacing: 0.2px;
        }

        .label-alamat-instansi {
            font-size: 6.8pt;
            font-weight: 600;
            color: #374151;
            line-height: 1.15;
            margin-top: 1px;
        }

        .label-badge-meja {
            background: #FFD43B;
            border-radius: 8px;
            padding: 3px 9px;
            text-align: center;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 90px;
        }

        .badge-ruang {
            font-size: 7.2pt;
            font-weight: 800;
            color: #000;
            line-height: 1.15;
            white-space: nowrap;
        }

        .badge-meja {
            font-size: 11pt;
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.15;
            white-space: nowrap;
        }

        .label-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex: 1;
            padding: 2px 0;
        }

        .label-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .label-nomor-peserta {
            font-size: 14pt;
            font-weight: 900;
            color: #000;
            letter-spacing: 0.5px;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .label-nama {
            font-weight: 800;
            text-transform: uppercase;
            color: #000;
            line-height: 1.18;
            word-break: break-word;
            overflow-wrap: break-word;
            text-wrap: balance;
            margin-bottom: 3px;
        }

        .label-nama.text-normal {
            font-size: 10pt;
        }

        .label-nama.text-medium {
            font-size: 9pt;
        }

        .label-nama.text-long {
            font-size: 8.2pt;
        }

        .label-kelas {
            font-size: 7.8pt;
            color: #1f2937;
            font-weight: 700;
            line-height: 1.2;
        }

        .label-qr {
            width: 52px;
            height: 52px;
            border: 1.5px solid #000;
            border-radius: 8px;
            padding: 2px;
            background: #fff;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-qr img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        .label-footer-ribbon {
            background: #005a2c;
            color: #fff;
            height: 22px;
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: auto;
        }

        .ribbon-text-left {
            font-size: 6.8pt;
            font-weight: 800;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: #ffffff;
            white-space: nowrap;
            display: flex;
            align-items: center;
            padding-left: 10px;
        }

        .ribbon-accent-wrapper {
            display: flex;
            align-items: center;
            margin-left: auto;
            margin-right: 10px;
            height: 100%;
        }

        .ribbon-slash {
            width: 14px;
            height: 130%;
            background: #FFD43B;
            transform: skewX(-30deg);
        }

        .ribbon-text-right {
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #ffffff;
            white-space: nowrap;
            display: flex;
            align-items: center;
            padding-right: 10px;
        }
    </style>

    <div class="sheet-label-meja">
        @forelse($chunkPages as $page)
            <div class="label-meja-grid {{ !$loop->last ? 'page-break' : '' }}">
                @foreach($page as $p)
                    @php
                        $nameLen = mb_strlen($p->nama_peserta ?? '');
                        $nameClass = $nameLen > 30 ? 'text-long' : ($nameLen > 22 ? 'text-medium' : 'text-normal');
                        $cleanRoomName = preg_replace('/\s+/', ' ', $p->ruang?->nama_ruang ?? 'Ruang Kelas');
                    @endphp
                    <div class="label-meja-card">
                        <div class="label-card-content">
                            <!-- Header Kartu Meja -->
                            <div class="label-header">
                                <div class="label-header-left">
                                    @if($logoPath)
                                        <img src="{{ $logoPath }}" alt="Logo" class="label-logo">
                                    @endif
                                    <div class="label-instansi">
                                        <div class="label-nama-instansi">{{ $pengaturan->nama_instansi ?? ($setting->school_name ?? 'MIN 2 TANGGAMUS') }}</div>
                                        <div class="label-alamat-instansi">{{ $defaultAddress }}</div>
                                    </div>
                                </div>
                                <div class="label-badge-meja">
                                    <div class="badge-ruang">{{ $cleanRoomName }} •</div>
                                    <div class="badge-meja">MEJA {{ str_pad($p->nomor_kursi ?: 1, 2, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>

                            <!-- Body Kartu Meja -->
                            <div class="label-body">
                                <div class="label-info">
                                    <div class="label-nomor-peserta">{{ $p->nomor_peserta }}</div>
                                    <div class="label-nama {{ $nameClass }}" title="{{ $p->nama_peserta }}">
                                        {{ $p->nama_peserta }}
                                    </div>
                                    <div class="label-kelas">
                                        NISN: {{ $p->nisn ?: '-' }} &bull; {{ $p->nama_kelas ?? ($p->schoolClass?->name ?? 'Kelas') }}
                                    </div>
                                </div>
                                <div class="label-qr">
                                    <img src="{{ $p->qr_data_uri }}" alt="QR">
                                </div>
                            </div>
                        </div>

                        <!-- Footer Ribbon Hijau Bawah -->
                        <div class="label-footer-ribbon">
                            <div class="ribbon-text-left">HARAP TEMPEL DI SUDUT KANAN ATAS MEJA</div>
                            <div class="ribbon-accent-wrapper">
                                <div class="ribbon-slash"></div>
                            </div>
                            <div class="ribbon-text-right">TP {{ $periodeObj->tahun_pelajaran ?? '2026/2027' }}</div>
                        </div>
                    </div>
                @endforeach

                <!-- Isi grid kosong jika jumlah di halaman terakhir < 10 -->
                @for($empty = $page->count(); $empty < 10; $empty++)
                    <div class="label-meja-card" style="border: 1.5px dashed #9ca3af; background: #f9fafb; opacity: 0.4;">
                        <div class="label-card-content">
                            <div class="label-header">
                                <span style="font-size: 8pt; font-weight: bold; color: #4b5563;">(BLANGKO KOSONG)</span>
                            </div>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; font-size: 8.5pt; font-weight: 600; color: #6b7280;">
                                Cadangan Meja
                            </div>
                        </div>
                        <div class="label-footer-ribbon" style="background: #9ca3af;">
                            <div class="ribbon-text-left">CADANGAN MEJA UJIAN</div>
                            <div class="ribbon-text-right">KOSONG</div>
                        </div>
                    </div>
                @endfor
            </div>
        @empty
            <div style="text-align: center; padding: 50px;">
                <h3 class="text-sm font-bold text-slate-700">Tidak ada peserta yang terdaftar untuk dicetak label mejanya.</h3>
            </div>
        @endforelse
    </div>
</x-print-preview-layout>
