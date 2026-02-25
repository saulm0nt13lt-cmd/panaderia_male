<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'precio_venta',
        'stock',
        'id_categoria',

        // extras por categoría
        'tipo_pan',     // Panadería
        'sabor',        // Pasteles
        'tam_pastel',   // Pasteles
        'tam_rosca',    // Roscas
        'relleno',      // Roscas
    ];

    protected $casts = [
        'precio_venta'  => 'float',
        'stock'         => 'int',
        'id_categoria'  => 'int',
    ];

    // RELACIÓN: Producto pertenece a una Categoría
    public function categoria()
    {
        return $this->belongsTo(\App\Models\Categoria::class, 'id_categoria', 'id_categoria');
    }
}