{{-- ============================================================== --}}
{{-- STYLES KHUSUS JADWAL PENGAWAS (NEO-BRUTALISM)                 --}}
{{-- ============================================================== --}}
@push('styles')
<style>
    .tab-btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .875rem;
        border: 2px solid #0f172a;
        border-radius: .5rem;
        background: #ffffff;
        color: #0f172a;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 2px 2px 0 0 #0f172a;
        transition: all .12s ease;
    }
    .tab-btn:hover {
        background: #fef08a;
        transform: translate(-1px, -1px);
        box-shadow: 3px 3px 0 0 #0f172a;
    }
    .tab-btn:active {
        transform: translate(1px, 1px);
        box-shadow: 1px 1px 0 0 #0f172a;
    }
    .tab-btn.is-active {
        border-color: #0f172a;
        background: #FFD43B;
        color: #0f172a;
        box-shadow: 3px 3px 0 0 #0f172a;
    }
    .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.25rem;
        padding: 0 .375rem;
        border-radius: 9999px;
        border: 1.5px solid #0f172a;
        background: #ffffff;
        color: #0f172a;
        font-size: 10px;
        font-weight: 900;
    }
    .tab-btn.is-active .tab-count {
        background: #0f172a;
        color: #ffffff;
    }
    .tab-content:not(.hidden) {
        animation: tabFadeIn .18s ease;
    }
    @keyframes tabFadeIn {
        from { opacity: 0; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [id^="modal"] :is(select, input:not([type="checkbox"]):not([type="radio"]), textarea):focus {
        outline: none;
        border-color: #0f172a;
        box-shadow: 0 0 0 3px rgba(255, 212, 59, 0.45);
    }
    .data-table-scroll {
        max-height: 70vh;
        overflow: auto;
    }
    .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f1f5f9;
        box-shadow: inset -1px 0 0 #94a3b8, inset 0 -2px 0 #0f172a;
    }
    .data-table thead th:last-child {
        box-shadow: inset 0 -2px 0 #0f172a;
    }
    .data-table tbody td {
        border-right: 1px solid #cbd5e1;
    }
    .data-table tbody td:last-child {
        border-right: 0;
    }
    .data-table tbody tr.pagination-group-row td,
    .data-table tbody tr.pagination-search-empty td {
        border-right: 0;
    }
    .data-table tbody tr.is-row-selected > td {
        background: #fef08a !important;
    }
    .pagination-arrow,
    .pagination-page {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.85rem;
        height: 1.85rem;
        padding: 0 .5rem;
        border: 2px solid #0f172a;
        border-radius: .375rem;
        background: #ffffff;
        color: #0f172a;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 1.5px 1.5px 0 0 #0f172a;
        transition: all .12s ease;
    }
    .pagination-arrow:hover:not(:disabled),
    .pagination-page:hover:not(.is-current) {
        border-color: #0f172a;
        background: #fef08a;
        transform: translate(-1px, -1px);
        box-shadow: 2px 2px 0 0 #0f172a;
    }
    .pagination-arrow:active:not(:disabled),
    .pagination-page:active:not(.is-current) {
        transform: translate(1px, 1px);
        box-shadow: 0.5px 0.5px 0 0 #0f172a;
    }
    .pagination-page.is-current {
        border-color: #0f172a;
        background: #FFD43B;
        color: #0f172a;
        box-shadow: 2px 2px 0 0 #0f172a;
    }
    .pagination-arrow:disabled {
        cursor: not-allowed;
        opacity: .35;
        box-shadow: none;
    }
    .pagination-ellipsis {
        padding: 0 .25rem;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
    }
</style>
@endpush
