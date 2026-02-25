<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteDiario extends Model
{
    use HasFactory;

    protected $table = 'cortes_diarios';

    protected $fillable = [
        'fecha',
        'total_ventas',
        'total_subtotal',
        'total_descuentos',
        'num_ventas',
    ];

    public $timestamps = true;
}