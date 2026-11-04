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
            background-color: #F0F3F8;
            background-image: radial-gradient(#CBD5E1 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }

        .student-card-side {
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Mode garis potong (cut guides) */
        .show-cut-guides .student-card-side {
            outline: 1px dashed #94a3b8 !important;
            outline-offset: 1px !important;
        }

        @media print {
            @page {
                size: auto;
                margin: 8mm;
            }
            body {
                background-color: #ffffff !important;
                background-image: none !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .student-card-side {
                box-shadow: none !important;
            }
            .print-workbench {
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 flex flex-col antialiased">

    <!-- Top Navigation & Action Toolbar (Hidden when printing) -->
    <header class="bg-white border-b-2 border-black sticky top-0 z-30 shadow-[0_4px_0_0_#000] print:hidden">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Left: Back Navigation & Student Identity Badge -->
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.cards') }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_#000] shrink-0">
                    <span>← Studio</span>
                </a>

                <div class="h-6 w-px bg-slate-300 hidden sm:block"></div>

                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-black text-white flex items-center justify-center font-heading font-black text-xs shrink-0 shadow-[1px_1px_0px_#000]">
                        🪪
                    </div>
                    <div class="min-w-0 leading-tight">
                        <div class="font-heading font-black text-sm text-black truncate max-w-[200px] sm:max-w-[280px]">
                            {{ $student->user?->name ?? 'Siswa' }}
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                            <span class="font-mono">NIS: {{ $student->nis }}</span>
                            <span>•</span>
                            <span class="bg-slate-100 text-slate-800 px-1.5 py-0.2 rounded border border-slate-300 font-semibold">
                                {{ $student->schoolClass?->name ?? 'Semua Kelas' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Print Settings & Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Sisi Cetak Selector -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Sisi:</span>
                    <select id="printSideSelector" onchange="changePrintSide(this.value)" class="neo-input px-2.5 py-1 text-xs font-bold bg-slate-50 focus:bg-white cursor-pointer shadow-[2px_2px_0px_#000]">
                        <option value="both" selected>Kedua Sisi (Depan & Belakang)</option>
                        <option value="front">Sisi Depan Saja (Validasi)</option>
                        <option value="back">Sisi Belakang Saja (Presensi)</option>
                    </select>
                </div>

                <!-- Toggle Garis Potong -->
                <button type="button" 
                        onclick="toggleCutGuides()" 
                        id="cutGuidesBtn"
                        class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1.5 text-xs font-black shadow-[2px_2px_0px_#000]"
                        title="Tampilkan garis bantu potong gunting">
                    <span>✂️ Garis Potong</span>
                </button>

                <!-- Tombol Cetak Utama -->
                <button onclick="window.print()" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#000] cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Kartu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Inspection Workbench -->
    <main class="flex-1 max-w-5xl mx-auto w-full p-4 sm:p-6 lg:p-8 flex flex-col items-center justify-center print-workbench">
        
        <!-- Workbench Specs Bar (Print:hidden) -->
        <div class="w-full max-w-3xl mb-6 flex flex-wrap items-center justify-between gap-2.5 bg-white border-2 border-black px-4 py-2.5 rounded-xl shadow-[3px_3px_0px_#000] print:hidden">
            <div class="flex items-center gap-2 text-xs font-black text-black">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Standar Fisik CR80: {{ $setting->card_width_cm }} × {{ $setting->card_height_cm }} cm</span>
            </div>
            <div class="flex items-center gap-3 text-[11px] font-bold text-slate-600">
                <span class="flex items-center gap-1">
                    <span>📐</span> Skala Asli 1:1
                </span>
                <span>•</span>
                <span class="flex items-center gap-1">
                    <span>✨</span> Dual Vector QR Code
                </span>
            </div>
        </div>

        <!-- The Student Card Canvas -->
        <div id="cardCanvas" class="flex justify-center transition-all duration-200">
            <x-student-card :student="$student" :setting="$setting" side="both" orientation="landscape" size="standard" :interactive="false" cardId="single_card" />
        </div>

        <!-- Printing Guidelines & Recommendations Panel (Print:hidden) -->
        <div class="w-full max-w-3xl mt-8 bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_#000] space-y-3 print:hidden">
            <div class="flex items-center gap-2 border-b-2 border-slate-100 pb-2.5">
                <span class="text-base">💡</span>
                <h2 class="font-heading font-black text-xs sm:text-sm uppercase tracking-wider text-black">
                    Panduan & Tips Mencetak Kartu Siswa Presisi
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Tip 1 -->
                <div class="space-y-1 bg-slate-50 border border-black p-3 rounded-lg shadow-[2px_2px_0px_#000]">
                    <div class="font-black text-black flex items-center gap-1.5">
                        <span>🖨️</span> Skala Cetak 100%
                    </div>
                    <p class="text-slate-600 leading-relaxed text-[11px]">
                        Pada jendela dialog cetak browser, pastikan <strong>Scale</strong> diatur ke <strong>100% (Actual Size)</strong>, jangan pilih <em>Fit to page</em> agar ukuran fisik tetap 85.6 × 54 mm.
                    </p>
                </div>

                <!-- Tip 2 -->
                <div class="space-y-1 bg-slate-50 border border-black p-3 rounded-lg shadow-[2px_2px_0px_#000]">
                    <div class="font-black text-black flex items-center gap-1.5">
                        <span>📄</span> Rekomendasi Media
                    </div>
                    <p class="text-slate-600 leading-relaxed text-[11px]">
                        Gunakan baki <strong>PVC Card Tray</strong> untuk printer ID Card, atau kertas <strong>Glossy Photo Paper 230-260 gsm</strong> lalu lakukan laminasi panas (hot lamination).
                    </p>
                </div>

                <!-- Tip 3 -->
                <div class="space-y-1 bg-slate-50 border border-black p-3 rounded-lg shadow-[2px_2px_0px_#000]">
                    <div class="font-black text-black flex items-center gap-1.5">
                        <span>✂️</span> Panduan Pemotongan
                    </div>
                    <p class="text-slate-600 leading-relaxed text-[11px]">
                        Aktifkan tombol <strong>Garis Potong</strong> di atas jika mencetak pada kertas lembaran untuk mempermudah pemotongan menggunakan alat potong kartu atau gunting.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function changePrintSide(side) {
            document.querySelectorAll('.card-front').forEach(el => {
                el.style.display = (side === 'back') ? 'none' : 'flex';
            });
            document.querySelectorAll('.card-back').forEach(el => {
                el.style.display = (side === 'front') ? 'none' : 'flex';
            });
        }

        let cutGuidesActive = false;
        function toggleCutGuides() {
            cutGuidesActive = !cutGuidesActive;
            const canvas = document.getElementById('cardCanvas');
            const btn = document.getElementById('cutGuidesBtn');

            if (cutGuidesActive) {
                canvas.classList.add('show-cut-guides');
                btn.classList.add('bg-[#FFD43B]');
                btn.classList.remove('bg-white');
            } else {
                canvas.classList.remove('show-cut-guides');
                btn.classList.remove('bg-[#FFD43B]');
                btn.classList.add('bg-white');
            }
        }
    </script>
</body>
</html>
