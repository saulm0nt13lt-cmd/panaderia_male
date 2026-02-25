<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Turno;
use Carbon\Carbon;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoVentaController extends Controller
{
    private function soloEmpleado()
    {
        if (strtolower(trim(auth()->user()->rol)) !== 'empleado') {
            abort(403);
        }
    }

    //  MIS VENTAS (DÍA POR DÍA) - 1 día por página
    public function misVentas(Request $request)
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;
        $q = trim((string) $request->get('q'));

        // 1) Cada página = un DÍA (solo ventas de ESTE empleado)
        $dias = Venta::query()
            ->where('id_usuario', $userId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('id_venta', 'like', "%{$q}%")
                        ->orWhere('metodo_pago', 'like', "%{$q}%")
                        ->orWhere('nota', 'like', "%{$q}%");
                });
            })
            ->selectRaw('DATE(fecha) as dia')
            ->groupBy('dia')
            ->orderByDesc('dia')
            ->paginate(1)
            ->withQueryString();

        $diaActual = $dias->first()->dia ?? null;

        $ventasDia = collect();
        $totalDia  = 0;
        $numVentas = 0;

        // 2) Todas las ventas del día actual
        if ($diaActual) {
            $ventasDia = Venta::query()
                ->where('id_usuario', $userId)
                ->whereDate('fecha', $diaActual)
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($qq) use ($q) {
                        $qq->where('id_venta', 'like', "%{$q}%")
                            ->orWhere('metodo_pago', 'like', "%{$q}%")
                            ->orWhere('nota', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('fecha')
                ->get();

            $totalDia  = (float) $ventasDia->sum('total');
            $numVentas = (int) $ventasDia->count();
        }

        return view('empleado.misventas', compact(
            'q',
            'dias',
            'diaActual',
            'ventasDia',
            'totalDia',
            'numVentas'
        ));
    }

    public function index()
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;

        $turnoActivo = Turno::where('id_usuario', $userId)
            ->whereNull('fecha_cierre')
            ->exists();

        $productos = Producto::with('categoria')
            ->orderBy('nombre')
            ->get(['id_producto', 'nombre', 'precio_venta', 'stock', 'id_categoria']);

        $ultimasVentas = Venta::where('id_usuario', $userId)
            ->orderByDesc('id_venta')
            ->limit(5)
            ->get();

        return view('empleado.ventas', compact('productos', 'ultimasVentas', 'turnoActivo'));
    }

    public function store(Request $request)
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;

        $turnoActivo = Turno::where('id_usuario', $userId)
            ->whereNull('fecha_cierre')
            ->exists();

        if (!$turnoActivo) {
            return back()->withErrors(['turno' => 'Turno cerrado. Abre el turno para poder vender.'])->withInput();
        }

        $data = $request->validate([
            'metodo_pago' => 'required|string|max:30',
            'descuento'   => 'nullable|numeric|min:0',
            'nota'        => 'nullable|string|max:200',
            'items'       => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        $descuento = (float) ($data['descuento'] ?? 0);

        return DB::transaction(function () use ($data, $descuento, $userId) {

            $ids = collect($data['items'])->pluck('id_producto')->unique()->values();

            $productos = Producto::whereIn('id_producto', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id_producto');

            $subtotal = 0;

            foreach ($data['items'] as $it) {
                $p = $productos[$it['id_producto']] ?? null;
                if (!$p) abort(422, 'Producto no encontrado.');

                if ((int) $p->stock < (int) $it['cantidad']) {
                    abort(422, "Stock insuficiente para: {$p->nombre}");
                }

                $subtotal += ((float) $p->precio_venta) * ((int) $it['cantidad']);
            }

            $total = max($subtotal - $descuento, 0);

            $venta = Venta::create([
                'id_usuario'   => $userId,
                'fecha'        => now(),
                'metodo_pago'  => $data['metodo_pago'],
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'total'        => $total,
                'nota'         => $data['nota'] ?? null,
            ]);

            foreach ($data['items'] as $it) {
                $p = $productos[$it['id_producto']];
                $cantidad = (int) $it['cantidad'];
                $precio   = (float) $p->precio_venta;
                $importe  = $precio * $cantidad;

                VentaDetalle::create([
                    'id_venta'        => $venta->id_venta,
                    'id_producto'     => $p->id_producto,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'importe'         => $importe,
                ]);

                $p->stock = (int) $p->stock - $cantidad;
                $p->save();
            }

            return redirect()->route('empleado.ticket.print', ['id' => $venta->id_venta]);
        });
    }

    // ✅ TICKETS (DÍA POR DÍA) - 1 día por página (igual que mis ventas)
    public function tickets(Request $request)
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;

        $q     = trim((string) $request->get('q'));
        $desde = $request->get('desde'); // opcional YYYY-MM-DD
        $hasta = $request->get('hasta'); // opcional YYYY-MM-DD

        // 1) Cada página = un DÍA
        $dias = Venta::query()
            ->where('id_usuario', $userId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('id_venta', 'like', "%{$q}%")
                       ->orWhere('metodo_pago', 'like', "%{$q}%")
                       ->orWhere('nota', 'like', "%{$q}%");
                });
            })
            ->when($desde, fn($query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($query) => $query->whereDate('fecha', '<=', $hasta))
            ->selectRaw('DATE(fecha) as dia')
            ->groupBy('dia')
            ->orderByDesc('dia')
            ->paginate(1) // 👈 1 día por página
            ->withQueryString();

        $diaActual = $dias->first()->dia ?? null;

        $ticketsDia = collect();
        $totalDia   = 0;
        $numTickets = 0;

        // 2) Todos los tickets del día actual
        if ($diaActual) {
            $ticketsDia = Venta::query()
                ->where('id_usuario', $userId)
                ->whereDate('fecha', $diaActual)
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($qq) use ($q) {
                        $qq->where('id_venta', 'like', "%{$q}%")
                           ->orWhere('metodo_pago', 'like', "%{$q}%")
                           ->orWhere('nota', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('fecha')
                ->get();

            $totalDia   = (float) $ticketsDia->sum('total');
            $numTickets = (int) $ticketsDia->count();
        }

        // ✅ Nota: ya NO regresamos partial por AJAX porque ahora es "día por día"
        return view('empleado.tickets', compact(
            'q', 'desde', 'hasta',
            'dias', 'diaActual',
            'ticketsDia', 'totalDia', 'numTickets'
        ));
    }

    public function ticketShow($id_venta)
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;

        $venta = Venta::with(['usuario', 'detalles.producto'])
            ->where('id_venta', $id_venta)
            ->where('id_usuario', $userId)
            ->firstOrFail();

        return view('empleado.ticket_print', compact('venta'));
    }

    public function printTicket($id)
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;

        $venta = Venta::with(['usuario', 'detalles.producto'])
            ->where('id_venta', $id)
            ->where('id_usuario', $userId)
            ->firstOrFail();

        return view('empleado.ticket_print', compact('venta'));
    }
}