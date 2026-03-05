@extends('layouts.app')

@section('title', 'Panadería MALE')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush


<!-- 
saul07oct@gmail.com
435224
susanatino2811@gmail.com
12345678
anacoootes18@gmail.com
123456
Susana@gamil.com
123456
maxima538@gmail.com
xd@xd
123456

-->

@section('content')
<div class="login-wrap">
    <div class="login-card">

            <!-- Logo-->
        <div class="login-left">
            <img src="{{ asset('img/logo_male.png') }}" alt="Panadería MALE Login">
        </div>

        <!-- tabla de login  -->
        <div class="login-right">
            <h2 class="title">Iniciar Sesión</h2>

           
        <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                <div class="field">
                    <label for="email">Correo</label>
                    <div class="input-icon">
                        <input id="email" name="email" type="email" placeholder="*******@***.com" required>
                        <span class="icon">👤</span>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <div class="input-icon">
                        <input id="password" name="password" type="password" placeholder="******" required>
                        <span class="icon">🔒</span>
                    </div>
                </div>

                <button class="btn-signin" type="submit">Iniciar sesión</button>
            </form>

        </div>

    </div>
</div>
@endsection