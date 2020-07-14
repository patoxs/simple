<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccionRest extends Accion
{

    public function displaySecurityForm($proceso_id)
    {
        $data = Doctrine::getTable('Proceso')->find($proceso_id);
        $conf_seguridad = $data->Admseguridad;
        $display = '
            <p>
                Esta accion consultara via REST la siguiente URL. Los resultados, seran almacenados en la variable de respuesta definida.
            </p>
        ';
        $display .= '<label>Variable respuesta</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[var_response]" value="' . ($this->extra ? $this->extra->var_response : '') . '" />';
        $display .= '<label>Endpoint</label>';
        $display .= '<input type="text" class="form-control col-4" placeholder="Server" name="extra[url]" value="' . ($this->extra ? $this->extra->url : '') . '" />';
        $display .= '<label>Resource</label>';
        $display .= '<input type="text" class="form-control col-4" placeholder="Uri" name="extra[uri]" value="' . ($this->extra ? $this->extra->uri : '') . '" />';
        $display .= '
                <label>Método</label>
                <select id="tipoMetodo" name="extra[tipoMetodo]" class="form-control col-2">
                    <option value="">Seleccione...</option>';
        if (!is_null($this->extra) && $this->extra->tipoMetodo && $this->extra->tipoMetodo == "POST") {
            $display .= '<option value="POST" selected>POST</option>';
        } else {
            $display .= '<option value="POST">POST</option>';
        }
        if (!is_null($this->extra) && $this->extra->tipoMetodo && $this->extra->tipoMetodo == "GET") {
            $display .= '<option value="GET" selected>GET</option>';
        } else {
            $display .= '<option value="GET">GET</option>';
        }
        if (!is_null($this->extra) && $this->extra->tipoMetodo && $this->extra->tipoMetodo == "PUT") {
            $display .= '<option value="PUT" selected>PUT</option>';
        } else {
            $display .= '<option value="PUT">PUT</option>';
        }
        if (!is_null($this->extra) && $this->extra->tipoMetodo && $this->extra->tipoMetodo == "DELETE") {
            $display .= '<option value="DELETE" selected>DELETE</option>';
        } else {
            $display .= '<option value="DELETE">DELETE</option>';
        }
        $display .= '</select>';
        $display .= '<label>Timeout</label>';
        $display .= '<input type="text" class="form-control col-2" placeholder="Tiempo en segundos..." name="extra[timeout]" value="' . ($this->extra ? $this->extra->timeout : '') . '" />';

        $display .= '<label>N&uacute;mero reintentos</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[timeout_reintentos]" value="' . ($this->extra ? $this->extra->timeout_reintentos : '3') . '" />';

        if (!is_null($this->extra) && $this->extra->tipoMetodo && ($this->extra->tipoMetodo == "PUT" || $this->extra->tipoMetodo == "POST")) {

            $paramType = isset($this->extra->paramType) ? $this->extra->paramType : GuzzleHttp\RequestOptions::JSON;

            $display .= '
            <div id="divObject">

                <div class="form-group">
                  <label>Request</label>
                </div>

                <div class="form-check form-group">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="extra[paramType]" id="jsonParam" value="'. GuzzleHttp\RequestOptions::JSON .'"
                          '.  ( $paramType === GuzzleHttp\RequestOptions::JSON ? "checked" : "" ) .'>
                    <label class="form-check-label" for="jsonParam">json</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="extra[paramType]" id="formParam" value="'. GuzzleHttp\RequestOptions::FORM_PARAMS .'"
                          '.  ( $paramType === GuzzleHttp\RequestOptions::FORM_PARAMS ? "checked" : "" ) .'>
                    <label class="form-check-label" for="formParam">form-data</label>
                  </div>
                </div>

                <div class="form-group" i>
                  <textarea id="request" name="extra[request]" rows="7" cols="70" placeholder="{ object }" class="form-control col-4">' . ($this->extra ? $this->extra->request : '') . '</textarea>
                  <br />
                  <span id="resultRequest" class="spanError"></span>
                </div>

            </div>';
        } else {
            $display .= '
            <div id="divObject" style="display:none;">
                <div class="form-group">
                  <label>Request</label>
                </div>

                <div class="form-check form-group">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="extra[paramType]" id="jsonParam" value="'. GuzzleHttp\RequestOptions::JSON .'" checked >
                    <label class="form-check-label" for="jsonParam">json</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="extra[paramType]" id="formParam" value="'. GuzzleHttp\RequestOptions::FORM_PARAMS .'" >
                    <label class="form-check-label" for="formParam">form-data</label>
                  </div>
                </div>

                <div class="form-group" i>
                  <textarea id="request" name="extra[request]" rows="7" cols="70" placeholder="{ object }" class="form-control col-4">' . ($this->extra ? $this->extra->request : '') . '</textarea>
                  <br />
                  <span id="resultRequest" class="spanError"></span>
                </div>
                <br /><br />
            </div>';
        }
        $display .= '
            <div>
                <label>Header</label>
                <textarea id="header" name="extra[header]" rows="7" cols="70" placeholder="{ Header }" class="form-control col-4">' . ($this->extra ? $this->extra->header : '') . '</textarea>
                <br />
                <span id="resultHeader" class="spanError"></span>
                <br /><br />
            </div>';

        $display .= '
                <label>Certificado</label>
                <div class="form-group form-inline">
                    <input type="file" class="form-control col-5 AlignText" name="extra[crt]" />
                </div>';
        $display .= '<input type="hidden" class="form-control col-2" name="certificado" value="' . ($this->extra && isset($this->extra->crt) && $this->extra->crt ? $this->extra->crt : '') . '" />';
        $display .= '<a href="#" class="alert-link"> ' . ($this->extra && isset($this->extra->crt) && $this->extra->crt ? $this->extra->crt : '') . ' </a><br><br> ';
        
        $display .= '<div id="modalImportarCrt" class="modal hide fade">
                
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Archivo Rest</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Cargue a continuación el archivo .crt del Servicio Rest.</p>
                            <input type="file" name="archivo" />
                            <input id="' . $this->id . '" type="hidden" name="' . $this->nombre . '" value=""  />
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                            <button type="button" id="btn-load" class="btn btn-primary" onclick="CargarCrt()">Importar</button>
                        </div>
                    </div>
                  </div> 
                
            </div>
            <div id="modal" class="modal hide fade"></div>';

        $display .= '
                <label>Seguridad</label>
                <select id="tipoSeguridad" class="form-control col-2" name="extra[idSeguridad]">
                <option value="-1">Sin seguridad</option>';

        foreach ($conf_seguridad as $seg) {
            if (!is_null($this->extra) && isset($this->extra->idSeguridad) && $this->extra->idSeguridad && $this->extra->idSeguridad == $seg->id) {
                $display .= '<option value="' . $seg->id . '" selected>' . $seg->institucion . ' - ' . $seg->servicio . '</option>';
            } else {
                $display .= '<option value="' . $seg->id . '">' . $seg->institucion . ' - ' . $seg->servicio . '</option>';
            }
        }
        $display .= '</select>';

        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.var_response' => 'required',
            'extra.url' => 'required',
            'extra.uri' => 'required',

        ], [
            'extra.var_response.required' => 'El campo Variable de respuesta es obligatorio',
            'extra.url.required' => 'El campo Endpoint es obligatorio',
            'extra.uri.required' => 'El campo Resource es obligatorio',
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;
        $msg = "Ejecuta REST";
        try {

            //$crt = $this->extra->crt;
            Log::info("Ejecutando llamado REST");

            ($this->extra->timeout ? $timeout = $this->extra->timeout : $timeout = 30);

            Log::info("TimeOut: " . $timeout);

            $r = new Regla($this->extra->url);
            $server = $r->getExpresionParaOutput($etapa->id);
            $caracter = "/";
            $f = substr($server, -1);

            if ($caracter === $f) {
                $server = substr($server, 0, -1);
            }

            $r = new Regla($this->extra->uri);
            $uri = $r->getExpresionParaOutput($etapa->id);
            $l = substr($uri, 0, 1);
            if ($caracter === $l) {
                $uri = substr($uri, 1);
            }

            Log::info("Server: " . $server);
            Log::info("Resource: " . $uri);

            $seguridad = new SeguridadIntegracion();   //Para usuario - clave servicio 
            $idSeguridad = null;

            if (isset($this->extra->idSeguridad)) {
                $idSeguridad = $this->extra->idSeguridad;
            }

            $crt = null;
            if (isset($this->extra->crt)) {
                $crt = public_path('uploads/certificados/').$this->extra->crt;
            }
            
            $config = $seguridad->getConfigRest($idSeguridad, $server, $timeout, $crt);

            $request = '';
            if (isset($this->extra->request)) {
                $request = $this->extra->request;
                // Si es de tipo array, debemos quitar las comillas dobles que encierran a la variable
                if(preg_match_all('/\"@@(\w+)\"/', $request, $out) > 0){
                    $datoSeguimiento_table = Doctrine::getTable('DatoSeguimiento');
                    $out = array_unique($out[0]);
                    foreach($out as $found_str){
                        $nombre_dato = substr($found_str, 3, -1);
                        $dato = $datoSeguimiento_table->findByNombreHastaEtapa($nombre_dato, $etapa->id);
                        if( $dato !== false && ( is_array($dato->valor)|| is_object($dato->valor))){
                            $request = str_replace($found_str, json_encode($dato->valor), $request);
                        }
                    }
                }

                $r = new Regla($request);
                $request = $r->getExpresionParaOutput($etapa->id);
            }

            //log::info("Request: " . $request);

            $headers = array();

            //obtenemos el Headers si lo hay
            if (isset($this->extra->header)) {
                $r = new Regla($this->extra->header);
                $header = $r->getExpresionParaOutput($etapa->id);
                $headers = json_decode($header,true);
            }

            $config['headers'] = $headers;
            $client = new GuzzleHttp\Client($config);

            //se verifica si existe numero de reintentos
            $reintentos = 0;
            $intentos = 0;
            if (isset($this->extra->timeout_reintentos)) {
                $reintentos = $this->extra->timeout_reintentos;
            }
            $timeout = false;

            Log::debug("Numero de reintentos: " . $reintentos);

            $ultimo_codigo_http = -1;
            do {
                
                $paramType = isset($this->extra->paramType) ? $this->extra->paramType : GuzzleHttp\RequestOptions::JSON;
                try {
                    
                    // Se ejecuta la llamada segun el metodo
                    if ($this->extra->tipoMetodo == "GET") {
                        $result = $client->request('GET', $uri);
                    } else if ($this->extra->tipoMetodo == "POST") {
                        $result = $client->request('POST', $uri, [
                            $paramType => json_decode($request)
                        ]);
                    } else if ($this->extra->tipoMetodo == "PUT") {
                        $result = $client->put($uri, [
                            $paramType => json_decode($request)
                        ]);
                    } else if ($this->extra->tipoMetodo == "DELETE") {
                        $result = $client->delete($uri, [
                            GuzzleHttp\RequestOptions::JSON => json_decode($request)
                        ]);
                    }

                    Log::debug("Result REST: " . $this->varDump($result));
                    $ultimo_codigo_http = $result->getStatusCode();

                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'timed out') !== false) {
                        Log::debug("Reintentando " . $reintentos . " veces . ");
                        $result2['code'] = $e->getCode();
                        $result2['desc'] = $e->getMessage();
                        $intentos++;
                        $timeout = true;
                        $ultimo_codigo_http = $e->getCode();
                    } else {
                        $ultimo_codigo_http = $e->getCode();
                        throw new ApiException($e->getMessage(), $e->getCode());
                    }
                }

                Log::debug("Intentos: " . $intentos);
                Log::debug("Reintentos: " . $reintentos);

            } while ($timeout && ($intentos < $reintentos));

            if ($intentos != $reintentos) {
                $response = $result->getBody()->getContents();
                $statusCode = $result->getStatusCode();
                if ($statusCode == '0') {
                    $result2['code'] = '500';
                    $result2['desc'] = $response->getMessage();
                    $ultimo_codigo_http = 500;
                } else {
                    if (!is_array($result) && !is_object($result)) {
                        $result2['code'] = '206';
                        $result2['desc'] = $response->getMessage();
                        $ultimo_codigo_http = 206;
                    } else {
                        $ultimo_codigo_http = $statusCode;
                        $result2 = $response;
                    }
                }
                Log::debug("Respuesta REST: " . $this->varDump($result2));
            }

        } catch (Exception $e) {
            Log::info("En catch de accion REST Error: " . $e->getMessage());
            $result2['code'] = $e->getCode();
            $ultimo_codigo_http = $e->getCode();
            $result2['desc'] = $e->getMessage();
        }

        try{
            if( ! empty($result2) && is_array($result2) && array_key_exists('code', $result2)
                    && ! array_key_exists('status', $result2) ){
                $result2['status'] = $result2['code'];
            }
        }catch (Exception $e){
            Log::info("Catch accion REST. Al result2['status']=result2['code']");
        }

        $result2 = json_encode($result2);
        $result2 = str_replace(" - ", "_", $result2);
        $result2 = json_decode($result2);

        $response = (object)[];
        $response->{$this->extra->var_response} = $result2;

        Log::debug("Respuesta REST Response: " . $this->varDump($response));

        foreach ($response as $key => $value) {
            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = $key;
            $dato->valor = $value;
            $dato->etapa_id = $etapa->id;
            $dato->save();

            $key_code = trim($key).'_http_code';
            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key_code, $etapa->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            if(is_string(trim($ultimo_codigo_http) && is_numeric($ultimo_codigo_http) ) ) {
                $ultimo_codigo_http = intval($ultimo_codigo_http);
            }
            $dato->nombre = $key_code;
            $dato->valor = $ultimo_codigo_http;
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }

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
}
