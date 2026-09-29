@extends('layouts.guru')

@section('title', 'Kalender Pendidikan & Hari Libur')
@section('page-title', 'Kalender Sekolah')

@section('content')
<div class="space-y-6">
    @include('partials._calendar-readonly')
</div>
@endsection
