<aside class="sidebar">

    <div class="brand">
        
        <small>Panel Administrativo</small>
    </div>

    <nav class="menu">
        <a href="{{ route('dashboard') }}"><i class="fi fi-rr-house-chimney"></i> Inicio</a>
        <a href="{{ route('products') }}"><i class="fi fi-rr-cream"></i> Productos</a>
        <a href="{{ route('sales') }}"><i class="fi fi-rr-point-of-sale-bill"></i> Ventas</a>
        <a href="{{ route('sales_history') }}"><i class="fi fi-rr-calendar"></i> Historial de Ventas</a>
        <a href="{{ route('pedidos_historial') }}"><i class="fi fi-rr-calendar"></i> Historial de Pedidos Especiales</a>
       <a href="{{ route('corte_diario') }}"><i class="fi fi-rr-calendar-clock"></i> Corte diario</a>
        <a href="{{ route('admin_users') }}"><i class="fi fi-rr-users-alt"></i> Usuarios</a>
    </nav>

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn-logout"><i class="fi fi-rr-exit"></i>
         Cerrar sesión
    </button>
</form>

</aside>