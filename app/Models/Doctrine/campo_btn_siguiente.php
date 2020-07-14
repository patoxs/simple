<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoBtnSiguiente extends Campo
{
    public $requiere_datos = false;
    public $estatico = true;

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }

        $display = '<button id="' . $this->id . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="submit" class="btn btn-danger btn_siguiente" name="' . $this->nombre . '" data-etiqueta="'.$this->etiqueta.'" value="' . ($dato ? htmlspecialchars($dato->valor) : htmlspecialchars($valor_default)) . '" data-modo="' . $modo . '" > ' . $this->etiqueta . '</button>';
        $display .= '<br>';

        return $display;
    }
}