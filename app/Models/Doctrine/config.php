<?php

class Config extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('idpar');
        $this->hasColumn('cuenta_id');
        $this->hasColumn('endpoint');
        $this->hasColumn('nombre');
        $this->hasColumn('nombre_visible');
    }

    function setUp() {
        parent::setUp();
         $this->hasMany('CuentaHasConfig',array(
            'local'=>'id',
            'foreign'=>'config_id',
            'refClass' => 'CuentaHasConfig'
        ));
    }
}