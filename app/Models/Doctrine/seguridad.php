<?php

class Seguridad extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('institucion');
        $this->hasColumn('servicio');
        $this->hasColumn('extra');
        $this->hasColumn('proceso_id');
        // $this->setSubclasses(array(
        //         'SeguridadForm'  => array('tipo' => 'SeguridadForm'),
        //     )
        // );
    }

    function setUp()
    {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));
    }

    // public function displayForm(){
    //     return NULL;
    // }

    public function validateForm()
    {
        return;
    }

    public function displayForm()
    {
        $display = '<label>Tipo de Seguridad</label>
                    <select id="tipoSeguridad" name="extra[tipoSeguridad]" class="form-control col-2">
                        <option value="">Seleccione...</option>
                        <option value="HTTP_BASIC">HTTP_BASIC</option>
                        <option value="API_KEY">API_KEY</option>
                        <option value="OAUTH2">OAUTH2</option>';
        if (!is_null($this->extra) && $this->extra->tipoSeguridad) {
            $display .= '<option value="' . ($this->extra->tipoSeguridad) . '" selected>' . ($this->extra->tipoSeguridad) . '</option>';
        }
        $display .= '</select>';

        $display .= '
            <div id="DivBasic">
                <label>Usuario</label>
                <input type="text" id="user" name="extra[user]" class="basic form-control col-2" value="' . (isset($this->extra->user) ? $this->extra->user : '') . '">
                <label>Contraseña</label>
                <input type="text" id="pass" name="extra[pass]" class="basic form-control col-2" value="' . (isset($this->extra->pass) ? $this->extra->pass : '') . '">
            </div>';

        $display .= '
            <div id="DivKey">
                <label>Llave de aplicación (Api key)</label>
                <input type="text" id="apikey" name="extra[apikey]" class="key form-control col-2" value="' . (isset($this->extra->apikey) ? $this->extra->apikey : '') . '">
                <label>Nombre de aplicación (Name key)</label>
                <input type="text" id="namekey" name="extra[namekey]" class="key form-control col-2" value="' . (isset($this->extra->namekey) ? $this->extra->namekey : '') . '">
            </div>';
        $display .= '
            <div id="DivAuth">

                <label>Endpoint</label>
                <input type="text" id="url_auth" name="extra[url_auth]" class="oauth form-control col-2" value="' . (isset($this->extra->url_auth) ? $this->extra->url_auth : '') . '">

                <label>Resource</label>
                <input type="text" id="uri_auth" name="extra[uri_auth]" class="oauth form-control col-2" value="' . (isset($this->extra->uri_auth) ? $this->extra->uri_auth : '') . '">

                <label>Request</label>
                <textarea id="request_seg" name="extra[request_seg]" rows="7" cols="70" placeholder="{ object }" class="oauth  form-control col-2">' . ($this->extra ? $this->extra->request_seg : '') . '</textarea>
                <br />
                <span id="resultRequest" class="spanError"></span>
                <br />
            </div>';
        return $display;
    }

    // public function validateForm() {
    //     $CI = & get_instance();
    //     $CI->form_validation->set_rules('extra[institucion]', 'Tipo de Seguridad', 'required');
    //     $CI->form_validation->set_rules('extra[servicio]', 'Tipo de Seguridad', 'required');
    //     // $CI->form_validation->set_rules('extra[tema]', 'Tema', 'required');
    //     // $CI->form_validation->set_rules('extra[contenido]', 'Contenido', 'required');
    // }

    //Ejecuta la regla, de acuerdo a los datos del tramite tramite_id
    public function ejecutar($tramite_id)
    {
        return;
    }

    public function setExtra($datos_array)
    {

        if ($datos_array) {
            $this->_set('extra', json_encode($datos_array));
        } else {
            log_message('info', 'Seguridad.setExtra, $datos_array: NULL');
            $this->_set('extra', NULL);
        }
    }

    public function getExtra()
    {
        return json_decode($this->_get('extra'));
    }

    public function exportComplete()
    {
        $seguridad = $this;
        $object = $seguridad->toArray();

        return json_encode($object);
    }

    /**
     * @param $input
     * @return Seguridad
     */
    public static function importComplete($input)
    {
        $json = json_decode($input);
        $seguridad = new Seguridad();

        try {

            //Asignamos los valores a las propiedades de la Seguridad
            foreach ($json as $keyp => $p_attr) {
                if ($keyp != 'id' && $keyp != 'proceso_id')
                    $seguridad->{$keyp} = $p_attr;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return $seguridad;
    }

}