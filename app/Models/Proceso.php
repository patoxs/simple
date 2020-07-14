<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Proceso extends Model
{
    use Searchable;

    protected $table = 'proceso';

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->with("tramites")
            ->with('tramites.etapas')
            ->with('tramites.etapas.datoSeguimientos')
            ->where("id", $this->id)
            ->first()
            ->toArray();

        return $array;
    }

    public function tramites()
    {
        return $this->hasMany(Tramite::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    /*
     * Usuario backend
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioBackend::class, 'usuario_id');
    }

    public function historialModificaciones()
    {
        return $this->hasMany(HistorialModificacion::class, 'proceso_id');
    }

    /*
     * Scope de procesos activos
    */
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
}
