<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Http\Request;

class FirmaElectronica extends Model {

    protected $table = 'hsm_configuracion';
    
    public function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('cuenta_id');
        $this->hasColumn('entidad');
        $this->hasColumn('proposito');
    }

    public function documentos(){
        return $this->hasMany(Documento::class, 'hsm_configuracion_id');
    }
    
}
