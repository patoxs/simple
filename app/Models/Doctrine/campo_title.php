<?php
require_once('campo.php');

use App\Helpers\Doctrine;

class CampoTitle extends Campo
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

    public function setReadonly($readonly)
    {
        $this->_set('readonly', 1);
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
            $display = '<h3 name="' . $this->extra->nombre . '" >' . $etiqueta . '</h3>';
        else
            $display = '<h3>' . $etiqueta . '</h3>';

        return $display;
    }

    public function backendExtraFields(){
        $nombre = isset($this->extra->nombre) ? $this->extra->nombre : null;

        $html = '<label>Nombre (Opcional). Solo configurar si desea visualizar contenido de variables generadas por botón asíncrono.</label>';
        $html .= '<input class="form-control" name="extra[nombre]" value="' . $nombre . '" />';

        return $html;
    }

}