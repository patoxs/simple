<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use \App\Models\Etapa;
use \App\Models\DatoSeguimiento;

class Campo extends Doctrine_Record
{
    public $requiere_datos = true;    //Indica si requiere datos seleccionables. Como las opciones de un checkbox, select, etc.
    public $estatico = false; //Indica si es un campo estatico, es decir que no es un input con informacion. Ej: Parrafos, titulos, etc.
    public $etiqueta_tamano = 'large'; //Indica el tamaño default que tendra el campo de etiqueta. Puede ser large o xxlarge.
    public $requiere_nombre = true;    //Indica si requiere que se le ingrese un nombre (Es decir, no generarlo aleatoriamente)
    public $datos_agenda = false;     // Indica si se deben mostrar los satos adicionales para la agenda.
    public $datos_mapa = false; // Indica si se deben mostrar que datos se deben mostrar asociados al mapa.

    public static function factory($tipo)
    {
        if ($tipo == 'text')
            $campo = new CampoText();
        else if ($tipo == 'password')
            $campo = new CampoPassword();
        else if ($tipo == 'textarea')
            $campo = new CampoTextArea();
        else if ($tipo == 'select')
            $campo = new CampoSelect();
        else if ($tipo == 'radio')
            $campo = new CampoRadio();
        else if ($tipo == 'checkbox')
            $campo = new CampoCheckbox();
        else if ($tipo == 'file')
            $campo = new CampoFile();
        else if ($tipo == 'file_s3')
            $campo = new CampoFileS3();
        else if ($tipo == 'date')
            $campo = new CampoDate();
        else if ($tipo == 'instituciones_gob')
            $campo = new CampoInstitucionesGob();
        else if ($tipo == 'comunas')
            $campo = new CampoComunas();
        else if ($tipo == 'paises')
            $campo = new CampoPaises();
        else if ($tipo == 'moneda')
            $campo = new CampoMoneda();
        else if ($tipo == 'title')
            $campo = new CampoTitle();
        else if ($tipo == 'subtitle')
            $campo = new CampoSubtitle();
        else if ($tipo == 'paragraph')
            $campo = new CampoParagraph();
        else if ($tipo == 'documento')
            $campo = new CampoDocumento();
        else if ($tipo == 'javascript')
            $campo = new CampoJavascript();
        else if ($tipo == 'grid')
            $campo = new CampoGrid();
        else if ($tipo == 'agenda')
            $campo = new CampoAgenda();
        else if ($tipo == 'recaptcha')
            $campo = new CampoRecaptcha();
        else if ($tipo == 'maps')
            $campo = new CampoMaps();
        else if ($tipo == 'grid_datos_externos')
            $campo = new CampoGridDatosExternos();
        else if ($tipo == 'provincias')
            $campo = new CampoProvincias();
        else if ($tipo == 'btn_asincrono')
            $campo = new CampoBtnAsincrono();
        else if ($tipo == 'hidden')
            $campo = new CampoHidden();
        else if ($tipo == 'btn_siguiente')
            $campo = new CampoBtnSiguiente();

        $campo->assignInheritanceValues();

        return $campo;
    }

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('posicion');
        $this->hasColumn('tipo');
        $this->hasColumn('formulario_id');
        $this->hasColumn('etiqueta');
        $this->hasColumn('validacion');
        $this->hasColumn('ayuda');
        $this->hasColumn('dependiente_tipo');
        $this->hasColumn('dependiente_campo');
        $this->hasColumn('dependiente_valor');
        $this->hasColumn('dependiente_relacion');
        $this->hasColumn('datos');
        $this->hasColumn('readonly');           //Indica que en este campo solo se mostrara la informacion.
        $this->hasColumn('valor_default');
        $this->hasColumn('documento_id');
        $this->hasColumn('extra');
        $this->hasColumn('agenda_campo');
        $this->hasColumn('exponer_campo');
        $this->hasColumn('condiciones_extra_visible');

