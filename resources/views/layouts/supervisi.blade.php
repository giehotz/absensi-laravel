<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supervisi Guru') - {{ config('app.name', 'Sistem Madrasah') }}</title>
    @php
        $schoolSetting = \App\Models\AttendanceSetting::first();
        $roles = $supervisiRoles ?? \App\Http\Middleware\EnsureSupervisiRole::resolveRoles(Auth::user());
        $activeRole = $activeSupervisiRole ?? ($roles[0] ?? 'guru');
        $roleLabels = $supervisiRoleLabels ?? \App\Http\Middleware\EnsureSupervisiRole::ROLE_LABELS;
    @endphp
    @if(!empty($schoolSetting?->favicon) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->favicon))
        <link rel="icon" href="{{ asset('storage/' . $schoolSetting->favicon) }}">
    @elseif(!empty($schoolSetting?->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
        <link rel="icon" href="{{ asset('storage/' . $schoolSetting->logo) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        dialog::backdrop {
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(2px);
            overflow: hidden;
            overscroll-behavior: contain;
        }
        dialog:modal {
            overscroll-behavior: contain;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-900 flex antialiased">
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/70 z-40 hidden lg:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0F172A] border-r-4 border-black flex flex-col justify-between transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0 shadow-[4px_0px_0px_0px_rgba(0,0,0,0.2)]">
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div class="h-20 border-b-4 border-black px-5 flex items-center justify-between bg-[#1E293B] shrink-0">
                <a href="{{ route('supervisi.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg border-2 border-black bg-[#FFD43B] flex items-center justify-center text-lg font-black shrink-0 shadow-[2px_2px_0px_0px_#000]">
                        📋
                    </div>
                    <div class="min-w-0">
                        <span class="font-heading font-black text-sm text-white block truncate tracking-tight">SUPERVISI GURU</span>
                        <span class="text-[10px] font-bold text-[#FFD43B] uppercase tracking-wider block truncate">{{ $roleLabels[$activeRole] ?? ucfirst($activeRole) }}</span>
                    </div>
                </a>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1" aria-label="Tutup Menu">✕</button>
            </div>

            <!-- Role Switcher -->
            @if(count($roles) > 1)
                <div class="p-3 border-b-2 border-slate-800 bg-slate-900/80">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Ganti Peran Aktif</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($roles as $roleKey)
                            <form action="{{ route('supervisi.switch-role', $roleKey) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-2 py-1 rounded text-[10px] font-black border-2 border-black transition-all cursor-pointer
                                    {{ $activeRole === $roleKey ? 'bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">
                                    {{ $roleLabels[$roleKey] ?? ucfirst($roleKey) }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1.5">
                @if($activeRole === 'admin')
                    <div class="px-2 mb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Menu Admin</div>
                    @php
                        $adminMenus = [
                            ['route' => 'supervisi.admin.dashboard', 'match' => 'supervisi.admin.dashboard', 'icon' => '📊', 'label' => 'Dashboard Admin'],
                            ['route' => 'supervisi.admin.supervisors.index', 'match' => 'supervisi.admin.supervisors.*', 'icon' => '🧑‍🏫', 'label' => 'Tim Supervisor'],
                            ['route' => 'supervisi.admin.instrumen.index', 'match' => 'supervisi.admin.instrumen.*', 'icon' => '📐', 'label' => 'Instrumen & Aspek'],
                            ['route' => 'supervisi.admin.jadwal.index', 'match' => 'supervisi.admin.jadwal.*', 'icon' => '📅', 'label' => 'Jadwal Supervisi'],
                            ['route' => 'supervisi.admin.coaching.index', 'match' => 'supervisi.admin.coaching.*', 'icon' => '💬', 'label' => 'Coaching TIRTA'],
                            ['route' => 'supervisi.admin.laporan.index', 'match' => 'supervisi.admin.laporan.*', 'icon' => '📁', 'label' => 'Hasil & Laporan'],
                        ];
                    @endphp
                    @foreach($adminMenus as $m)
                        <a href="{{ route($m['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all {{ request()->routeIs($m['match']) ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000] translate-x-1' : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800 hover:text-white' }}">
                            <span>{{ $m['icon'] }}</span><span>{{ $m['label'] }}</span>
                        </a>
                    @endforeach
                @elseif($activeRole === 'supervisor')
                    <div class="px-2 mb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Menu Supervisor</div>
                    @php
                        $supMenus = [
                            ['route' => 'supervisi.supervisor.dashboard', 'match' => 'supervisi.supervisor.dashboard', 'icon' => '📊', 'label' => 'Dashboard'],
                            ['route' => 'supervisi.supervisor.guru-binaan', 'match' => 'supervisi.supervisor.guru-binaan', 'icon' => '👥', 'label' => 'Guru Binaan'],
                            ['route' => 'supervisi.supervisor.jadwal.index', 'match' => 'supervisi.supervisor.jadwal.*', 'icon' => '📅', 'label' => 'Jadwal Observasi'],
                            ['route' => 'supervisi.supervisor.coaching.index', 'match' => 'supervisi.supervisor.coaching.*', 'icon' => '💬', 'label' => 'Coaching TIRTA'],
                            ['route' => 'supervisi.supervisor.hasil.index', 'match' => 'supervisi.supervisor.hasil.*', 'icon' => '✅', 'label' => 'Hasil & Cetak'],
                        ];
                    @endphp
                    @foreach($supMenus as $m)
                        <a href="{{ route($m['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all {{ request()->routeIs($m['match']) ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000] translate-x-1' : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800 hover:text-white' }}">
                            <span>{{ $m['icon'] }}</span><span>{{ $m['label'] }}</span>
                        </a>
                    @endforeach
                @elseif($activeRole === 'kepala')
                    <div class="px-2 mb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Menu Kepala Madrasah</div>
                    @php
                        $kepMenus = [
                            ['route' => 'supervisi.kepala.dashboard', 'match' => 'supervisi.kepala.dashboard', 'icon' => '📊', 'label' => 'Dashboard Eksekutif'],
                            ['route' => 'supervisi.kepala.performance', 'match' => 'supervisi.kepala.performance', 'icon' => '📈', 'label' => 'Performa Guru'],
                            ['route' => 'supervisi.kepala.jadwal.index', 'match' => 'supervisi.kepala.jadwal.*', 'icon' => '📅', 'label' => 'Jadwal Supervisi'],
                            ['route' => 'supervisi.kepala.hasil.index', 'match' => 'supervisi.kepala.hasil.*', 'icon' => '✍️', 'label' => 'Hasil & Pengesahan'],
                        ];
                    @endphp
                    @foreach($kepMenus as $m)
                        <a href="{{ route($m['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all {{ request()->routeIs($m['match']) ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000] translate-x-1' : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800 hover:text-white' }}">
                            <span>{{ $m['icon'] }}</span><span>{{ $m['label'] }}</span>
                        </a>
                    @endforeach
                @else
                    <div class="px-2 mb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Menu Guru</div>
                    @php
                        $guruMenus = [
                            ['route' => 'supervisi.guru.dashboard', 'match' => 'supervisi.guru.dashboard', 'icon' => '📊', 'label' => 'Dashboard Saya'],
                            ['route' => 'supervisi.guru.jadwal.index', 'match' => 'supervisi.guru.jadwal.*', 'icon' => '📅', 'label' => 'Jadwal Supervisi'],
                            ['route' => 'supervisi.guru.dokumen-ajar.index', 'match' => 'supervisi.guru.dokumen-ajar.*', 'icon' => '📂', 'label' => 'Perangkat Ajar'],
                            ['route' => 'supervisi.guru.hasil.index', 'match' => 'supervisi.guru.hasil.*', 'icon' => '🏆', 'label' => 'Hasil & TTD Digital'],
                        ];
                    @endphp
                    @foreach($guruMenus as $m)
                        <a href="{{ route($m['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all {{ request()->routeIs($m['match']) ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000] translate-x-1' : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800 hover:text-white' }}">
                            <span>{{ $m['icon'] }}</span><span>{{ $m['label'] }}</span>
                        </a>
                    @endforeach
                @endif

                <div class="pt-4 mt-4 border-t border-slate-800">
                    <div class="px-2 mb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">Portal Utama</div>
                    @if(Auth::user()->isAdmin() || Auth::user()->isKepala())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold text-slate-300 hover:bg-slate-800 hover:text-white">
                            <span>←</span><span>Kembali ke Panel Induk</span>
                        </a>
                    @elseif(Auth::user()->isGuru())
                        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold text-slate-300 hover:bg-slate-800 hover:text-white">
                            <span>←</span><span>Kembali ke Panel Guru</span>
                        </a>
                    @endif
                </div>
            </nav>

            <!-- Footer -->
            <div class="p-3 border-t-4 border-black bg-[#1E293B] shrink-0">
                <div class="flex items-center gap-2.5 p-2 bg-[#0F172A] border-2 border-black rounded-lg">
                    <div class="w-8 h-8 rounded-md bg-[#FFD43B] border-2 border-black flex items-center justify-center font-black text-xs text-black shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] font-semibold text-[#20C997] truncate">{{ $roleLabels[$activeRole] ?? ucfirst($activeRole) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
        <header class="h-20 bg-white border-b-4 border-black px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-[0_4px_0_0_rgba(0,0,0,0.06)]">
            <div class="flex items-center gap-4">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg border-2 border-black bg-white shadow-[2px_2px_0px_0px_#000]" aria-label="Buka Menu">☰</button>
                <div>
                    <h1 class="font-heading font-black text-base sm:text-lg text-slate-900 tracking-tight leading-tight">@yield('page_title', 'Modul Supervisi Guru')</h1>
                    <p class="text-[11px] font-semibold text-slate-500">{{ $schoolSetting?->school_name ?? config('app.name') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg border-2 border-black bg-[#FFD43B] text-black text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                    {{ $roleLabels[$activeRole] ?? ucfirst($activeRole) }}
                </span>
            </div>
        </header>

        <main class="supervisi-portal flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-5">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-3.5 bg-emerald-100 border-2 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-sm font-bold text-emerald-950 flex items-center justify-between">
                    <span>✅ {{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div role="alert" aria-live="assertive" class="p-3.5 bg-rose-100 border-2 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-sm font-bold text-rose-950">
                    ⚠️ {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div role="alert" class="p-3.5 bg-rose-100 border-2 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-xs font-bold text-rose-950 space-y-1">
                    <div class="font-black">Periksa kembali input Anda:</div>
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobileBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.supervisi-portal table').forEach((table, tableIdx) => {
                if (table.classList.contains('no-responsive-table')) {
                    return;
                }

                const headers = Array.from(table.querySelectorAll('thead tr th'));
                if (headers.length === 0) {
                    return;
                }

                headers.forEach((th, idx) => {
                    const hasCheckbox = th.querySelector('input[type="checkbox"]') !== null;
                    const rawText = (th.textContent || '').replace(/\s+/g, ' ').trim();
                    const label = hasCheckbox || rawText === '' || rawText === '✓' ? 'Pilih' : rawText;
                    table.style.setProperty(`--label-${idx + 1}`, JSON.stringify(label));
                });

                const parent = table.parentElement;
                let wrap = parent;
                const needsInnerWrap = !parent
                    || (!parent.classList.contains('overflow-x-auto') && !parent.classList.contains('supervisi-table-wrap'))
                    || parent.children.length > 1;

                if (needsInnerWrap) {
                    wrap = document.createElement('div');
                    parent?.insertBefore(wrap, table);
                    wrap.appendChild(table);
                }

                wrap.classList.add('supervisi-table-wrap');
                if (!wrap.hasAttribute('tabindex')) {
                    wrap.setAttribute('tabindex', '0');
                }
                if (!wrap.hasAttribute('role')) {
                    wrap.setAttribute('role', 'region');
                }
                if (!wrap.hasAttribute('aria-label') && !wrap.hasAttribute('aria-labelledby')) {
                    const captionText = table.querySelector('caption')?.textContent?.trim();
                    const nearestHeading = table.closest('.bg-white')?.querySelector('h1, h2, h3')?.textContent?.trim();
                    wrap.setAttribute('aria-label', captionText || nearestHeading || `Tabel Data ${tableIdx + 1}`);
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
