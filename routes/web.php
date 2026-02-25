<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoEspecialController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoVentaController;
use App\Http\Controllers\EmpleadoDashboardController;

use App\Models\PedidoEspecial;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD GENERAL (REDIRECCIÓN POR ROL)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMIN
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/dashboard', function () {

        if (strtolower(trim(auth()->user()->rol)) !== 'administrador') {
            abort(403);
        }

        $pedidosPendientes = PedidoEspecial::where('estado', 'pendiente')->count();
        $pedidosEntregados = PedidoEspecial::where('estado', 'entregado')->count();

        $pedidosEspeciales = PedidoEspecial::where('estado', 'pendiente')
            ->orderBy('fecha_entrega', 'asc')
            ->limit(12)
            ->get()
            ->map(function ($p) {
                $hoy = Carbon::today();
                $entrega = Carbon::parse($p->fecha_entrega);
                $dias = $hoy->diffInDays($entrega, false);

                if (empty($p->tag)) {
                    if ($dias < 0) $p->tag_auto = 'urgent';
                    elseif ($dias <= 1) $p->tag_auto = 'warn';
                    elseif ($dias <= 3) $p->tag_auto = 'ok';
                    else $p->tag_auto = 'normal';
                } else {
                    $p->tag_auto = $p->tag;
                }

                $p->dias_restantes = $dias;
                return $p;
            });

        return view('admin.dashboard', compact(
            'pedidosEspeciales',
            'pedidosPendientes',
            'pedidosEntregados'
        ));
    })->name('admin.dashboard');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD EMPLEADO
    |--------------------------------------------------------------------------
    */


Route::get('/empleado/dashboard', [EmpleadoDashboardController::class, 'index'])
  ->name('empleado.dashboard');

    /*
    |--------------------------------------------------------------------------
    | VENTAS (EMPLEADO)  ✅ POS REAL
    |--------------------------------------------------------------------------
    */
    Route::get('/empleado/ventas', [EmpleadoVentaController::class, 'index'])
        ->name('empleado.ventas');

    Route::post('/empleado/ventas', [EmpleadoVentaController::class, 'store'])
        ->name('empleado.ventas.store');

 Route::get('/empleado/mis-ventas', [EmpleadoVentaController::class, 'misVentas'])
    ->name('empleado.misventas');

Route::get('/empleado/ticket/{id}/print', [EmpleadoVentaController::class, 'printTicket'])
    ->name('empleado.ticket.print');

   
Route::get('/empleado/tickets', [EmpleadoVentaController::class, 'tickets'])
  ->name('empleado.tickets');

Route::get('/empleado/tickets/{id_venta}', [EmpleadoVentaController::class, 'ticketShow'])
  ->name('empleado.tickets.show');

    Route::get('/empleado/turno', function () {
        if (strtolower(trim(auth()->user()->rol)) !== 'empleado') abort(403);
        return view('empleado.turno');
    })->name('empleado.turno');


    /*
    |--------------------------------------------------------------------------
    | TURNO
    |--------------------------------------------------------------------------
    */
    Route::get('/turno/estado', [TurnoController::class, 'estado'])->name('turno.estado');
    Route::post('/turno/abrir', [TurnoController::class, 'abrir'])->name('turno.abrir');
    Route::post('/turno/cerrar', [TurnoController::class, 'cerrar'])->name('turno.cerrar');

    /*
    |--------------------------------------------------------------------------
    | ADMIN - USUARIOS
    |--------------------------------------------------------------------------
    */
    Route::get('/users', [UserController::class, 'usuarios'])->name('admin_users');
    Route::post('/users', [UserController::class, 'store'])->name('admin_users.store');
    Route::put('/users/{id_usuario}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id_usuario}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/admin/ticket/{id}/print', [SaleController::class, 'printTicket'])->name('admin.ticket.print');
    
    Route::get('/admin/pedidos/{id}/print', [PedidoEspecialController::class, 'print'])->name('admin.pedidos.print');
    /*
    |--------------------------------------------------------------------------
    | ADMIN - PRODUCTOS
    |--------------------------------------------------------------------------
    */
    Route::get('/products', [ProductoController::class, 'index'])->name('products');
    Route::post('/products', [ProductoController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductoController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductoController::class, 'destroy'])->name('products.destroy');

    /*
    |--------------------------------------------------------------------------
    | ADMIN - PEDIDOS ESPECIALES
    |--------------------------------------------------------------------------
    */
    Route::get('/pedidos/historial', [PedidoEspecialController::class, 'historial'])
        ->name('pedidos_historial');

    Route::post('/admin/pedidos-especiales', [PedidoEspecialController::class, 'store'])
        ->name('special_orders.store');

    Route::patch('/admin/pedidos-especiales/{id}/tag', [PedidoEspecialController::class, 'updateTag'])
        ->name('special_orders.tag');

    Route::patch('/admin/pedidos-especiales/{id}/estado', [PedidoEspecialController::class, 'updateEstado'])
        ->name('special_orders.estado');

    /*
    |--------------------------------------------------------------------------
    | ADMIN - VENTAS
    |--------------------------------------------------------------------------
    */
    Route::get('/sales', [SaleController::class, 'index'])->name('sales');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/history', [SaleController::class, 'history'])->name('sales_history');

    /*
    |--------------------------------------------------------------------------
    | CORTE DIARIO
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/corte-diario', [SaleController::class, 'cortesDiarios'])
        ->name('corte_diario');

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

});