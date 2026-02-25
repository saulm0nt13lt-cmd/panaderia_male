@extends('admin.layout')

@section('title', 'Historial de Ventas | Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/historial.css') }}">
@endpush

@section('admin-content')

<div class="historial">

  <div class="head">
    <div>
      <h1 class="title">Historial de Ventas</h1>
      <p class="subtitle">Ventas registradas (día por día)</p>
    </div>

    <div class="actions">
      <a class="hbtn ghost" href="{{ route('sales') }}">
        <i class="fi fi-rr-angle-left"></i> Volver a Ventas
      </a>
    </div>
  </div>

  <div class="hpanel">
    <div class="hpanel-head" style="align-items:flex-start;">
      <div>
        <div style="font-weight:950; color:var(--text);">Ventas</div>

        {{-- Día actual (según página) --}}
        <div style="margin-top:6px; font-weight:900; color:rgba(17,24,39,.75);">
          Día:
          <span style="font-weight:950; color:var(--text);">
            {{ $diaActual ? \Carbon\Carbon::parse($diaActual)->format('d/m/Y') : '—' }}
          </span>
        </div>

        {{-- Totales del día --}}
        <div style="margin-top:6px; display:flex; gap:10px; flex-wrap:wrap;">
          <span class="badge cash">
            <i class="fi fi-rr-coins"></i>
            Total del día: ${{ number_format($totalDia ?? 0, 2) }}
          </span>

          <span class="badge transfer">
            <i class="fi fi-rr-receipt"></i>
            Ventas: {{ $numVentas ?? 0 }}
          </span>
        </div>
      </div>

      <div class="meta">
        {{ $numVentas ?? 0 }} registros
      </div>
    </div>

    {{-- Buscador automático --}}
    <form method="GET" action="{{ route('sales_history') }}" class="hsearch" id="historySearchForm">
      <input
        class="hinput"
        id="q"
        name="q"
        value="{{ $q ?? '' }}"
        placeholder="Buscar por ticket, método o nota…"
        autocomplete="off"
      >
    </form>

    <div class="htable-wrap">
      <table class="htable">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Método</th>
            <th style="text-align:right;">Total</th>
            <th style="text-align:right;">Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse($ventasDia as $v)
            @php
              $met = strtolower(trim($v->metodo_pago ?? ''));
              $cls = 'cash';
              $ico = 'fi fi-rr-money';
              if (str_contains($met, 'transfer')) { $cls = 'transfer'; $ico = 'fi fi-rr-exchange'; }
              if (str_contains($met, 'tarj') || str_contains($met, 'card')) { $cls = 'card'; $ico = 'fi fi-rr-credit-card'; }
            @endphp

            <tr>
              <td data-label="Ticket" class="t-ticket">#{{ $v->id_venta }}</td>
              <td data-label="Fecha" class="t-date">
                {{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') }}
              </td>
              <td data-label="Usuario" class="t-user">{{ $v->usuario->nombre_completo ?? '—' }}</td>

              <td data-label="Método">
                <span class="badge {{ $cls }}">
                  <i class="{{ $ico }}"></i> {{ $v->metodo_pago }}
                </span>
              </td>

              <td data-label="Total" class="t-total" style="text-align:right;">
                ${{ number_format($v->total, 2) }}
              </td>

              {{-- ✅ IMPRIMIR TICKET --}}
              <td data-label="Acciones" style="text-align:right;">
                <a class="hbtn"
                   target="_blank"
                   href="{{ route('admin.ticket.print', ['id' => $v->id_venta]) }}">
                  <i class="fi fi-rr-print"></i> Imprimir
                </a>
              </td>
            </tr>

          @empty
            <tr>
              <td colspan="6" class="empty">
                Aún no hay ventas registradas para este día.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ✅ Paginación por DÍA (prev/next) --}}
    <div class="hpager">
      {{ $dias->links('vendor.pagination.iconos') }}
    </div>
  </div>


<script>
(() => {
  const form = document.getElementById('historySearchForm');
  const input = document.getElementById('q');
  if (!form || !input) return;

  let t = null;

  input.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => {
      form.submit();
    }, 350);
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') form.submit();
  });

  if (input.value.trim().length > 0) {
    setTimeout(() => {
      input.focus();
      input.setSelectionRange(input.value.length, input.value.length);
    }, 50);
  }
})();
</script>

@endsection