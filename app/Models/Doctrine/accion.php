<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class Accion extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('tipo');
        $this->hasColumn('extra');
        $this->hasColumn('proceso_id');
        $this->hasColumn('exponer_variable');


        $this->setSubclasses(array(
                'AccionEnviarCorreo' => array('tipo' => 'enviar_correo'),
                'AccionEventoAnalytics' => array('tipo' => 'evento_analytics'),
                'AccionWebservice' => array('tipo' => 'webservice'),
                'AccionVariable' => array('tipo' => 'variable'),
                'AccionRest' => array('tipo' => 'rest'),
                'AccionSoap' => array('tipo' => 'soap'),
                'AccionCallback' => array('tipo' => 'callback'),
                'AccionNotificaciones' => array('tipo' => 'webhook'),
                'AccionIniciarTramite' => array('tipo' => 'iniciar_tramite'),
                'AccionContinuarTramite' => array('tipo' => 'continuar_tramite'),
                'AccionDescargaDocumento' => array('tipo' => 'descarga_documento'),
                'AccionRedirect' => array('tipo' => 'redirect'),
                'AccionGenerarDocumento' => array('tipo' => 'generar_documento')
            )
        );
    }

    function setUp()
    {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Evento as Eventos', array(
            'local' => 'id',
            'foreign' => 'accion_id'
        ));
    }

    public function displayForm()
    {
        return NULL;
    }

    public function displaySecurityForm($id_proceso)
    {
        return NULL;
    }

    //Ejecuta la regla, de acuerdo a los datos del tramite tramite_id
    public function ejecutar($tramite_id)
    {
        return;
    }

    public function setExtra($datos_array)
    {
        if ($datos_array) {
            Log::info('Accion.setExtra,Estos son los datos de la accion $datos_array: ' . json_encode($datos_array));
            $this->_set('extra', json_encode($datos_array));
        } else {
            Log::info('Accion.setExtra, $datos_array: NULL');
            $this->_set('extra', NULL);
        }
    }

    public function getExtra()
    {
        return json_decode($this->_get('extra'));
    }


    public function exportComplete()
    {
        $accion = $this;
        $object = $accion->toArray();

        return json_encode($object);
    }

    /**
     * @param $input
     * @return Accion
     */
    public static function importComplete($input)
    {
        $json = json_decode($input);
        $accion = new Accion();

        try {

            //Asignamos los valores a las propiedades de la Accion
            foreach ($json as $keyp => $p_attr) {
                if ($keyp != 'id' && $keyp != 'proceso_id')
                    $accion->{$keyp} = $p_attr;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $accion;
    }

}
