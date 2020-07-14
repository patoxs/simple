<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tramite extends Model
{
    use Searchable;

    protected $table = 'tramite';

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public $timestamps = false;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->with("proceso")
            ->with('etapas')
            ->with('etapas.datoSeguimientos')
            ->where("id", $this->id)
            ->first()
            ->toArray();

        return $array;
    }

    public function proceso()
    {
        return $this->belongsTo(Proceso::class);
    }

    public function etapas()
    {
        return $this->hasMany(Etapa::class);
    }
    public function files()
    {
        return $this->hasMany(File::class, 'tramite_id');
    }
}
