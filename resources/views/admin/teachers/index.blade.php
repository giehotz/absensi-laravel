@extends('layouts.admin')

@section('title', 'Data Guru & Penugasan Mengajar')

@section('content')
<script>
    window.TEACHER_INDEX_STORAGE_KEY = 'admin_teachers_index_state_{{ auth()->id() }}';

    window.normalizeTeacherTab = function (tab) {
        return tab === 'tu' || tab === 'kepala' ? tab : 'guru';
    };

    window.buildTeacherIndexQueryString = function (state) {
        if (!state || typeof state !== 'object') return '';
        const params = new URLSearchParams();
        const tab = window.normalizeTeacherTab(state.tab);
        const search = typeof state.search === 'string' ? state.search.trim() : '';
        const sort = typeof state.sort === 'string' && state.sort !== '' ? state.sort : 'name_asc';
        const page = parseInt(state.page, 10);

        if (tab !== 'guru') params.set('tab', tab);
        if (search !== '') params.set('search', search);
        if (sort !== 'name_asc') params.set('sort', sort);
        if (!Number.isNaN(page) && page > 1) params.set('page', String(page));

        return params.toString();
    };

    window.saveTeacherIndexState = function (state) {
        try {
            const payload = {
                tab: window.normalizeTeacherTab(state?.tab),
                page: String(state?.page || '1'),
                search: typeof state?.search === 'string' ? state.search : '',
                sort: typeof state?.sort === 'string' && state.sort !== '' ? state.sort : 'name_asc',
            };
            localStorage.setItem(window.TEACHER_INDEX_STORAGE_KEY, JSON.stringify(payload));
        } catch (e) {}
    };

    window.clearTeacherIndexState = function (keepSort = false, targetTab = null) {
        try {
            const raw = localStorage.getItem(window.TEACHER_INDEX_STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : {};
            const activeTab = targetTab === 'tu' || targetTab === 'kepala' || targetTab === 'guru'
                ? targetTab
                : window.normalizeTeacherTab(parsed?.tab);

            if (keepSort || activeTab !== 'guru') {
                window.saveTeacherIndexState({
                    tab: activeTab,
                    page: '1',
                    search: '',
                    sort: keepSort ? (parsed?.sort || 'name_asc') : 'name_asc',
                });
            } else {
                localStorage.removeItem(window.TEACHER_INDEX_STORAGE_KEY);
            }
        } catch (e) {}
    };

    (function () {
        const indexUrl = @json(route('admin.teachers.index'));
        const hasFlash = @json(session()->has('success') || session()->has('error'));
        const currentState = {
            tab: @json($tab ?? 'guru'),
            page: String({{ $teachers->currentPage() }}),
            search: @json($search ?? ''),
            sort: @json($sort ?? 'name_asc'),
        };

        if (window.location.search === '' && !hasFlash) {
            try {
                const raw = localStorage.getItem(window.TEACHER_INDEX_STORAGE_KEY);
                if (raw) {
                    const savedQuery = window.buildTeacherIndexQueryString(JSON.parse(raw));
                    if (savedQuery !== '') {
                        window.location.replace(indexUrl + '?' + savedQuery);
                        return;
                    }
                }
            } catch (e) {}
        }

        window.saveTeacherIndexState(currentState);
    })();
</script>

<div class="teachers-page space-y-6">
    @include('admin.teachers.partials._header')
    @include('admin.teachers.partials._table')
</div>

@include('admin.teachers.partials._modals')
@include('admin.teachers.partials._scripts')
@endsection
