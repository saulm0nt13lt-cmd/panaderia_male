<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoEspecial;
use Carbon\Carbon;

class PedidoEspecialController extends Controller
{
    private function soloAdmin()
    {
        if (strtolower(trim(auth()->user()->rol)) !== 'administrador') {
            abort(403);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_entrega'    => ['required','date'],
            'cliente_nombre'   => ['required','string','max:120'],
            'cliente_telefono' => ['required','string','max:20'],
            'total'            => ['required','numeric','min:0'],
            'anticipo'         => ['nullable','numeric','min:0'],
            'descripcion'      => ['required','string','max:500'],
        ]);

        $total = (float) $data['total'];
        $antic = (float) ($data['anticipo'] ?? 0);

        if ($antic > $total) {
            return back()->withErrors(['anticipo' => 'El anticipo no puede ser mayor al total.'])->withInput();
        }

        // ✅ Puedes calcularlo aquí (y el trigger lo confirma)
        $data['anticipo']  = $antic;
        $data['restante']  = max($total - $antic, 0);

        PedidoEspecial::create($data);

        return back()->with('ok', 'Pedido especial guardado ✅');
    }

    public function updateTag(Request $request, $id)
    {
        $this->soloAdmin();

        $request->validate([
            'tag' => ['nullable','in:normal,ok,warn,urgent'],
        ]);

        $pedido = PedidoEspecial::where('id_pedido', $id)->firstOrFail();
        $pedido->tag = $request->tag; // puede ser null para "Auto"
        $pedido->save();

        return back()->with('ok', 'Etiqueta actualizada ✅');
    }

    public function updateEstado(Request $request, $id)
    {
        $this->soloAdmin();

        $request->validate([
            'estado' => ['required','in:pendiente,entregado'],
        ]);

        $pedido = PedidoEspecial::where('id_pedido', $id)->firstOrFail();
        $nuevoEstado = $request->estado;

        // ✅ Si cambia a ENTREGADO, guardamos la fecha/hora real de entrega
        if ($nuevoEstado === 'entregado') {
            // solo la primera vez (para que no se mueva si luego editas otras cosas)
            $pedido->entregado_en = $pedido->entregado_en ?? now();
        }

        // ✅ Si lo regresan a pendiente, limpiamos entregado_en (opcional pero recomendado)
        if ($nuevoEstado === 'pendiente') {
            $pedido->entregado_en = null;
        }

        $pedido->estado = $nuevoEstado;
        $pedido->save();

        return back()->with('ok', 'Estado actualizado ✅');
    }

    // ✅ HISTORIAL (DÍA POR DÍA) PARA pedidos_historial.blade.php
    public function historial(Request $request)
    {
        $this->soloAdmin();

        $q = $request->get('q');

        // ✅ 1) CADA PÁGINA = UN DÍA (por created_at)
        $dias = PedidoEspecial::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('cliente_nombre', 'like', "%{$q}%")
                       ->orWhere('cliente_telefono', 'like', "%{$q}%")
                       ->orWhere('descripcion', 'like', "%{$q}%")
                       ->orWhere('estado', 'like', "%{$q}%");
                });
            })
            ->selectRaw('DATE(created_at) as dia')
            ->groupBy('dia')
            ->orderByDesc('dia')
            ->paginate(1) // 👈 1 día por página
            ->withQueryString();

        $diaActual = $dias->first()->dia ?? null;

        $pedidosDia = collect();
        $totalDia   = 0;
        $numPedidos = 0;

        if ($diaActual) {
            // ✅ 2) TODOS los pedidos de ese día
            $pedidosDia = PedidoEspecial::query()
                ->whereDate('created_at', $diaActual)
                ->when($q, function ($query) use ($q) {
                    $query->where(function ($qq) use ($q) {
                        $qq->where('cliente_nombre', 'like', "%{$q}%")
                           ->orWhere('cliente_telefono', 'like', "%{$q}%")
                           ->orWhere('descripcion', 'like', "%{$q}%")
                           ->orWhere('estado', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('created_at')
                ->get();

            $totalDia   = (float) $pedidosDia->sum('total');
            $numPedidos = (int) $pedidosDia->count();
        }

        return view('admin.pedidos_historial', compact(
            'q',
            'dias',
            'diaActual',
            'pedidosDia',
            'totalDia',
            'numPedidos'
        ));
    }
 public function print($id)
{
    $this->soloAdmin();

    $pedido = PedidoEspecial::findOrFail($id);

    return view('admin.pedido_print', compact('pedido'));
}
}