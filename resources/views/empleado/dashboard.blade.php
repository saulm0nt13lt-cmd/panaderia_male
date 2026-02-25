@extends('empleado.layout')

@section('title', 'Dashboard | Empleado - Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/empleado/dashboardempl.css') }}">
@endpush

@section('empleado-content')
@php $u = auth()->user(); @endphp
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="empl-wrap">

  {{-- TOPBAR BLANCA (como admin) --}}
  <header class="empl-topbar">
    <div class="empl-topbar-inner">
      <div class="empl-brand">
        <span class="empl-brand-title">PANADERIA MALE</span>
      </div>

      <div class="empl-user">
        <span class="empl-user-name">{{ $u->nombre_completo }}</span>
      </div>
    </div>
  </header>

  {{-- STATS --}}
  <section class="empl-cards">

    <article class="empl-card">
      <h3>Ventas del día</h3>
      <p class="big">${{ number_format($ventasDelDia ?? 0, 2) }}</p>
      <span class="empl-muted">Hoy</span>
    </article>

    <article class="empl-card">
      <h3>Tickets</h3>
       <p class="big">{{ $ticketsHoy ?? 0 }}</p>
      <span class="empl-muted">Generados</span>
    </article>

    <article class="empl-card">
      <h3>Turno</h3>
      <p class="empl-big" id="turnoEstadoTexto">Cargando…</p>
      <span class="empl-muted">Estado</span>
    </article>

  </section>

  {{-- ACCIONES --}}
  <section class="empl-panel">
    <div class="empl-panel-head">
      <h2>Acciones rápidas</h2>
    </div>

    <div class="empl-actions">
      <a class="empl-btn blue" href="{{ route('empleado.ventas') }}">
        <i class="fi fi-rr-shopping-cart"></i>
        <span> Generar venta</span>
      </a>

      <a class="empl-btn blue2" href="{{ route('empleado.misventas') }}">
        <i class="fi fi-rr-receipt"></i>
        <span> Mis ventas</span>
      </a>

      <a class="empl-btn blue3" href="{{ route('empleado.tickets') }}">
        <i class="fi fi-rr-document"></i>
        <span> Reimpresión</span>
      </a>
    </div>
  </section>

  {{-- TARJETA TURNO --}}
  <section class="empl-panel">
    <div class="empl-panel-head turn-head">
      <h2>Turno</h2>
      <span class="turn-pill" id="turnoBadge">Cargando…</span>
    </div>

    <div class="turn-grid">
      <div class="turn-info" id="turnoDetalles"></div>

      <button type="button" class="turn-btn" id="btnToggleTurno">
        <i class="fi fi-rr-time-check"></i>
        <div class="txt">
          <span class="t" id="btnTxt">Abrir turno</span>
          <span class="s" id="btnSub">Sin turno activo</span>
        </div>
      </button>
    </div>
  </section>

</div>

<script>
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

  const elEstado   = document.getElementById('turnoEstadoTexto');
  const elDetalles = document.getElementById('turnoDetalles');
  const btnToggle  = document.getElementById('btnToggleTurno');
  const elBadge    = document.getElementById('turnoBadge');
  const elBtnTxt   = document.getElementById('btnTxt');
  const elBtnSub   = document.getElementById('btnSub');

  let interval = null;
  let baseSeconds = 0;
  let activo = false;
  let apertura = null;
  let cierre = null;

  function pad(n){ return String(n).padStart(2,'0'); }
  function formatHMS(total){
    total = parseInt(total || 0, 10);
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;
    return `${pad(h)}:${pad(m)}:${pad(s)}`;
  }

  function stopCounter(){
    if (interval) clearInterval(interval);
    interval = null;
  }

  function startCounter(){
    stopCounter();
    interval = setInterval(() => {
      baseSeconds++;
      const span = document.getElementById('contadorTurno');
      if (span) span.textContent = formatHMS(baseSeconds);
    }, 1000);
  }

  function render(){
    if (activo) {
      if (elEstado) elEstado.textContent = 'Activo';
      if (elBadge){
        elBadge.textContent = 'Activo';
        elBadge.classList.remove('off');
        elBadge.classList.add('on');
      }

      if (elDetalles){
        elDetalles.innerHTML = `
          <div class="trow"><b>Apertura:</b> ${apertura ?? '-'}</div>
          <div class="trow"><b>Tiempo abierto:</b> <span id="contadorTurno">${formatHMS(baseSeconds)}</span></div>
        `;
      }

      btnToggle.classList.remove('is-open');
      btnToggle.classList.add('is-close');
      elBtnTxt.textContent = 'Cerrar turno';
      elBtnSub.textContent = 'Turno activo';

      startCounter();
    } else {
      if (elEstado) elEstado.textContent = 'Cerrado';
      if (elBadge){
        elBadge.textContent = 'Cerrado';
        elBadge.classList.remove('on');
        elBadge.classList.add('off');
      }

      if (elDetalles){
        elDetalles.innerHTML = `
          <div class="trow"><b>Apertura:</b> ${apertura ?? '-'}</div>
          <div class="trow"><b>Cierre:</b> ${cierre ?? '-'}</div>
          <div class="trow"><b>Tiempo total:</b> ${formatHMS(baseSeconds)}</div>
        `;
      }

      btnToggle.classList.remove('is-close');
      btnToggle.classList.add('is-open');
      elBtnTxt.textContent = 'Abrir turno';
      elBtnSub.textContent = 'Sin turno activo';

      stopCounter();
    }
  }

  async function cargarEstado(){
    const res = await fetch("{{ route('turno.estado') }}", {
      headers: { "Accept": "application/json" }
    });

    const data = await res.json();

    activo = !!data.activo;
    apertura = data.apertura;
    cierre = data.cierre;
    baseSeconds = parseInt(data.segundos || 0, 10);

    render();
  }

  async function abrirTurno(){
    const res = await fetch("{{ route('turno.abrir') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrf
      },
      body: JSON.stringify({})
    });

    if (!res.ok) {
      const err = await res.json().catch(()=>({msg:'Error'}));
      alert(err.msg ?? 'No se pudo abrir turno');
      return;
    }
    await cargarEstado();
  }

  async function cerrarTurno(){
    const res = await fetch("{{ route('turno.cerrar') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrf
      },
      body: JSON.stringify({})
    });

    if (!res.ok) {
      const err = await res.json().catch(()=>({msg:'Error'}));
      alert(err.msg ?? 'No se pudo cerrar turno');
      return;
    }
    await cargarEstado();
  }

  btnToggle.addEventListener('click', async () => {
    if (activo) await cerrarTurno();
    else await abrirTurno();
  });

  cargarEstado();
</script>
@endsection