<x-print-preview-layout 
    :title="'Label Pintu Ruang - ' . $ruang->nama_ruang"
    orientation="landscape"
    paperSize="A4"
    :backUrl="route('ujian.ruang.index')"
    pageMargin="0.8cm"
    sheetClass="p-4 sm:p-6"
>
    <style>
        .sheet-label-pintu {
            font-family: Arial, sans-serif;
            color: #000;
            width: 100%;
        }
        .banner-ruang {
            border: 4px solid #000;
            background-color: #FFD43B;
            text-align: center;
            padding: 12px 0;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 4px 4px 0 #000;
        }
        .banner-ruang h1 {
            margin: 0;
            font-size: 40pt;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .banner-ruang .sub-banner {
            font-size: 13pt;
            font-weight: bold;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }
        .meta-grid {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11pt;
            background-color: #f9f9f9;
        }
        .meta-grid td {
            padding: 6px 10px;
            border: 1px solid #000;
        }
        .tabel-peserta-multi {
            width: 100%;
            display: flex;
            gap: 15px;
        }
        .tabel-kolom {
            flex: 1;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .tabel-kolom th, .tabel-kolom td {
            border: 1px solid #000;
            padding: 2.5px 5px;
        }
        .tabel-kolom th {
            background-color: #eee;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .peringatan-box {
            margin-top: 10px;
            border: 2px dashed #000;
            padding: 6px;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            background-color: #fff3cd;
        }
    </style>

    <div class="sheet-label-pintu">
        <!-- Kop Surat Resmi -->
        <x-kop-surat :useBase64="true" />

        <!-- Banner Ruang Raksasa -->
        <div class="banner-ruang">
            <h1>{{ $ruang->nama_ruang }}</h1>
            <div class="sub-banner">
                {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
                @if($activePeriode && $activePeriode->tahun_pelajaran)
                    - TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran }}
                @endif
            </div>
        </div>

        @php
            $nomorAwal = $pesertas->first()?->nomor_peserta ?? '-';
            $nomorAkhir = $pesertas->last()?->nomor_peserta ?? '-';
            $kelasUnik = $pesertas->pluck('nama_kelas')->filter()->unique()->implode(', ');
            if (empty($kelasUnik)) {
                $kelasUnik = $ruang->isKelas() && $ruang->schoolClass ? 'Kelas ' . $ruang->schoolClass->name : 'Semua Rombel Terdaftar';
            }
        @endphp

        <table class="meta-grid">
            <tr>
                <td style="width: 20%;" class="font-bold">Daftar Rombel / Kelas</td>
                <td style="width: 30%;" class="font-bold">: {{ $kelasUnik }}</td>
                <td style="width: 20%;" class="font-bold">Rentang No. Peserta</td>
                <td style="width: 30%;" class="font-bold font-mono">: {{ $nomorAwal }} s.d. {{ $nomorAkhir }}</td>
            </tr>
            <tr>
                <td class="font-bold">Kapasitas Tempat Duduk</td>
                <td>: {{ $ruang->kapasitas_maksimal }} Meja / Kursi</td>
                <td class="font-bold">Jumlah Peserta Terdaftar</td>
                <td class="font-bold">: {{ $pesertas->count() }} Siswa</td>
            </tr>
        </table>

        <!-- Daftar Peserta 2 Kolom -->
        @php
            $half = (int) ceil($pesertas->count() / 2);
            $col1 = $pesertas->slice(0, $half);
            $col2 = $pesertas->slice($half);
        @endphp

        <div class="tabel-peserta-multi">
            <table class="tabel-kolom">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 45px;">Meja</th>
                        <th style="width: 80px;">No. Peserta</th>
                        <th>Nama Peserta</th>
                        <th style="width: 60px;">Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($col1 as $idx => $p)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="text-center font-bold">{{ $p->nomor_kursi }}</td>
                            <td class="text-center font-mono font-bold">{{ $p->nomor_peserta }}</td>
                            <td class="font-bold">{{ $p->nama_peserta }}</td>
                            <td class="text-center">{{ $p->nama_kelas ?? ($p->schoolClass?->name ?? '') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 10px;">Belum ada peserta yang dialokasikan di ruang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="tabel-kolom">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 45px;">Meja</th>
                        <th style="width: 80px;">No. Peserta</th>
                        <th>Nama Peserta</th>
                        <th style="width: 60px;">Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($col2 as $idx => $p)
                        <tr>
                            <td class="text-center">{{ $half + $loop->iteration }}</td>
                            <td class="text-center font-bold">{{ $p->nomor_kursi }}</td>
                            <td class="text-center font-mono font-bold">{{ $p->nomor_peserta }}</td>
                            <td class="font-bold">{{ $p->nama_peserta }}</td>
                            <td class="text-center">{{ $p->nama_kelas ?? ($p->schoolClass?->name ?? '') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="peringatan-box">
            ⚠️ PERHATIAN: Peserta dilarang membawa tas, buku, catatan, handphone (HP), atau perangkat elektronik lainnya ke dalam ruang ujian. Harap duduk sesuai nomor meja masing-masing.
        </div>
    </div>
</x-print-preview-layout>