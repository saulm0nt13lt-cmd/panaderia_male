<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ticket #{{ $venta->id_venta }} | Panadería MALE</title>

  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.3.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
  <link rel="stylesheet" href="{{ asset('css/empleado/ticket_print.css') }}">
</head>
<body>

  <div class="tp-actions no-print">
    <button class="tp-btn" type="button" id="btnPrint">
      <i class="fi fi-rr-print"></i> Imprimir
    </button>

    <button class="tp-btn ghost" type="button" id="btnClose">
      <i class="fi fi-rr-cross"></i> Cerrar
    </button>
  </div>

  <div class="ticket">
    <div class="t-head">
      <img src="{{ asset('img/logo_male.png') }}" alt="MALE">
      <div>
        <div class="t-title">Panadería MALE</div>
        <div class="t-sub">Ticket #{{ $venta->id_venta }}</div>
      </div>
    </div>

    <div class="t-meta">
      <div><b>Fecha:</b> {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</div>
      <div><b>Método:</b> {{ $venta->metodo_pago }}</div>
      <div><b>Empleado:</b> {{ $venta->usuario->nombre_completo ?? '—' }}</div>
      @if(!empty($venta->nota))
        <div><b>Nota:</b> {{ $venta->nota }}</div>
      @endif
    </div>

    <div class="t-line"></div>

    <table class="t-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th class="r">Cant</th>
          <th class="r">P.U.</th>
          <th class="r">Imp.</th>
        </tr>
      </thead>
      <tbody>
        @foreach($venta->detalles as $d)
          <tr>
            <td>{{ $d->producto->nombre ?? 'Producto' }}</td>
            <td class="r">{{ $d->cantidad }}</td>
            <td class="r">${{ number_format($d->precio_unitario, 2) }}</td>
            <td class="r">${{ number_format($d->importe, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="t-line"></div>

    <div class="t-totals">
      <div class="row"><span>Subtotal</span><b>${{ number_format($venta->subtotal, 2) }}</b></div>
      <div class="row"><span>Descuento</span><b>${{ number_format($venta->descuento, 2) }}</b></div>
      <div class="row total"><span>Total</span><b>${{ number_format($venta->total, 2) }}</b></div>
    </div>

    <div class="t-footer">
      Gracias por su compra
    </div>
  </div>

  <script>
    const btnPrint = document.getElementById('btnPrint');
    const btnClose = document.getElementById('btnClose');

    function goBack(){
      // ✅ cámbialo si quieres que regrese a otra vista
      window.location.href = "{{ route('empleado.ventas') }}";
    }

    // Imprimir manual
    btnPrint?.addEventListener('click', () => window.print());

    // ✅ Cerrar: intenta cerrar; si el navegador no deja, redirige
    btnClose?.addEventListener('click', () => {
      window.close();
      setTimeout(() => {
        // si sigue abierta, entonces redirige
        if (!window.closed) goBack();
      }, 120);
    });

    // Auto imprimir al entrar
    window.addEventListener('load', () => {
      setTimeout(() => window.print(), 200);
    });

    // ✅ opcional: cuando cierre el diálogo de impresión, regresar automático
    window.addEventListener('afterprint', () => {
      // comenta esta línea si NO quieres que regrese solo
      // goBack();
    });
  </script>

</body>
</html>