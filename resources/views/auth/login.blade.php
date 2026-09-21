@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="w-full max-w-md mx-auto my-auto space-y-5">
    <!-- Brand / School Header Banner -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center">
            @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-2xl border-4 border-black shadow-[5px_5px_0px_0px_#000] p-2 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo {{ $schoolSetting->school_name ?? 'Lembaga' }}" class="w-full h-full object-contain">
                </div>
            @elseif(!empty($schoolSetting->school_name))
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-[#FFD43B] text-black font-heading font-black text-2xl flex items-center justify-center rounded-2xl border-4 border-black shadow-[5px_5px_0px_0px_#000]">
                    {{ strtoupper(substr($schoolSetting->school_name, 0, 2)) }}
                </div>
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-[#FFD43B] text-black font-heading font-black text-3xl flex items-center justify-center rounded-2xl border-4 border-black shadow-[5px_5px_0px_0px_#000]">
                    🏫
                </div>
            @endif
        </div>
        
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-black text-white text-[10px] font-black uppercase tracking-wider rounded border border-black mb-1">
                <span>PORTAL RESMI</span>
            </div>
            <h1 class="font-heading font-black text-xl sm:text-2xl text-black tracking-tight leading-tight uppercase">
                {{ $schoolSetting->school_name ?? 'SISTEM ABSENSI DIGITAL' }}
            </h1>
            <p class="text-xs font-semibold text-slate-600">
                Presensi Terpadu & Kas Tabungan Siswa
            </p>
        </div>
    </div>

    <!-- Main Login Card -->
    <div class="bg-white neo-box-lg p-6 sm:p-8 border-4 border-black relative overflow-hidden space-y-6">
        <!-- Accent Top Bar -->
        <div class="border-b-2 border-black pb-4 text-center">
            <span class="neo-badge bg-[#5294FF] text-white text-[10px] font-black uppercase px-2 py-0.5 inline-block mb-1.5">
                AUTENTIKASI PENGGUNA
            </span>
            <h2 class="font-heading font-black text-2xl text-black uppercase tracking-tight">
                MASUK KE SISTEM
            </h2>
            <p class="text-xs font-medium text-slate-600 mt-0.5">
                Silakan masukkan kredensial akun terdaftar Anda.
            </p>
        </div>

        @if(session('error'))
            <div class="p-3 bg-[#FFE3E3] border-2 border-black text-rose-950 font-bold text-xs flex items-center gap-2 rounded-sm">
                <span class="text-base">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Identifier Input -->
            <div class="space-y-1.5">
                <label for="login_identifier" class="block font-heading font-black text-xs uppercase text-black flex items-center justify-between">
                    <span class="flex items-center gap-1">
                        <span>Email / NIP / NIS</span>
                        <span class="text-rose-600">*</span>
                    </span>
                    <span class="text-[10px] font-mono text-blue-900 bg-[#E7F5FF] border border-black px-1.5 py-0.2 font-bold rounded">
                        Universal ID
                    </span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="login_identifier" 
                        name="login_identifier" 
                        value="{{ old('login_identifier') }}" 
                        placeholder="Contoh: 12345 (NIS) / NIP / Email"
                        required 
                        autofocus
                        class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-medium text-xs sm:text-sm text-black placeholder:text-slate-400 @error('login_identifier') border-rose-600 bg-rose-50 @enderror"
                    >
                </div>
                @error('login_identifier')
                    <p class="text-[11px] font-bold text-rose-600 flex items-center gap-1 mt-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password Input with Show/Hide Eye Toggle -->
            <div class="space-y-1.5">
                <label for="password" class="block font-heading font-black text-xs uppercase text-black flex items-center justify-between">
                    <span class="flex items-center gap-1">
                        <span>Kata Sandi</span>
                        <span class="text-rose-600">*</span>
                    </span>
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required 
                        class="w-full px-3.5 py-2.5 pr-10 neo-input bg-slate-50 font-medium text-xs sm:text-sm text-black placeholder:text-slate-400 @error('password') border-rose-600 bg-rose-50 @enderror"
                    >
                    <button type="button" onclick="togglePasswordVisibility()" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                            title="Tampilkan / Sembunyikan Kata Sandi">
                        <span id="passwordToggleIcon">👁️</span>
                    </button>
                </div>
                @error('password')
                    <p class="text-[11px] font-bold text-rose-600 flex items-center gap-1 mt-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember Me & Hint -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-2 border-black text-[#5294FF] focus:ring-0 cursor-pointer">
                    <span class="font-bold text-slate-700 text-xs">Ingat Saya</span>
                </label>
                <span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-1.5 py-0.5 border border-slate-300 rounded font-mono">
                    Default: password
                </span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white py-3 text-xs sm:text-sm uppercase tracking-wider font-heading font-black flex items-center justify-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                <span>Masuk Sekarang</span>
                <span>→</span>
            </button>
        </form>

        <!-- Quick Demo Accounts Helper -->
        <div class="pt-5 border-t-2 border-dashed border-slate-300 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1">
                    <span>⚡</span> <span>Akun Demo (1-Klik Isi)</span>
                </span>
                <span class="text-[9px] bg-[#E7F5FF] text-[#1971C2] border border-[#1971C2] px-1.5 py-0.2 font-black uppercase rounded">
                    Siap Uji
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-left">
                <!-- Admin -->
                <button type="button" onclick="fillCredential('admin@sekolah.sch.id', 'password')" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-left p-2 sm:p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-rose-700 flex items-center gap-1 text-[11px]">
                        <span>👑</span> Admin
                    </span>
                    <span class="font-mono text-[9px] text-slate-600 truncate">admin@sekolah.sch.id</span>
                </button>

                <!-- Guru -->
                <button type="button" onclick="fillCredential('198501012010011001', 'password')" class="neo-btn bg-[#E7F5FF] hover:bg-[#d0ebff] text-left p-2 sm:p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-blue-700 flex items-center gap-1 text-[11px]">
                        <span>👨‍🏫</span> Guru (NIP)
                    </span>
                    <span class="font-mono text-[9px] text-slate-600 truncate">198501012010011001</span>
                </button>

                <!-- Siswa -->
                <button type="button" onclick="fillCredential('12345', 'password')" class="neo-btn bg-[#D3F9D8] hover:bg-[#b2f2bb] text-left p-2 sm:p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-emerald-800 flex items-center gap-1 text-[11px]">
                        <span>🎓</span> Siswa (NIS)
                    </span>
                    <span class="font-mono text-[9px] text-slate-600 truncate">NIS: 12345</span>
                </button>

                <!-- Orang Tua -->
                <button type="button" onclick="fillCredential('ortu@sekolah.sch.id', 'password')" class="neo-btn bg-[#FFF3BF] hover:bg-[#ffec99] text-left p-2 sm:p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-amber-800 flex items-center gap-1 text-[11px]">
                        <span>👨‍👩‍👧</span> Orang Tua
                    </span>
                    <span class="font-mono text-[9px] text-slate-600 truncate">ortu@sekolah.sch.id</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Back to Home Link -->
    <div class="text-center pt-1">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-black transition-colors">
            <span>←</span>
            <span>Kembali ke Halaman Beranda</span>
        </a>
    </div>
</div>

<script>
    function fillCredential(id, pass) {
        const idInput = document.getElementById('login_identifier');
        const passInput = document.getElementById('password');
        if (idInput && passInput) {
            idInput.value = id;
            passInput.value = pass;
            passInput.focus();
        }
    }

    function togglePasswordVisibility() {
        const passInput = document.getElementById('password');
        const toggleIcon = document.getElementById('passwordToggleIcon');
        if (!passInput) return;
        
        if (passInput.type === 'password') {
            passInput.type = 'text';
            if (toggleIcon) toggleIcon.textContent = '🙈';
        } else {
            passInput.type = 'password';
            if (toggleIcon) toggleIcon.textContent = '👁️';
        }
    }
</script>
@endsection
