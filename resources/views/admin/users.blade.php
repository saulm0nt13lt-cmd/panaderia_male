@extends('admin.layout')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('admin-content')

<div class="users-page">

  <div class="users-top">
    <div>
      <h1 class="users-title">Usuarios</h1>
    </div>

    <div class="users-actions">
      <div class="searchbox">
        <span class="icon">🔎</span>
        <input id="userSearch" type="text" placeholder="Buscar por nombre, email o usuario…">
      </div>

    <button type="button" class="btn-primary" id="openUserModal">
  <i class="fi fi-rr-user-add"></i> Nuevo</button>
    </div>
  </div>

  {{-- ADMINISTRADORES --}}
  <section class="cardpro" data-table="admins">
    <header class="cardpro-head">
      <div class="cardpro-title">
        <span class="pill pill-admin">Administradores</span>
        <span class="count">{{ $admins->count() }}</span>
      </div>
      <div class="hint">Rol: <b>Administrador</b></div>
    </header>

    <div class="tablewrap">
      <table class="protable" id="adminsTable">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Usuario</th>
            <th style="width:120px">Estado</th>
            <th style="width:140px">Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse($admins as $i => $u)
            <tr class="user-row">
              <td class="muted">{{ $i+1 }}</td>

              <td>
                <div class="who">
                  <div class="avatar">{{ strtoupper(substr($u->nombre_completo,0,1)) }}</div>
                  <div class="who-text">
                    <div class="who-name">{{ $u->nombre_completo }}</div>
                  </div>
                </div>
              </td>

              <td>{{ $u->email }}</td>
              <td><span class="chip">{{ ' ' . $u->username }}</span></td>

              <td>
                @if((int)($u->estado ?? 1) === 1)
                  <span class="badge ok">Activo</span>
                @else
                  <span class="badge off">Inactivo</span>
                @endif
              </td>
              <td>
  <div class="actions-col">
    <button type="button"
            class="btn-edit"
            data-id="{{ $u->id_usuario }}"
            data-nombre="{{ $u->nombre_completo }}"
            data-username="{{ $u->username }}"
            data-email="{{ $u->email }}"
            data-rol="{{ $u->rol }}"
            data-estado="{{ $u->estado }}"
            onclick="openEdit(this)"><i class="fi fi-rr-pencil"></i>  Editar</button>

    <form action="{{ route('users.destroy', $u->id_usuario) }}"
          method="POST">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn-danger"><i class="fi fi-rr-delete-user"></i>  Eliminar</button>
    </form>
  </div>
</td>

            </tr>
          @empty
            <tr>
              <td colspan="6" class="empty">
                No hay administradores
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- EMPLEADOS --}}
  <section class="cardpro" data-table="empleados">
    <header class="cardpro-head">
      <div class="cardpro-title">
        <span class="pill pill-emp">Empleados</span>
        <span class="count">{{ $empleados->count() }}</span>
      </div>
      <div class="hint">Rol: <b>Empleado</b></div>
    </header>

    <div class="tablewrap">
      <table class="protable" id="empleadosTable">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Usuario</th>
            <th style="width:120px">Estado</th>
            <th style="width:140px">Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse($empleados as $i => $u)
            <tr class="user-row">
              <td class="muted">{{ $i+1 }}</td>

              <td>
                <div class="who">
                  <div class="avatar">{{ strtoupper(substr($u->nombre_completo,0,1)) }}</div>
                  <div class="who-text">
                    <div class="who-name">{{ $u->nombre_completo }}</div>
                    <div class="who-role">Empleado</div>
                  </div>
                </div>
              </td>

              <td>{{ $u->email }}</td>
              <td><span class="chip">{{ ' ' . $u->username }}</span></td>

              <td>
                @if((int)($u->estado ?? 1) === 1)
                  <span class="badge ok">Activo</span>
                @else
                  <span class="badge off">Inactivo</span>
                @endif
              </td>

              <td>
  <div class="actions-col">
    <button type="button"
            class="btn-edit"
            data-id="{{ $u->id_usuario }}"
            data-nombre="{{ $u->nombre_completo }}"
            data-username="{{ $u->username }}"
            data-email="{{ $u->email }}"
            data-rol="{{ $u->rol }}"
            data-estado="{{ $u->estado }}"
            onclick="openEdit(this)"><i class="fi fi-rr-pencil"></i></i> Editar</button>

    <form action="{{ route('users.destroy', $u->id_usuario) }}"
          method="POST">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn-danger"><i class="fi fi-rr-delete-user"></i> Eliminar</button>
    </form>
  </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="empty">
                No hay empleados
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>

