<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function usuarios()
    {
        
        $admins = User::where('rol', 'Administrador')->get();
        $empleados = User::where('rol', 'Empleado')->get();

        return view('admin.users', compact('admins', 'empleados'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'nombre_completo' => 'required|string|max:120',
        'username'        => 'required|string|max:50|alpha_dash|unique:usuarios,username',
        'email'           => 'required|email|max:120|unique:usuarios,email',
        'password'        => 'required|string|min:6|max:60',
        'rol'             => 'required|in:Administrador,Empleado',
        'estado'          => 'required|in:0,1',
    ]);

    User::create([
        'nombre_completo' => $data['nombre_completo'],
        'username'        => $data['username'],
        'email'           => $data['email'],
        'password_hash'   => Hash::make($data['password']),
        'rol'             => $data['rol'],
        'estado'          => (int)$data['estado'],
    ]);

    return redirect()->route('admin_users')->with('ok', 'Usuario creado correctamente.');
}
public function destroy($id_usuario)
{
    $u = User::where('id_usuario', $id_usuario)->firstOrFail();

    
    if(auth()->id() == $u->id_usuario) return back()->with('err','No puedes eliminar tu usuario.');

    $u->delete();

    return redirect()->route('admin_users')->with('ok', 'Usuario eliminado.');
}
public function update(Request $request, $id_usuario)
{
    $u = User::findOrFail($id_usuario);

    $data = $request->validate([
        'nombre_completo' => 'required|string|max:120',
        'username'        => 'required|string|max:50|alpha_dash|unique:usuarios,username,'.$id_usuario.',id_usuario',
        'email'           => 'required|email|max:120|unique:usuarios,email,'.$id_usuario.',id_usuario',
        'rol'             => 'required|in:Administrador,Empleado',
        'estado'          => 'required|in:0,1',
        
        'password'        => 'nullable|string|min:6|max:60',
    ]);

    $u->nombre_completo = $data['nombre_completo'];
    $u->username        = $data['username'];
    $u->email           = $data['email'];
    $u->rol             = $data['rol'];
    $u->estado          = (int)$data['estado'];


    if (!empty($data['password'])) {
        $u->password_hash = Hash::make($data['password']);
    }

    $u->save();

    return redirect()->route('admin_users')->with('ok', 'Usuario actualizado.');
}
}