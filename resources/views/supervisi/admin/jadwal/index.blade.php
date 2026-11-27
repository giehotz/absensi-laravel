@extends('layouts.supervisi')
@section('title', 'Jadwal Supervisi')
@section('page_title', 'Manajemen Jadwal Supervisi')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="academic_year_id" aria-label="Filter Tahun Ajaran" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
            @foreach($academicYears as $yr)
                <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-2 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Filter</button>
    </form>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('supervisi.admin.jadwal.create') }}" class="px-3.5 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">+ Tambah Manual</a>
        <a href="{{ route('supervisi.admin.jadwal.generate') }}" class="px-3.5 py-2 bg-[#5294FF] text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">⚡ Generate Otomatis</a>
        <a href="{{ route('supervisi.admin.jadwal.print', ['academic_year_id' => $selectedYearId]) }}" target="_blank" class="px-3.5 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Jadwal</a>
    </div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <div class="flex flex-wrap justify-between items-center gap-2">
        <div>
            <h2 class="font-heading font-black text-sm">Daftar Jadwal ({{ $jadwals->count() }})</h2>
            <p class="text-[11px] font-semibold text-slate-600">Penghapusan data (termasuk jadwal bernilai) hanya dapat dilakukan oleh Admin dengan konfirmasi token 6 digit.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <label class="sm:hidden inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 text-xs font-black rounded-lg border-2 border-black cursor-pointer">
                <input type="checkbox" id="select-all-jadwal-mobile" class="cursor-pointer">
                <span>Pilih Semua</span>
            </label>
            <button type="button" id="btn-open-bulk-delete"
                class="px-3 py-1.5 bg-rose-200 hover:bg-rose-300 text-rose-950 text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                🗑️ Hapus Terpilih (<span id="selected-jadwal-count">0</span>)
            </button>
        </div>
    </div>

    <div id="jadwal-selection-alert" role="status" aria-live="polite" hidden
        class="p-2.5 rounded-lg border-2 border-black bg-amber-100 text-amber-950 text-xs font-bold"></div>

    <div tabindex="0" role="region" aria-label="Tabel Daftar Jadwal Supervisi" class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black w-8 text-center">
                        <input type="checkbox" id="select-all-jadwal" aria-label="Pilih semua jadwal" class="cursor-pointer">
                    </th>
                    <th class="p-2 border border-black text-left">Tanggal &amp; Jam</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                    <th class="p-2 border border-black text-left">Status</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $j)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black text-center">
                            <input type="checkbox"
                                class="jadwal-select-checkbox cursor-pointer"
                                value="{{ $j->id }}"
                                data-label="{{ $j->teacher?->user?->name }} ({{ $j->tanggal?->format('d/m/Y') }})"
                                data-has-results="{{ $j->hasils_count > 0 ? '1' : '0' }}"
                                aria-label="Pilih jadwal {{ $j->teacher?->user?->name }}">
                        </td>
                        <td class="p-2 border border-black">
                            <div class="font-black">{{ $j->tanggal?->format('d/m/Y') }} ({{ $j->hari }})</div>
                            <div class="text-[11px] text-slate-600">Jam ke-{{ $j->jam_ke ?? '-' }} ({{ substr((string) $j->jam_mulai, 0, 5) }}–{{ substr((string) $j->jam_selesai, 0, 5) }})</div>
                        </td>
                        <td class="p-2 border border-black font-black">
                            <div>{{ $j->teacher?->user?->name }}</div>
                            @if($j->hasils_count > 0)
                                <span class="mt-1 inline-block px-1.5 py-0.5 rounded border border-black bg-amber-100 text-[10px] font-black">
                                    Ada Hasil ({{ $j->hasils_count }})
                                </span>
                            @endif
                        </td>
                        <td class="p-2 border border-black">{{ $j->supervisor_display_name }}</td>
                        <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td class="p-2 border border-black">
                            <span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span>
                        </td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex flex-wrap justify-center gap-1">
                                <a href="{{ route('supervisi.admin.penilaian.edit', $j) }}" class="px-2 py-1 bg-sky-100 border border-black rounded font-bold">Nilai</a>
                                <a href="{{ route('supervisi.admin.jadwal.edit', $j) }}" class="px-2 py-1 bg-amber-100 border border-black rounded font-bold">Edit</a>
                                <a href="{{ route('supervisi.admin.laporan.detail', $j) }}" class="px-2 py-1 bg-emerald-100 border border-black rounded font-bold">Detail</a>
                                <button type="button"
                                    data-delete-single-url="{{ route('supervisi.admin.jadwal.destroy', $j) }}"
                                    data-delete-label="{{ $j->teacher?->user?->name }} ({{ $j->tanggal?->format('d/m/Y') }})"
                                    data-has-results="{{ $j->hasils_count > 0 ? '1' : '0' }}"
                                    class="px-2 py-1 bg-rose-200 hover:bg-rose-300 text-rose-950 border border-black rounded font-black cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Dialog Konfirmasi Hapus Jadwal Khusus Admin dengan Token 6 Digit -->
