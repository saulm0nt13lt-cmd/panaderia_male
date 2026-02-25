<!doctype html>
<html lang="es">
<head>
  <link rel="stylesheet"
      href="https://cdn-uicons.flaticon.com/2.3.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
      
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title', 'Panadería MALE')</title>

  <!-- CSS global -->
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">

  <!-- CSS específico por vista -->
  @stack('styles')
  
  
</head>
<body class="admin-body">
  <main class="container">
    @yield('content')
  </main>
     @stack('scripts')
</body>
</html>