        $this->setSubclasses(array(
            'CampoText' => array('tipo' => 'text'),
            'CampoPassword' => array('tipo' => 'password'),
            'CampoTextArea' => array('tipo' => 'textarea'),
            'CampoSelect' => array('tipo' => 'select'),
            'CampoRadio' => array('tipo' => 'radio'),
            'CampoCheckbox' => array('tipo' => 'checkbox'),
            'CampoFile' => array('tipo' => 'file'),
            'CampoFileS3' => array('tipo' => 'file_s3'),
            'CampoDate' => array('tipo' => 'date'),
            'CampoInstitucionesGob' => array('tipo' => 'instituciones_gob'),
            'CampoComunas' => array('tipo' => 'comunas'),
            'CampoPaises' => array('tipo' => 'paises'),
            'CampoMoneda' => array('tipo' => 'moneda'),
            'CampoTitle' => array('tipo' => 'title'),
            'CampoSubtitle' => array('tipo' => 'subtitle'),
            'CampoParagraph' => array('tipo' => 'paragraph'),
            'CampoDocumento' => array('tipo' => 'documento'),
            'CampoJavascript' => array('tipo' => 'javascript'),
            'CampoGrid' => array('tipo' => 'grid'),
            'CampoAgenda' => array('tipo' => 'agenda'),
            'CampoRecaptcha' => array('tipo' => 'recaptcha'),
            'CampoMaps' => array('tipo' => 'maps'),
            'CampoGridDatosExternos' => array('tipo' => 'grid_datos_externos'),
            'CampoProvincias' => array('tipo' => 'provincias'),
            'CampoBtnAsincrono' => array('tipo' => 'btn_asincrono'),
            'CampoHidden' => array('tipo' => 'hidden'),
            'CampoBtnSiguiente' => array('tipo' => 'btn_siguiente'),
        ));
    }

    function setUp()
    {
        parent::setUp();

        $this->hasOne('Formulario', array(
            'local' => 'formulario_id',
            'foreign' => 'id'
        ));

        $this->hasOne('Documento', array(
            'local' => 'documento_id',
            'foreign' => 'id'
        ));

        $this->hasOne('Reporte', array(
            'local' => 'reporte_id',
            'foreign' => 'id'
        ));
    }

    //Despliega la vista de un campo del formulario utilizando los datos de seguimiento (El dato que contenia el tramite al momento de cerrar la etapa)
    //etapa_id indica a la etapa que pertenece este campo
    //modo es visualizacion o edicion
    public function displayConDatoSeguimiento($etapa_id, $modo = 'edicion')
    {
        $dato = NULL;
        $dato = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($this->nombre, $etapa_id);
        if ($this->readonly) {
            $modo = 'visualizacion';
        }
        return $this->display($modo, $dato, $etapa_id);
    }

    /**
     * Muestra el valor de este campo pero sin rendering HTML, solo el valor.
     * @param type $etapa_id
     * @return type
     */
    public function displayDatoSeguimiento($etapa_id)
    {
        try {
            Log::info("Obteniendo valor de campo para etapa: " . $etapa_id);
            Log::info("Nombre campo: " . $this->nombre);

            $dato = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($this->nombre, $etapa_id);

            if (!$dato) {
                //Se deben crear
                $dato = new DatoSeguimiento();
                $dato->nombre = $this->nombre;
                $dato->etapa_id = $etapa_id;
                $dato->valor = NULL;
            }

            Log::info("Nombre dato: " . $dato->nombre);
            Log::info("Valor dato: ." . $dato->valor . ".");
            Log::info("this->valor_default: " . $this->valor_default);

            if (isset($this->valor_default) && strlen($this->valor_default) > 0 && $dato->valor === NULL) {
                $regla = new Regla($this->valor_default);
                $valor_dato = $regla->getExpresionParaOutput($etapa_id);
                $dato->valor = $valor_dato;
                $dato->save();
            } else {
                $valor_dato = $dato->valor;
            }

            Log::info("valor_default: " . $valor_dato);

            return $valor_dato;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            die;
            throw $e;
        }
    }


    public function displaySinDato($modo = 'edicion')
    {
        if ($this->readonly) $modo = 'visualizacion';
        return $this->display($modo, NULL, NULL);
    }


    protected function display($modo, $dato)
    {
        return '';
    }


    private function extractVariable(Request $request, $nombre, $ispost = TRUE)
    {
        if ($ispost) {
            return $request->input($nombre);
        } else {
            return $CI['data'][$nombre];
        }
    }

    //Funcion que retorna si este campo debiera poderse editar de acuerdo al input POST del usuario

    /**
     *
     * @param type $etapa_id
     * @param type $body Este parametro es opcional y debe contener una lista de
     *                    varibales que llegan por POST JSON. Es usado por  la API REST
     * @return type
     */

    public function isEditableWithCurrentPOST(Request $request, $etapa_id, $body = NULL) //
    {
        $resultado = true;

        if ($this->readonly) {
            $resultado = false;
        } else if ($this->dependiente_campo) {
            $nombre_campo = preg_replace('/\[\w*\]$/', '', $this->dependiente_campo);

            $variable = ($body == NULL) ? $this->extractVariable($request, $nombre_campo, TRUE) : //$CI->input->post($nombre_campo);
                $this->extractVariable($body, $nombre_campo, FALSE);
            //Parche para el caso de campos dependientes con accesores. Ej: ubicacion[comuna]!='Las Condes|Santiago'
            if (preg_match('/\[(\w+)\]$/', $this->dependiente_campo, $matches))
                $variable = $variable[$matches[1]];

            if (is_null($variable)) {
                //buscar en este tramite la ultima aparición de la variable buscada
                $dato_dependiente = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($this->dependiente_campo, $etapa_id);

                // Si no se encuentra, volvemos a buscar eliminando los corchetes(agregados para el checkbox) si existen
                $dato_dependiente = substr($this->dependiente_campo, abs(strlen($this->dependiente_campo) - 2), 2) != '[]' && !is_null($dato_dependiente) ?
                    $dato_dependiente : Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa(substr($this->dependiente_campo, 0, strlen($this->dependiente_campo) - 2)
                        , $etapa_id);
                if ($dato_dependiente)
                    $variable = is_array($dato_dependiente->valor) ? $dato_dependiente->valor : array($dato_dependiente->valor);
            }

            if ($variable === false) {    //Si la variable dependiente no existe
                $resultado = false;
            } else {
                if (is_array($variable)) { //Es un arreglo
                    if ($this->dependiente_tipo == 'regex') {
                        foreach ($variable as $x) {
                            if (!preg_match('/' . $this->dependiente_valor . '/', $x))
                                $resultado = false;
                        }
                    } else {
                        if (!in_array($this->dependiente_valor, $variable))
                            $resultado = false;
                    }
                } else {
                    if ($this->dependiente_tipo == 'regex') {
                        if (!preg_match('/' . $this->dependiente_valor . '/', $variable))
                            $resultado = false;
                    } else {
                        if ($variable != $this->dependiente_valor)
                            $resultado = false;
                    }

                }


                if ($this->dependiente_relacion == '!=')
                    $resultado = !$resultado;
            } 


            $resultados = array();
            array_push($resultados,$resultado);

            //condiciones extras de visibilidad
            if (count($this->condiciones_extra_visible)>0){
                $condiciones = $this->condiciones_extra_visible;

                foreach($condiciones as $condicion){

                    $nombre_campo = preg_replace('/\[\w*\]$/', '', $condicion->campo);
                    $variable = ($body == NULL) ? $this->extractVariable($request, $nombre_campo, TRUE) : $this->extractVariable($body, $nombre_campo, FALSE);
                    //Parche para el caso de campos dependientes con accesores. Ej: ubicacion[comuna]!='Las Condes|Santiago'
                    if (preg_match('/\[(\w+)\]$/', $condicion->campo, $matches))
                        $variable = $variable[$matches[1]];
                    
                    if (is_null($variable)) {
                        //buscar en este tramite la ultima aparición de la variable buscada
                        $dato_dependiente = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($condicion->campo, $etapa_id);
        
                        // Si no se encuentra, volvemos a buscar eliminando los corchetes(agregados para el checkbox) si existen
                        $dato_dependiente = substr($condicion->campo, abs(strlen($condicion->campo) - 2), 2) != '[]' && !is_null($dato_dependiente) ?
                            $dato_dependiente : Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa(substr($condicion->campo, 0, strlen($condicion->campo) - 2)
                                , $etapa_id);
                        if ($dato_dependiente)
                            $variable = is_array($dato_dependiente->valor) ? $dato_dependiente->valor : array($dato_dependiente->valor);
                    }

                    if ($variable === false) {    //Si la variable dependiente no existe
                        $resultado = false;
                        array_push($resultados,$resultado);
                    } else {
                        if (is_array($variable)) { //Es un arreglo
                            if ($condicion->tipo == 'regex') {
                                foreach ($variable as $x) {
                                    if (!preg_match('/' . $condicion->valor . '/', $x))
                                        $resultado = false;
                                }
                            } else {
                                if (!in_array($condicion->valor, $variable))
                                    $resultado = false;
                            }
                        } else {
                            if ($condicion->tipo == 'regex') {
                                if (!preg_match('/' . $condicion->valor . '/', $variable))
                                    $resultado = false;
                            } else {
                                if ($variable != $condicion->valor)
                                    $resultado = false;
                            }

        
                        }
        
                        if ($condicion->igualdad == '!=')
                            $resultado = !$resultado;

                        array_push($resultados,$resultado);
                    }
                }

                if(in_array(false,$resultados))
                        $resultado = false;
                    else
                        $resultado = true;
            }else{
                
                if(in_array(false,$resultados))
                    $resultado = false;
                else
                    $resultado = true;
                
            }
        }

        return $resultado;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {

        $validacion = $this->validacion;
        if ($etapa_id) {
            $regla = new Regla($this->validacion);
            $validacion = $regla->getExpresionParaOutput($etapa_id);
        }

        return [$this->nombre, implode('|', $validacion)];

        //$request->validate([
        //    $this->nombre => implode('|', $validacion)
        //]);

        //$CI->form_validation->set_rules($this->nombre, $this->etiqueta, implode('|', $validacion));
    }


    //Señala como se debe mostrar en el formulario de edicion del backend, cualquier field extra.
    public function backendExtraFields()
    {
        return;
    }

    //Validaciones adicionales que se le deben hacer a este campo en su edicion en el backend.
    public function backendExtraValidate(Request $request)
    {

    }

    public function setValidacion($validacion)
    {
        if ($validacion)
            $this->_set('validacion', implode('|', $validacion));
        else
            $this->_set('validacion', '');
    }

    public function getValidacion()
    {
        if ($this->_get('validacion'))
            return explode('|', $this->_get('validacion'));
        else
            return array();
    }

    public function setDatos($datos_array)
    {
        if ($datos_array)
            $this->_set('datos', json_encode($datos_array));
        else
            $this->_set('datos', NULL);
    }

    public function getDatos()
    {
        return json_decode($this->_get('datos'));
    }

    public function setDocumentoId($documento_id)
    {
        if ($documento_id == '')
            $documento_id = null;

        $this->_set('documento_id', $documento_id);
    }

    public function extraForm()
    {
        return false;
    }

    public function setExtra($datos_array)
    {
        if ($datos_array)
            $this->_set('extra', json_encode($datos_array));
        else
            $this->_set('extra', NULL);
    }

    public function getExtra()
    {
        return json_decode($this->_get('extra'));
    }

    public function isCurrentlyVisible($etapa_id)
    {
        if (strlen($this->dependiente_campo) == 0)
            return true;

        $visible = false;
        $dato_dependiente = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($this->dependiente_campo, $etapa_id);

        // Si no se encuentra, volvemos a buscar eliminando los corchetes(agregados para el checkbox) si existen
        $dato_dependiente = substr($this->dependiente_campo, abs(strlen($this->dependiente_campo) - 2), 2) != '[]' && !is_null($dato_dependiente) ?
            $dato_dependiente : Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa(substr($this->dependiente_campo, 0, strlen($this->dependiente_campo) - 2)
                , $etapa_id);
            
        if ($dato_dependiente) {

            $valores = is_array($dato_dependiente->valor) ? $dato_dependiente->valor : array($dato_dependiente->valor);
            foreach ($valores as $valor) {
                if($this->dependiente_tipo == "regex"){
                    if (preg_match('/' . $this->dependiente_valor . '/', $valor) == 1) {
                        $visible = true;
                    }
                }elseif($this->dependiente_tipo == "string"){
                    $visible = $this->dependiente_valor == $valor || $this->dependiente_valor == '"' . $valor . '"';                    
                }elseif($this->dependiente_tipo == "numeric"){
                    if($this->dependiente_relacion == "<" || $this->dependiente_relacion == ">" || $this->dependiente_relacion == "<=" || $this->dependiente_relacion == ">="){
                        if($this->dependiente_relacion == "<" && $this->dependiente_valor < $valor){
                            $visible = true;
                        }elseif($this->dependiente_relacion == ">" && $this->dependiente_valor > $valor){
                            $visible = true;
                        }elseif($this->dependiente_relacion == "<=" && $this->dependiente_valor <= $valor){
                            $visible = true;
                        }elseif($this->dependiente_relacion == ">=" && $this->dependiente_valor >= $valor){
                            $visible = true;
                        }
                    }
                }
                
                if ($this->dependiente_relacion == "!=")
                    $visible = !$visible;

                $resultados = array();
                array_push($resultados,$visible);

            }

            //condiciones extras de visibilidad
            if (count($this->condiciones_extra_visible)>0){
                $condiciones = $this->condiciones_extra_visible;
                
                foreach($condiciones as $condicion){
                    $visible_extra = false;
                    $dato_dependiente = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($condicion->campo, $etapa_id);

                    // Si no se encuentra, volvemos a buscar eliminando los corchetes(agregados para el checkbox) si existen
                    $dato_dependiente = substr($condicion->campo, abs(strlen($condicion->campo) - 2), 2) != '[]' && !is_null($dato_dependiente) ?
                        $dato_dependiente : Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa(substr($condicion->campo, 0, strlen($condicion->campo) - 2)
                            , $etapa_id);

                    if($dato_dependiente){
                        $valores = is_array($dato_dependiente->valor) ? $dato_dependiente->valor : array($dato_dependiente->valor);
                        foreach($valores as $valor){
                            if($condicion->tipo == "regex"){
                                if (preg_match('/' . $condicion->valor . '/', $valor) == 1) {
                                    $visible_extra = true;
                                }
                            }elseif($condicion->tipo == "string") {
                                $visible_extra = $condicion->valor == $valor || $condicion->valor == '"' . $valor . '"';
                            }elseif($condicion->tipo == "numeric"){
                                if($condicion->igualdad == "<" || $condicion->igualdad == ">" || $condicion->igualdad == "<=" || $condicion->igualdad == ">="){
                                    if($condicion->igualdad == "<" && $condicion->valor < $valor){
                                        $visible_extra = true;
                                    }elseif($condicion->igualdad == ">" && $condicion->valor > $valor){
                                        $visible_extra = true;
                                    }elseif($condicion->igualdad == "<=" && $condicion->valor <= $valor){
                                        $visible_extra = true;
                                    }elseif($condicion->igualdad == ">=" && $condicion->valor >= $valor){
                                        $visible_extra = true;
                                    }
                                }
                            }

                            if ($condicion->igualdad == "!=")
                                $visible_extra = !$visible_extra;
            
                            array_push($resultados,$visible_extra);
                        }
                    }

                }

                if(in_array(false,$resultados))
                    $visible = false;
                else
                    $visible = true;
            }else{
                
                if(in_array(false,$resultados))
                    $visible = false;
                else
                    $visible = true;

                Log::info($visible);
            }                
        }
        return $visible;
    }

    public function obtenerResultados($etapa)
    {
        $varProexp = $this->getVariablesExportables($etapa);
        $varexp = $this->getCamposExportables($etapa);
        $retval = array_merge($varexp, $varProexp);
        return $retval;
    }

    /**
     * Obtiene la lista de variables de formulario que se pueden exportar
     * @param type $etapa
     * @return type
     */
    public function getCamposExportables($etapa)
    {

        Log::Info("getListaExportables");
        if (!is_object($etapa)) {
            throw new ApiException('Se esperaba una instancia del "Objeto" de "Etapa"');
        }
        $campos = null;
        foreach ($etapa->Tarea->Pasos as $paso) {
            foreach ($paso->Formulario->Campos as $campo) {
                if ($campo->exponer_campo) {
                    $campos[] = $campo;
                }
            }
        }
        $return = array();
        if (isset($campos)) {
            foreach ($campos as $campo) {

                $key = $campo->nombre;//$value['nombre'];

                Log::Info("Nombre variable a retornar: " . $key);
                Log::Info("Tipo variable a retornar: " . $campo->tipo);
                if ($campo->tipo == 'file') {
                    //FIX valor
                    $filename = 'uploads/datos/' . str_replace('"', '', $campo->nombre);
                    $data = file_get_contents($filename);
                    if (isset($data) && $data != '' && $campo->isCurrentlyVisible($etapa->id)) {
                        $return[$key] = base64_encode($data);
                    }
                } else if ($campo->tipo == 'documento') {
                    $documento = Doctrine::getTable('Documento')->findOneByIdAndProcesoId($campo->documento_id, $etapa->Tarea->proceso_id);
                    //Revisar si variables del documento han sido reemplazadas
                    $contenido = $documento->contenido;

                    $docCompleto = $this->esDocumentoCompleto($etapa->id, $contenido);

                    if ($docCompleto) {
                        $file = $documento->generar($etapa->id);
                        $data = file_get_contents('uploads/documentos/' . $file->filename);
                        if (isset($data) && $data != '' && $campo->isCurrentlyVisible($etapa->id)) {
                            $return[$key] = base64_encode($data);
                        }
                    }
                } else {
                    Log::Info("Obteniendo valor para etapa: " . $etapa->id);
                    $valor_campo = $campo->displayDatoSeguimiento($etapa->id);
                    if (isset($valor_campo) && $valor_campo != '') {
                        $return[$key] = str_replace('"', '', $valor_campo);
                    }
                }

            }
        }
        Log::info("Variables a retornar: " . $this->varDump($return));
        return $return;
    }

    private function esDocumentoCompleto($etapa_id, $contenido)
    {

        $estaCompleto = true;

        $contenido = preg_replace_callback('/@@(\w+)((->\w+|\[\w+\])*)/', function ($match) use ($etapa_id) {
            $nombre_dato = $match[1];

            $dato = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($nombre_dato, $etapa_id);
            $retorno = '';
            if (!$dato) {
                $retorno = 'NO_DATA_FOUND';
            }

            return $retorno;

        }, $contenido);

        if (strpos($contenido, 'NO_DATA_FOUND') !== false) {
            $estaCompleto = false;
        }

        return $estaCompleto;

    }


    /**
     * Exporta todas las variables creadas por una accion
     * @param type $etapa
     * @return type
     */
    public function getVariablesExportables($etapa)
    {

        $proceso_id = $etapa->Tarea->proceso_id;
        $tramite_id = $etapa->Tramite->id;
        $result = Doctrine_Query::create()
            ->from('Accion a, a.Proceso p')
            ->where('p.activo=1 AND p.id=?', $proceso_id)
            ->andWhere("tipo = 'variable'")
            ->andWhere("a.exponer_variable = 1")
            ->execute();

        $return = array();
        Log::Info("#### Recorriendo resultados: " . $proceso_id);
        foreach ($result as $value) {
            Log::Info("#### key: " . $value->nombre);
            $key = $value->extra->variable;
            $valor_var = $this->getValosVariableGlobal($key, $tramite_id);
            if (isset($valor_var) && $valor_var != '') {
                $return[$key] = str_replace('"', '', $valor_var);
            }
        }
        return $return;
    }

    private function getValosVariableGlobal($nombre, $tramite_id)
    {
        try {
            $result = Doctrine_Query::create()
                ->from('DatoSeguimiento d, d.Etapa e')
                ->where('d.etapa_id = e.id')
                ->andWhere('e.tramite_id = ?', $tramite_id)
                ->andWhere("d.nombre = ? ", $nombre)
                ->execute();
            if ($result != NULL && count($result) === 1) {
                return json_decode(json_encode($result[0]->valor), true);
            }

        } catch (Exception $e) {
            log_message('error', $e->getMessage());
        }
        return null;
    }

    /**
     * Obtiene las variables que han sido como exportables pero de una definicion
     * de proceso
     *
     * @param type $form
     * @return type
     */
    public static function getVarsExpFromFormulario($form_id, $proceso_id)
    {
        try {

            return $result = Doctrine_Query::create()
                ->from('Campo c, c.Formulario f ')
                ->where('c.formulario_id = f.id')
                ->andWhere("c.exponer_campo = 1 or c.tipo='documento'")
                ->andWhere('f.proceso_id = ?', $proceso_id)
                ->andWhere("f.id= ? ", $form_id)
                ->execute();
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            throw $e;
        }
        return null;
    }

    /**
     * Obtiene las variables que se generan bajo una acción en la definición de un
     * proceso
     *
     * @param type $id_proceso
     * @param type $id_form
     * @return type  { variable : {nombre} , experesion : { valor indeerminado JSON o String }
     */
    public static function getVarsAccionExpFromProceso($id_proceso)
    {
        try {
            //select * from accion where tipo = 'variable' and proceso_id = 6 and exponer_variable = 1
            return $result = Doctrine_Query::create()
                ->from('Accion a')
                ->where('a.tipo = "variable" ')
                ->andWhere('exponer_variable = 1')
                ->andWhere('a.proceso_id = ?', $id_proceso)
                ->execute();

        } catch (Exception $e) {
            log_message('error', $e->getMessage());
        }
        return null;
    }


    public function getVariableValor($nombre, $etapa)
    {
        if(is_null($nombre)||is_null($etapa))
            return null;
        // Log::debug('Buscando valores de variable: ' . $nombre . " " . $etapa->id);
        $var = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($nombre, $etapa->id);
        if ($var != NULL) {

            return $var->valor;
        } else {
            return "";
        }
    }

    public function getVariableUltimoValor($nombre, $etapa)
    {
        // Busca a traves de las distintas etapas, desde las mas nueva  a la mas vieja
        if(is_null($nombre)||is_null($etapa))
            return null;
        // Log::debug('Buscando valores de variable: ' . $nombre . " " . $etapa->id);

        $var = DatoSeguimiento::where(['nombre'=> $nombre, 'etapa_id' => $etapa->id])->first();
        // $var = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($nombre, $etapa->id*10);
        if ($var) {
            $j = json_decode($var->valor);
            if(json_last_error() == JSON_ERROR_NONE){
                return $j;
            }
            return $var->valor;
        }else{
            $etapas = Etapa::select('id')->where('tramite_id',  $etapa->tramite_id)->orderBy('id', 'DESC')->get()->flatten();
            foreach($etapas as $etapa){
                $var = DatoSeguimiento::where(['etapa_id'=> $etapa->id, 'nombre' => $nombre])->first();
                if($var){
                    // Si no podemos decodificar la variable, la retornamos como estaba guardada
                    $j = json_decode($var->valor);
                    if(json_last_error() == JSON_ERROR_NONE){
                        return $j;
                    }
                    return $var->valor;
                }
            }
        }
        return '';
    }

    function varDump($data)
    {
        ob_start();
        //var_dump($data);
        print_r($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }

    public function getCondicionesExtraVisible()
    {
        return json_decode($this->_get('condiciones_extra_visible'));
    }
}
