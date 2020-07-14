<?php

class ColaContinuarTramite extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('tramite_id');
        $this->hasColumn('tarea_id');
        $this->hasColumn('request');
        $this->hasColumn('procesado');
    }

    function setUp()
    {
        parent::setUp();

    }

    public function validateForm()
    {
        return;
    }

    public function setRequest($datos_array)
    {

        if ($datos_array) {
            $this->_set('request', json_encode($datos_array));
        } else {
            $this->_set('request', NULL);
        }
    }

    public function getRequest()
    {
        return json_decode($this->_get('request'));
    }

    public function findTareasEncoladas($tramite_id)
    {

        $cola = Doctrine_Query::create()
            ->from('ColaContinuarTramite c')
            ->where('c.tramite_id = ?', $tramite_id)
            ->andWhere('c.procesado = 0')
            ->execute();

        return $cola;

    }

}