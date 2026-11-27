@extends('layouts.supervisi')
@section('title', 'Step Form Siklus Supervisi Guru')
@section('page_title', 'Pengisian Siklus Supervisi Guru (Step Form)')

@section('content')
@php
    $doneCount = collect($stepStates)->where('state', 'done')->count();
@endphp

<!-- Ringkasan Jadwal & Checklist Progres Warna -->
<section class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="text-xs font-bold text-slate-500 uppercase">{{ $jadwal->academicYear?->name }} ({{ ucfirst($jadwal->academicYear?->semester ?? '') }}) · {{ $jadwal->tanggal?->format('d/m/Y') }}</div>
            <h2 class="font-heading font-black text-lg text-slate-900">{{ $jadwal->schoolClass?->name ?? '-' }} — {{ $jadwal->subject?->name ?? 'Mapel Umum' }}</h2>
            <p class="text-xs font-semibold text-slate-600">
                Supervisor: {{ $jadwal->supervisor_display_name }} · Status Siklus:
                <span class="font-black text-slate-900">{{ $jadwal->statusEnum()->label() }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1.5 rounded-lg border-2 border-black bg-[#FFD43B] text-xs font-black tabular-nums">
                Checklist Selesai: {{ $doneCount }} / {{ count($stepStates) }}
            </span>
            <a href="{{ route('supervisi.guru.jadwal.index') }}" class="px-3 py-1.5 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-xs font-black shadow-[2px_2px_0px_0px_#000]">
                ← Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Legenda Warna Status -->
    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold">
        <span class="px-2 py-0.5 rounded border border-black bg-emerald-200 text-emerald-950">✓ Hijau: Selesai Diisi</span>
        <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-black">⏳ Kuning: Belum Lengkap / Draf</span>
        <span class="px-2 py-0.5 rounded border border-black bg-rose-200 text-rose-950">✕ Merah: Belum Diisi</span>
    </div>

    <!-- Stepper Checklist Navigasi 4 Tahap -->
    <nav id="guru-siklus-stepper" aria-label="Tahapan Pengisian Supervisi Guru" class="pt-3 border-t-2 border-slate-200">
        <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($stepStates as $st)
                @php $isActive = (int) $activeStep === (int) $st['step']; @endphp
                <li>
                    <button type="button"
                        data-guru-step-nav="{{ $st['step'] }}"
                        aria-current="{{ $isActive ? 'step' : 'false' }}"
                        class="w-full text-left p-3 rounded-xl border-2 border-black transition-all cursor-pointer shadow-[2px_2px_0px_0px_#000] {{ $st['card_class'] }} {{ $isActive ? 'ring-4 ring-black' : 'opacity-90 hover:opacity-100' }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-black uppercase text-slate-700">Step {{ $st['step'] }}</span>
                            <span class="px-2 py-0.5 rounded border border-black text-[10px] font-black {{ $st['badge_class'] }}">
                                {{ $st['icon'] }} {{ $st['status_label'] }}
                            </span>
                        </div>
                        <div class="font-heading font-black text-xs text-slate-900 mt-1">{{ $st['title'] }}</div>
                        <div class="text-[11px] text-slate-700 mt-0.5 line-clamp-2">{{ $st['description'] }}</div>
                    </button>
                </li>
            @endforeach
        </ol>
    </nav>
</section>

<!-- Step 1: Perangkat Ajar -->
<section id="guru-step-panel-1" data-guru-step-panel="1" {{ (int) $activeStep === 1 ? '' : 'hidden' }} class="space-y-4">
    <div class="flex items-center justify-between gap-2">
        <h3 id="guru-step-heading-1" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
            Step 1: {{ $stepStates[0]['icon'] }} Perangkat Ajar ({{ $stepStates[0]['status_label'] }})
        </h3>
    </div>

    @include('supervisi.guru.partials.step_perangkat', ['redirectTo' => 'siklus'])

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex justify-end">
        <button type="button" data-guru-go-step="2" enterkeyhint="next" class="px-4 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            Lanjut ke Step 2 (Form A) →
        </button>
    </div>
</section>

<!-- Step 2: Form A (Pra-Observasi) -->
<section id="guru-step-panel-2" data-guru-step-panel="2" {{ (int) $activeStep === 2 ? '' : 'hidden' }} class="space-y-4">
    <div class="flex items-center justify-between gap-2">
        <h3 id="guru-step-heading-2" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
            Step 2: {{ $stepStates[1]['icon'] }} Formulir A — Pra-Observasi ({{ $stepStates[1]['status_label'] }})
        </h3>
    </div>

    @include('supervisi.guru.partials.step_pra_observasi', ['redirectTo' => 'siklus'])

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap justify-between gap-2">
        <button type="button" data-guru-go-step="1" enterkeyhint="previous" class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
            ← Kembali ke Step 1
        </button>
        <button type="button" data-guru-go-step="3" enterkeyhint="next" class="px-4 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            Lanjut ke Step 3 (Form C &amp; D) →
        </button>
    </div>
</section>

<!-- Step 3: Form C & D (Tindak Lanjut) -->
<section id="guru-step-panel-3" data-guru-step-panel="3" {{ (int) $activeStep === 3 ? '' : 'hidden' }} class="space-y-4">
    <div class="flex items-center justify-between gap-2">
        <h3 id="guru-step-heading-3" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
            Step 3: {{ $stepStates[2]['icon'] }} Formulir C &amp; D — Tindak Lanjut ({{ $stepStates[2]['status_label'] }})
        </h3>
    </div>

    @include('supervisi.guru.partials.step_tindak_lanjut', ['redirectTo' => 'siklus'])

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap justify-between gap-2">
        <button type="button" data-guru-go-step="2" enterkeyhint="previous" class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
            ← Kembali ke Step 2
        </button>
        <button type="button" data-guru-go-step="4" enterkeyhint="next" class="px-4 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            Lanjut ke Step 4 (Hasil &amp; TTD) →
        </button>
    </div>
</section>

<!-- Step 4: Hasil & TTD -->
<section id="guru-step-panel-4" data-guru-step-panel="4" {{ (int) $activeStep === 4 ? '' : 'hidden' }} class="space-y-4">
    <div class="flex items-center justify-between gap-2">
        <h3 id="guru-step-heading-4" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
            Step 4: {{ $stepStates[3]['icon'] }} Hasil Penilaian &amp; TTD Digital ({{ $stepStates[3]['status_label'] }})
        </h3>
    </div>

    @include('supervisi.guru.partials.step_hasil_ttd', ['redirectTo' => 'siklus'])

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex justify-start">
        <button type="button" data-guru-go-step="3" enterkeyhint="previous" class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
            ← Kembali ke Step 3
        </button>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    const stepButtons = Array.from(document.querySelectorAll('[data-guru-step-nav]'));
    const stepPanels = Array.from(document.querySelectorAll('[data-guru-step-panel]'));
    let currentStep = @json((int) $activeStep);

    function switchGuruStep(targetStep) {
        if (targetStep === currentStep) {
            return;
        }
        currentStep = targetStep;

        stepPanels.forEach((panel) => {
            const pStep = parseInt(panel.dataset.guruStepPanel || '0', 10);
            panel.hidden = pStep !== currentStep;
        });

        stepButtons.forEach((btn) => {
            const bStep = parseInt(btn.dataset.guruStepNav || '0', 10);
            const isCurrent = bStep === currentStep;
            btn.setAttribute('aria-current', isCurrent ? 'step' : 'false');
            btn.classList.toggle('ring-4', isCurrent);
            btn.classList.toggle('ring-black', isCurrent);
            btn.classList.toggle('opacity-90', !isCurrent);
        });

        const url = new URL(window.location.href);
        url.searchParams.set('step', String(currentStep));
        window.history.replaceState({}, '', url.toString());

        const heading = document.getElementById(`guru-step-heading-${currentStep}`);
        heading?.focus({ preventScroll: true });
        document.getElementById('guru-siklus-stepper')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    stepButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            switchGuruStep(parseInt(btn.dataset.guruStepNav || '1', 10));
        });
    });

    document.querySelectorAll('[data-guru-go-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            switchGuruStep(parseInt(btn.dataset.guruGoStep || '1', 10));
        });
    });

    const signDialog = document.getElementById('dialogGuruSiklusSign');
    document.querySelector('[data-open-sign-dialog]')?.addEventListener('click', () => {
        signDialog?.showModal();
    });
    document.querySelector('[data-close-sign-dialog]')?.addEventListener('click', () => {
        signDialog?.close();
    });
})();
</script>
@endpush
