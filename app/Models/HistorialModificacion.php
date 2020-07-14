<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HistorialModificacion extends Model
{
    protected $table = 'historial_modificacion';

    public $timestamps = false;

    public function getDate()
    {
        $fecha = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at);
        return $fecha->format('d-m-Y H:i:s');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Models\UsuarioBackend');
    }

    public function proceso()
    {
        return $this->belongsTo('App\Models\Proceso');
    }
}
