@extends('layouts.admin')

@section('title', 'Manajemen Data Siswa & QR')
@section('page-title', 'Manajemen Data Siswa & Kartu QR')

@section('content')
<div class="space-y-6">
    @include('admin.students.partials._header')

    @include('admin.students.partials._table')
</div>

@include('admin.students.partials._modals')

@include('admin.students.partials._scripts')
@endsection