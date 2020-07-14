<?php
class CuentaHasConfig extends Doctrine_Record {
    function  setTableDefinition() {
        $this->hasColumn('idpar','integer',4,array('primary' => true));   
        $this->hasColumn('config_id','integer',4,array('primary' => true));
        $this->hasColumn('cuenta_id','integer',4,array('primary' => true));
    }
}