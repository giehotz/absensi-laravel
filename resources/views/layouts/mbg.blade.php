<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Makan Bergizi Gratis') - {{ config('app.name', 'Sistem MBG') }}</title>
    @php
        $schoolSetting = \App\Models\AttendanceSetting::first();
    @endphp
    @if(!empty($schoolSetting?->favicon) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->favicon))
        <link rel="icon" href="{{ asset('storage/' . $schoolSetting->favicon) }}">
    @elseif(!empty($schoolSetting?->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
        <link rel="icon" href="{{ asset('storage/' . $schoolSetting->logo) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:600,700" rel="stylesheet" />
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
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #0F172A;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
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
<body class="min-h-screen text-slate-900 flex antialiased">
    <!-- Mobile Sidebar Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/70 z-40 hidden transition-opacity lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0F172A] border-r-4 border-black flex flex-col justify-between transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0 shadow-[4px_0px_0px_0px_rgba(0,0,0,0.2)]">
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Sidebar Header -->
            <div class="h-20 border-b-4 border-black px-5 flex items-center justify-between bg-[#1E293B] shrink-0">
                <a href="{{ route('mbg.dashboard') }}" class="flex items-center gap-3 group">
                    @if(!empty($schoolSetting?->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                        <div class="w-10 h-10 rounded-lg border-2 border-black bg-white p-1 flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                            <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-lg border-2 border-black bg-[#FFD43B] flex items-center justify-center text-lg font-black shrink-0 shadow-[2px_2px_0px_0px_#000]">
                            🍱
                        </div>
                    @endif
                    <div class="min-w-0">
                        <span class="font-heading font-black text-sm text-white block truncate tracking-tight">PORTAL MBG</span>
                        <span class="text-[10px] font-bold text-[#FFD43B] uppercase tracking-wider block truncate">Makan Bergizi Gratis</span>
                    </div>
                </a>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav id="sidebarNav" class="flex-1 overflow-y-auto sidebar-scroll px-3.5 py-4 space-y-4">
                <div>
                    <div class="px-2 mb-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 font-heading">
                        Menu Utama
                    </div>
                    <div class="space-y-1">
                        <!-- Dashboard -->
                        <a href="{{ route('mbg.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('mbg.dashboard') 
                                ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard & Riwayat</span>
                        </a>

                        <!-- Catat Distribusi -->
                        <a href="{{ route('mbg.distributions.create') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('mbg.distributions.create') 
                                ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Catat Distribusi Baru</span>
                        </a>

                        <!-- Rekapitulasi Laporan -->
                        <a href="{{ route('mbg.rekap.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('mbg.rekap.*') 
                                ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Rekapitulasi Laporan</span>
                        </a>
                    </div>
                </div>

                @if(Auth::user()->isAdmin())
                    <div>
                        <div class="px-2 mb-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 font-heading">
                            Kembali ke Administrator
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold text-slate-300 border-2 border-transparent hover:bg-slate-800/90 hover:text-white hover:border-slate-700 cursor-pointer">
                                <svg class="w-4 h-4 shrink-0 text-[#20C997]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                                </svg>
                                <span>Portal Admin Panel</span>
                            </a>
                        </div>
                    </div>
                @elseif(Auth::user()->isGuru())
                    <div>
                        <div class="px-2 mb-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 font-heading">
                            Kembali ke Panel Guru
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('guru.dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold text-slate-300 border-2 border-transparent hover:bg-slate-800/90 hover:text-white hover:border-slate-700 cursor-pointer">
                                <svg class="w-4 h-4 shrink-0 text-[#FFD43B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                                </svg>
                                <span>Panel Utama Guru</span>
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t-4 border-black bg-[#1E293B] shrink-0 space-y-2.5">
                <div class="flex items-center gap-2.5 p-2 bg-[#0F172A] border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                    <div class="w-8 h-8 rounded-md bg-[#FFD43B] border-2 border-black flex items-center justify-center font-bold text-xs shrink-0 shadow-[1px_1px_0px_0px_#000]">
                        🍱
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] font-semibold text-[#FFD43B] flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#20C997]"></span>
                            <span>{{ Auth::user()->role === 'admin' ? 'Admin / Pengawas MBG' : 'Petugas MBG' }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
        <!-- Topbar -->
        <header class="h-20 bg-white border-b-4 border-black px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-[0_4px_0_0_rgba(0,0,0,0.06)]">
            <div class="flex items-center gap-4">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg border-2 border-black bg-white shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-base sm:text-lg font-black font-heading tracking-tight">@yield('page_title', 'Makan Bergizi Gratis (MBG)')</h1>
                    <p class="text-[11px] font-semibold text-slate-500 hidden sm:block">Pencatatan Penyaluran & Rekapitulasi Konsumsi Siswa dan Guru</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg border-2 border-black bg-slate-100 text-xs font-bold shadow-[2px_2px_0px_0px_#000]">
                    <span>📅</span>
                    <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-4 sm:mx-8 mt-6 p-4 rounded-xl border-2 border-black bg-[#D1F2D9] text-[#0A5C28] font-bold text-xs sm:text-sm flex items-center justify-between shadow-[3px_3px_0px_0px_#000]">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-600 hover:text-black font-black">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-4 sm:mx-8 mt-6 p-4 rounded-xl border-2 border-black bg-[#FFE3E3] text-[#A61E1E] font-bold text-xs sm:text-sm flex items-center justify-between shadow-[3px_3px_0px_0px_#000]">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-600 hover:text-black font-black">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-4 sm:mx-8 mt-6 p-4 rounded-xl border-2 border-black bg-[#FFE3E3] text-[#A61E1E] text-xs sm:text-sm shadow-[3px_3px_0px_0px_#000]">
                <div class="font-black mb-1 flex items-center gap-2">
                    <span>⚠️</span>
                    <span>Terjadi beberapa kesalahan:</span>
                </div>
                <ul class="list-disc list-inside font-semibold space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Body -->
        <main class="flex-1 p-4 sm:p-8">
            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobileBackdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
    @include('partials._sidebar-scroll-persistence', ['portalKey' => 'mbg'])
    @stack('scripts')
</body>
</html>
