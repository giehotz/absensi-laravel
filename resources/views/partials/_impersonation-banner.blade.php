@if(session()->has('impersonator_id'))
<div class="mb-6 flex flex-col gap-3 rounded-xl border-2 border-black bg-[#FFF3BF] p-4 shadow-[4px_4px_0px_0px_#000] sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-[#FFD43B] shadow-[2px_2px_0px_0px_#000]">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-black text-black">
                Mode Impersonasi Aktif
            </p>
            <p class="mt-0.5 text-xs font-semibold text-slate-700">
                Anda masuk sebagai <strong class="font-black text-black">{{ Auth::user()->name }}</strong> (guru)
                melalui akun admin <strong class="font-black text-black">{{ session('impersonator_name', 'Admin') }}</strong>.
                Seluruh aktivitas akan tercatat atas nama akun guru ini.
            </p>
        </div>
    </div>
    <form action="{{ route('impersonation.stop') }}" method="POST" class="shrink-0">
        @csrf
        <button type="submit"
                class="inline-flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-black bg-[#FF6B6B] px-3.5 py-2 text-xs font-extrabold uppercase tracking-wide text-white shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FA5252] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h13m0 0l-4-4m4 4l-4 4M10 4H6a2 2 0 00-2 2v12a2 2 0 002 2h4"/>
            </svg>
            <span>Kembali ke Admin</span>
        </button>
    </form>
</div>
@endif
