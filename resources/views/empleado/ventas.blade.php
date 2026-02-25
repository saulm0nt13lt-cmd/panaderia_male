@extends('empleado.layout')

@section('title', 'Ventas | Empleado')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sales.css') }}">
@endpush

@section('empleado-content')

<div class="sales-page">

  <div class="sales-head">
    <div>
      <h1 class="sales-title">Ventas</h1>
      <p class="sales-subtitle">Caja / Ticket</p>
    </div>

    <div class="sales-actions">
      <button type="button" class="btn btn-green" id="btnClear">
        <i class="fi fi-rr-trash"></i> Limpiar
      </button>
    </div>
  </div>

  {{-- ✅ ALERTA TURNO CERRADO --}}
  @if(!$turnoActivo)
    <div class="alert danger" style="display:flex;align-items:center;gap:10px;">
      <i class="fi fi-rr-lock"></i>
      <div>
        <strong>Turno cerrado.</strong> No puedes registrar ventas hasta abrir el turno en el dashboard.
      </div>
    </div>
  @endif

  @if(session('ok'))
    <div class="alert success">{{ session('ok') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert danger">
      <strong>Ojo:</strong> {{ $errors->first() }}
    </div>
  @endif

  <div class="sales-grid">
    {{-- IZQUIERDA: catálogo --}}
    <section class="panel">
      <div class="panel-head">
        <h3>Catálogo</h3>
        <div class="search-wrap">
          <input id="q" class="input" placeholder="Busca por nombre…" autocomplete="off">
        </div>
      </div>

      <div class="catalog" id="catalog">
        @php
          $grupos = $productos->groupBy(function($p){
            return $p->categoria->nombre ?? 'Otros';
          });

          $orden = ['Panadería','Pastelería','Roscas','Otros'];

          $grupos = $grupos->sortBy(function($items, $cat) use ($orden){
            $pos = array_search($cat, $orden);
            return $pos === false ? 999 : $pos;
          });
        @endphp

        @foreach($grupos as $categoria => $items)
          <div class="cat-group" data-cat="{{ $categoria }}">
            <div class="cat-title">
              <span class="cat-dot"></span>
              <span>{{ $categoria }}</span>
              <small>{{ $items->count() }}</small>
            </div>

            <div class="cat-list">
              @foreach($items as $p)
                <button
                  type="button"
                  class="catalog-item"
                  data-id="{{ $p->id_producto }}"
                  data-nombre="{{ $p->nombre }}"
                  data-precio="{{ $p->precio_venta }}"
                  data-stock="{{ $p->stock }}"
                  data-cat="{{ $p->categoria->nombre ?? '' }}"
                >
                  <div class="ci-left">
                    <div class="ci-name">{{ $p->nombre }}</div>
                    <div class="ci-meta">
                      <span class="chip">{{ $p->categoria->nombre ?? 'Producto' }}</span>
                      <span class="chip {{ ($p->stock ?? 0) > 0 ? 'ok' : 'low' }}">Stock: {{ $p->stock ?? 0 }}</span>
                    </div>
                  </div>

                  <div class="ci-right">
                    <div class="ci-price">${{ number_format($p->precio_venta, 2) }}</div>
                    <div class="ci-add"><i class="fi fi-rr-plus"></i></div>
                  </div>
                </button>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </section>

    {{-- DERECHA: ticket --}}
    <section class="panel">
      <div class="panel-head">
        <h3>Ticket</h3>
        <div class="ticket-mini" id="ticketCount">0 productos</div>
      </div>

      <div class="ticket">
        <div class="ticket-table">
          <div class="t-row t-head">
            <div>Producto</div>
            <div>Cant.</div>
            <div>Importe</div>
            <div></div>
          </div>

          <div id="ticketBody"></div>

          <div class="t-empty" id="emptyTicket">
            Agrega productos desde el catálogo
          </div>
        </div>

        <form method="POST" action="{{ route('empleado.ventas.store') }}" id="saleForm">
          @csrf

          <div class="ticket-totals">
            <div class="row">
              <span>Subtotal</span>
              <strong id="sub">$0.00</strong>
            </div>

            <div class="row">
              <span>Descuento</span>
              <div class="inline">
                <span class="currency">$</span>
                <input class="input sm" type="number" min="0" step="0.01" name="descuento" id="descuento" value="0">
              </div>
            </div>

            <div class="row total">
              <span>Total</span>
              <strong id="tot">$0.00</strong>
            </div>

            <div class="row">
              <span>Método</span>
              <select class="input" name="metodo_pago" id="metodo_pago">
                <option value="Efectivo">Efectivo</option>
                <option value="Transferencia">Transferencia</option>
              </select>
            </div>

            <div class="row">
              <span>Nota</span>
              <input class="input" name="nota" maxlength="200" placeholder="Opcional…">
            </div>

            {{-- ✅ DESHABILITADO SI TURNO CERRADO --}}
            <button class="btn btn-blue w100" type="submit" id="btnSave" {{ !$turnoActivo ? 'disabled' : '' }}>
              <i class="fi fi-rr-receipt"></i> Registrar venta
            </button>

          </div>
        </form>
      </div>

      {{-- Últimas 5 ventas del empleado --}}
      <div class="recent">
        <div class="recent-head">
          <h4>Mis últimas 5 ventas</h4>
        </div>

        <div class="recent-list">
          @forelse($ultimasVentas as $v)
            <div class="recent-item">
              <div>
                <div class="r-title">Ticket #{{ $v->id_venta }}</div>
                <div class="r-sub">{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y H:i') }}</div>
              </div>
              <div class="r-total">${{ number_format($v->total, 2) }}</div>
            </div>
          @empty
            <div class="recent-empty">Aún no hay ventas.</div>
          @endforelse
        </div>
      </div>

    </section>
  </div>
</div>

<script>
(() => {
  const TURNO_ACTIVO = @json($turnoActivo);

  const ticket = new Map();

  const q = document.getElementById('q');
  const catalog = document.getElementById('catalog');
  const ticketBody = document.getElementById('ticketBody');
  const emptyTicket = document.getElementById('emptyTicket');
  const subEl = document.getElementById('sub');
  const totEl = document.getElementById('tot');
  const descEl = document.getElementById('descuento');
  const countEl = document.getElementById('ticketCount');
  const btnClear = document.getElementById('btnClear');
  const saleForm = document.getElementById('saleForm');

  q.addEventListener('input', () => {
    const term = q.value.toLowerCase().trim();

    document.querySelectorAll('#catalog .cat-group').forEach(group => {
      let visible = 0;

      group.querySelectorAll('.catalog-item').forEach(btn => {
        const name = (btn.dataset.nombre || '').toLowerCase();
        const show = name.includes(term);
        btn.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      group.style.display = visible ? '' : 'none';
    });
  });

  // ✅ No permitir agregar si turno cerrado
  catalog.addEventListener('click', (e) => {
    if (!TURNO_ACTIVO) {
      alert('Turno cerrado. Abre el turno para poder vender.');
      return;
    }

    const btn = e.target.closest('.catalog-item');
    if (!btn) return;

    const id = Number(btn.dataset.id);
    const nombre = btn.dataset.nombre;
    const precio = Number(btn.dataset.precio);
    const stock = Number(btn.dataset.stock);

    if (!stock || stock <= 0) {
      alert('Ese producto no tiene stock.');
      return;
    }

    const cur = ticket.get(id);
    if (!cur) {
      ticket.set(id, { id, nombre, precio, cantidad: 1, stock });
    } else {
      if (cur.cantidad + 1 > cur.stock) {
        alert('No hay suficiente stock para agregar más.');
        return;
      }
      cur.cantidad += 1;
      ticket.set(id, cur);
    }

    render();
  });

  btnClear.addEventListener('click', () => {
    ticket.clear();
    descEl.value = 0;
    render();
  });

  descEl.addEventListener('input', render);

  function render() {
    ticketBody.innerHTML = '';

    if (ticket.size === 0) {
      emptyTicket.style.display = 'block';
      countEl.textContent = '0 productos';
      subEl.textContent = '$0.00';
      totEl.textContent = '$0.00';
      return;
    }

    emptyTicket.style.display = 'none';

    let subtotal = 0;

    for (const it of ticket.values()) {
      const importe = it.precio * it.cantidad;
      subtotal += importe;

      const row = document.createElement('div');
      row.className = 't-row';
      row.innerHTML = `
        <div class="t-prod">
          <div class="tp-name">${escapeHtml(it.nombre)}</div>
          <div class="tp-sub">$${it.precio.toFixed(2)} c/u • Stock ${it.stock}</div>
        </div>
        <div class="t-qty">
          <button type="button" class="qty-btn" data-act="minus" data-id="${it.id}">-</button>
          <input class="qty-in" type="number" min="1" step="1" inputmode="numeric" value="${it.cantidad}" data-id="${it.id}">
          <button type="button" class="qty-btn" data-act="plus" data-id="${it.id}">+</button>
        </div>
        <div class="t-imp">$${importe.toFixed(2)}</div>
        <div class="t-x">
          <button type="button" class="x-btn" data-act="del" data-id="${it.id}">
            <i class="fi fi-rr-cross-small"></i>
          </button>
        </div>
      `;
      ticketBody.appendChild(row);
    }

    const desc = Math.max(Number(descEl.value || 0), 0);
    const total = Math.max(subtotal - desc, 0);

    const productosCount = ticket.size;
    countEl.textContent = `${productosCount} ${productosCount === 1 ? 'producto' : 'productos'}`;

    subEl.textContent = `$${subtotal.toFixed(2)}`;
    totEl.textContent = `$${total.toFixed(2)}`;
  }

  ticketBody.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;

    const act = btn.dataset.act;
    const id = Number(btn.dataset.id);
    const it = ticket.get(id);
    if (!it) return;

    if (act === 'minus') {
      it.cantidad -= 1;
      if (it.cantidad <= 0) ticket.delete(id);
      else ticket.set(id, it);
    }

    if (act === 'plus') {
      if (it.cantidad + 1 > it.stock) alert('No hay suficiente stock.');
      else { it.cantidad += 1; ticket.set(id, it); }
    }

    if (act === 'del') ticket.delete(id);

    render();
  });

  saleForm.addEventListener('submit', (e) => {
    if (!TURNO_ACTIVO) {
      e.preventDefault();
      alert('Turno cerrado. Abre el turno para poder vender.');
      return;
    }

    if (ticket.size === 0) {
      e.preventDefault();
      alert('Agrega al menos un producto.');
      return;
    }

    [...saleForm.querySelectorAll('input[name^="items["]')].forEach(n => n.remove());

    let i = 0;
    for (const it of ticket.values()) {
      addHidden(`items[${i}][id_producto]`, it.id);
      addHidden(`items[${i}][cantidad]`, it.cantidad);
      i++;
    }
  });

  function addHidden(name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    saleForm.appendChild(input);
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  render();
})();
</script>

@endsection