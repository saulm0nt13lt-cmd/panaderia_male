@extends('admin.layout')

@section('title', 'Corte Diario | Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/historial.css') }}">
@endpush

@section('admin-content')

<div class="historial">

  <div class="head">
    <div>
      <h1 class="title">Corte diario</h1>
      <p class="subtitle">Totales por día</p>
    </div>

    <div class="actions">
      <a class="hbtn ghost" href="{{ route('dashboard') }}">
        <i class="fi fi-rr-angle-left"></i> Volver al Inicio
      </a>
    </div>
  </div>

  <div class="hpanel">
    <div class="hpanel-head">
      <div>
        <div style="font-weight:950; color:var(--text);">Resumen</div>

        <div style="margin-top:8px; display:flex; gap:10px; flex-wrap:wrap;">
          <span class="badge cash">
            <i class="fi fi-rr-coins"></i>
            Ventas: ${{ number_format($totalVentasPagina ?? 0, 2) }}
          </span>

          <span class="badge card">
            <i class="fi fi-rr-stars"></i> Pedidos especiales: ${{ number_format($totalPedidosPagina ?? 0, 2) }}
          </span>

          <span class="badge transfer">
            <i class="fi fi-rr-calculator"></i>
            Total: ${{ number_format($totalGeneralPagina ?? 0, 2) }}
          </span>
        </div>
      </div>

      <div class="meta">
        {{ $dias->total() }} días
      </div>
    </div>

    {{-- Buscador por fecha --}}
    <form method="GET" action="{{ route('corte_diario') }}" class="hsearch" id="corteSearchForm">
      <input
        class="hinput"
        id="q"
        name="q"
        value="{{ $q ?? '' }}"
        placeholder="Buscar por fecha… (ej: 2026-02-23 ó 23/02/2026)"
        autocomplete="off"
      >
    </form>

    <div class="htable-wrap">
      <table class="htable">
        <thead>
          <tr>
            <th>Día</th>
            <th style="text-align:right;">Ventas</th>
            <th style="text-align:right;">Pedidos especiales</th>
            <th style="text-align:right;">Total del día</th>
          </tr>
        </thead>

        <tbody>
          @forelse($dias as $d)
            @php
              // columnas reales del SQL
              $ventas   = (float) ($d->ventas ?? 0);
              $pedidos  = (float) ($d->pedidos_especiales ?? 0);
              $totalDia = (float) ($d->total_dia ?? ($ventas + $pedidos));
            @endphp

            <tr>
              <td data-label="Día" class="t-date">
                {{ \Carbon\Carbon::parse($d->dia)->format('d/m/Y') }}
              </td>

             <td data-label="Ventas" class="t-total">
            @if($ventas > 0)
         <span class="ventas-chip">
             <i class="fi fi-rr-coins"></i>
      ${{ number_format($ventas, 2) }}
         </span>
        @else
              <span style="color:rgba(17,24,39,.55); font-weight:900;">—</span>
     @endif
          </td>

              {{-- OJO: aquí NO usamos .t-total en el td para que el badge no se vea raro --}}
              <td data-label="Pedidos especiales" style="text-align:right;">
                @if($pedidos > 0)
                  <span class="badge card">
                    <i class="fi fi-rr-stars"></i>
                    ${{ number_format($pedidos, 2) }}
                  </span>
                @else
                  <span style="color:rgba(17,24,39,.55); font-weight:900;">—</span>
                @endif
              </td>

              <td data-label="Total del día" class="t-total">
                <span class="badge transfer">
                  <i class="fi fi-rr-calculator"></i>
                  ${{ number_format($totalDia, 2) }}
                </span>
              </td>
            </tr>

          @empty
            <tr>
              <td colspan="4" class="empty">
                Aún no hay registros para mostrar.
              </td>
            </tr>
          @endforelse

          {{-- TOTAL GENERAL (página) --}}
          @if($dias->count() > 0)
            <tr>
              <td class="t-ticket" style="font-weight:950;">TOTAL GENERAL (página)</td>

              <td class="t-total" style="font-weight:950;">
                ${{ number_format($totalVentasPagina ?? 0, 2) }}
              </td>

              <td class="t-total" style="font-weight:950;">
                ${{ number_format($totalPedidosPagina ?? 0, 2) }}
              </td>

              <td class="t-total" style="font-weight:950;">
                ${{ number_format($totalGeneralPagina ?? 0, 2) }}
              </td>
            </tr>
          @endif

        </tbody>
      </table>
    </div>

    <div class="hpager">
      {{ $dias->links('vendor.pagination.iconos') }}
    </div>
  </div>

</div>

<script>
(() => {
  const form = document.getElementById('corteSearchForm');
  const input = document.getElementById('q');
  if (!form || !input) return;

  let t = null;
  input.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => form.submit(), 350);
  });
})();
</script>

@endsection