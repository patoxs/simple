<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $table = 'tarea';

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'proceso_id');
    }
}
