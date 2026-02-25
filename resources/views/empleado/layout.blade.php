<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Empleado | Panadería MALE')</title>

  {{-- Base neutra --}}
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">

  
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

  {{-- ✅ CSS del empleado (si quieres overrides sin pisar admin) --}}
  <link rel="stylesheet" href="{{ asset('css/empleado/baseempl.css') }}">

  @stack('styles')

  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.3.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="empleado-body">
  <div class="dash">

    {{-- Sidebar (partial) --}}
    @include('empleado.partials.sidebar_empleado')

    {{-- Contenido --}}
    <main class="content">
      @yield('empleado-content')
    </main>

  </div>
</body>
</html>