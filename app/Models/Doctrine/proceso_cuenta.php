<?php

class ProcesoCuenta extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('id_cuenta_origen');
        $this->hasColumn('id_cuenta_destino');
        $this->hasColumn('id_proceso');
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'id_proceso',
            'foreign' => 'id'
        ));
    }

    public function findCuentasProcesos($proceso_id){
        $sql = "select c.id, c.nombre from proceso_cuenta pc, cuenta c 
            where pc.id_proceso = ".$proceso_id." and pc.id_cuenta_destino = c.id;";

        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)
            ->fetchAll();
        return $result;
    }

    public function findCuentasAcceso($cuenta_id){
        /*$sql = "select c.id, c.nombre from proceso p, proceso_cuenta pc, cuenta c
            where pc.id_cuenta_destino = ".$cuenta_id." and pc.id_proceso = p.id and p.cuenta_id = c.id;";*/

        $sql = "select c.id, c.nombre from proceso_cuenta pc, cuenta c 
            where pc.id_cuenta_destino = ".$cuenta_id." and pc.id_cuenta_origen = c.id;";

        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)
            ->fetchAll();
        return $result;
    }

    public function findProcesosExpuestosConPermiso($cuenta_id, $id_cuenta_origen){
        $sql = "select p.id, p.nombre from proceso p, proceso_cuenta pc 
            where pc.id_cuenta_origen = ".$cuenta_id." and pc.id_cuenta_destino = ".$id_cuenta_origen." and pc.id_proceso = p.id;";

        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)
            ->fetchAll();
        return $result;
    }

    public function deleteCuentasConPermiso($proceso_id){

        $sql = "select pc.id from proceso_cuenta pc 
            where pc.id_proceso = ".$proceso_id.";";

        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)->fetchAll();

        foreach ($result as $id){
            $procesoCuenta = Doctrine::getTable('ProcesoCuenta')->find($id["id"]);
            $procesoCuenta->delete();
        }

    }

}