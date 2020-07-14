<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    protected $table = 'etapa';
    protected $date = ['created_at', 'updated_at','vencimiento_at'];
    public function tramite()
    {
        return $this->belongsTo(Tramite::class);
    }

    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    public function datoSeguimientos()
    {
        return $this->hasMany(DatoSeguimiento::class);
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class);
    }
}