@if(session('ok'))
  <div class="alert-ok">{{ session('ok') }}</div>
@endif

<div class="modal-backdrop" id="userModal" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true">
    <div class="modal-head">
      <div>
        <h3 id="modalTitle">Crear usuario</h3>
        <p>Registra un empleado o administrador</p>
      </div>
      <button class="modal-close" type="button" id="closeUserModal" aria-label="Cerrar">
  <i class="fi fi-rr-exit"></i>
</button>
    </div>

    <form method="POST" action="{{ route('admin_users.store') }}" id="userForm" class="modal-body">
      @csrf
      @method('POST')

      <div class="grid">
        <div class="field">
          <label>Nombre completo</label>
          <input 
          name="nombre_completo" 
          required 
          oninput="this.value = this.value.replace(/[0-9]/g, '')" 
          value="{{ old('nombre_completo') }}">
          @error('nombre_completo') <small class="err">{{ $message }}</small> @enderror
        </div>

        <div class="field">
          <label>Usuario</label>
          <input name="username" 
          required 
           oninput="this.value = this.value.replace(/[0-9]/g, '')" 
          value="{{ old('username') }}">
          @error('username') <small class="err">{{ $message }}</small> @enderror
        </div>

        <div class="field">
  <label>Email</label>
  <input name="email"
         type="email"
         autocomplete="off"
         required
         value="{{ old('email') }}">
  @error('email') <small class="err">{{ $message }}</small> @enderror
</div>

<div class="field">
  <label>Contraseña</label>
  <input name="password"
         type="password"
         autocomplete="new-password">
  @error('password') <small class="err">{{ $message }}</small> @enderror
</div>

        <div class="field">
          <label>Rol</label>
          <select name="rol" required>
            <option value="Empleado" {{ old('rol')==='Empleado' ? 'selected' : '' }}>Empleado</option>
            <option value="Administrador" {{ old('rol')==='Administrador' ? 'selected' : '' }}>Administrador</option>
          </select>
          @error('rol') <small class="err">{{ $message }}</small> @enderror
        </div>

        <div class="field">
          <label>Estado</label>
          <select name="estado" required>
            <option value="1" {{ old('estado','1')==='1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado')==='0' ? 'selected' : '' }}>Inactivo</option>
          </select>
          @error('estado') <small class="err">{{ $message }}</small> @enderror
        </div>
      </div>

      <div class="modal-foot">
        <button type="button" class="btn-cancel" id="cancelUserModal">Cancelar</button>
        <button type="submit" class="btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
  const modal = document.getElementById('userModal');
  const openBtn = document.getElementById('openUserModal');
  const closeBtn = document.getElementById('closeUserModal');
  const cancelBtn = document.getElementById('cancelUserModal');

  function openModal(){ modal.classList.add('show'); }
  function closeModal(){ modal.classList.remove('show'); }

  const userForm = document.getElementById('userForm');
  const methodField = userForm.querySelector('input[name="_method"]');

  function openCreate(){
    userForm.action = "{{ route('admin_users.store') }}";
    methodField.value = 'POST';
    userForm.reset();
    document.getElementById('modalTitle').innerText = 'Crear usuario';
    openModal();
  }

  function openEdit(btn){
    const id = btn.dataset.id;

    userForm.action = `/users/${id}`;
    methodField.value = 'PUT';

    userForm.querySelector('[name="nombre_completo"]').value = btn.dataset.nombre || '';
    userForm.querySelector('[name="username"]').value = btn.dataset.username || '';
    userForm.querySelector('[name="email"]').value = btn.dataset.email || '';
    userForm.querySelector('[name="rol"]').value = btn.dataset.rol || 'Empleado';
    userForm.querySelector('[name="estado"]').value = btn.dataset.estado || '1';
    userForm.querySelector('[name="password"]').value = '';

    document.getElementById('modalTitle').innerText = 'Editar usuario';
    openModal();
  }

  openBtn?.addEventListener('click', openCreate);
  closeBtn?.addEventListener('click', closeModal);
  cancelBtn?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => { if(e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeModal(); });

  @if(request()->query('new') == '1')
  openModal();
@endif

  @if ($errors->any())
    openModal();
  @endif
</script>

<script>
  const input = document.getElementById('userSearch');
  input?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();
    ['adminsTable','empleadosTable'].forEach(id => {
      const table = document.getElementById(id);
      if(!table) return;
      table.querySelectorAll('tbody tr.user-row').forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(q) ? '' : 'none';
      });
    });
  });
</script>

@endsection