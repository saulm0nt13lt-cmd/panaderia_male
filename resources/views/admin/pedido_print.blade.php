<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pedido #{{ $pedido->id_pedido }} | Panadería MALE</title>

  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.3.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
  <link rel="stylesheet" href="{{ asset('css/empleado/ticket_print.css') }}">
</head>
<body>

<div class="tp-actions no-print">
  <button class="tp-btn" onclick="window.print()">
    <i class="fi fi-rr-print"></i> Imprimir
  </button>

  <button class="tp-btn ghost" onclick="window.location.href='{{ route('pedidos_historial') }}'">
    <i class="fi fi-rr-cross"></i> Cerrar
  </button>
</div>

<div class="ticket">
  <div class="t-head">
    <img src="{{ asset('img/logo_male.png') }}" alt="MALE">
    <div>
      <div class="t-title">Panadería MALE</div>
      <div class="t-sub">Pedido #{{ $pedido->id_pedido }}</div>
    </div>
  </div>

  <div class="t-meta">
    <div><b>Cliente:</b> {{ $pedido->cliente_nombre }}</div>
    <div><b>Teléfono:</b> {{ $pedido->cliente_telefono }}</div>
    <div><b>Entrega:</b> {{ $pedido->fecha_entrega }}</div>
    <div><b>Estado:</b> {{ ucfirst($pedido->estado) }}</div>
    @if(!empty($pedido->descripcion))
  <div class="t-line"></div>

  <div class="t-desc">
    <b>Descripción del pedido:</b>
    <p>{{ $pedido->descripcion }}</p>
  </div>
@endif
  </div>

  <div class="t-line"></div>

  <div class="t-totals">
    <div class="row total">
      <span>Total</span>
      <b>${{ number_format($pedido->total, 2) }}</b>
      
    </div>
  </div>

  <div class="t-footer">
    Pedido especial – Panadería MALE
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    setTimeout(() => window.print(), 200);
  });
</script>

</body>
</html>