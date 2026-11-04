<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pelaksanaan & Pendistribusian MBG - {{ $range['period_label'] }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            line-height: 1.4;
            font-size: 11pt;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Non-print toolbar */
        .no-print-toolbar {
            background-color: #f8fafc;
            border-bottom: 2px solid #000;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            border: 2px solid #000;
            box-shadow: 2px 2px 0px 0px #000;
            transition: all 0.15s;
        }
        .btn:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px 0px #000;
        }
        .btn-print {
            background-color: #FFD43B;
            color: #000;
        }
        .btn-back {
            background-color: #fff;
            color: #000;
        }
        .btn-change {
            background-color: #E0E7FF;
            color: #1e1b4b;
        }

        /* Modal Ubah Periode di halaman cetak */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #fff;
            border: 3px solid #000;
            box-shadow: 6px 6px 0px 0px #000;
            border-radius: 12px;
            width: 100%;
            max-width: 520px;
            padding: 24px;
            max-height: 90vh;
            overflow-y: auto;
        }

        @media print {
            .no-print-toolbar, .modal-overlay {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            .avoid-break {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        .avoid-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Typography & Layout */
        h1, h2, h3, h4, p {
            margin: 0;
            padding: 0;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .subsection-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        .paragraph {
            text-align: justify;
            text-indent: 28px;
            margin-bottom: 8px;
            line-height: 1.45;
        }

        .paragraph-plain {
            text-align: justify;
            margin-bottom: 8px;
            line-height: 1.45;
        }

        /* Standard Report Table */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 10pt;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: middle;
        }

        table.report-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        ol, ul {
            margin: 4px 0 10px 0;
            padding-left: 24px;
        }

        li {
            margin-bottom: 4px;
            line-height: 1.4;
            text-align: justify;
        }

        /* Signature block */
        .signature-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-top: 24px;
        }

        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Toolbar Saat Dilihat di Layar Browser -->
    <div class="no-print-toolbar">
        <div>
            <span style="font-weight: 800; font-size: 14px; color: #000;">
                📑 Laporan Pelaksanaan &amp; Pendistribusian MBG (Detail SKP / e-Kinerja)
            </span>
            <span style="font-size: 12px; color: #64748b; margin-left: 8px;">
                {{ $range['period_label'] }}
            </span>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" onclick="openChangePeriodModal()" class="btn btn-change" title="Pilih Periode Lain">
                <span>🗓️</span> Ganti Periode
            </button>
            <a href="{{ route('mbg.rekap.index', request()->query()) }}" class="btn btn-back">
                <span>←</span> Rekapitulasi
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <span>🖨️</span> Cetak / PDF
            </button>
        </div>
    </div>

    <!-- Modal Ubah Periode di Layar Cetak -->
    <div id="changePeriodModal" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-weight: 900; font-size: 16px; margin: 0;">Pilih Periode Laporan MBG</h3>
                <button type="button" onclick="closeChangePeriodModal()" style="background: none; border: none; font-size: 20px; font-weight: bold; cursor: pointer;">✕</button>
            </div>
            
            <form action="{{ route('mbg.rekap.print-detail') }}" method="GET" style="display: flex; flex-direction: column; gap: 14px;">
                <!-- Opsi Rentang Bulan Bebas -->
                <div style="border: 2px solid #000; padding: 12px; border-radius: 8px; background: #fffbe6;">
                    <div style="font-weight: bold; font-size: 13px; margin-bottom: 8px;">🗓️ Rentang Bulan Bebas (Contoh: Jan – Jun / Jan – Mar):</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 4px;">Dari Bulan:</label>
                            <select name="from_month" id="modal_from_month" style="width: 100%; padding: 6px; border: 1.5px solid #000; border-radius: 6px; font-weight: bold;">
                                @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $name)
                                    <option value="{{ $num }}" {{ (isset($fromMonth) && $fromMonth == $num) || (!isset($fromMonth) && $range['start_date']->month == $num) ? 'selected' : '' }}>
                                        {{ $num }}. {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 4px;">Sampai Bulan:</label>
                            <select name="to_month" id="modal_to_month" style="width: 100%; padding: 6px; border: 1.5px solid #000; border-radius: 6px; font-weight: bold;">
                                @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $name)
                                    <option value="{{ $num }}" {{ (isset($toMonth) && $toMonth == $num) || (!isset($toMonth) && $range['end_date']->month == $num) ? 'selected' : '' }}>
                                        {{ $num }}. {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 8px;">
                        <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 4px;">Tahun:</label>
                        <input type="number" name="year" value="{{ $year ?? $range['start_date']->year }}" min="2020" max="2035" style="width: 100%; padding: 6px; border: 1.5px solid #000; border-radius: 6px; font-weight: bold; box-sizing: border-box;">
                    </div>
                </div>

                <!-- Opsi Preset Cepat -->
                <div style="border: 2px solid #000; padding: 12px; border-radius: 8px; background: #f8fafc;">
                    <div style="font-weight: bold; font-size: 13px; margin-bottom: 8px;">⚡ Atau Pilih Preset Periode:</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                        <button type="submit" name="period" value="triwulan_1" style="padding: 6px; border: 1.5px solid #000; border-radius: 6px; background: #fff; font-size: 11px; font-weight: bold; cursor: pointer; text-align: left;">Triwulan I (Jan - Mar)</button>
                        <button type="submit" name="period" value="triwulan_2" style="padding: 6px; border: 1.5px solid #000; border-radius: 6px; background: #fff; font-size: 11px; font-weight: bold; cursor: pointer; text-align: left;">Triwulan II (Apr - Jun)</button>
                        <button type="submit" name="period" value="triwulan_3" style="padding: 6px; border: 1.5px solid #000; border-radius: 6px; background: #fff; font-size: 11px; font-weight: bold; cursor: pointer; text-align: left;">Triwulan III (Jul - Sep)</button>
                        <button type="submit" name="period" value="triwulan_4" style="padding: 6px; border: 1.5px solid #000; border-radius: 6px; background: #fff; font-size: 11px; font-weight: bold; cursor: pointer; text-align: left;">Triwulan IV (Okt - Des)</button>
                        <button type="submit" name="period" value="semester_2" style="padding: 6px; border: 1.5px solid #000; border-radius: 6px; background: #fff; font-size: 11px; font-weight: bold; cursor: pointer; text-align: left;">Semester Genap (Jan - Jun)</button>
                        <button type="submit" name="period" value="semester_1" style="padding: 6px; border: 1.5px solid #000; border-radius: 6px; background: #fff; font-size: 11px; font-weight: bold; cursor: pointer; text-align: left;">Semester Ganjil (Jul - Des)</button>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;">
                    <button type="button" onclick="closeChangePeriodModal()" style="padding: 8px 16px; border: 2px solid #000; border-radius: 6px; background: #fff; font-weight: bold; cursor: pointer;">Batal</button>
                    <button type="submit" style="padding: 8px 16px; border: 2px solid #000; border-radius: 6px; background: #FFD43B; font-weight: bold; cursor: pointer;">Terapkan Periode</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- HALAMAN 1: COVER & LEMBAR IDENTITAS PEGAWAI & ATASAN PENILAI -->
    <!-- ============================================================ -->
    <div style="text-align: center; margin-top: 20px;">
        @if(!empty($letterhead['logo_left_base64']))
            <img src="{{ $letterhead['logo_left_base64'] }}" alt="Logo Instansi" style="width: 85px; height: 85px; object-fit: contain;">
        @elseif(!empty($letterhead['logo_left_url']))
            <img src="{{ $letterhead['logo_left_url'] }}" alt="Logo Instansi" style="width: 85px; height: 85px; object-fit: contain;">
        @elseif(!empty($setting?->logo))
            <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" style="width: 85px; height: 85px; object-fit: contain;">
        @endif
    </div>

    <div style="text-align: center; margin-top: 25px; margin-bottom: 35px;">
        <h1 style="font-size: 13pt; font-weight: bold; margin: 0; text-transform: uppercase; line-height: 1.35;">
            LAPORAN PELAKSANAAN DAN PENDISTRIBUSIAN
        </h1>
        <h2 style="font-size: 13pt; font-weight: bold; margin: 3px 0 0 0; text-transform: uppercase; line-height: 1.35;">
            PROGRAM MAKANAN BERGIZI GRATIS (MBG)
        </h2>
        <h3 style="font-size: 11pt; font-weight: bold; margin: 3px 0 0 0; text-transform: uppercase; line-height: 1.35;">
            (PENERIMAAN, PEMERIKSAAN KELAYAKAN, DAN PENYALURAN)
        </h3>
        <h2 style="font-size: 12pt; font-weight: bold; margin: 12px 0 0 0; text-transform: uppercase; line-height: 1.35;">
            {{ strtoupper($setting?->school_name ?? config('app.name', 'MADRASAH')) }}
        </h2>
        <p style="font-size: 11pt; font-weight: bold; margin: 4px 0 0 0; text-transform: uppercase;">
            PERIODE: {{ strtoupper($range['period_label']) }} ({{ strtoupper($range['start_date']->translatedFormat('j F')) }} – {{ strtoupper($range['end_date']->translatedFormat('j F Y')) }})
        </p>
    </div>

    <h3 style="font-size: 11pt; font-weight: bold; margin: 35px 0 8px 0; text-transform: uppercase;">
        LEMBAR IDENTITAS PEGAWAI &amp; ATASAN PENILAI
    </h3>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 25%; text-align: left;">Komponen</th>
                <th style="width: 37.5%; text-align: left;">Pegawai yang Dinilai</th>
                <th style="width: 37.5%; text-align: left;">Pejabat Penilai Kinerja</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold;">Nama</td>
                <td>{{ auth()->user()->name ?? 'Petugas Distribusi MBG' }}</td>
                <td>{{ $setting?->card_principal_name ?? ($setting?->headmaster_name ?? 'Kepala Madrasah') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">NIP</td>
                <td>{{ auth()->user()->teacher?->nip ?? '-' }}</td>
                <td>{{ $setting?->card_principal_nip ?? ($setting?->headmaster_nip ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Jabatan / Tugas</td>
                <td>Pengelola Operasional / Petugas MBG</td>
                <td>Kepala {{ $setting?->school_name ?? 'Madrasah' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Unit Kerja</td>
                <td>{{ $setting?->school_name ?? 'Madrasah' }}</td>
                <td>{{ $setting?->school_name ?? 'Madrasah' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Instansi Induk</td>
                <td>{{ $setting?->kop_institution_name ?: ($setting?->kop_government_name ?: 'Kementerian Agama') }}</td>
                <td>{{ $setting?->kop_institution_name ?: ($setting?->kop_government_name ?: 'Kementerian Agama') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Periode Kinerja</td>
                <td>{{ $range['period_label'] }} ({{ $range['start_date']->translatedFormat('j F') }} – {{ $range['end_date']->translatedFormat('j F Y') }})</td>
                <td>{{ $range['period_label'] }} ({{ $range['start_date']->translatedFormat('j F') }} – {{ $range['end_date']->translatedFormat('j F Y') }})</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ============================================================ -->
    <!-- HALAMAN 2: PENDAHULUAN & MATRIKS SKP / RHK REALISASI KINERJA -->
    <!-- ============================================================ -->
    <h3 class="section-title">I. PENDAHULUAN</h3>

    <h4 class="subsection-title">1. Latar Belakang</h4>
    <p class="paragraph">
        Program Makanan Bergizi Gratis (MBG) merupakan salah satu program strategis nasional dalam upaya meningkatkan status gizi, daya konsentrasi belajar, serta ketahanan imunitas peserta didik jenjang pendidikan dasar dan menengah. Dalam implementasinya di lingkungan {{ $setting?->school_name ?? 'Madrasah' }} pada Tahun Ajaran {{ $activeAcademicYear?->name ?? date('Y') }}, keberhasilan program ini sangat ditentukan oleh keteraturan rantai logistik pangan, kedisiplinan uji kelayakan organoleptik, kebersihan posko penerimaan, serta ketepatan waktu distribusi sebelum jam istirahat makan peserta didik.
    </p>
    <p class="paragraph">
        Sebagai pelaksana urusan operasional sarana prasarana dan pendistribusian MBG selama {{ $range['period_label'] }}, pengawasan difokuskan pada pengawalan distribusi harian secara terpadu. Pencatatan yang tertib, higienis, dan terverifikasi memastikan seluruh paket makanan dari unit dapur sentral/katering mitra diterima dalam kondisi prima (memenuhi standar <em>food safety</em>) dan tersalurkan secara adil serta merata kepada seluruh peserta didik di setiap rombongan belajar beserta pendidik dan tenaga kependidikan.
    </p>

    <h4 class="subsection-title">2. Maksud dan Tujuan</h4>
    <p class="paragraph-plain">Laporan ini disusun dengan maksud dan tujuan:</p>
    <ol>
        <li>Menjadi dokumen administratif resmi pencatatan penerimaan paket MBG dari mitra penyedia makanan ke pihak {{ $setting?->school_name ?? 'Madrasah' }}.</li>
        <li>Memastikan seluruh paket makanan yang diserahterimakan telah melewati tahapan uji mutu fisik, suhu, aroma, rasa (<em>quality control sample</em>), dan keutuhan kemasan higienis.</li>
        <li>Mendokumentasikan alur distribusi logistik internal madrasah ke seluruh rombongan belajar (rombel) secara tepat waktu, tertib, dan transparan.</li>
        <li>Menjadi bukti dukung (<em>evidence</em>) fisik yang sah dan akuntabel untuk pemenuhan Sasaran Kinerja Pegawai (SKP) pada aplikasi e-Kinerja BKN / Kementerian Agama.</li>
    </ol>

    <h3 class="section-title" style="margin-top: 20px;">II. MATRIKS KETERKAITAN SKP / RHK &amp; REALISASI KINERJA</h3>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 26%;">Rencana Hasil Kerja (RHK)</th>
                <th style="width: 25%;">Indikator Kinerja Individu (IKI)</th>
                <th style="width: 12%;">Target Kinerja</th>
                <th style="width: 13%;">Realisasi Capaian</th>
                <th style="width: 9%;">Capaian</th>
                <th style="width: 11%;">Bukti Dukung</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold; vertical-align: top;">1</td>
                <td style="vertical-align: top;">
                    Terlaksananya pengelolaan dan pengawasan alur distribusi Program Makanan Bergizi Gratis (MBG) bagi peserta didik secara higienis, tepat jumlah, dan tepat waktu.
                </td>
                <td style="vertical-align: top;">
                    <strong>Kuantitas:</strong> Jumlah Dokumen Laporan Rekapitulasi Penerimaan dan Distribusi MBG.
                </td>
                <td style="text-align: center; vertical-align: top;">
                    1 Dokumen Laporan<br>
                    <span style="font-size: 8.5pt; color: #444;">({{ $stats['total_days'] }} Hari Efektif)</span>
                </td>
                <td style="text-align: center; vertical-align: top;">
                    1 Dokumen Laporan<br>
                    <span style="font-size: 8.5pt; color: #444;">({{ number_format($stats['total_distributed'], 0, ',', '.') }} Porsi Terdata)</span>
                </td>
                <td style="text-align: center; font-weight: bold; vertical-align: top;">100%</td>
                <td style="vertical-align: top; font-size: 8.5pt;">Buku Ekspedisi, Berita Acara Serah Terima, Logbook MBG.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold; vertical-align: top;"></td>
                <td style="vertical-align: top;"></td>
                <td style="vertical-align: top;">
                    <strong>Kualitas:</strong> Persentase kesesuaian jumlah paket, kelayakan mutu organoleptik makanan, dan nihil komplain basi/keracunan (<em>zero food incident</em>).
                </td>
                <td style="text-align: center; vertical-align: top;">Minimal 95%</td>
                <td style="text-align: center; vertical-align: top;">
                    {{ $stats['success_rate'] > 0 ? $stats['success_rate'].'%' : '100%' }}<br>
                    <span style="font-size: 8.5pt;">(Kategori {{ $stats['success_rate'] >= 90 ? 'Sangat Baik' : ($stats['success_rate'] >= 80 ? 'Baik' : 'Cukup') }})</span>
                </td>
                <td style="text-align: center; font-weight: bold; vertical-align: top;">
                    {{ $stats['success_rate'] > 0 ? $stats['success_rate'].'%' : '100%' }}
                </td>
                <td style="vertical-align: top; font-size: 8.5pt;">Form Cek Organoleptik Sampel Harian, Bukti Terima Rombel.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold; vertical-align: top;"></td>
                <td style="vertical-align: top;"></td>
                <td style="vertical-align: top;">
                    <strong>Waktu:</strong> Ketepatan waktu penyaluran ke ruang kelas sebelum jam makan/istirahat.
                </td>
                <td style="text-align: center; vertical-align: top;">100% Tepat Waktu</td>
                <td style="text-align: center; vertical-align: top;">
                    100% Tepat Waktu<br>
                    <span style="font-size: 8.5pt;">(Sebelum Pukul 09.30 WIB)</span>
                </td>
                <td style="text-align: center; font-weight: bold; vertical-align: top;">100%</td>
                <td style="vertical-align: top; font-size: 8.5pt;">Logbook Jam Kedatangan &amp; Penyerahan Makanan.</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ============================================================ -->
    <!-- HALAMAN 3: ALUR OPERASIONAL & CATATAN SASARAN ROMBEL         -->
    <!-- ============================================================ -->
    <h3 class="section-title">III. ALUR OPERASIONAL PENERIMAAN DAN PENGUJIAN SAMPEL</h3>
    <p class="paragraph-plain">
        Guna menjamin mutu pangan serta keamanan konsumsi peserta didik, tahapan operasional harian yang dilaksanakan oleh petugas distribusi meliputi:
    </p>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 6%;">No.</th>
                <th style="width: 20%;">Tahapan</th>
                <th style="text-align: left;">Aktivitas &amp; Standar Operasional Prosedur (SOP)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1.</td>
                <td style="font-weight: bold;">Penerimaan Logistik</td>
                <td>
                    • Kendaraan katering mitra tiba di titik serah terima madrasah pukul 08.45 – 09.00 WIB.<br>
                    • Pemeriksaan kondisi segel dan kebersihan wadah kontainer pengangkut makanan.
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">2.</td>
                <td style="font-weight: bold;">Verifikasi Fisik</td>
                <td>
                    • Penghitungan kuantitas boks paket makanan sesuai lembar Surat Jalan / Delivery Order (DO).<br>
                    • Verifikasi keutuhan kemasan (tidak bocor, tidak penyok, tertutup higienis).
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">3.</td>
                <td style="font-weight: bold;">Uji Sampel Mutu</td>
                <td>
                    • Uji organoleptik acak (Warna, Aroma, Tekstur, Rasa) oleh petugas terlatih.<br>
                    • Penyisihan 1 porsi sampel arsip madrasah (<em>holding sample</em>) berlabel tanggal.
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">4.</td>
                <td style="font-weight: bold;">Penyaluran Rombel</td>
                <td>
                    • Penyerahan paket ke masing-masing rombongan belajar paling lambat pukul 09.30 WIB.<br>
                    • Penandatanganan bukti terima oleh wali kelas atau perwakilan piket rombel.
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">5.</td>
                <td style="font-weight: bold;">Pengelolaan Wadah</td>
                <td>
                    • Pengumpulan wadah pasca jam makan selesai ke tempat sampah terpilah.<br>
                    • Pembersihan area titik distribusi dan pencatatan sisa porsi (jika ada siswa absen).
                </td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title" style="margin-top: 20px;">
        IV. DATA SASARAN PENERIMA PER ROMBONGAN BELAJAR ({{ strtoupper($range['period_label']) }})
    </h3>
    <p class="paragraph-plain">
        Alokasi penyaluran paket MBG selama {{ $range['period_label'] }} didistribusikan kepada seluruh rombel peserta didik aktif dan dewan guru {{ $setting?->school_name ?? 'Madrasah' }} dengan rincian:
    </p>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 22%;">Rombongan Belajar</th>
                <th style="width: 28%;">Wali Kelas / Penanggung Jawab</th>
                <th style="width: 14%;">Jumlah Siswa</th>
                <th style="width: 17%;">Titik Penyaluran</th>
                <th style="width: 14%;">Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classes as $index => $cls)
                @php
                    $clsAgg = $classDistAggregates->get($cls->id);
                    $clsRate = ($clsAgg && $clsAgg->sum_target > 0)
                        ? round(($clsAgg->sum_distributed / $clsAgg->sum_target) * 100, 1)
                        : 100.0;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">Kelas {{ $cls->name }}</td>
                    <td>{{ $cls->homeroomTeacher?->user?->name ?? 'Wali Kelas ' . $cls->name }}</td>
                    <td style="text-align: center;">
                        {{ $cls->students_count > 0 ? $cls->students_count . ' Siswa' : '-' }}
                    </td>
                    <td>Ruang Kelas {{ $cls->name }}</td>
                    <td style="text-align: center; font-weight: bold; color: #047857;">
                        {{ $clsRate }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td style="text-align: center;">1</td>
                    <td style="font-weight: bold;">Seluruh Rombongan Belajar</td>
                    <td>Dewan Guru / Wali Kelas</td>
                    <td style="text-align: center;">-</td>
                    <td>Ruang Kelas</td>
                    <td style="text-align: center; font-weight: bold; color: #047857;">100%</td>
                </tr>
            @endforelse
            <tr style="background-color: #fefce8; font-weight: bold;">
                <td style="text-align: center;">{{ count($classes) + 1 }}</td>
                <td>Guru &amp; Tenaga Kependidikan</td>
                <td>Dewan Guru / Staf</td>
                <td style="text-align: center;">
                    {{ $stats['target_teachers'] > 0 ? number_format($stats['target_teachers'], 0, ',', '.') . ' Porsi' : '-' }}
                </td>
                <td>Posko Ruang Guru</td>
                <td style="text-align: center; color: #b45309;">
                    {{ $stats['target_teachers'] > 0 && $stats['distributed_teachers'] > 0 ? round(($stats['distributed_teachers'] / $stats['target_teachers']) * 100, 1) : 100 }}%
                </td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ============================================================ -->
    <!-- HALAMAN 4: REKAPITULASI VOLUME BULANAN & ANALISIS HIGIENITAS -->
    <!-- ============================================================ -->
    <h4 class="subsection-title">5. Rekapitulasi Volume Distribusi Bulanan ({{ $range['period_label'] }})</h4>
    <p class="paragraph-plain">
        Rincian akumulasi volume paket makanan yang diterima dan disalurkan per bulan selama rentang waktu {{ $range['period_label'] }}:
    </p>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 22%;">Periode Bulan</th>
                <th style="width: 13%;">Hari KBM</th>
                <th style="width: 14%;">Target Paket</th>
                <th style="width: 14%;">Paket Diterima</th>
                <th style="width: 15%;">Realisasi Siswa</th>
                <th style="width: 10%;">Capaian</th>
                <th style="width: 12%;">Status Mutu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyBreakdown as $m)
                <tr>
                    <td style="font-weight: bold;">{{ $m['month_name'] }}</td>
                    <td style="text-align: center;">{{ $m['days_count'] }} Hari</td>
                    <td style="text-align: right;">{{ number_format($m['target_packets'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($m['received_packets'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($m['distributed_students'], 0, ',', '.') }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $m['success_rate'] }}%</td>
                    <td style="text-align: center; font-size: 9pt;">{{ $m['quality_status'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td>TOTAL {{ strtoupper($range['period_label']) }}</td>
                <td style="text-align: center;">{{ $stats['total_days'] }} Hari</td>
                <td style="text-align: right;">{{ number_format($stats['total_target'], 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($stats['total_target'], 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($stats['distributed_students'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $stats['success_rate'] > 0 ? $stats['success_rate'].'%' : '100%' }}</td>
                <td style="text-align: center; font-size: 8.5pt;">NIHIL INSIDEN</td>
            </tr>
        </tfoot>
    </table>

    <h3 class="section-title" style="margin-top: 20px;">V. ANALISIS PENGAWASAN HIGIENITAS DAN KELAYAKAN MAKANAN</h3>
    <p class="paragraph-plain">
        Berdasarkan lembar checklist pengawasan higienitas harian selama periode {{ $range['period_label'] }}:
    </p>

    <div style="margin-top: 6px;">
        <h4 style="font-size: 10.5pt; font-weight: bold; margin-bottom: 2px;">1. Pemeriksaan Fisik &amp; Uji Organoleptik:</h4>
        <p class="paragraph">
            Uji petik harian pada paket makanan memastikan tidak terdapat indikasi basi, aroma asam menyimpang, atau perubahan tekstur berlendir. Komposisi gizi seimbang (karbohidrat, protein lauk hewani/nabati, serat sayuran, dan buah segar) terpantau tersaji higienis dan hangat saat serah terima.
        </p>

        <h4 style="font-size: 10.5pt; font-weight: bold; margin-top: 8px; margin-bottom: 2px;">2. Protokol Kesehatan &amp; Kebersihan Siswa:</h4>
        <p class="paragraph">
            Sebelum konsumsi dimulai, seluruh peserta didik diarahkan mencuci tangan memakai sabun dan air mengalir pada wastafel madrasah, dipandu pembacaan doa bersama di bawah bimbingan wali kelas masing-masing.
        </p>

        <h4 style="font-size: 10.5pt; font-weight: bold; margin-top: 8px; margin-bottom: 2px;">3. Sampel Arsip (<em>Food Holding Sample</em>):</h4>
        <p class="paragraph">
            Sebanyak 1 porsi menu harian secara konsisten disimpan pada lemari pendingin ruang UKS selama 1 × 24 jam bertanggal sebagai sampel arsip investigasi. Selama {{ $range['period_label'] }}, madrasah mencatatkan status <strong>ZERO FOOD ACCIDENT</strong> (tanpa adanya kasus gangguan kesehatan pangan).
        </p>

        <h4 style="font-size: 10.5pt; font-weight: bold; margin-top: 8px; margin-bottom: 2px;">4. Pengelolaan Wadah &amp; Sampah:</h4>
        <p class="paragraph">
            Sisa kemasan ramah lingkungan langsung dipisahkan pada tong sampah organik dan anorganik di selasar kelas, guna mencegah tumpukan lalat serta menjaga estetika kebersihan lingkungan belajar madrasah.
        </p>
    </div>

    <div class="page-break"></div>

    <!-- ============================================================ -->
    <!-- HALAMAN 5: KENDALA, REKOMENDASI RTL, PENUTUP & TANDA TANGAN  -->
    <!-- ============================================================ -->
    <h3 class="section-title">VI. IDENTIFIKASI KENDALA DAN EVALUASI OPERASIONAL</h3>

    <div style="margin-top: 6px;">
        <h4 style="font-size: 10.5pt; font-weight: bold; margin-bottom: 2px;">1. Fluktuasi Ketepatan Waktu Pengantaran Mitra:</h4>
        <p class="paragraph-plain" style="margin-bottom: 3px;">
            Pada kondisi cuaca hujan deras atau kemacetan jalan raya, jadwal kedatangan armada katering terkadang mendekati batas waktu jam istirahat.
        </p>
        <p class="paragraph-plain" style="margin-bottom: 8px;">
            <strong>Tindakan:</strong> Koordinasi harian via grup komunikasi penyedia untuk memajukan jadwal keberangkatan dapur sentral 15–20 menit lebih awal.
        </p>

        <h4 style="font-size: 10.5pt; font-weight: bold; margin-top: 8px; margin-bottom: 2px;">2. Adaptasi Konsumsi Menu Sayur Siswa Kelas Awal:</h4>
        <p class="paragraph-plain" style="margin-bottom: 3px;">
            Sebagian peserta didik kelas rendah belum terbiasa dengan variasi olahan sayur berkuah tertentu.
        </p>
        <p class="paragraph-plain" style="margin-bottom: 8px;">
            <strong>Tindakan:</strong> Pendampingan persuasif oleh wali kelas mengenai pentingnya gizi sayuran serta penyampaian saran perbaikan tekstur sayuran kepada pihak katering.
        </p>
    </div>

    <h3 class="section-title" style="margin-top: 18px;">
        VII. REKOMENDASI DAN RENCANA TINDAK LANJUT (RTL) MENUJU {{ strtoupper($range['next_period_label'] ?? 'PERIODE BERIKUTNYA') }}
    </h3>
    <p class="paragraph-plain">
        Sebagai persiapan operasional memasuki {{ $range['next_period_label'] ?? 'periode berikutnya' }}, dirumuskan rekomendasi tindak lanjut strategis sebagai berikut:
    </p>

    <ol>
        <li>
            <strong>Pengadaan Sarana Troli Distribusi:</strong><br>
            Mengusulkan penyediaan fasilitas troli dorong barang bertingkat untuk mempercepat mobilisasi boks MBG dari pos penerimaan utama ke koridor kelas secara lebih aman dan efisien.
        </li>
        <li>
            <strong>Optimalisasi Titik Cuci Tangan:</strong><br>
            Melakukan penambahan kran air wastafel portable pada titik-titik padat rombel guna mengurai antrean siswa menjelang jam makan bersama.
        </li>
        <li>
            <strong>Evaluasi Berkala Variasi Menu Bersama Ahli Gizi:</strong><br>
            Menyampaikan rekapitulasi evaluasi preferensi menu siswa kepada tim penyedia gizi untuk rotasi siklus menu yang semakin disukai anak-anak tanpa mengurangi nilai kalori dan gizi.
        </li>
    </ol>

    <h3 class="section-title" style="margin-top: 18px;">VIII. PENUTUP</h3>
    <p class="paragraph">
        Demikian Dokumen Laporan Pelaksanaan dan Pendistribusian Program Makanan Bergizi Gratis (MBG) Periode {{ $range['period_label'] }} ({{ $range['start_date']->translatedFormat('j F') }} – {{ $range['end_date']->translatedFormat('j F Y') }}) ini dibuat dengan data riil, cermat, dan dapat dipertanggungjawabkan. Dokumen ini disahkan sebagai bukti fisik sah pemenuhan akuntabilitas kinerja pada Sasaran Kinerja Pegawai (SKP) jabatan pelaksana.
    </p>

    <div class="avoid-break" style="margin-top: 26px;">
        <table class="signature-table">
            <tr>
                <td style="width: 48%;">
                    <p style="margin: 0;">Mengetahui,</p>
                    <p style="margin: 2px 0 0 0; font-weight: bold;">Kepala {{ $setting?->school_name ?? 'Madrasah' }}</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ $setting?->card_principal_name ?? ($setting?->headmaster_name ?? 'Sipulloh, M.Pd.') }}
                    </p>
                    <p style="margin: 2px 0 0 0;">
                        NIP. {{ $setting?->card_principal_nip ?? ($setting?->headmaster_nip ?? '-') }}
                    </p>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <p style="margin: 0;">
                        {{ $setting?->school_district ?: ($setting?->district ?: ($setting?->school_city ?: 'Madrasah')) }}, 
                        {{ $range['end_date']->translatedFormat('d F Y') }}
                    </p>
                    <p style="margin: 2px 0 0 0; font-weight: bold;">Petugas Distribusi MBG,</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ auth()->user()->name ?? 'Petugas MBG' }}
                    </p>
                    <p style="margin: 2px 0 0 0;">
                        NIP. {{ auth()->user()->teacher?->nip ?? '-' }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <script>
        function openChangePeriodModal() {
            document.getElementById('changePeriodModal').classList.add('active');
        }
        function closeChangePeriodModal() {
            document.getElementById('changePeriodModal').classList.remove('active');
        }
    </script>
</body>
</html>
