@extends('layouts.admin')

@section('title', 'Data Guru & Penugasan Mengajar')

@section('content')
<div class="space-y-6">
    @include('admin.teachers.partials._header')
    @include('admin.teachers.partials._table')
</div>

@include('admin.teachers.partials._modals')
@include('admin.teachers.partials._scripts')
@endsection
