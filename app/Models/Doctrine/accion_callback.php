<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccionCallback extends Accion
{

    public function displaySecurityForm($proceso_id)
    {
        $data = Doctrine::getTable('Proceso')->find($proceso_id);
        $conf_seguridad = $data->Admseguridad;
        $display = '
            <p>
                Generar un acción de tipo Callback para responder a un agente externo que inicie un trámite en simple. (Debe existir la variable Callback creada. De lo contrario el proceso será interrumpido).
            </p>
        ';

        $display .= '
                <label>Método</label>
                <select id="tipoMetodoC" name="extra[tipoMetodoC]" class="form-control col-2">.
                    <option value="">Seleccione...</option>';
        if (!is_null($this->extra) && $this->extra->tipoMetodoC && $this->extra->tipoMetodoC == "POST") {
            $display .= '<option value="POST" selected>POST</option>';
        } else {
            $display .= '<option value="POST">POST</option>';
        }
        if (!is_null($this->extra) && $this->extra->tipoMetodoC && $this->extra->tipoMetodoC == "PUT") {
            $display .= '<option value="PUT" selected>PUT</option>';
        } else {
            $display .= '<option value="PUT">PUT</option>';
        }
        if (!is_null($this->extra) && $this->extra->tipoMetodoC && $this->extra->tipoMetodoC == "DELETE") {
            $display .= '<option value="DELETE" selected>DELETE</option>';
        } else {
            $display .= '<option value="DELETE">DELETE</option>';
        }
        $display .= '</select>';

        $display .= '
            <div class="">
                <label>Header</label>
                <textarea id="header" class="form-control col-4" name="extra[header]" rows="7" cols="70" placeholder="{ Header }" class="input-xxlarge">' . ($this->extra ? $this->extra->header : '') . '</textarea>
                <br />
                <span id="resultHeader" class="spanError"></span>
                <br /><br />
            </div>';
        $display .= '
            <label>Seguridad</label>
            <select id="tipoSeguridad" name="extra[idSeguridad]" class="form-control col-2">';
        foreach ($conf_seguridad as $seg) {
            $display .= '
                    <option value="">Sin seguridad</option>';
            if ($this->extra->idSeguridad && $this->extra->idSeguridad == $seg->id) {
                $display .= '<option value="' . $seg->id . '" selected>' . $seg->institucion . ' - ' . $seg->servicio . '</option>';
            } else {
                $display .= '<option value="' . $seg->id . '">' . $seg->institucion . ' - ' . $seg->servicio . '</option>';
            }
        }
        $display .= '</select>';
        return $display;
    }

    public function validateForm(Request $request = null)
    {
        $request->validate(['extra.tipoMetodoC' => 'required'],
            ['extra.tipoMetodoC.required' => 'El campo Método es obligatorio']);
    }

    public function ejecutar($tramite_id)
    {
        $etapa = Etapa::find($tramite_id);

        Log::info("Ejecutando Callback");

        $proceso = Doctrine::getTable('Proceso')->findProceso($etapa['Tarea']['proceso_id']);
        $callback = Doctrine::getTable('Proceso')->findVaribleCallback($etapa->id);

        if ($callback['valor'] > 0) {

            foreach ($callback['data'] as $res) {
                if ($res['nombre'] == 'callback') {
                    if (strlen($res['valor']) > 5) {
                        $callback_url = $res['valor'];
                    }
                } else if ($res['nombre'] == 'callback_id') {
                    $callback_id = $res['valor'];
                }
            }

            $callback_url = str_replace('\/', '/', $callback_url);
            $base = explode("/", $callback_url);
            $server = $base[0] . '//' . $base[2];
            $server = str_replace('"', '', $server);
            $uri = '';
            for ($i = 3; $i < count($base); $i++) {
                if ($i == 3)
                    $uri .= $base[$i];
                else
                    $uri .= '/' . $base[$i];
            }
            $uri = str_replace('"', '', $uri);

            Log::info("Server: " . $server);
            Log::info("Uri: " . $uri);

            $seguridad = new SeguridadIntegracion();
            $config = $seguridad->getConfigRest($this->extra->idSeguridad, $server);

            $campo = new Campo();
            $data = $campo->obtenerResultados($etapa, $etapa['Tarea']['proceso_id']);
            $output['idInstancia'] = $etapa['tramite_id'];
            $output['idTarea'] = $etapa['Tarea']['id'];
            $output['callback-id'] = $callback_id;
            $output['data'] = $data;

            $request = json_encode($output);

            $CI = &get_instance();

            // obtenemos el Headers si lo hay
            if (isset($this->extra->header) && strlen($this->extra->header) > 0) {
                $r = new Regla($this->extra->header);
                $header = $r->getExpresionParaOutput($etapa->id);
                $headers = json_decode($header);
                foreach ($headers as $name => $value) {
                    $CI->rest->header($name . ": " . $value);
                }
            }

            try {
                Log::info("Llamando a callback URL: " . $server . "/" . $uri);
                Log::info("Llamando a callback Request: " . $request);
                Log::info("Llamando a callback Metodo: " . $this->extra->tipoMetodoC);

                $client = new GuzzleHttp\Client($config);

                // Se ejecuta la llamada segun el metodo
                if ($this->extra->tipoMetodoC == "POST") {
                    $result = $client->post($uri, [
                        GuzzleHttp\RequestOptions::JSON => $request
                    ]);
                } else if ($this->extra->tipoMetodoC == "PUT") {
                    $result = $client->put($uri, [
                        GuzzleHttp\RequestOptions::JSON => $request
                    ]);
                } else if ($this->extra->tipoMetodoC == "DELETE") {
                    $result = $client->delete($uri, [
                        GuzzleHttp\RequestOptions::JSON => $request
                    ]);
                }

                //Se obtiene la codigo de la cabecera HTTP
                $response = $result->getBody();
                $statusCode = $response->getStatusCode();

                if ($statusCode < 200 || $statusCode > 204) {
                    // Ocurio un error en el server del Callback ## Error en el servidor externo ##
                    // Se guarda en Auditoria el error
                    $response['code'] = $statusCode;
                    $response['des_code'] = $response->getContents();
                    $response = json_encode($response->getContents());
                    $operacion = 'Error en llamada Callback';

                    AuditoriaOperaciones::registrarAuditoria($proceso->nombre,
                        "Error en llamada Callback", $response, array());

                    // Se genera la variable callback_error y se le asigna el codigo y la descripcion del error.
                    $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("callback_error", $etapa->id);
                    if (!$dato) {
                        $dato = new DatoSeguimiento();
                        $dato->nombre = "callback_error";
                        $dato->valor = $response;
                        $dato->etapa_id = $etapa->id;
                        $dato->save();
                    } else {
                        $dato->valor = $response;
                        $dato->save();
                    }
                } else {
                    // Caso OK, sin errores
                    $result2 = get_object_vars($result);
                    $response = $result2;
                    $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId('callback', $etapa->id);
                    if (!$dato) {
                        $dato = new DatoSeguimiento();
                        $dato->nombre = 'callback';
                        $dato->valor = $response;
                        $dato->etapa_id = $etapa->id;
                        $dato->save();
                    } else {
                        $dato->valor = $response;
                        $dato->save();
                    }
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("error_rest", $etapa->id);
                if (!$dato)
                    $dato = new DatoSeguimiento();
                $dato->nombre = "error_rest";
                $dato->valor = $e->getMessage();
                $dato->etapa_id = $etapa->id;
                $dato->save();

                AuditoriaOperaciones::registrarAuditoria($proceso->nombre, "Ejecutar Callback", $e->getMessage(), array());
            }
        } else {
            /////////////////////////////////////////////////////////////////////////////////////
            /// Caso donde no existe la variable callback y no se ejecuta la accion,
            //  Aqui falta agregar la auditoria.
            /////////////////////////////////////////////////////////////////////////////////////
            $response = "No se pudo ejecutar el proceso de Callback debido a que no existe una variable para tal fin.";
            Log::info('####################################################################################');
            Log::info($response);
            Log::info('####################################################################################');
            // Auditoria

            AuditoriaOperaciones::registrarAuditoria($proceso->nombre, "Ejecutar Callback", $response);
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