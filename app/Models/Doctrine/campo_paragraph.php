<?php
require_once('campo.php');

use \App\Helpers\Doctrine;

class CampoParagraph extends Campo
{

    public $requiere_nombre = false;
    public $requiere_datos = false;
    public $estatico = true;
    public $etiqueta_tamano = 'xxlarge';

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
        if($this->extra && $this->extra->nombre)
            $display = '<p name="' . $this->extra->nombre . '" >' . $etiqueta . '</p>';
        else
            $display = '<p>' . $etiqueta . '</p>';

        return $display;
    }

    public function setReadonly($readonly)
    {
        $this->_set('readonly', 1);
    }

    public function backendExtraFields(){
        $nombre = isset($this->extra->nombre) ? $this->extra->nombre : null;

        $html = '<label>Nombre (Opcional). Solo configurar si desea visualizar contenido de variables generadas por botón asíncrono.</label>';
        $html .= '<input class="form-control" name="extra[nombre]" value="' . $nombre . '" />';

        return $html;
    }


}