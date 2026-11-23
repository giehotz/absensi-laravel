<!-- Page Header & Primary Actions -->
<div class="rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1.5">
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-[#FFD43B] shadow-[2px_2px_0px_0px_#000]">
                    <svg class="h-5 w-5 text-black" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-black tracking-tight text-black">
                    Daftar Guru & Tenaga Pendidik
                </h1>
                <span class="inline-flex items-center rounded-md border-2 border-black bg-[#FFF9DB] px-2.5 py-0.5 text-xs font-extrabold text-black shadow-[2px_2px_0px_0px_#000] tabular-nums">
                    {{ $teachers->total() }} Guru
                </span>
            </div>
            <p class="text-xs font-medium text-slate-600 sm:text-sm">
                Kelola profil guru, akun login (NUPTK / NIP), penugasan mengajar, dan wewenang tabungan siswa.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-2.5 sm:flex sm:flex-wrap sm:items-center">
            <a href="{{ route('admin.teachers.template') }}"
               class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-lg border-2 border-black bg-[#FFF9DB] px-3.5 py-2 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FFEC99] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
               title="Unduh Format Template Excel 9 Kolom">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Template Excel</span>
            </a>

            <button type="button"
                    onclick="openModal('importTeacherModal')"
                    class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-black bg-[#FFD43B] px-3.5 py-2 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Import Excel</span>
            </button>

            <button type="button"
                    onclick="openModal('createTeacherModal')"
                    class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-black bg-[#20C997] px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#12B886] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Guru</span>
            </button>
        </div>
    </div>
</div>
