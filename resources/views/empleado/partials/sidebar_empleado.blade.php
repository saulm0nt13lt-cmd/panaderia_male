<aside class="sidebar">

  <div class="brand">
    <small>Panel Empleado</small>
  </div>

  <nav class="menu">
    <a href="{{ route('empleado.dashboard') }}">
      <i class="fi fi-rr-house-chimney"></i> Inicio
    </a>

    <a href="{{ route('empleado.ventas') }}">
      <i class="fi fi-rr-point-of-sale-bill"></i> Generar Venta
    </a>

    <a href="{{ route('empleado.misventas') }}">
      <i class="fi fi-rr-calendar"></i> Mis ventas
    </a>

    <a href="{{ route('empleado.tickets') }}">
      <i class="fi fi-rr-document"></i> Tickets
    </a>
  </nav>

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn-logout">
      <i class="fi fi-rr-exit"></i> Cerrar sesión
    </button>
  </form>

</aside>