<x-print-preview-layout title="Program Perencanaan & Laporan Supervisi (Format SK 6334/2021)" paperSize="A4" orientation="portrait">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 14px;">
        <h1 style="font-size: 12.5pt; font-weight: bold; text-transform: uppercase; margin: 0;">
            Program Perencanaan &amp; Laporan Pelaksanaan Supervisi Pembelajaran
        </h1>
        <p style="font-size: 9pt; margin: 3px 0 0 0; color: #334155;">
            Mengacu SK Dirjen Pendis No. 6334 Tahun 2021 (Juknis Supervisi Pembelajaran Madrasah Ibtidaiyah)
            @if($academicYear)
                · Tahun Pelajaran {{ $academicYear->name }} (Semester {{ ucfirst((string) $academicYear->semester) }})
            @endif
        </p>
    </div>

    <div style="font-size: 9pt; line-height: 1.5; margin-bottom: 12px;">
        <h2 style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin: 0 0 6px 0;">
            BAB I. PROGRAM PERENCANAAN SUPERVISI (BAB III C.1 SK 6334/2021)
        </h2>
        <p style="margin: 0 0 4px 0;"><strong>a. Latar Belakang:</strong> Supervisi akademik dilaksanakan sebagai upaya penjaminan mutu proses perencanaan, pelaksanaan, dan penilaian pembelajaran guru madrasah agar selaras dengan kecakapan abad ke-21, pembelajaran bermakna, serta penguatan karakter peserta didik.</p>
        <p style="margin: 0 0 4px 0;"><strong>b. Tujuan:</strong> Mengembangkan profesionalisme guru secara kolaboratif melalui siklus Pra-Observasi (<em>Planning Conference</em>), Observasi Kelas, dan Umpan Balik Reflektif (<em>Feedback Conference</em> / Coaching TIRTA).</p>
        <p style="margin: 0 0 4px 0;"><strong>c. Manfaat:</strong> Meningkatkan efektivitas strategi pembelajaran guru, ketersediaan perangkat ajar bermutu, serta keselarasan tindak lanjut pengembangan diri guru.</p>
        <p style="margin: 0 0 4px 0;"><strong>d. Ruang Lingkup &amp; Pendekatan:</strong> Mencakup supervisi Perencanaan Pembelajaran, Pelaksanaan Pembelajaran, dan Penilaian Pembelajaran menggunakan pendekatan Direktif, Non-Direktif, maupun Kolaboratif dengan teknik Kunjungan Kelas, Observasi Kelas, Pertemuan Individual, dan KKG.</p>
    </div>

    <div style="margin-bottom: 12px;">
        <h2 style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin: 0 0 6px 0;">
            BAB II. TIM SUPERVISOR PENUNJUKAN KEPALA MADRASAH
        </h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 8.6pt;">
            <thead>
                <tr style="background: #EAEAEA;">
                    <th style="border: 1px solid #000; padding: 4px; width: 28px; text-align: center;">No</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Nama Supervisor</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">NIP</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Jabatan</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Nomor SK Penugasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supervisors as $idx => $sup)
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $idx + 1 }}</td>
                        <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">{{ $sup->teacher?->user?->name ?? '-' }}</td>
                        <td style="border: 1px solid #000; padding: 4px;">{{ $sup->teacher?->nip ?: '-' }}</td>
                        <td style="border: 1px solid #000; padding: 4px;">{{ $sup->jabatan ?: 'Tim Supervisor' }}</td>
                        <td style="border: 1px solid #000; padding: 4px;">{{ $sup->nomor_sk ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="border: 1px solid #000; padding: 6px; text-align: center;">Belum ada tim supervisor aktif terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-bottom: 12px;">
        <h2 style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin: 0 0 6px 0;">
            BAB III. JADWAL, HASIL PELAKSANAAN &amp; TINDAK LANJUT SUPERVISI
        </h2>
        <p style="font-size: 8.6pt; margin: 0 0 6px 0;">
            Total Jadwal Aktif: <strong>{{ $stats['total_jadwal'] }}</strong> · Selesai: <strong>{{ $stats['selesai'] }}</strong> · Sesi Coaching TIRTA: <strong>{{ $stats['coaching_count'] }}</strong> · Rata-Rata Nilai: <strong>{{ number_format((float) $stats['rata_nilai'], 2) }}%</strong>
        </p>

        <table style="width: 100%; border-collapse: collapse; font-size: 8.2pt;">
            <thead>
                <tr style="background: #EAEAEA;">
                    <th style="border: 1px solid #000; padding: 4px; width: 24px; text-align: center;">No</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Tanggal</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Guru &amp; Kelas/Mapel</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Supervisor</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Pendekatan / Teknik</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: center;">Nilai</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Tindak Lanjut / TIRTA</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $idx => $j)
                    @php
                        $coaching = $j->coachingTirtas->first();
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; vertical-align: top;">{{ $idx + 1 }}</td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">
                            <strong>{{ $j->teacher?->user?->name ?? '-' }}</strong><br>
                            <span style="color: #475569;">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</span>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">{{ $j->supervisor_display_name }}</td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">
                            {{ ucfirst(str_replace('_', ' ', (string) ($j->pendekatan ?? 'kolaboratif'))) }}<br>
                            <span style="color: #475569;">{{ ucfirst(str_replace('_', ' ', (string) ($j->teknik_supervisi ?? 'kunjungan_kelas'))) }}</span>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; vertical-align: top;">
                            {{ $j->nilai_akhir !== null ? number_format((float) $j->nilai_akhir, 1) . '% (' . $j->ketercapaian . ')' : '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">
                            {{ $coaching?->rencana_aksi ?: ($j->tindakLanjut?->upaya_tindak_lanjut ?: '-') }}
                            @if($j->fotoBuktis->isNotEmpty())
                                <div style="font-size: 7.5pt; color: #334155; margin-top: 2px;">Lampiran Foto: {{ $j->fotoBuktis->count() }} berkas</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="border: 1px solid #000; padding: 8px; text-align: center;">Belum ada data pelaksanaan supervisi pada periode terpilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 16px; page-break-inside: avoid; font-size: 9pt;">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                {{ $kotaMadrasah }}, {{ now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                <strong>Kepala Madrasah</strong>
                <div style="height: 55px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $namaKepala }}</div>
                <div style="font-size: 8pt; color: #475569;">NIP. {{ $nipKepala }}</div>
            </td>
        </tr>
    </table>
</x-print-preview-layout>
