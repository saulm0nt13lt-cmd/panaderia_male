@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<div class="dash">

    {{-- lateral --}}
    @include('partials.sidebar')

    {{-- contenido principal --}}
    <main class="content">
        @yield('admin-content')
        </main> 
</div>
@endsection
