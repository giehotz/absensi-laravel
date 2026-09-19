<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Siswa (Format A4) - {{ $setting->school_name ?? 'Absensi Siswa' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        /* Standar Ukuran Kartu ID-1 (CR80) */
        .id-card-cr80 {
            width: 85.6mm;
            height: 54mm;
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .print-page {
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            /* Hilangkan bayangan neobrutalism saat cetak fisik agar hasil presisi */
            .id-card-cr80 {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased p-4 sm:p-6">

    <!-- Top Action Bar (Hidden when printed) -->
    <div class="max-w-5xl mx-auto mb-6 bg-white border-2 border-black p-4 rounded-lg shadow-[4px_4px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>🖨️</span> Pratinjau Lembar Cetak Kartu Siswa (Format A4)
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                Total <strong>{{ count($students) }}</strong> kartu siap dicetak. Tata letak diatur 8 kartu per halaman A4 (grid 2 × 4) lengkap dengan garis panduan potong.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.close()" class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-bold">
                Tutup
            </button>
            <button onclick="window.print()" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Lembar Kerja A4 Grid -->
    <div class="max-w-[210mm] mx-auto bg-white p-2 sm:p-4 rounded-lg shadow-sm border border-slate-300 print:border-none print-page">
        <div class="grid grid-cols-2 gap-3 justify-items-center">
            @foreach($students as $student)
            <!-- Satu Kartu Siswa (CR80: 85.6mm x 54mm) -->
            <div class="id-card-cr80 border-2 border-black rounded bg-white flex flex-col justify-between overflow-hidden relative select-none">
                
                <!-- Kop Sekolah -->
                <div class="bg-slate-900 text-white px-2 py-1 flex items-center justify-between border-b-2 border-black">
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-[#FFD43B] border border-black flex items-center justify-center font-black text-[10px] text-black shrink-0">
                            🎓
                        </div>
                        <div class="leading-none truncate max-w-[55mm]">
                            <div class="font-heading font-black text-[9px] uppercase tracking-tight truncate text-[#FFD43B]">
                                {{ $setting->school_name ?? 'SMP NEGERI 1 GARUDA' }}
                            </div>
                            <div class="text-[6.5px] font-mono text-slate-300 mt-0.5 truncate">
                                NPSN: {{ $setting->npsn ?? '-' }} • {{ $setting->level ?? 'SMP' }}
                            </div>
                        </div>
                    </div>
                    <span class="text-[7px] font-mono font-bold bg-[#20C997] text-black px-1 py-0.2 rounded border border-black uppercase shrink-0">
                        PELAJAR
                    </span>
                </div>

                <!-- Pita Judul -->
                <div class="bg-[#FFD43B] text-black border-b border-black text-center py-0.2 font-heading font-black text-[7.5px] uppercase tracking-wider">
                    KARTU TANDA PELAJAR & PRESENSI
                </div>

                <!-- Badan Kartu: Foto, Identitas, & QR Code -->
                <div class="px-2 py-1 flex items-center justify-between gap-1.5 flex-1 bg-white">
                    <!-- Foto Siswa -->
                    <div class="w-[14mm] h-[19mm] bg-slate-100 border border-black rounded flex flex-col items-center justify-center shrink-0 relative overflow-hidden">
                        @if($student->photo)
                            <img src="{{ $student->photo_url }}" alt="Foto {{ $student->user?->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-sm">👤</span>
                            <span class="text-[5.5px] font-bold text-slate-500 uppercase">3×4</span>
                        @endif
                        <span class="absolute bottom-0 inset-x-0 bg-black text-white text-[5.5px] font-bold text-center uppercase">
                            {{ $student->gender == 'L' ? 'L' : 'P' }}
                        </span>
                    </div>

                    <!-- Identitas Siswa -->
                    <div class="flex-1 min-w-0 leading-tight space-y-0.5">
                        <div class="font-heading font-black text-[8.5px] text-black truncate">
                            {{ $student->user?->name ?? '-' }}
                        </div>
                        <div class="grid grid-cols-3 gap-x-0.5 text-[7px] text-slate-800 font-medium">
                            <span class="text-slate-500">NIS</span>
                            <span class="col-span-2 font-mono font-bold text-black">: {{ $student->nis }}</span>

                            <span class="text-slate-500">NISN</span>
                            <span class="col-span-2 font-mono text-black">: {{ $student->nisn ?? '-' }}</span>

                            <span class="text-slate-500">Kelas</span>
                            <span class="col-span-2 font-bold text-black">: {{ $student->schoolClass?->name ?? '-' }}</span>

                            <span class="text-slate-500">Lahir</span>
                            <span class="col-span-2 font-mono text-black">: {{ $student->birth_date ? $student->birth_date->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>

                    <!-- QR Code Absensi -->
                    <div class="flex flex-col items-center justify-center shrink-0">
                        <div class="bg-white p-0.5 border border-black rounded">
                            <img src="{{ $student->qr_data_uri }}" 
                                 alt="QR Code" 
                                 class="w-[15mm] h-[15mm] object-contain">
                        </div>
                        <span class="text-[5.5px] font-black uppercase text-black mt-0.5 tracking-tighter">
                            SCAN ABSEN
                        </span>
                    </div>
                </div>

                <!-- Footer Kartu -->
                <div class="bg-slate-100 px-2 py-0.5 border-t border-black flex items-center justify-between text-[6.5px] text-slate-700 font-medium">
                    <span>Berlaku Selama Menjadi Siswa Aktif</span>
                    <span class="font-mono font-bold text-black">{{ $student->qr_code_identifier }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</body>
</html>
