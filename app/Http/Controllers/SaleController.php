<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\PedidoEspecial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleController extends Controller
{
    private function soloAdmin(): void
    {
        if (strtolower(trim(auth()->user()->rol ?? '')) !== 'administrador') {
            abort(403);
        }
    }

    public function index()
    {
        $this->soloAdmin();

        $productos = Producto::with('categoria')
            ->orderBy('nombre')
            ->get(['id_producto','nombre','precio_venta','stock','id_categoria']);

        $ultimasVentas = Venta::with('usuario')
            ->orderByDesc('id_venta')
            ->limit(5)
            ->get();

        return view('admin.sales', compact('productos','ultimasVentas'));
    }

    public function history(Request $request)
    {
        $this->soloAdmin();

        $q = $request->get('q');

        $dias = Venta::query()
            ->when($q, function ($query) use ($q) {
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

        if ($diaActual) {
            $ventasDia = Venta::with('usuario')
                ->whereDate('fecha', $diaActual)
                ->when($q, function ($query) use ($q) {
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

        $pedidosEntregados = PedidoEspecial::where('estado', 'entregado')
            ->orderByDesc('id_pedido')
            ->limit(10)
            ->get();

        return view('admin.sales_history', compact(
            'q',
            'dias',
            'diaActual',
            'ventasDia',
            'totalDia',
            'numVentas',
            'pedidosEntregados'
        ));
    }

    public function cortesDiarios(Request $request)
    {
        $this->soloAdmin();

        $q = trim((string) $request->get('q'));

        $sql = "
            SELECT
              d.dia,
              COALESCE(v.total_ventas, 0) AS ventas,
              COALESCE(p.total_pedidos, 0) AS pedidos_especiales,
              COALESCE(v.total_ventas, 0) + COALESCE(p.total_pedidos, 0) AS total_dia
            FROM (
              SELECT DATE(fecha) AS dia
              FROM ventas
              UNION
              SELECT DATE(entregado_en) AS dia
              FROM pedidos_especiales
              WHERE estado = 'entregado'
                AND entregado_en IS NOT NULL
            ) d
            LEFT JOIN (
              SELECT DATE(fecha) AS dia, SUM(total) AS total_ventas
              FROM ventas
              GROUP BY DATE(fecha)
            ) v ON v.dia = d.dia
            LEFT JOIN (
              SELECT DATE(entregado_en) AS dia, SUM(total) AS total_pedidos
              FROM pedidos_especiales
              WHERE estado = 'entregado'
                AND entregado_en IS NOT NULL
              GROUP BY DATE(entregado_en)
            ) p ON p.dia = d.dia
            ORDER BY d.dia DESC
        ";

        $rows = collect(DB::select($sql));

        if ($q !== '') {
            $qNorm = preg_replace('/\s+/', '', $q);

            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $qNorm)) {
                [$dd, $mm, $yyyy] = explode('/', $qNorm);
                $qNorm = "{$yyyy}-{$mm}-{$dd}";
            }

            $rows = $rows->filter(fn($r) => str_contains((string) $r->dia, $qNorm))->values();
        }

        $perPage = 1;
        $page = (int) $request->get('page', 1);
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        $dias = new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $first = $items->first();

        $diaActual          = $first->dia ?? null;
        $totalVentasPagina  = (float) ($first->ventas ?? 0);
        $totalPedidosPagina = (float) ($first->pedidos_especiales ?? 0);
        $totalGeneralPagina = (float) ($first->total_dia ?? 0);

        return view('admin.corte_diario', compact(
            'q',
            'dias',
            'diaActual',
            'totalVentasPagina',
            'totalPedidosPagina',
            'totalGeneralPagina'
        ));
    }

    public function printTicket($id)
    {
        $this->soloAdmin();

        $venta = Venta::with(['usuario', 'detalles.producto'])
            ->findOrFail($id);

        return view('admin.ticket_print', compact('venta'));
    }

    public function store(Request $request)
    {
        $this->soloAdmin();

        $data = $request->validate([
            'metodo_pago' => 'required|string|max:30',
            'descuento'   => 'nullable|numeric|min:0',
            'nota'        => 'nullable|string|max:200',
            'items'       => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        $descuento = (float) ($data['descuento'] ?? 0);

        return DB::transaction(function () use ($data, $descuento) {

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
                'id_usuario'  => auth()->user()->id_usuario,
                'fecha'       => now(),
                'metodo_pago' => $data['metodo_pago'],
                'subtotal'    => $subtotal,
                'descuento'   => $descuento,
                'total'       => $total,
                'nota'        => $data['nota'] ?? null,
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

         
            return redirect()
                ->route('admin.ticket.print', ['id' => $venta->id_venta])
                ->with('ok', "Venta registrada. Ticket #{$venta->id_venta}");
        });
    }
}