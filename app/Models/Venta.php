<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario','fecha','metodo_pago','subtotal','descuento','total','nota'
    ];

    protected $casts = [
        'subtotal' => 'float',
        'descuento' => 'float',
        'total' => 'float',
    ];

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta', 'id_venta');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}