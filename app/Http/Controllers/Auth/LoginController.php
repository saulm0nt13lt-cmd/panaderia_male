<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

  public function login(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $credentials = $request->only('email', 'password');
    $credentials['estado'] = 1;

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $rol = strtolower(trim(Auth::user()->rol));

        if ($rol === 'empleado') {
            return redirect()->route('empleado.dashboard');
        }

        return redirect()->route('dashboard'); 
    }

    return back()->withErrors([
        'email' => 'Correo o contraseña incorrectos',
    ])->onlyInput('email');
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}