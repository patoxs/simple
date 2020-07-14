<?php

class EventoExterno extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('metodo');
        $this->hasColumn('url');
        $this->hasColumn('mensaje');
        $this->hasColumn('regla');
        $this->hasColumn('tarea_id');
        $this->hasColumn('opciones');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Tarea',array(
            'local'=>'tarea_id',
            'foreign'=>'id'
        ));

        $this->hasMany('Evento as Eventos', array(
            'local' => 'id',
            'foreign' => 'evento_externo_id'
        ));
    }
}
