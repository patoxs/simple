<?php

namespace App\Models;

use App\Helpers\Doctrine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Cuenta extends Model
{
    protected $table = 'cuenta';

    public function UsuarioBackend()
    {
        return $this->hasMany(UsuarioBackend::class);
    }

    
    /**
     * @param $key
     * @param $value
     *
     * Permite agregar individualmente datos a la
     * columna metadata en forma de json tipo texto
     */
    public function setMetadata($key, $value)
    {
        if (!$this->metadata) {
            $this->metadata = "[]";
        }
        $metadata = json_decode($this->metadata, true);
        $metadata[$key] = $value;
        $this->metadata = json_encode($metadata, true);
    }

    /**
     * @param null $key
     * @return mixed|null
     *
     * Permite obtener todos, ninguno  o un valor especifico de
     * la metadata mediante su llave
     */
    public function getMetadata($key=null)
    {
        $metadata = json_decode($this->metadata, true);
        if ($key != null) {
           $metadata = ($metadata[$key]) ? $metadata[$key]:null;
        }

        return $metadata;
    }

    public function procesos()
    {
        return $this->hasMany(Proceso::class);
    }

    public function procesosActivos()
    {
        return $this->procesos()->activos();
    }

}
