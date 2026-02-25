@extends('admin.layout')

@section('title', 'Historial de Pedidos Especiales | Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/historial.css') }}">
@endpush

@section('admin-content')

<div class="historial">

  <div class="head">
    <div>
      <h1 class="title">Historial de Pedidos Especiales</h1>
      <p class="subtitle">Pedidos registrados</p>
    </div>

    <div class="actions">
      <a class="hbtn ghost" href="{{ route('dashboard') }}">
        <i class="fi fi-rr-angle-left"></i> Volver al Inicio
      </a>
    </div>
  </div>

  <div class="hpanel">
    <div class="hpanel-head" style="align-items:flex-start;">
      <div>
        <div style="font-weight:950; color:var(--text);">Pedidos</div>

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
            Pedidos: {{ $numPedidos ?? 0 }}
          </span>
        </div>
      </div>

      <div class="meta">
        {{ $numPedidos ?? 0 }} registros
      </div>
    </div>

    {{-- Buscador automático --}}
    <form method="GET" action="{{ route('pedidos_historial') }}" class="hsearch" id="historySearchForm">
      <input
        class="hinput"
        id="q"
        name="q"
        value="{{ $q ?? '' }}"
        placeholder="Buscar por cliente, teléfono, descripción o estado…"
        autocomplete="off"
      >
    </form>

    <div class="htable-wrap">
      <table class="htable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Entrega</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th style="text-align:right;">Total</th>
            <th>Estado</th>
            <th style="text-align:right;">Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse($pedidosDia as $p)
            @php
              $estadoKey = strtolower(trim($p->estado ?? ''));
              $estadoKey = str_contains($estadoKey, 'entreg') ? 'entregado' : 'pendiente';
            @endphp

            <tr>
              <td data-label="ID" class="t-ticket">#{{ $p->id_pedido }}</td>

              <td data-label="Fecha" class="t-date">
                {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') : '—' }}
              </td>

              <td data-label="Entrega" class="t-date">
                {{ $p->fecha_entrega ? \Carbon\Carbon::parse($p->fecha_entrega)->format('d/m/Y') : '—' }}
              </td>

              <td data-label="Cliente" class="t-user">{{ $p->cliente_nombre ?? '—' }}</td>
              <td data-label="Teléfono">{{ $p->cliente_telefono ?? '—' }}</td>

              <td data-label="Total" class="t-total" style="text-align:right;">
                ${{ number_format($p->total ?? 0, 2) }}
              </td>

              <td data-label="Estado">
                <span class="pstate {{ $estadoKey }}">
                  <i class="fi {{ $estadoKey === 'entregado' ? 'fi-rr-check-circle' : 'fi-rr-clock' }}"></i>
                  {{ $estadoKey }}
                </span>
              </td>

              {{-- ✅ IMPRIMIR PEDIDO --}}
              <td data-label="Acciones" style="text-align:right;">
                <a class="hbtn"
                   target="_blank"
                   href="{{ route('admin.pedidos.print', ['id' => $p->id_pedido]) }}">
                  <i class="fi fi-rr-print"></i> Imprimir
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="empty">
                Aún no hay pedidos registrados para este día.
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