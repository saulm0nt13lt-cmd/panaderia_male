<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario'; 

    public $timestamps = false;

    public $incrementing = true; 
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nombre_completo','username','email','password_hash','rol','estado'
    ];

    // password
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];
}