<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoHidden extends Campo
{
    public $requiere_datos = false;

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }

        $display = '<div class="form-group">';
        $display .= '<input id="' . $this->id . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="hidden" class="form-control has-error" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : htmlspecialchars($valor_default)) . '" data-modo="' . $modo . '" />';
        $display .= '</div>';

        return $display;
    }

}