<dialog id="dialogDeleteJadwalConfirm" aria-labelledby="delete-dialog-title"
    class="m-auto w-full max-w-md rounded-xl border-4 border-black bg-white p-5 shadow-[6px_6px_0px_0px_#000]">
    <form id="form-delete-jadwal-confirm" action="{{ route('supervisi.admin.jadwal.bulk-destroy') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="_method" id="delete-method-override" value="DELETE" disabled>
        <div id="delete-selected-ids-container"></div>

        <div class="space-y-1">
            <h3 id="delete-dialog-title" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
                Konfirmasi Penghapusan Jadwal (Khusus Admin)
            </h3>
            <p id="delete-dialog-summary" class="text-xs font-semibold text-slate-700 leading-relaxed"></p>
        </div>

        <div id="delete-results-warning" hidden class="p-3 rounded-lg border-2 border-black bg-rose-100 text-rose-950 text-xs font-bold leading-relaxed">
            ⚠️ Peringatan: Target mencakup jadwal yang <strong>sudah memiliki hasil penilaian</strong>. Menghapus jadwal ini akan menghapus permanen skor komponen, catatan, foto bukti, dokumen ajar, tindak lanjut, serta tanda tangan digital terkait.
        </div>

        <div class="p-3.5 rounded-lg border-2 border-black bg-[#FFF9DB] space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-700">Token Konfirmasi 6 Digit</span>
                <button type="button" id="btn-copy-delete-token"
                    class="px-2 py-0.5 rounded border border-black bg-white hover:bg-slate-100 text-[10px] font-black cursor-pointer">
                    📋 Salin Token
                </button>
            </div>
            <div id="display-delete-token" class="font-mono text-2xl font-black tracking-[0.35em] text-center py-2 rounded-lg border-2 border-dashed border-black bg-white select-all">
                {{ $deleteConfirmToken }}
            </div>
        </div>

        <div class="space-y-1">
            <label for="confirm_token" class="block text-xs font-black text-slate-900">
                Ketik Token 6 Digit di Atas untuk Konfirmasi <span aria-hidden="true" class="text-rose-600">*</span>
            </label>
            <input type="text"
                id="confirm_token"
                name="confirm_token"
                inputmode="numeric"
                pattern="\d{6}"
                maxlength="6"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="none"
                spellcheck="false"
                enterkeyhint="done"
                required
                placeholder="Contoh: {{ $deleteConfirmToken }}"
                class="w-full px-3 py-2.5 text-center font-mono text-base font-black tracking-[0.25em] border-2 border-black rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-700">
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t-2 border-slate-200">
            <button type="button" id="btn-cancel-delete-dialog"
                class="px-4 py-2 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-xs font-black cursor-pointer">
                Batal
            </button>
            <button type="submit"
                class="px-4 py-2 rounded-lg border-2 border-black bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                Konfirmasi &amp; Hapus Permanen
            </button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
