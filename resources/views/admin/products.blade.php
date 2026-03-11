@extends('admin.layout')

@section('title', 'Productos | Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endpush

@section('admin-content')

<div class="products-page">

  {{-- Header --}}
  <div class="products-head">
    <div>
      <h1 class="products-title">Productos</h1>
      <p class="products-subtitle">Inventario</p>
    </div>

    {{-- Buscador + botón Nuevo --}}
    <div class="products-actions">
      <form class="products-search" method="GET" action="{{ route('products') }}">
        <input class="input"id="productSearch"name="q"value="{{ $q ?? '' }}"placeholder="Buscar producto…"autocomplete="off"></form>

      <button type="button" class="btn btn-green" id="openAddProduct"><i class="fi fi-rr-add-document"></i> Nuevo producto</button>
<button type="button" class="btn-purple" id="openSpecialOrder">
  <i class="fi fi-rr-magic-wand"></i> Pedido especial</button>
    </div>
  </div>

  {{-- Mensaje --}}
  @if(session('ok'))
    <div class="alert success">{{ session('ok') }}</div>
  @endif

  {{-- =========================
      SECCIÓN: PANADERÍA
  ========================== --}}
  <h2 class="section-title">Panadería</h2>
  <div class="products-grid">
    @forelse($panaderia as $p)
      <article class="p-card product-card">
        <div class="p-top">
          <div class="p-img">
            <i class="fi fi-rr-bread"></i>
          </div>
        </div>

        <div class="p-body">
          <h3 class="p-name">{{ $p->nombre }}</h3>

          <div class="p-meta">
            <span class="badge">{{ $p->categoria->nombre ?? 'Panadería' }}</span>

            <span class="badge badge-stock {{ ($p->stock ?? 0) > 0 ? 'ok' : 'low' }}">
              Cantidad: {{ $p->stock ?? 0 }}
            </span>

            @if(!empty($p->tipo_pan))
              <span class="badge">{{ $p->tipo_pan }}</span>
            @endif
          </div>

          <p class="p-price">${{ number_format($p->precio_venta, 2) }}</p>

          <div class="p-actions">
            <button type="button"
              class="btn-pill btn-edit openEditProduct"
              data-action="{{ route('products.update', $p->id_producto) }}"
              data-id="{{ $p->id_producto }}"
              data-nombre="{{ $p->nombre }}"
              data-precio="{{ $p->precio_venta }}"
              data-stock="{{ $p->stock }}"
              data-categoria="{{ $p->id_categoria }}"
              data-tipo_pan="{{ $p->tipo_pan }}"
              data-tam_pastel="{{ $p->tam_pastel }}"
              data-sabor="{{ $p->sabor }}"
              data-tam_rosca="{{ $p->tam_rosca }}"
              data-relleno="{{ $p->relleno }}">
              Editar
            </button>

            <form method="POST" action="{{ route('products.destroy', $p->id_producto) }}">
              @csrf
              @method('DELETE')
              <button class="btn-pill btn-del" onclick="return confirm('¿Eliminar producto?')" type="submit">
                Eliminar
              </button>
            </form>
          </div>
        </div>
      </article>
    @empty
      <div class="empty-cards">No hay panes aún.</div>
    @endforelse
  </div>

  {{--PASTELES--}}
  <h2 class="section-title">Pasteles</h2>
  <div class="products-grid">
    @forelse($pasteles as $p)
      <article class="p-card product-card">
        <div class="p-top">
          <div class="p-img">
            <i class="fi fi-rr-cake-birthday"></i>
          </div>
        </div>

        <div class="p-body">
          <h3 class="p-name">{{ $p->nombre }}</h3>

          <div class="p-meta">
            <span class="badge">{{ $p->categoria->nombre ?? 'Pasteles' }}</span>

            <span class="badge badge-stock {{ ($p->stock ?? 0) > 0 ? 'ok' : 'low' }}">
              Cantidad: {{ $p->stock ?? 0 }}
            </span>

            @if(!empty($p->tam_pastel))
              <span class="badge">{{ $p->tam_pastel }}</span>
            @endif

            @if(!empty($p->sabor))
              <span class="badge">{{ $p->sabor }}</span>
            @endif
          </div>

          <p class="p-price">${{ number_format($p->precio_venta, 2) }}</p>

          <div class="p-actions">
            <button type="button"
              class="btn-pill btn-edit openEditProduct"
              data-action="{{ route('products.update', $p->id_producto) }}"
              data-id="{{ $p->id_producto }}"
              data-nombre="{{ $p->nombre }}"
              data-precio="{{ $p->precio_venta }}"
              data-stock="{{ $p->stock }}"
              data-categoria="{{ $p->id_categoria }}"
              data-tipo_pan="{{ $p->tipo_pan }}"
              data-tam_pastel="{{ $p->tam_pastel }}"
              data-sabor="{{ $p->sabor }}"
              data-tam_rosca="{{ $p->tam_rosca }}"
              data-relleno="{{ $p->relleno }}">
              Editar
            </button>

            <form method="POST" action="{{ route('products.destroy', $p->id_producto) }}">
              @csrf
              @method('DELETE')
              <button class="btn-pill btn-del" onclick="return confirm('¿Eliminar producto?')" type="submit">
                Eliminar
              </button>
            </form>
          </div>
        </div>
      </article>
    @empty
      <div class="empty-cards">No hay pasteles aún.</div>
    @endforelse
  </div>

  {{--SECCIÓN: ROSCAS --}}
  <h2 class="section-title">Roscas</h2>
  <div class="products-grid">
    @forelse($roscas as $p)
      <article class="p-card product-card">
        <div class="p-top">
          <div class="p-img">
            <i class="fi fi-rr-donut"></i>
          </div>
        </div>

        <div class="p-body">
          <h3 class="p-name">{{ $p->nombre }}</h3>

          <div class="p-meta">
            <span class="badge">{{ $p->categoria->nombre ?? 'Roscas' }}</span>

            <span class="badge badge-stock {{ ($p->stock ?? 0) > 0 ? 'ok' : 'low' }}">
              Cantidad: {{ $p->stock ?? 0 }}
            </span>

            @if(!empty($p->tam_rosca))
              <span class="badge">{{ $p->tam_rosca }}</span>
            @endif

            @if(!empty($p->relleno))
              <span class="badge">{{ $p->relleno }}</span>
            @endif
          </div>

          <p class="p-price">${{ number_format($p->precio_venta, 2) }}</p>

          <div class="p-actions">
            <button type="button"
              class="btn-pill btn-edit openEditProduct"
              data-action="{{ route('products.update', $p->id_producto) }}"
              data-id="{{ $p->id_producto }}"
              data-nombre="{{ $p->nombre }}"
              data-precio="{{ $p->precio_venta }}"
              data-stock="{{ $p->stock }}"
              data-categoria="{{ $p->id_categoria }}"
              data-tipo_pan="{{ $p->tipo_pan }}"
              data-tam_pastel="{{ $p->tam_pastel }}"
              data-sabor="{{ $p->sabor }}"
              data-tam_rosca="{{ $p->tam_rosca }}"
              data-relleno="{{ $p->relleno }}">
              Editar
            </button>

            <form method="POST" action="{{ route('products.destroy', $p->id_producto) }}">
              @csrf
              @method('DELETE')
              <button class="btn-pill btn-del" onclick="return confirm('¿Eliminar producto?')" type="submit">
                Eliminar
              </button>
            </form>
          </div>
        </div>
      </article>
    @empty
      <div class="empty-cards">No hay roscas aún.</div>
    @endforelse
  </div>

  {{-- MODAL: Agregar producto--}}
  <div class="modal-backdrop" id="addProductBackdrop" aria-hidden="true"></div>

  <div class="modal" id="addProductModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card">
      <div class="modal-head">
        <div>
          <h3 class="modal-title">Agregar producto</h3>
          <p class="modal-sub">Registra un producto nuevo</p>
        </div>

        <button type="button" class="modal-x" id="closeAddProduct" aria-label="Cerrar">
          <i class="fi fi-rr-exit"></i>
        </button>
      </div>

      <form class="modal-form" method="POST" action="{{ route('products.store') }}" id="addProductForm">
        @csrf

        <div class="modal-grid">
          <div class="field field-full">
            <label>Categoría</label>
            <select class="input" name="id_categoria" id="catSelect" required>
              <option value="">Selecciona una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div class="field">
            <label>Nombre</label>
            <input class="input" 
            name="nombre" 
            id="nombre" 
            oninput="this.value = this.value.replace(/[0-9]/g, '')" 
            required>
          </div>

          <div class="field">
            <label>Precio</label>
            <input class="input" name="precio_venta" id="precio" type="number" step="0.01" min="0" required>
          </div>

          <div class="field">
            <label>Cantidad</label>
            <input class="input" name="stock" id="stock" type="number" min="0" required>
          </div>

          {{-- PANADERÍA --}}
          <div class="field field-full dyn dyn-pan" style="display:none;">
            <label>Tipo de pan</label>
            <select class="input" name="tipo_pan" id="tipoPan">
              <option value="">Seleccionar</option>
              <option value="Dulce">Dulce</option>
              <option value="Salado">Salado</option>
            </select>
          </div>

          {{-- PASTELES --}}
          <div class="field dyn dyn-pastel" style="display:none;">
            <label>Tamaño pastel</label>
            <select class="input" name="tam_pastel" id="tamPastel">
              <option value="">Seleccionar</option>
              <option value="Chico">Chico</option>
              <option value="Mediano">Mediano</option>
              <option value="Grande">Grande</option>
            </select>
          </div>

          <div class="field dyn dyn-pastel" style="display:none;">
            <label>Sabor</label>
            <input class="input"
             name="sabor" 
             id="sabor" 
             maxlength="60" 
             oninput="this.value = this.value.replace(/[0-9]/g, '')" 
             placeholder="Ej. Chocolate, Vainilla…">
          </div>

          {{-- ROSCAS --}}
          <div class="field dyn dyn-rosca" style="display:none;">
            <label>Tamaño rosca</label>
            <select class="input" name="tam_rosca" id="tamRosca">
              <option value="">Seleccionar</option>
              <option value="Chica">Chica</option>
              <option value="Mediana">Mediana</option>
              <option value="Grande">Grande</option>
            </select>
          </div>

          <div class="field dyn dyn-rosca" style="display:none;">
            <label>Relleno</label>
            <input class="input" 
            name="relleno" 
            id="relleno" 
            oninput="this.value = this.value.replace(/[0-9]/g, '')" 
            maxlength="80">
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-gray" id="cancelAddProduct">Cancelar</button>
          <button class="btn btn-green" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  {{--MODAL: Editar producto--}}
  <div class="modal-backdrop" id="editProductBackdrop" aria-hidden="true"></div>

  <div class="modal" id="editProductModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card">
      <div class="modal-head">
        <div>
          <h3 class="modal-title">Editar producto</h3>
          <p class="modal-sub">Actualiza la información</p>
        </div>

        <button type="button" class="modal-x" id="closeEditProduct" aria-label="Cerrar">
          <i class="fi fi-rr-exit"></i>
        </button>
      </div>

      <form class="modal-form" method="POST" id="editProductForm">
        @csrf
        @method('PUT')

        <div class="modal-grid">
          <div class="field field-full">
            <label>Nombre</label>
            <input class="input" 
            name="nombre" 
            id="edit_nombre" 
            oninput="this.value = this.value.replace(/[0-9]/g, '')" 
            required>
          </div>

          <div class="field">
            <label>Precio</label>
            <input class="input" name="precio_venta" id="edit_precio" type="number" step="0.01" min="0" required>
          </div>

          <div class="field">
            <label>Cantidad</label>
            <input class="input" name="stock" id="edit_stock" type="number" min="0" required>
          </div>

          <div class="field field-full">
            <label>Categoría</label>
            <select class="input" name="id_categoria" id="edit_categoria" required>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
              @endforeach
            </select>
          </div>

          {{-- PAN --}}
          <div class="field field-full edit-pan" style="display:none;">
            <label>Tipo de pan</label>
            <select class="input" name="tipo_pan" id="edit_tipo_pan">
              <option value="">Seleccionar</option>
              <option value="Dulce">Dulce</option>
              <option value="Salado">Salado</option>
            </select>
          </div>

          {{-- PASTEL --}}
          <div class="field edit-pastel" style="display:none;">
            <label>Tamaño pastel</label>
            <select class="input" name="tam_pastel" id="edit_tam_pastel">
              <option value="">Seleccionar</option>
              <option value="Chico">Chico</option>
              <option value="Mediano">Mediano</option>
              <option value="Grande">Grande</option>
            </select>
          </div>

          <div class="field edit-pastel" style="display:none;">
            <label>Sabor</label>
            <input class="input" name="sabor" id="edit_sabor" maxlength="60">
          </div>

          {{-- ROSCA --}}
          <div class="field edit-rosca" style="display:none;">
            <label>Tamaño rosca</label>
            <select class="input" name="tam_rosca" id="edit_tam_rosca">
              <option value="">Seleccionar</option>
              <option value="Chica">Chica</option>
              <option value="Mediana">Mediana</option>
              <option value="Grande">Grande</option>
            </select>
          </div>

          <div class="field edit-rosca" style="display:none;">
            <label>Relleno</label>
            <input class="input" name="relleno" id="edit_relleno" maxlength="80">
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-gray" id="cancelEditProduct">Cancelar</button>
          <button type="submit" class="btn btn-green">Guardar</button>
        </div>
      </form>
    </div>
  </div>

</div>
{{-- =========================
    MODAL: Pedido especial
========================== --}}
<div class="modal-backdrop" id="specialOrderBackdrop" aria-hidden="true"></div>

<div class="modal" id="specialOrderModal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-card">
    <div class="modal-head">
      <div>
        <h3 class="modal-title">Pedido Especial</h3>
        <p class="modal-sub">Pedido Personalizado</p>
      </div>

      <button type="button" class="modal-x" id="closeSpecialOrder" aria-label="Cerrar">
        <i class="fi fi-rr-exit"></i>
      </button>
    </div>

    <form class="modal-form" method="POST" action="{{ route('special_orders.store') }}" id="specialOrderForm">
      @csrf

      <div class="modal-grid">

      <div class="field">
          <label>Nombre del Cliente</label>
          <input class="input" 
          type="text" 
          name="cliente_nombre" 
          maxlength="120" 
          oninput="this.value = this.value.replace(/[0-9]/g, '')" 
          required>
        </div>

        <div class="field">
          <label>Fecha de entrega</label>
          <input class="input" type="date" name="fecha_entrega" required>
        </div>

        <div class="field">
          <label>Teléfono celular</label>
          <input class="input" type="number" name="cliente_telefono" maxlength="20" placeholder="Ej. 246293819" required>
        </div>

        <div class="field">
          <label>Precio total</label>
          <input class="input" type="number" step="0.01" min="0" name="total" id="so_total" required>
        </div>

        <div class="field">
          <label>Anticipo</label>
          <input class="input" type="number" step="0.01" min="0" name="anticipo" id="so_anticipo" value="0">
        </div>

        <div class="field">
          <label>Restante</label>
          <input class="input" type="number" step="0.01" min="0" name="restante" id="so_restante" readonly>
        </div>

        <div class="field field-full">
          <label>Descripción</label>
          <textarea class="input" name="descripcion" rows="3" maxlength="500"
            placeholder="Describe lo que quiere el cliente…" required></textarea>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-red" id="cancelSpecialOrder"> Cancelar</button>
        <button class="btn btn-green" type="submit"> Guardar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

  // =========================
  // MODAL AGREGAR
  // =========================
  const openBtn   = document.getElementById('openAddProduct');
  const addModal  = document.getElementById('addProductModal');
  const addBack   = document.getElementById('addProductBackdrop');
  const closeBtn  = document.getElementById('closeAddProduct');
  const cancelBtn = document.getElementById('cancelAddProduct');

  function openAdd(){
    addModal?.classList.add('is-open');
    addBack?.classList.add('is-open');
    addModal?.setAttribute('aria-hidden','false');
    addBack?.setAttribute('aria-hidden','false');
  }
  function closeAdd(){
    addModal?.classList.remove('is-open');
    addBack?.classList.remove('is-open');
    addModal?.setAttribute('aria-hidden','true');
    addBack?.setAttribute('aria-hidden','true');

    // limpiar ?new=1 cuando cierres
    const url = new URL(window.location.href);
    url.searchParams.delete('new');
    window.history.replaceState({}, '', url.toString());
  }

  openBtn?.addEventListener('click', () => {
    openAdd();
    setTimeout(() => {
      if(!catSelect) return;
      catSelect.value = '';
      applyCategory();
    }, 10);
  });

  closeBtn?.addEventListener('click', closeAdd);
  cancelBtn?.addEventListener('click', closeAdd);
  addBack?.addEventListener('click', closeAdd);


  // =========================
  // FORM DINÁMICO (AGREGAR)
  // =========================
  const catSelect  = document.getElementById('catSelect');
  const panFields    = document.querySelectorAll('.dyn-pan');
  const pastelFields = document.querySelectorAll('.dyn-pastel');
  const roscaFields  = document.querySelectorAll('.dyn-rosca');

  function showGroup(group, show){
    group.forEach(el => {
      el.style.display = show ? '' : 'none';
      el.querySelectorAll('input, select, textarea').forEach(inp => {
        inp.disabled = !show;
        if(!show) inp.value = '';
      });
    });
  }

  function applyCategory(){
    const id = parseInt(catSelect?.value || '0', 10);

    showGroup(panFields, false);
    showGroup(pastelFields, false);
    showGroup(roscaFields, false);

    if(id === 1) showGroup(panFields, true);
    if(id === 2) showGroup(pastelFields, true);
    if(id === 3) showGroup(roscaFields, true);
  }

  catSelect?.addEventListener('change', applyCategory);


  // =========================
  // MODAL EDITAR
  // =========================
  const editModal = document.getElementById('editProductModal');
  const editBack  = document.getElementById('editProductBackdrop');
  const closeEdit = document.getElementById('closeEditProduct');
  const cancelEdit= document.getElementById('cancelEditProduct');
  const editForm  = document.getElementById('editProductForm');

  function openEdit(){
    editModal?.classList.add('is-open');
    editBack?.classList.add('is-open');
    editModal?.setAttribute('aria-hidden','false');
    editBack?.setAttribute('aria-hidden','false');
  }
  function closeEditModal(){
    editModal?.classList.remove('is-open');
    editBack?.classList.remove('is-open');
    editModal?.setAttribute('aria-hidden','true');
    editBack?.setAttribute('aria-hidden','true');
  }

  function setEditGroup(selector, show){
    document.querySelectorAll(selector).forEach(el => {
      el.style.display = show ? '' : 'none';
      el.querySelectorAll('input, select, textarea').forEach(inp => {
        inp.disabled = !show;
        if(!show) inp.value = '';
      });
    });
  }

  document.querySelectorAll('.openEditProduct').forEach(btn => {
    btn.addEventListener('click', () => {
      if(!editForm) return;

      editForm.action = btn.dataset.action;

      document.getElementById('edit_nombre').value   = btn.dataset.nombre || '';
      document.getElementById('edit_precio').value   = btn.dataset.precio || '';
      document.getElementById('edit_stock').value    = btn.dataset.stock || '';
      document.getElementById('edit_categoria').value= btn.dataset.categoria || '';

      setEditGroup('.edit-pan', false);
      setEditGroup('.edit-pastel', false);
      setEditGroup('.edit-rosca', false);

      const cat = parseInt(btn.dataset.categoria || '0', 10);

      if(cat === 1){
        setEditGroup('.edit-pan', true);
        document.getElementById('edit_tipo_pan').value = btn.dataset.tipo_pan || '';
      }
      if(cat === 2){
        setEditGroup('.edit-pastel', true);
        document.getElementById('edit_tam_pastel').value = btn.dataset.tam_pastel || '';
        document.getElementById('edit_sabor').value      = btn.dataset.sabor || '';
      }
      if(cat === 3){
        setEditGroup('.edit-rosca', true);
        document.getElementById('edit_tam_rosca').value = btn.dataset.tam_rosca || '';
        document.getElementById('edit_relleno').value   = btn.dataset.relleno || '';
      }

      openEdit();
    });
  });

  closeEdit?.addEventListener('click', closeEditModal);
  cancelEdit?.addEventListener('click', closeEditModal);
  editBack?.addEventListener('click', closeEditModal);


  // =========================
  // MODAL PEDIDO ESPECIAL
  // =========================
  const soOpen   = document.getElementById('openSpecialOrder'); // botón en productos (si existe)
  const soModal  = document.getElementById('specialOrderModal');
  const soBack   = document.getElementById('specialOrderBackdrop');
  const soClose  = document.getElementById('closeSpecialOrder');
  const soCancel = document.getElementById('cancelSpecialOrder');

  const soTotal    = document.getElementById('so_total');
  const soAnticipo = document.getElementById('so_anticipo');
  const soRestante = document.getElementById('so_restante');

  function openSO(){
    soModal?.classList.add('is-open');
    soBack?.classList.add('is-open');
    soModal?.setAttribute('aria-hidden','false');
    soBack?.setAttribute('aria-hidden','false');
    setTimeout(calcRestante, 10);
  }

  function closeSO(){
    soModal?.classList.remove('is-open');
    soBack?.classList.remove('is-open');
    soModal?.setAttribute('aria-hidden','true');
    soBack?.setAttribute('aria-hidden','true');

    // ✅ limpiar ?special=1 cuando cierres
    const url = new URL(window.location.href);
    url.searchParams.delete('special');
    window.history.replaceState({}, '', url.toString());
  }

  function calcRestante(){
    const total = parseFloat(soTotal?.value || '0');
    const antic = parseFloat(soAnticipo?.value || '0');
    const rest  = Math.max(total - antic, 0);
    if(soRestante) soRestante.value = rest.toFixed(2);
  }

  soOpen?.addEventListener('click', openSO);
  soClose?.addEventListener('click', closeSO);
  soCancel?.addEventListener('click', closeSO);
  soBack?.addEventListener('click', closeSO);
  soTotal?.addEventListener('input', calcRestante);
  soAnticipo?.addEventListener('input', calcRestante);


  // =========================
  // BUSCADOR EN VIVO
  // =========================
  const input = document.getElementById('productSearch');
  input?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.product-card').forEach(card => {
      const name = card.querySelector('.p-name')?.innerText.toLowerCase() || '';
      card.style.display = name.includes(q) ? '' : 'none';
    });
  });


  // =========================
  // ESC para ambos modales (LOS 3)
  // =========================
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){
      closeAdd();
      closeEditModal();
      closeSO();
    }
  });


  // =========================
  // ABRIR MODALES DESDE URL
  // =========================
  @if(request('new'))
    openAdd();
  @endif

  @if(request('special'))
    openSO();
  @endif

});
</script>
@endpush