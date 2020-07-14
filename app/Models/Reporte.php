<?php

namespace App\Models;

use App\Helpers\Doctrine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Reporte extends Model
{
    protected $table = 'reporte';

    public function proceso()
    {
        return $this->belongsTo(Proceso::class);
    }

}
