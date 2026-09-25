<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isValid ? 'Verifikasi Kartu Siswa: ' . $student->user?->name : 'Verifikasi Kartu Siswa Tidak Valid' }}</title>
    
    {{-- Fonts Google --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8F9FA;
        }
        .neo-box {
            border: 2px solid #000;
            box-shadow: 4px 4px 0px 0px #000;
        }
        .neo-btn {
            border: 2px solid #000;
            box-shadow: 2px 2px 0px 0px #000;
            transition: all 0.15s ease;
        }
        .neo-btn:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px 0px #000;
        }
    </style>
</head>
<body class="min-h-screen text-black antialiased py-6 px-4 sm:py-10 flex flex-col items-center justify-between">
    
    <div class="w-full max-w-xl mx-auto space-y-6">
        {{-- Header Instansi / Lembaga --}}
        <div class="text-center space-y-2">
            @if($setting->card_logo_url)
                <div class="w-16 h-16 mx-auto bg-white border-2 border-black rounded-xl p-1 flex items-center justify-center shadow-[3px_3px_0px_#000]">
                    <img src="{{ $setting->card_logo_url }}" alt="Logo" class="w-full h-full object-contain">
                </div>
            @else
                <div class="w-16 h-16 mx-auto bg-[#20C997] border-2 border-black rounded-xl flex items-center justify-center text-3xl shadow-[3px_3px_0px_#000]">
                    🏫
                </div>
            @endif
            <div>
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">
                    Kementerian Agama Republik Indonesia
                </div>
                <h1 class="font-heading font-black text-xl sm:text-2xl text-black uppercase tracking-tight">
                    {{ $setting->resolved_card_school_name }}
                </h1>
                <p class="text-xs text-slate-600 font-medium">
                    Layanan Verifikasi & Otentikasi Kartu Pelajar Digital Resmi
                </p>
            </div>
        </div>

        @if($isValid)
            {{-- Status Banner Sukses Terverifikasi --}}
            <div class="bg-[#D3F9D8] border-3 border-black p-4 rounded-xl shadow-[4px_4px_0px_#000] flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center text-2xl shrink-0 shadow-[2px_2px_0px_#000]">
                    ✅
                </div>
                <div>
                    <div class="inline-block bg-black text-white font-mono text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider mb-0.5">
                        STATUS: TERVERIFIKASI RESMI
                    </div>
                    <h2 class="font-heading font-black text-base text-emerald-950">
                        Kartu Tanda Pelajar Sah & Asli
                    </h2>
                    <p class="text-xs text-emerald-900 font-medium">
                        Identitas siswa tercatat aktif dalam sistem pangkalan data madrasah.
                    </p>
                </div>
            </div>

            {{-- Kartu Pelajar Asli (Single Source of Truth) --}}
            <div class="bg-white neo-box p-4 rounded-xl space-y-3">
                <div class="text-xs font-black uppercase text-black flex items-center justify-between border-b-2 border-black pb-2">
                    <span>🪪 Pratinjau Kartu Siswa:</span>
                    <span class="text-[10px] font-mono bg-slate-100 px-2 py-0.5 border border-black rounded">
                        ID: {{ $student->qr_code_identifier }}
                    </span>
                </div>

                <div class="flex justify-center py-2 overflow-x-auto">
                    <x-student-card :student="$student" :setting="$setting" side="both" :interactive="true" />
                </div>
            </div>

            {{-- Rincian Data Siswa --}}
            <div class="bg-white neo-box p-5 rounded-xl space-y-4">
                <div class="flex items-center gap-2 border-b-2 border-black pb-2">
                    <span class="text-lg">📋</span>
                    <h3 class="font-heading font-black text-sm uppercase text-black">
                        Data Identitas Lengkap
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-lg">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-0.5">Nama Lengkap</span>
                        <span class="font-heading font-black text-sm text-black uppercase">{{ $student->user?->name }}</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-lg">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-0.5">NISM</span>
                        <span class="font-mono font-black text-sm text-black">{{ $student->nis ?? '-' }}</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-lg">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-0.5">NISN</span>
                        <span class="font-mono font-black text-sm text-black">{{ $student->nisn ?? '-' }}</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-lg">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-0.5">Status Keaktifan</span>
                        <span class="inline-flex items-center gap-1 font-bold text-xs bg-emerald-100 text-emerald-900 border border-emerald-900 px-2 py-0.5 rounded">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            <span>Siswa Aktif Terdaftar</span>
                        </span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-lg sm:col-span-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-0.5">Tempat & Tanggal Lahir</span>
                        <span class="font-bold text-xs text-black">
                            {{ $student->birth_place ?: '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>
                </div>

                {{-- Timestamp Verifikasi --}}
                <div class="bg-[#E7F5FF] border-2 border-black p-3 rounded-lg flex items-center justify-between text-[11px] font-medium text-blue-950">
                    <div>
                        <span class="font-bold">Diverifikasi pada:</span>
                        <span>{{ $verifiedAt->translatedFormat('l, d F Y - H:i:s') }} WIB</span>
                    </div>
                    <span class="text-xs">🔒 Secure SSL</span>
                </div>
            </div>
        @else
            {{-- Status Banner Gagal / Tidak Ditemukan --}}
            <div class="bg-[#FFE3E3] border-3 border-black p-5 rounded-xl shadow-[4px_4px_0px_#000] text-center space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full bg-[#FF6B6B] border-2 border-black flex items-center justify-center text-3xl shadow-[2px_2px_0px_#000]">
                    ❌
                </div>
                <div>
                    <h2 class="font-heading font-black text-xl text-rose-950 uppercase">
                        Kartu Siswa Tidak Terdaftar!
                    </h2>
                    <p class="text-xs text-rose-900 mt-1 max-w-sm mx-auto font-medium">
                        Kode identifikasi <code class="bg-white px-1.5 py-0.5 rounded border border-black font-mono font-bold">{{ $identifier }}</code> tidak ditemukan atau telah dinonaktifkan dari basis data sekolah.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="/" class="neo-btn bg-black text-white px-4 py-2 text-xs font-bold inline-block">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="text-center text-[11px] font-medium text-slate-500 pt-4 border-t border-slate-300">
            &copy; {{ date('Y') }} {{ $setting->resolved_card_school_name }}. Sistem Kehadiran & Identitas Digital Madrasah.
        </div>
    </div>

</body>
</html>
