<?php
require_once('accion.php');

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;

class AccionSoap extends Accion
{

    public function displaySecurityForm($proceso_id)
    {
        $data = Doctrine::getTable('Proceso')->find($proceso_id);
        $conf_seguridad = $data->Admseguridad;
        $display = '<p>
            Esta accion consultara via SOAP la siguiente URL. Los resultados, seran almacenados en la variable de respuesta definida.
            </p>';
        $display .= '<label>Variable respuesta</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[var_response]" value="' . ($this->extra ? $this->extra->var_response : '') . '" />';
        $display .= '
                    <label>WSDL</label>
                <div class="form-group form-inline">
                    <input type="text" class="form-control col-5 AlignText" id="urlsoap" name="extra[wsdl]" value="' . ($this->extra ? $this->extra->wsdl : '') . '" />
                    <button type="button" class="ml-2 btn btn-secondary" id="btn-consultar" ><i class="material-icons">search</i> Consultar</button>
                    <a class="ml-2 btn btn-secondary" href="#modalImportarWsdl" data-toggle="modal" ><i class="material-icons">file_upload</i> Importar</a>
                </div>';

        $display .= '<label>Timeout</label>';
        $display .= '<input type="text" class="form-control col-2" placeholder="Tiempo en segundos..." name="extra[timeout]" value="' . ($this->extra ? $this->extra->timeout : '') . '" />';

        $display .= '<label>N&uacute;mero reintentos</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[timeout_reintentos]" value="' . ($this->extra ? $this->extra->timeout_reintentos : '3') . '" />';

        $display .= '
                <div id="divMetodos" class="">
                    <label>Métodos</label>
                    <select id="operacion" name="extra[operacion]" class="form-control col-2">';
        if (!is_null($this->extra) && $this->extra->operacion) {
            $display .= '<option value="' . ($this->extra->operacion) . '" selected>' . ($this->extra->operacion) . '</option>';
        }
        $display .= '</select>
                </div>
                <div id="divMetodosE" style="display:none;" class="col-md-12">
                    <span id="warningSpan" class="spanError"></span>
                    <br /><br />
                </div>';
        $display .= '
                <label>Request (XML)</label>
                <textarea id="request" class="form-control col-5" name="extra[request]" rows="7" cols="70" placeholder="<xml></xml>" class="form-control">' . ($this->extra ? $this->extra->request : '') . '</textarea>
                <!-- <span id="resultRequest" class="spanError"></span> -->
                <br /><br />';
        $display .= '
                <label>Headers</label>
                <textarea id="header" class="form-control col-5" name="extra[header]" rows="7" cols="70" placeholder="header" class="form-control">' . (isset($this->extra->header) ? $this->extra->header : '') . '</textarea>
                <!-- <span id="headerRequest" class="spanError"></span> -->
                <br /><br />';
        /*<div class="col-md-12">
             <label>Response</label>
             <textarea id="response" name="extra[response]" rows="7" cols="70" placeholder="{ object }" class="form-control" readonly>' . ($this->extra ? $this->extra->response : '') . '</textarea>
             <br /><br />
         </div>';*/
        $display .= '<div id="modalImportarWsdl" class="modal hide fade">
                <form method="POST" enctype="multipart/form-data" action="backend/acciones/upload_file">
                  <div class="modal-dialog" role="document">
                  <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importar Archivo Soap</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Cargue a continuación el archivo .wsdl del Servio Soap.</p>
                    <input type="file" name="archivo" />
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                    <button type="button" id="btn-load" class="btn btn-primary">Importar</button>
                </div>
                </div>
                </div>
                </form>
            </div>
            <div id="modal" class="modal hide fade"></div>';
        $display .= '<label>Seguridad</label>
                <select id="tipoSeguridad" class="form-control col-2" name="extra[idSeguridad]">';
        foreach ($conf_seguridad as $seg) {
            $display .= '<option value="">Sin seguridad</option>';
            if(!is_null($this->extra) && isset($this->extra->idSeguridad) && $this->extra->idSeguridad && $this->extra->idSeguridad == $seg->id) {
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
            'extra.request' => 'required',
            'extra.operacion' => 'required',
            'extra.var_response' => 'required'
        ], [
            'extra.request.required' => 'El campo Request es obligatorio',
            'extra.operacion.required' => 'El campo Métodos es obligatorio',
            'extra.var_response.required' => 'El campo Variable de respuesta es obligatorio'
        ]);
    }

    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;

        //Se declara el cliente soap
        $timeout = isset($this->extra->timeout) ? $this->extra->timeout : 30;
        $client = new \App\Helpers\nusoap\lib\nusoap_client($this->extra->wsdl,'wsdl',false,false,false,false,0,$timeout);

        if (isset($this->extra->idSeguridad) && strlen($this->extra->idSeguridad) > 0 && $this->extra->idSeguridad > 0) {
            $seguridad = new SeguridadIntegracion();
            $client = $seguridad->setSecuritySoap($client);
        }

        // Se asigna valor de timeout
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;

        try {
            if (isset($this->extra->header)) {
                $r = new Regla($this->extra->header);
                $header = $r->getExpresionParaOutput($etapa->id);
                $client->additionalHeaders = json_decode($header, true);
            }

            //$CI = &get_instance();
            $r = new Regla($this->extra->wsdl);
            $wsdl = $r->getExpresionParaOutput($etapa->id);
            if (isset($this->extra->request)) {
                $r = new Regla($this->extra->request);
                $request = $r->getExpresionParaOutput($etapa->id);
            }

            $intentos = -1;

            //se verifica si existe numero de reintentos
            $reintentos = 0;

            if (isset($this->extra->timeout_reintentos)) {
                $reintentos = $this->extra->timeout_reintentos;
            }

            do {
                //Se EJECUTA el llamado Soap
                $result = $client->call($this->extra->operacion, $request, null, '', false, null, 'rpc', 'literal', true);

                Log::info("Error SOAP");

                $error = $client->getError();
                Log::info("Error SOAP " . $this->varDump($error));

                //se verifica si existe numero de reintentos
                if (isset($error) && strpos($error, 'timed out') !== false) {
                    Log::info("Intento Nro: " . $intentos);
                    $intentos++;
                }
            } while ($intentos < $reintentos && strpos($error, 'timed out') !== false);

            if ($error) {
                if (strpos($error, 'timed out') !== false) {
                    $result_soap['code'] = '504';
                    $result_soap['desc'] = $error;
                } else {
                    $result_soap['code'] = '500';
                    $result_soap['desc'] = $error;
                }
            } else {
                $result = empty($result) ? [] : $result;
                $result_soap = $this->utf8ize($result);
                $result_soap['code'] = '200';
            }

        } catch (Exception $e) {
            $result_soap['code'] = $e->getCode();
            $result_soap['desc'] = $e->getMessage();
        }

        $result[$this->extra->var_response] = $result_soap;

        foreach ($result as $key => $value) {

            Log::info('key ' . $key . ': ' . $this->varDump($value));

            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa->id);

            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = $key;
            $dato->valor = $value;
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }
    }

    public function varDump($data)
    {
        ob_start();
        //var_dump($data);
        print_r($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }

    private function utf8ize($d)
    {
        try {
            if (is_array($d))
                foreach ($d as $k => $v)
                    $d[$k] = $this->utf8ize($v);
            else if (is_object($d))
                foreach ($d as $k => $v)
                    $d->$k = $this->utf8ize($v);
            else
                return utf8_encode($d);
        } catch (Exception $e) {
            Log::info('Exception utf8ize: ' . $this->varDump($e));
        }
        return $d;
    }

}
