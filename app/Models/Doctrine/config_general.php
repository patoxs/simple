<?php

class Config_general extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('componente','string',45,array(
            'type' => 'string',
            'primary' => true,
            'length' => 45
        ));
        $this->hasColumn('cuenta','integer',2,array(
            'type' => 'integer',
            'primary' => true,
            'length' => 2
        ));
        $this->hasColumn('llave','string',80,array(
            'type' => 'string',
            'primary' => true,
            'length' => 80
        ));
        $this->hasColumn('valor','string',256,array(
            'type' => 'string',
            'length' => 256
        ));
    }
    function setUp() {
        parent::setUp();
        
        $this->hasOne('Cuenta',array(
            'local'=>'cuenta',
            'foreign'=>'id'
        ));
    }
    public function actualizar(){
        try{
            $result = Doctrine_Query::create ()
            ->select('COUNT(componente) AS cuenta')
            ->from ('Config_general')
            ->where ("componente = ? AND cuenta = ? AND llave = ?",array('token_services',$this->cuenta,$this->llave))
            ->execute ();
            if($result[0]->cuenta>=1){
                $q=Doctrine_Query::create()
                ->update('Config_general')
                ->set('valor','?',$this->valor)
                ->where('componente=? and cuenta=? and llave=?',array($this->componente,$this->cuenta,$this->llave));
                $q->execute();    
            }else{
                $this->save();
            }
        }catch(Exception $err){
            try{
                $this->save();
            }catch(Exception $err2){
                throw new Exception($err->getMessage());
            }            
        }
    }
}
