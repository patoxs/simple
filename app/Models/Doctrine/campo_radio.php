<?php
require_once('campo.php');

use Illuminate\Http\Request;

class CampoRadio extends Campo
{

    protected function display($modo, $dato)
    {
        $display = '<div class="form-group">';
        $display .= '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        foreach ($this->datos as $d) {
            $display .= '<div class="form-check">';
            $display .= '<input class="form-check-input" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="radio" name="' . $this->nombre . '" value="' . $d->valor . '"  id="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'checked' : '') . ' /> ';
            $display .= '<label class="form-check-label" for="' . $d->valor . '">' . $d->etiqueta . '</label>';
            $display .= '</div>';
        }
        if ($this->ayuda)
            $display .= '<span class="form-text text-muted">' . $this->ayuda . '</span>';
        $display .= '</div>';

        return $display;
    }

    public function backendExtraValidate(Request $request)
    {
        $request->validate(['datos' => 'required']);
        /*$request->validate(['datos' => 'required']);
        $CI =& get_instance();
        $CI->form_validation->set_rules('datos', 'Datos', 'required');*/
    }

}