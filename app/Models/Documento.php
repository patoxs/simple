<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Http\Request;

class Documento extends Model {

    protected $table = 'documento';
    
    public function firma(){
        return $this->belongsTo(FirmaElectronica::class, 'hsm_configuracion_id');
    }

    public function proceso(){
        return $this->belongsTo(Proceso::class);
    }
}
