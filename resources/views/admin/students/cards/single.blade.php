<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Siswa - {{ $student->user?->name ?? $student->nis }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        .single-card {
            width: 85.6mm;
            height: 54mm;
            box-sizing: border-box;
        }

        @media print {
            @page {
                size: 85.6mm 54mm;
                margin: 0;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            .print\:hidden {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Action Toolbar -->
    <div class="mb-6 flex items-center gap-3 print:hidden">
        <button onclick="window.close()" class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-bold">
            Tutup
        </button>
        <button onclick="window.print()" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_#000]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Kartu Ini</span>
        </button>
    </div>

    <!-- The Single CR80 ID Card -->
    <div class="single-card border-2 border-black rounded-md bg-white flex flex-col justify-between overflow-hidden shadow-[4px_4px_0px_#000] print:shadow-none select-none">
        
        <!-- Kop Sekolah -->
        <div class="bg-slate-900 text-white px-2.5 py-1.5 flex items-center justify-between border-b-2 border-black">
            <div class="flex items-center gap-1.5">
                @if(!empty($setting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo))
                    <div class="w-6 h-6 rounded-full bg-white border border-black flex items-center justify-center overflow-hidden shrink-0 p-0.5">
                        <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-6 h-6 rounded-full bg-[#FFD43B] border border-black flex items-center justify-center font-black text-xs text-black shrink-0">
                        🎓
                    </div>
                @endif
                <div class="leading-none truncate max-w-[55mm]">
                    <div class="font-heading font-black text-[9.5px] uppercase tracking-tight truncate text-[#FFD43B]">
                        {{ $setting->school_name ?? 'SMP NEGERI 1 GARUDA' }}
                    </div>
                    <div class="text-[7px] font-mono text-slate-300 mt-0.5 truncate">
                        NPSN: {{ $setting->npsn ?? '-' }} • {{ $setting->level ?? 'SMP' }}
                    </div>
                </div>
            </div>
            <span class="text-[7px] font-mono font-bold bg-[#20C997] text-black px-1.5 py-0.2 rounded border border-black uppercase shrink-0">
                PELAJAR
            </span>
        </div>

        <!-- Pita Judul -->
        <div class="bg-[#FFD43B] text-black border-b border-black text-center py-0.2 font-heading font-black text-[8px] uppercase tracking-wider">
            KARTU TANDA PELAJAR & PRESENSI ELEKTRONIK
        </div>

        <!-- Badan Kartu: Foto, Identitas, & Real QR Code -->
        <div class="px-2.5 py-1 flex items-center justify-between gap-2 flex-1 bg-white">
            <!-- Foto Siswa -->
            <div class="w-[15mm] h-[20mm] bg-slate-100 border border-black rounded flex flex-col items-center justify-center shrink-0 relative overflow-hidden">
                @if($student->photo)
                    <img src="{{ $student->photo_url }}" alt="Foto {{ $student->user?->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-base">👤</span>
                    <span class="text-[6px] font-bold text-slate-500 uppercase">3×4</span>
                @endif
                <span class="absolute bottom-0 inset-x-0 bg-black text-white text-[6px] font-bold text-center uppercase">
                    {{ $student->gender == 'L' ? 'LAKI' : 'PEREMPUAN' }}
                </span>
            </div>

            <!-- Identitas Siswa -->
            <div class="flex-1 min-w-0 leading-tight space-y-0.5">
                <div class="font-heading font-black text-[9px] text-black truncate">
                    {{ $student->user?->name ?? '-' }}
                </div>
                <div class="grid grid-cols-3 gap-x-0.5 text-[7.5px] text-slate-800 font-medium">
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
                         alt="QR Code Absensi" 
                         class="w-[17mm] h-[17mm] object-contain">
                </div>
                <span class="text-[6px] font-black uppercase text-black mt-0.5 tracking-tighter">
                    SCAN ABSEN
                </span>
            </div>
        </div>

        <!-- Footer Kartu -->
        <div class="bg-slate-100 px-2 py-0.5 border-t border-black flex items-center justify-between text-[7px] text-slate-700 font-medium">
            <span>Berlaku Selama Menjadi Siswa Aktif</span>
            <span class="font-mono font-bold text-black">{{ $student->qr_code_identifier }}</span>
        </div>
    </div>

</body>
</html>
