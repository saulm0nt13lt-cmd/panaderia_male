@extends('empleado.layout')

@section('title', 'Tickets | Empleado - Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/empleado/tickets.css') }}">
@endpush

@section('empleado-content')

<div class="tk-wrap">
  <div class="tk-head">
    <div>
      <h1 class="tk-title">Tickets</h1>
      <p class="tk-sub">Reimpresiones</p>
    </div>
  </div>

  <div class="tk-panel">

    {{-- CABECERA DEL DÍA + TOTALES (igual que mis ventas pero con clases tk) --}}
    <div class="tk-dayhead" style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start; margin-bottom:12px;">
      <div>
        <div style="font-weight:950; color:#111827;">Tickets</div>

        <div style="margin-top:6px; font-weight:900; color:rgba(17,24,39,.75);">
          Día:
          <span style="font-weight:950; color:#111827;">
            {{ $diaActual ? \Carbon\Carbon::parse($diaActual)->format('d/m/Y') : '—' }}
          </span>
        </div>

        <div style="margin-top:8px; display:flex; gap:10px; flex-wrap:wrap;">
          <span class="tk-badge">
            <small>Total:</small> ${{ number_format($totalDia ?? 0, 2) }}
          </span>

          <span class="tk-badge">
            <small>Tickets:</small> {{ $numTickets ?? 0 }}
          </span>
        </div>
      </div>

      <div style="font-weight:800; color:rgba(17,24,39,.7);">
        {{ $numTickets ?? 0 }} registros
      </div>
    </div>

    <form method="GET" action="{{ route('empleado.tickets') }}" class="tk-filters" id="tkForm">
      <input class="tk-input" id="tkQ" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por ticket, método o nota…" autocomplete="off">
      <input class="tk-date" id="tkDesde" type="date" name="desde" value="{{ $desde ?? '' }}">
      <input class="tk-date" id="tkHasta" type="date" name="hasta" value="{{ $hasta ?? '' }}">
      <a class="tk-btn ghost" href="{{ route('empleado.tickets') }}">Limpiar</a>

      <div class="tk-loading" id="tkLoading">
        <span class="tk-spinner"></span>
        <span>Buscando…</span>
      </div>
    </form>

    {{-- ✅ TABLA DEL DÍA (ya no partial) --}}
    <div class="tk-table-wrap">
      <table class="tk-table">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Fecha</th>
            <th>Método</th>
            <th class="tk-right">Total</th>
            <th class="tk-right">Acción</th>
          </tr>
        </thead>

        <tbody>
          @forelse(($ticketsDia ?? collect()) as $v)
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

              <td class="tk-right">${{ number_format($v->total, 2) }}</td>

              <td class="tk-right">
                <a class="mv-btn-print"
   target="_blank"
   href="{{ route('empleado.ticket.print', ['id' => $v->id_venta]) }}">
  <i class="fi fi-rr-print"></i> Imprimir
</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="tk-empty">Aún no hay tickets para este día con esos filtros.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ✅ PAGINACIÓN POR DÍA --}}
    <div class="tk-pager">
      @if(isset($dias) && method_exists($dias, 'links'))
        {{ $dias->links('vendor.pagination.iconos') }}
      @endif
    </div>

  </div>
</div>

{{-- ✅ Ya NO AJAX. Solo auto-submit normal --}}
<script>
(() => {
  const form = document.getElementById('tkForm');
  const q = document.getElementById('tkQ');
  const desde = document.getElementById('tkDesde');
  const hasta = document.getElementById('tkHasta');
  const loading = document.getElementById('tkLoading');

  if (!form) return;

  let t = null;

  function showLoading(){
    loading?.classList.add('show');
  }

  function submitSoon(ms){
    clearTimeout(t);
    t = setTimeout(() => { showLoading(); form.submit(); }, ms);
  }

  q?.addEventListener('input', () => submitSoon(260));
  q?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { showLoading(); form.submit(); } });

  desde?.addEventListener('change', () => { showLoading(); form.submit(); });
  hasta?.addEventListener('change', () => { showLoading(); form.submit(); });

})();
</script>

@endsection