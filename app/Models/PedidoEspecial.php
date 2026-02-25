<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoEspecial extends Model
{
    protected $table = 'pedidos_especiales';
    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'fecha_entrega',
        'cliente_nombre',
        'cliente_telefono',
        'total',
        'anticipo',
        'restante',
        'descripcion',
    ];
}