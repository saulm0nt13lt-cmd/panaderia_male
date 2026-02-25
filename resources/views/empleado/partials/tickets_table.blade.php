<div class="tk-badges">
  
  <span class="tk-badge"><small>Total:</small> ${{ number_format($totalVentas ?? 0, 2) }}</span>
<span class="tk-badge"><small>Tickets:</small> {{ $numVentas ?? 0 }}</span>
</div>

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
      @forelse(($tickets ?? collect()) as $v)
        @php
          $met = strtolower(trim($v->metodo_pago ?? ''));
          $cls = str_contains($met,'transfer') ? 'transfer' : 'cash';
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
              {{ $v->metodo_pago }}
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
          <td colspan="5" class="tk-empty">No hay tickets con esos filtros.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mv-pager">
  {{ $tickets->links('vendor.pagination.iconos') }}
</div