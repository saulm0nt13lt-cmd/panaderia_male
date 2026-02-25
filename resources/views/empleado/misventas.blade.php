@extends('empleado.layout')

@section('title', 'Mis ventas | Empleado - Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/empleado/misventas.css') }}">
@endpush

@section('empleado-content')

@php
  /** @var \Illuminate\Pagination\LengthAwarePaginator $dias */
  /** @var string|null $diaActual */
  /** @var \Illuminate\Support\Collection $ventasDia */
  /** @var float|int $totalDia */
  /** @var int $numVentas */
  /** @var string|null $q */
@endphp

<div class="mv-wrap">

  <div class="mv-head">
    <div>
      <h1 class="mv-title">Mis ventas</h1>
      <p class="mv-sub">Solo tus tickets registrados (día por día)</p>
    </div>
  </div>

  <div class="mv-panel">

    {{-- CABECERA DEL DÍA + TOTALES --}}
    <div class="mv-dayhead" style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start; margin-bottom:12px;">
      <div>
        <div style="font-weight:950; color:#111827;">Ventas</div>

        <div style="margin-top:6px; font-weight:900; color:rgba(17,24,39,.75);">
          Día:
          <span style="font-weight:950; color:#111827;">
            {{ $diaActual ? \Carbon\Carbon::parse($diaActual)->format('d/m/Y') : '—' }}
          </span>
        </div>

        <div style="margin-top:8px; display:flex; gap:10px; flex-wrap:wrap;">
           <span class="mv-badge">
            <small>Total:</small> ${{ number_format($totalDia ?? 0, 2) }}
          </span>
          
          <span class="mv-badge">
            <small>Tickets:</small> {{ $numVentas ?? 0 }}
          </span>
         
        </div>
      </div>

      <div style="font-weight:800; color:rgba(17,24,39,.7);">
        {{ $numVentas ?? 0 }} registros
      </div>
    </div>

    {{-- BUSCADOR AUTOMÁTICO (SIN FECHAS YA, porque es por día) --}}
    <form method="GET" action="{{ route('empleado.misventas') }}" class="mv-filters" id="mvSearchForm">
      <input
        class="mv-input"
        id="mvQ"
        name="q"
        value="{{ $q ?? '' }}"
        placeholder="Buscar por ticket, método o nota…"
        autocomplete="off"
      >
      <a class="mv-btn ghost" href="{{ route('empleado.misventas') }}">Limpiar</a>
    </form>

    <div class="mv-table-wrap">
      <table class="mv-table">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Fecha</th>
            <th>Método</th>
            <th class="mv-right">Total</th>
            <th class="mv-right"> Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse(($ventasDia ?? collect()) as $v)
            @php
              $met = strtolower(trim($v->metodo_pago ?? ''));
              $cls = str_contains($met,'transfer') ? 'transfer' : 'cash';
              $txt = $v->metodo_pago ?? '—';
            @endphp

            <tr>
              <td>#{{ $v->id_venta }}</td>

              <td>{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') }}</td>

              <td>
                <span class="pill {{ $cls }}">
                  @if($cls === 'transfer')
                    <i class="fi fi-rr-exchange"></i>
                  @else
                    <i class="fi fi-rr-coins"></i>
                  @endif
                  {{ $txt }}
                </span>
              </td>

              <td class="mv-right">${{ number_format($v-> total, 2) }}</td>

              <td class="mv-right">
               <a class="mv-btn-print"
   target="_blank"
   href="{{ route('empleado.ticket.print', ['id' => $v->id_venta]) }}">
  <i class="fi fi-rr-print"></i> Imprimir
</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="mv-empty">
                Aún no tienes ventas registradas para este día.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ✅ PAGINACIÓN POR DÍA --}}
    <div class="mv-pager">
      @if(isset($dias) && method_exists($dias, 'links'))
        {{ $dias->links('vendor.pagination.iconos') }}
      @endif
    </div>

  </div>
</div>

<script>
(() => {
  const form = document.getElementById('mvSearchForm');
  const input = document.getElementById('mvQ');
  if (!form || !input) return;

  let t = null;

  input.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => form.submit(), 320);
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') form.submit();
  });

  if (input.value.trim().length > 0) {
    setTimeout(() => {
      input.focus();
      input.setSelectionRange(input.value.length, input.value.length);
    }, 60);
  }
})();
</script>

@endsection