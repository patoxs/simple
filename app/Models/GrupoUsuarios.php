<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoUsuarios extends Model
{
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany('App\User', 'grupo_usuarios_has_usuario', 'grupo_usuarios_id', 'usuario_id');
    }
}
