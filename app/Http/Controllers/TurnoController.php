<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
   
    public function estado()
    {
        $userId = auth()->id();

      
        $turno = Turno::where('id_usuario', $userId)
            ->whereNull('fecha_cierre')
            ->orderByDesc('id_turno')
            ->first();

        if (!$turno) {
          
            $ultimo = Turno::where('id_usuario', $userId)
                ->orderByDesc('id_turno')
                ->first();

            if (!$ultimo) {
                return response()->json([
                    'activo' => false,
                    'apertura' => null,
                    'cierre' => null,
                    'segundos' => 0,
                ]);
            }

            $apertura = $ultimo->fecha_apertura;
            $cierre   = $ultimo->fecha_cierre;

            // ✅ usar lo guardado en BD
            $segundos = (int) ($ultimo->duracion_segundos ?? 0);

            return response()->json([
                'activo' => false,
                'apertura' => $apertura ? Carbon::parse($apertura)->format('Y-m-d H:i:s') : null,
                'cierre' => $cierre ? Carbon::parse($cierre)->format('Y-m-d H:i:s') : null,
                'segundos' => $segundos,
            ]);
        }

        // activo
        $apertura = $turno->fecha_apertura;

        // ✅ segundos transcurridos en vivo
        $segundos = (int) Carbon::parse($apertura)->diffInSeconds(now());

        return response()->json([
            'activo' => true,
            'apertura' => $apertura ? Carbon::parse($apertura)->format('Y-m-d H:i:s') : null,
            'cierre' => null,
            'segundos' => $segundos,
        ]);
    }

    // 2) Abrir turno (sin monto inicial)
    public function abrir(Request $request)
    {
        $userId = auth()->id();

        $yaActivo = Turno::where('id_usuario', $userId)
            ->whereNull('fecha_cierre')
            ->exists();

        if ($yaActivo) {
            return response()->json(['ok' => false, 'msg' => 'Ya tienes un turno activo.'], 422);
        }

        $turno = Turno::create([
            'id_usuario' => $userId,
            'fecha_apertura' => now(),
            'fecha_cierre' => null,
            'duracion_segundos' => null,
            'monto_inicial' => 0,
            'monto_final' => null,
        ]);

        return response()->json(['ok' => true, 'id_turno' => $turno->id_turno]);
    }

    // 3) Cerrar turno (guardar duración, sin monto final)
    public function cerrar(Request $request)
    {
        $userId = auth()->id();

        $turno = Turno::where('id_usuario', $userId)
            ->whereNull('fecha_cierre')
            ->orderByDesc('id_turno')
            ->first();

        if (!$turno) {
            return response()->json(['ok' => false, 'msg' => 'No hay turno activo.'], 422);
        }

        $turno->fecha_cierre = now();

        // ✅ guardar duración total en segundos
        $turno->duracion_segundos = Carbon::parse($turno->fecha_apertura)
            ->diffInSeconds(Carbon::parse($turno->fecha_cierre));

        $turno->save();

        return response()->json(['ok' => true]);
    }
}