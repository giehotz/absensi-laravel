<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Absensi Siswa') - Sistem Presensi Sekolah</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F4F6FB;
        }
        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        if (typeof Swal === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
        }
    </script>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-900 flex flex-col antialiased">
    @unless(View::hasSection('hide_header'))
    <!-- Navbar Neobrutalism -->
    <header class="bg-white border-b-4 border-black sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                        <div class="w-11 h-11 bg-white rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-[1px_1px_0px_0px_#000] transition-all">
                            <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo {{ $schoolSetting->school_name ?? 'Sekolah' }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-11 h-11 bg-[#5294FF] text-white font-heading font-black text-2xl flex items-center justify-center border-2 border-black shadow-[3px_3px_0px_0px_#000] group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-[1px_1px_0px_0px_#000] transition-all shrink-0">
                            {{ !empty($schoolSetting->school_name) ? strtoupper(substr($schoolSetting->school_name, 0, 1)) : 'A' }}
                        </div>
                    @endif
                    <div>
                        <div class="font-heading font-bold text-lg sm:text-xl tracking-tight leading-none text-black">
                            ABSENSI<span class="bg-[#FFD43B] px-1 ml-1 border border-black text-sm">SISWA</span>
                        </div>
                        <div class="text-[11px] font-semibold text-slate-600 tracking-wider uppercase">
                            {{ $schoolSetting->school_name ?? 'Sistem Presensi Terpadu' }}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Right Header: Date, Clock & User Status -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Date & Live Clock (Waktu Indonesia) -->
                @include('partials._header-clock')

                @auth
                <div class="hidden md:flex items-center gap-2 bg-[#FFF9DB] border-2 border-black px-3 py-1.5 shadow-[2px_2px_0px_0px_#000] rounded-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#20C997] border border-black animate-pulse"></div>
                    <div class="text-xs font-bold text-black">
                        {{ Auth::user()->name }}
                    </div>
                    <span class="text-[10px] font-black uppercase px-1.5 py-0.5 border border-black 
                        @if(Auth::user()->role === 'admin') bg-[#FF6B6B] text-white
                        @elseif(Auth::user()->role === 'guru') bg-[#5294FF] text-white
                        @elseif(Auth::user()->role === 'siswa') bg-[#20C997] text-black
                        @else bg-[#FFD43B] text-black
                        @endif">
                        {{ Auth::user()->role }}
                    </span>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="neo-btn bg-[#FF6B6B] text-white hover:bg-[#ff5252] px-3.5 py-2 text-xs uppercase tracking-wider flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </header>
    @endunless

    <!-- Main Content Wrapper -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Message Alerts with Auto-Dismiss -->
        @if(session('success'))
        <div id="flashAlertSuccess" class="mb-6 bg-[#D3F9D8] border-3 border-black p-4 neo-box flex items-center justify-between gap-3 text-emerald-950 font-bold transition-all duration-500">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center font-black text-black shrink-0">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="dismissFlashAlert('flashAlertSuccess')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer leading-none px-1">✕</button>
        </div>
        <script>
            setTimeout(() => {
                dismissFlashAlert('flashAlertSuccess');
            }, 4000);
        </script>
        @endif

        @if(session('error'))
        <div id="flashAlertError" class="mb-6 bg-[#FFE3E3] border-3 border-black p-4 neo-box flex items-center justify-between gap-3 text-rose-950 font-bold transition-all duration-500">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-[#FF6B6B] border-2 border-black flex items-center justify-center font-black text-white shrink-0">!</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="dismissFlashAlert('flashAlertError')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer leading-none px-1">✕</button>
        </div>
        <script>
            setTimeout(() => {
                dismissFlashAlert('flashAlertError');
            }, 6000);
        </script>
        @endif

        <script>
            function dismissFlashAlert(id) {
                const el = document.getElementById(id);
                if (el) {
                    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (el && el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    }, 400);
                }
            }
        </script>

        @yield('content')
    </main>

    <!-- Footer Neobrutalism -->
    <footer class="bg-white border-t-4 border-black py-6 mt-12 text-center text-xs font-semibold text-slate-700">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-[#FFD43B] border border-black font-mono font-bold text-[10px]">VERSI 1.0</span>
                <span>Sistem Absensi Digital Sekolah Berbasis QR & Multi-Peran</span>
            </div>
            <div>
                © {{ date('Y') }} Absensi Siswa • Didesain dengan <strong class="text-black font-bold">Neobrutalism UI</strong>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
