<?php

class Formulario extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('proceso_id');
        if(\Schema::hasColumn('formulario', 'descripcion')){
            $this->hasColumn('descripcion');
        }
    }

    function setUp() {
        parent::setUp();

        $this->hasMany('Campo as Campos',array(
            'local'=>'id',
            'foreign'=>'formulario_id',
            'orderBy'=>'posicion'
        ));
        
        $this->hasMany('Paso as Pasos',array(
            'local'=>'id',
            'foreign'=>'formulario_id'
        ));
        
        $this->hasOne('Proceso',array(
            'local'=>'proceso_id',
            'foreign'=>'id'
        ));
        

    }

    
    public function updatePosicionesCamposFromJSON($json){
        $posiciones=  json_decode($json);
        
        Doctrine_Manager::connection()->beginTransaction();
        foreach($this->Campos as $c){
            $c->posicion=array_search($c->id, $posiciones);
            $c->save();
        }
        Doctrine_Manager::connection()->commit();
    }
    
    //Obtiene la ultima posicion de los campos de este formulario
    public function getUltimaPosicionCampo(){
        $max=0;
        foreach($this->Campos as $c){
            if($c->posicion>$max){
                $max=$c->posicion;
            }
        }
        return $max;
    }
    
    public function exportComplete()
    {        
        $formulario = $this;
        $formulario->Campos;        
        $object = $formulario->toArray();

        return json_encode($object);
    }
    
    /**
     * @param $input
     * @return Formulario
     */
    public static function importComplete($input)
    {
        $json = json_decode($input);                
        $formulario = new Formulario();
        
        try {
            
            foreach ($json->Campos as $c) {
                $campo = new Campo();
                foreach ($c as $keyc => $c_attr) {
                    if ($keyc != 'id' && $keyc != 'formulario_id' && $keyc != 'Formulario' && $keyc!= 'condiciones_extra_visible') {
                        $campo->{$keyc} = $c_attr;
                    }
                    if($keyc === 'condiciones_extra_visible' && !is_null($c_attr)){
                        $campo->condiciones_extra_visible = json_encode($c_attr);
                    }
                }
                $formulario->Campos[] = $campo;
            }

            //Asignamos los valores a las propiedades del Formulario
            foreach ($json as $keyp => $p_attr) {
                if ($keyp != 'id' && $keyp != 'proceso_id' && $keyp != 'Campos')
                    $formulario->{$keyp} = $p_attr;
            }
        
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
        
        return $formulario;
    }
    /**
    public function getEtapaIdFormFormulario(){
        
        $conexiones=  Doctrine_Query::create()
                ->select('rec.city')->distinct()
                ->from('Paso p, formulario f,etapa e')
                ->where('f.id = p.formulario_id and e.tarea_id = p.tarea_id ' +
                        'and f.id = ?',$this->id)
                ->execute();
        
    }
     * 
     */
    public function getCamposEntrada(){
        try{
        return $result = Doctrine_Query::create()
            ->from('Campo c, c.Formulario f')
          
            ->andWhere('c.readonly = 0')
            ->andWhere('f.id = ?', $this->id)
            ->execute();
        }catch(Exception $e){
            log_message('error',$e->getMessage());
            throw $e;
        }       
    }
}
