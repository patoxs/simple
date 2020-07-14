<?php

class Acontecimiento extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('estado');
        $this->hasColumn('evento_externo_id');
        $this->hasColumn('etapa_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('EventoExterno',array(
            'local'=>'evento_externo_id',
            'foreign'=>'id'
        ));

        $this->hasOne('Etapa',array(
            'local'=>'etapa_id',
            'foreign'=>'id'
        ));
    }
}