(() => {
    const bulkRoute = @json(route('supervisi.admin.jadwal.bulk-destroy'));
    const tokenValue = @json($deleteConfirmToken);

    const selectAllCheckbox = document.getElementById('select-all-jadwal');
    const selectAllMobileCheckbox = document.getElementById('select-all-jadwal-mobile');
    const rowCheckboxes = Array.from(document.querySelectorAll('.jadwal-select-checkbox'));
    const countBadge = document.getElementById('selected-jadwal-count');
    const selectionAlert = document.getElementById('jadwal-selection-alert');

    const dialog = document.getElementById('dialogDeleteJadwalConfirm');
    const form = document.getElementById('form-delete-jadwal-confirm');
    const methodOverride = document.getElementById('delete-method-override');
    const idsContainer = document.getElementById('delete-selected-ids-container');
    const dialogSummary = document.getElementById('delete-dialog-summary');
    const resultsWarning = document.getElementById('delete-results-warning');
    const tokenInput = document.getElementById('confirm_token');
    const copyBtn = document.getElementById('btn-copy-delete-token');

    function updateSelectedCount() {
        const checkedCount = rowCheckboxes.filter((cb) => cb.checked).length;
        const allChecked = rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length;
        if (countBadge) {
            countBadge.textContent = String(checkedCount);
        }
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
        }
        if (selectAllMobileCheckbox) {
            selectAllMobileCheckbox.checked = allChecked;
        }
    }

    function applySelectAll(checked) {
        rowCheckboxes.forEach((cb) => {
            cb.checked = checked;
        });
        updateSelectedCount();
        if (selectionAlert) {
            selectionAlert.hidden = true;
        }
    }

    selectAllCheckbox?.addEventListener('change', () => applySelectAll(selectAllCheckbox.checked));
    selectAllMobileCheckbox?.addEventListener('change', () => applySelectAll(selectAllMobileCheckbox.checked));

    rowCheckboxes.forEach((cb) => {
        cb.addEventListener('change', () => {
            updateSelectedCount();
            if (selectionAlert) {
                selectionAlert.hidden = true;
            }
        });
    });

    function openDeleteModal({ actionUrl, useDeleteMethod, selectedIds, summaryText, hasAnyResults }) {
        if (!dialog || !form) {
            return;
        }

        form.action = actionUrl;
        if (methodOverride) {
            methodOverride.disabled = !useDeleteMethod;
        }

        if (idsContainer) {
            idsContainer.replaceChildren();
            selectedIds.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = String(id);
                idsContainer.appendChild(input);
            });
        }

        if (dialogSummary) {
            dialogSummary.textContent = summaryText;
        }
        if (resultsWarning) {
            resultsWarning.hidden = !hasAnyResults;
        }
        if (tokenInput) {
            tokenInput.value = '';
        }

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
            tokenInput?.focus();
        }
    }

    document.getElementById('btn-open-bulk-delete')?.addEventListener('click', () => {
        const selected = rowCheckboxes.filter((cb) => cb.checked);
        if (selected.length === 0) {
            if (selectionAlert) {
                selectionAlert.textContent = '⚠️ Pilih minimal satu jadwal terlebih dahulu sebelum menghapus.';
                selectionAlert.hidden = false;
            }
            return;
        }

        const selectedIds = selected.map((cb) => cb.value);
        const withResultsCount = selected.filter((cb) => cb.dataset.hasResults === '1').length;
        const summaryText = `Anda akan menghapus ${selected.length} jadwal supervisi terpilih${withResultsCount > 0 ? ` (${withResultsCount} di antaranya sudah memiliki hasil penilaian)` : ''}.`;

        openDeleteModal({
            actionUrl: bulkRoute,
            useDeleteMethod: false,
            selectedIds,
            summaryText,
            hasAnyResults: withResultsCount > 0,
        });
    });

    document.querySelectorAll('[data-delete-single-url]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const label = btn.dataset.deleteLabel || 'jadwal terpilih';
            const hasResults = btn.dataset.hasResults === '1';
            openDeleteModal({
                actionUrl: btn.dataset.deleteSingleUrl,
                useDeleteMethod: true,
                selectedIds: [],
                summaryText: `Anda akan menghapus jadwal supervisi untuk ${label}.`,
                hasAnyResults: hasResults,
            });
        });
    });

    copyBtn?.addEventListener('click', async () => {
        if (tokenInput) {
            tokenInput.value = tokenValue;
            tokenInput.focus();
        }
        if (navigator.clipboard?.writeText) {
            try {
                await navigator.clipboard.writeText(tokenValue);
                copyBtn.textContent = '✓ Tersalin';
                setTimeout(() => {
                    copyBtn.textContent = '📋 Salin Token';
                }, 1500);
            } catch (_) {
                // Clipboard API opsional bila HTTP non-HTTPS
            }
        }
    });

    document.getElementById('btn-cancel-delete-dialog')?.addEventListener('click', () => {
        dialog?.close();
    });

    updateSelectedCount();
})();
</script>
@endpush
