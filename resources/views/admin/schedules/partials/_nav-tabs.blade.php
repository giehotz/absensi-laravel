<!-- Navigation Tabs & Toggle Bar -->
<div class="flex flex-col xl:flex-row xl:items-center justify-between border-b-2 border-black gap-2">
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.schedules.index') }}" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm">
            📅 Plot Jadwal Kelas
        </a>
        <a href="{{ route('admin.schedules.slots.index') }}" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
            ⚙️ Template Jam Simpatika / EMIS GTK
        </a>
    </div>

    <!-- Kontrol Akses Wali Kelas: Toggle Switch & Batas Waktu Otomatis -->
    <div class="flex flex-wrap items-center gap-2 pb-2 xl:pb-0 px-1 xl:px-0">
        <!-- 1. Toggle Manual -->
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            <span class="text-xs">🔐</span>
            <div class="text-left">
                <span class="block text-[10px] font-black uppercase text-black leading-none">Akses Wali Kelas:</span>
                <span id="homeroomToggleStatusText" class="text-[10px] font-bold {{ ($canHomeroomEdit ?? false) ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ ($canHomeroomEdit ?? false) ? '● DIBUKA (Dapat Input)' : '○ DITUTUP (Hanya Baca)' }}
                </span>
            </div>
            <button type="button" 
                    id="homeroomToggleBtn"
                    onclick="toggleHomeroomScheduleAccess()"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-black transition-colors duration-200 ease-in-out focus:outline-hidden {{ (($setting->can_homeroom_edit_schedule ?? false)) ? 'bg-[#20C997]' : 'bg-slate-300' }}"
                    title="Buka / Tutup hak akses Wali Kelas untuk menginput jadwal kelas binaannya">
                <span id="homeroomToggleThumb" 
                      class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white border border-black shadow-sm transition duration-200 ease-in-out mt-0.5 ml-0.5 {{ (($setting->can_homeroom_edit_schedule ?? false)) ? 'translate-x-5' : 'translate-x-0' }}">
                </span>
            </button>
        </div>

        <!-- 2. Form Batas Waktu Otomatis (Deadline) -->
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            <span class="text-xs">⏰</span>
            <div class="text-left">
                <label for="homeroomDeadlineInput" class="block text-[10px] font-black uppercase text-black leading-none mb-0.5">
                    Batas Waktu Otomatis:
                </label>
                <div class="flex items-center gap-1.5">
                    <input type="date" 
                           id="homeroomDeadlineInput" 
                           value="{{ ($homeroomDeadline ?? false) ? \Carbon\Carbon::parse($homeroomDeadline)->format('Y-m-d') : '' }}"
                           onchange="saveHomeroomDeadline()"
                           class="bg-[#FFF9DB] border border-black px-1.5 py-0.5 text-[11px] font-mono font-bold text-black focus:outline-hidden">
                    
                    <button type="button" 
                            id="clearDeadlineBtn"
                            onclick="clearHomeroomDeadline()"
                            title="Hapus batas waktu (berlaku tanpa batas)"
                            class="text-rose-700 hover:text-black font-black text-xs px-1 border border-black bg-rose-50 hover:bg-rose-100 rounded cursor-pointer {{ ($homeroomDeadline ?? false) ? '' : 'hidden' }}">
                        ✕
                    </button>
                </div>
            </div>
            <div id="deadlineInfoBadge" class="text-[10px] font-bold border border-black px-1.5 py-0.5 rounded
                @if(!($homeroomDeadline ?? false))
                    bg-slate-100 text-slate-600
                @elseif($isDeadlineExpired ?? false)
                    bg-[#FFE3E3] text-rose-950
                @else
                    bg-[#D3F9D8] text-emerald-950
                @endif">
                @if(!($homeroomDeadline ?? false))
                    Tanpa Batas
                @elseif($isDeadlineExpired ?? false)
                    Berakhir {{ \Carbon\Carbon::parse($homeroomDeadline)->translatedFormat('d M Y') }}
                @else
                    s.d. {{ \Carbon\Carbon::parse($homeroomDeadline)->translatedFormat('d M Y') }}
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function updateHomeroomStatusUI(data) {
    const btn = document.getElementById('homeroomToggleBtn');
    const thumb = document.getElementById('homeroomToggleThumb');
    const statusText = document.getElementById('homeroomToggleStatusText');
    const clearBtn = document.getElementById('clearDeadlineBtn');
    const deadlineInput = document.getElementById('homeroomDeadlineInput');
    const badge = document.getElementById('deadlineInfoBadge');

    if (btn && thumb) {
        if (data.manual_enabled) {
            btn.className = 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-black transition-colors duration-200 ease-in-out focus:outline-hidden bg-[#20C997]';
            thumb.className = 'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white border border-black shadow-sm transition duration-200 ease-in-out mt-0.5 ml-0.5 translate-x-5';
        } else {
            btn.className = 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-black transition-colors duration-200 ease-in-out focus:outline-hidden bg-slate-300';
            thumb.className = 'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white border border-black shadow-sm transition duration-200 ease-in-out mt-0.5 ml-0.5 translate-x-0';
        }
    }

    if (statusText) {
        if (data.is_open) {
            statusText.className = 'text-[10px] font-bold text-emerald-700';
            statusText.innerText = '● DIBUKA (Dapat Input)';
        } else {
            statusText.className = 'text-[10px] font-bold text-rose-700';
            if (data.is_expired) {
                statusText.innerText = '○ DITUTUP (Waktu Berakhir)';
            } else {
                statusText.innerText = '○ DITUTUP (Hanya Baca)';
            }
        }
    }

    if (badge) {
        if (!data.deadline) {
            badge.className = 'text-[10px] font-bold border border-black px-1.5 py-0.5 rounded bg-slate-100 text-slate-600';
            badge.innerText = 'Tanpa Batas';
            if (clearBtn) clearBtn.classList.add('hidden');
        } else if (data.is_expired) {
            badge.className = 'text-[10px] font-bold border border-black px-1.5 py-0.5 rounded bg-[#FFE3E3] text-rose-950';
            badge.innerText = 'Berakhir ' + (data.formatted_deadline || data.deadline);
            if (clearBtn) clearBtn.classList.remove('hidden');
        } else {
            badge.className = 'text-[10px] font-bold border border-black px-1.5 py-0.5 rounded bg-[#D3F9D8] text-emerald-950';
            badge.innerText = 's.d. ' + (data.formatted_deadline || data.deadline);
            if (clearBtn) clearBtn.classList.remove('hidden');
        }
    }
}

function toggleHomeroomScheduleAccess() {
    const btn = document.getElementById('homeroomToggleBtn');
    if (!btn) return;

    btn.disabled = true;
    btn.style.opacity = '0.6';

    fetch('{{ route('admin.schedules.toggle-homeroom-access') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.style.opacity = '1';
        if (data.success) {
            updateHomeroomStatusUI(data);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.style.opacity = '1';
        alert('Gagal mengubah akses jadwal wali kelas. Silakan coba lagi.');
    });
}

function saveHomeroomDeadline() {
    const input = document.getElementById('homeroomDeadlineInput');
    const deadlineVal = input ? input.value : '';

    fetch('{{ route('admin.schedules.set-homeroom-deadline') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            deadline: deadlineVal
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateHomeroomStatusUI(data);
        }
    })
    .catch(err => {
        alert('Gagal menyimpan batas waktu otomatis. Silakan coba lagi.');
    });
}

function clearHomeroomDeadline() {
    const input = document.getElementById('homeroomDeadlineInput');
    if (input) input.value = '';
    saveHomeroomDeadline();
}
</script>
