<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\CorteDiario;
use App\Models\PedidoEspecial;
use App\Models\Producto;

class DashboardController extends Controller
{
    public function index()
    {
        // ✅ Solo admin
        if (strtolower(trim(auth()->user()->rol ?? '')) !== 'administrador') {
            abort(403);
        }

        $hoy = Carbon::today();

        // ✅ Ventas del día (desde cortes_diarios)
        $corteHoy = CorteDiario::whereDate('fecha', $hoy)->first();
        $ventasHoy = $corteHoy ? (float)$corteHoy->total_ventas : 0;

        // ✅ Pedidos especiales (contadores)
        $pedidosPendientes = PedidoEspecial::where('estado', 'pendiente')->count();
        $pedidosEntregados = PedidoEspecial::where('estado', 'entregado')->count();

        // ✅ Pedidos especiales (lista para cards)
        $pedidosEspeciales = PedidoEspecial::where('estado', 'pendiente')
            ->orderBy('fecha_entrega', 'asc')
            ->limit(12)
            ->get()
            ->map(function ($p) {
                $hoy = Carbon::today();
                $entrega = Carbon::parse($p->fecha_entrega);
                $dias = $hoy->diffInDays($entrega, false);

                // si no tiene tag manual, asigna automático
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

        // ✅ Productos totales
        $totalProductos = Producto::count();

        return view('admin.dashboard', compact(
            'ventasHoy',
            'pedidosPendientes',
            'pedidosEntregados',
            'pedidosEspeciales',
            'totalProductos'
        ));
    }
    
}