<?php
require_once('campo.php');

use App\Helpers\Doctrine;

class CampoAgenda extends Campo
{

    public $requiere_datos = false;
    public $datos_agenda = true;

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }
        $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<input type="hidden" name="objappointments[]" data-id-etapa="' . $etapa_id . '" value="0" id="codcita' . $this->id . '" />';
        $display .= '<input type="hidden" id="' . $this->id . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="text" class="input-semi-large js-data-cita" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : htmlspecialchars($valor_default)) . '" data-modo="' . $modo . '" />';
        $display .= '<div class="cont-link">
            <a onclick="calendarioFront(' . $this->agenda_campo . ',' . $this->id . ',0,' . $etapa_id . ');" href="#" >
                <div class="icon_calendar"></div>
                <i class="material-icons">today</i>
            </a>
            </div><label id="txtresult' . $this->id . '" class="labresreserv float-left"></label>';
        $display .= '</div>';

        return $display;
    }

}