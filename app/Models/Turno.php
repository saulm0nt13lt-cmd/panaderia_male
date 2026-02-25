<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turnos';
    protected $primaryKey = 'id_turno';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha_apertura',
        'fecha_cierre',
        'duracion_segundos',
        'monto_inicial',
        'monto_final',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre'   => 'datetime',
    ];
}