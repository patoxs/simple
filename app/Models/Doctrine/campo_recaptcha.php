<?php

require_once('campo.php');

use App\Helpers\Doctrine;

class CampoRecaptcha extends Campo
{

    public $requiere_nombre = false;
    public $requiere_datos = false;
    public $estatico = true;

    function setTableDefinition()
    {
        parent::setTableDefinition();

        $this->hasColumn('readonly', 'bool', 1, array('default' => 1));
    }

    function setUp()
    {
        parent::setUp();
        $this->setTableName("campo");
    }

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->etiqueta);
            $etiqueta = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $etiqueta = $this->etiqueta;
        }
        $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . '</label>';
        $display .= '<div class="controls">';
        $display .= '<div id="form_captcha"></div>';
        $display .= '</div>';

        return $display;
    }

    public function setReadonly($readonly)
    {
        $this->_set('readonly', 1);
    }

}