@extends('admin.layout')

@section('title', 'Dashboard | Panadería MALE')

@section('admin-content')
  <!-- tarjeta #1 -->
  <header class="topbar">
    <h1>PANADERIA MALE</h1>

    <div class="userchip">
      @php $user = auth()->user(); @endphp
      <strong>{{ $user->nombre_completo ?? '' }}</strong>
    </div>
  </header>

  <!-- tarjetas principales -->
  <section class="cards">
    <div class="card">
      <h3>Ventas del día</h3>
<p class="big">${{ number_format($ventasHoy ?? 0, 2) }}</p>
<span class="muted">Hoy</span>

</div>

  <div class="card">
  <h3>Pedidos Especiales</h3>
  <p class="big">{{ $pedidosPendientes ?? 0 }}</p>
<span class="muted">Pendientes</span>

  <div class="sub">
    Entregados: {{ $pedidosEntregados ?? 0 }}
  </div>
</div>


    <div class="card">
      <h3>Productos</h3>
   <p class="big">{{ $totalProductos ?? 0 }}</p>
      <span class="muted">En inventario</span>
    </div>
  </section>

  <!-- PANEL -->
  <section class="panel">
    <h2>Acciones rápidas</h2>

    <div class="actions">
      <a href="{{ route('admin_users', ['new' => 1]) }}" class="btn-edit btn-create">
        <i class="fi fi-rr-user-add"></i> Crear usuario
      </a>

     <a href="{{ route('sales') }}" class="btn-edit btn-create">
  ➕ Nueva venta
</a>

      <a href="{{ route('products', ['new' => 1]) }}" class="btn-edit btn-create">
        <i class="fi fi-rr-box-open"></i> Agregar producto
      </a>

      <a href="{{ route('products', ['special' => 1]) }}" class="btn-edit btn-special">
        <i class="fi fi-rr-magic-wand"></i> Pedido especial
      </a>
    </div>
  </section>

  {{-- =========================
      PEDIDOS ESPECIALES (CARDS)
  ========================== --}}
  @php
    $pedidosEspeciales = $pedidosEspeciales ?? collect();
  @endphp

  <div class="so-wrap">
    <div class="so-head">
      <h2>Pedidos Especiales</h2>
      <p>Etiquetas: Verde (POR ENTREGAR), Amarillo (SE ACERCA FECHA DE ENTREGA), Rojo (URGENTE), Morado (AUTOMATICOl)</p>
    </div>

    <div class="so-grid">
      @forelse($pedidosEspeciales as $p)
        @php
          $tag = $p->tag_auto ?? 'normal';
          $tagManual = !empty($p->tag);

          // ✅ Texto bonito para etiqueta
          $tagText = match($tag){
            'ok' => 'POR ENTREGAR',
            'warn' => 'SE ACERCA',
            'urgent' => 'URGENTE',
            default => 'NORMAL',
          };

          // ✅ Texto bonito para auto/manual
          $modeText = $tagManual ? 'MANUAL' : 'AUTOMÁTICO';
        @endphp

        <article class="so-card so-{{ $tag }}">
          <div class="so-top">
            <div class="so-title">
              <strong>{{ $p->cliente_nombre }}</strong>
              <small>{{ $p->cliente_telefono }}</small>
            </div>

            <div style="display:flex; gap:8px; align-items:center;">
              <span class="so-badge">{{ $tagText }}</span>
              <span class="so-badge" style="opacity:.85;">
                {{ $modeText }}
              </span>
            </div>
          </div>

          <div class="so-meta">
            <div>
              <span>Entrega:</span>
              <b>{{ \Carbon\Carbon::parse($p->fecha_entrega)->format('d/m/Y') }}</b>
            </div>
            <div>
              <span>Días:</span>
              <b class="{{ ($p->dias_restantes ?? 0) < 0 ? 'so-neg' : '' }}">
                {{ $p->dias_restantes ?? 0 }}
              </b>
            </div>
          </div>

          <div class="so-money">
            <div>Total: <b>${{ number_format($p->total,2) }}</b></div>
            <div>Anticipo: <b>${{ number_format($p->anticipo,2) }}</b></div>
            <div>Restante: <b>${{ number_format($p->restante,2) }}</b></div>
          </div>

          <p class="so-desc">{{ $p->descripcion }}</p>

          <div class="so-actions">
            {{-- Cambiar etiqueta (manual) --}}
            <form method="POST" action="{{ route('special_orders.tag', $p->id_pedido) }}">
              @csrf
              @method('PATCH')

              <select name="tag" class="so-select" onchange="this.form.submit()">
                <option value="" {{ empty($p->tag) ? 'selected' : '' }}>Auto</option>
                <option value="ok" {{ $p->tag === 'ok' ? 'selected' : '' }}>Verde</option>
                <option value="warn" {{ $p->tag === 'warn' ? 'selected' : '' }}>Amarillo</option>
                <option value="urgent" {{ $p->tag === 'urgent' ? 'selected' : '' }}>Rojo</option>
              </select>
            </form>

            {{-- Marcar entregado --}}
            <form method="POST" action="{{ route('special_orders.estado', $p->id_pedido) }}">
              @csrf
              @method('PATCH')
              <input type="hidden" name="estado" value="entregado">
              <button class="so-done" type="submit">Entregado</button>
            </form>
          </div>
        </article>
      @empty
        <div class="so-empty">No hay pedidos especiales pendientes.</div>
      @endforelse
    </div>
  </div>
@endsection