@extends('layouts.neobrutalism')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="max-w-md mx-auto my-8">
    <!-- Header Box -->
    <div class="bg-[#FFD43B] neo-box p-6 mb-6 text-center relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-20 h-20 bg-white/40 rounded-full border-2 border-black pointer-events-none"></div>
        <span class="neo-badge bg-white text-black mb-2 inline-block">PORTAL AUTENTIKASI</span>
        <h1 class="font-heading text-2xl sm:text-3xl font-black text-black tracking-tight mt-1">
            MASUK KE SISTEM
        </h1>
        <p class="text-xs font-semibold text-slate-800 mt-1">
            Gunakan Email, NIP (Guru), atau NIS (Siswa) Anda.
        </p>
    </div>

    <!-- Login Card -->
    <div class="bg-white neo-box-lg p-6 sm:p-8">
        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Identifier Input -->
            <div>
                <label for="login_identifier" class="block font-heading font-bold text-sm text-black mb-1.5 flex items-center justify-between">
                    <span>Email / NIP / NIS</span>
                    <span class="text-[10px] font-mono text-slate-500 font-normal">Universal ID</span>
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
                        class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-medium text-sm text-black placeholder:text-slate-400 @error('login_identifier') border-rose-600 bg-rose-50 @enderror"
                    >
                </div>
                @error('login_identifier')
                    <p class="text-xs font-bold text-rose-600 mt-1.5 flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block font-heading font-bold text-sm text-black mb-1.5">
                    Kata Sandi
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••"
                    required 
                    class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-medium text-sm text-black placeholder:text-slate-400 @error('password') border-rose-600 bg-rose-50 @enderror"
                >
                @error('password')
                    <p class="text-xs font-bold text-rose-600 mt-1.5 flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-2 border-black text-[#5294FF] focus:ring-0">
                    <span class="text-xs font-bold text-slate-700">Ingat Saya</span>
                </label>
                <span class="text-[11px] font-semibold text-slate-500">Default: password</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white py-3 text-sm uppercase tracking-wider font-heading cursor-pointer">
                Masuk Sekarang →
            </button>
        </form>

        <!-- Quick Demo Accounts Helper -->
        <div class="mt-8 pt-6 border-t-2 border-dashed border-slate-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black uppercase tracking-wider text-slate-700">Akun Demo (1-Klik Isi)</span>
                <span class="text-[10px] bg-[#E7F5FF] text-[#1971C2] border border-[#1971C2] px-1.5 py-0.5 font-bold">Siap Uji</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-left">
                <!-- Admin -->
                <button type="button" onclick="fillCredential('admin@sekolah.sch.id', 'password')" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-left p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-rose-700 flex items-center gap-1">
                        <span>👑</span> Admin
                    </span>
                    <span class="font-mono text-[10px] text-slate-600 truncate">admin@sekolah.sch.id</span>
                </button>

                <!-- Guru -->
                <button type="button" onclick="fillCredential('198501012010011001', 'password')" class="neo-btn bg-[#E7F5FF] hover:bg-[#d0ebff] text-left p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-blue-700 flex items-center gap-1">
                        <span>👨‍🏫</span> Guru (NIP)
                    </span>
                    <span class="font-mono text-[10px] text-slate-600 truncate">198501012010011001</span>
                </button>

                <!-- Siswa -->
                <button type="button" onclick="fillCredential('12345', 'password')" class="neo-btn bg-[#D3F9D8] hover:bg-[#b2f2bb] text-left p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-emerald-800 flex items-center gap-1">
                        <span>🎓</span> Siswa (NIS)
                    </span>
                    <span class="font-mono text-[10px] text-slate-600 truncate">NIS: 12345</span>
                </button>

                <!-- Orang Tua -->
                <button type="button" onclick="fillCredential('ortu@sekolah.sch.id', 'password')" class="neo-btn bg-[#FFF3BF] hover:bg-[#ffec99] text-left p-2.5 text-xs flex flex-col gap-0.5 cursor-pointer">
                    <span class="font-black text-amber-800 flex items-center gap-1">
                        <span>👨‍👩‍👧</span> Orang Tua
                    </span>
                    <span class="font-mono text-[10px] text-slate-600 truncate">ortu@sekolah.sch.id</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillCredential(id, pass) {
        document.getElementById('login_identifier').value = id;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
