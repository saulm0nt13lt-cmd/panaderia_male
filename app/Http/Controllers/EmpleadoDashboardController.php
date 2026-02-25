<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\Venta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmpleadoDashboardController extends Controller
{
    private function soloEmpleado()
    {
        if (strtolower(trim(auth()->user()->rol ?? '')) !== 'empleado') {
            abort(403);
        }
    }

    public function index()
    {
        $this->soloEmpleado();

        $userId = auth()->user()->id_usuario;
        $hoy = Carbon::today();

        // ✅ ventas del día SOLO del empleado
        $ventasDelDia = (float) Venta::where('id_usuario', $userId)
            ->whereDate('fecha', $hoy)
            ->sum('total');

        // ✅ tickets del día (cada venta = 1 ticket)
        $ticketsHoy = (int) Venta::where('id_usuario', $userId)
            ->whereDate('fecha', $hoy)
            ->count();

        // ✅ turno activo (para tu card de Turno)
        $turnoActivo = Turno::where('id_usuario', $userId)
            ->whereNull('fecha_cierre')
            ->exists();

        return view('empleado.dashboard', compact(
            'ventasDelDia',
            'ticketsHoy',
            'turnoActivo'
        ));
    }
}