<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class AccionNotificaciones extends Accion
{

    public function displaySuscriptorForm($proceso_id)
    {

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        $suscriptores = $proceso->Suscriptores;

        $display = '
            <p>
                Genera una accion de notificación a los suscriptores seleccionados que esten registrados en este proceso.
            </p>
        ';

        $display .= '<div class="campo control-group">';
        $display .= '<label class="control-label">Suscriptores:</label>';
        $display .= '<div class="controls">';

        foreach ($suscriptores as $suscriptor) {
            $nombre_checkbox = ' <a target="_blank" href="' . url('backend/suscriptores/editar/' . $suscriptor->id) . '">' . $suscriptor->institucion . '</a>';
            if (isset($this->extra->suscriptorSel) && count($this->extra->suscriptorSel) > 0) {
                if (in_array($suscriptor->id, $this->extra->suscriptorSel)) {
                    $display .= '<label class="checkbox"><input type="checkbox" name="extra[suscriptorSel][]" value="' . $suscriptor->id . '" checked=true />' . $nombre_checkbox . '</label>';
                } else {
                    $display .= '<label class="checkbox"><input type="checkbox" class="SelectAll" name="extra[suscriptorSel][]" value="' . $suscriptor->id . '"/>' . $nombre_checkbox . '</label>';
                }
            } else {
                $display .= '<label class="checkbox"><input type="checkbox" class="SelectAll" name="extra[suscriptorSel][]" value="' . $suscriptor->id . '"/>' . $nombre_checkbox . '</label>';
            }
        }
        $display .= '</div></div>';

        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.suscriptorSel' => 'required',
        ], [
            'extra.suscriptorSel.required' => 'El campo Suscriptores es obligatorio'
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;

        Log::info("Notificando a suscriptores");

        $proceso = Doctrine::getTable('Proceso')->find($etapa['Tarea']['proceso_id']);
        $suscriptores = $proceso->Suscriptores;
        if (isset($this->extra->suscriptorSel) && count($this->extra->suscriptorSel) > 0) {
            foreach ($this->extra->suscriptorSel as $suscriptor_id) {
                Log::info("Notificando a suscriptor id: " . $suscriptor_id);
                $suscriptor = Doctrine::getTable('Suscriptor')->find($suscriptor_id);
                Log::info("Suscriptor institucion: " . $suscriptor->institucion);
                Log::info("Suscriptor request: " . $suscriptor->extra->request);

                $idSeguridad = $suscriptor->extra->idSeguridad;

                $webhook_url = str_replace('\/', '/', $suscriptor->extra->webhook);
                $base = explode("/", $webhook_url);
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

                $campo = new Campo();
                $data = $campo->obtenerResultados($etapa, $etapa['Tarea']['proceso_id']);
                $output['idInstancia'] = $etapa['tramite_id'];
                $output['idTarea'] = $etapa['Tarea']['id'];
                $output['data'] = $data;

                $request = json_encode($output);

                $request_suscriptor = $suscriptor->extra->request;
                if (isset($request_suscriptor) && strlen($request_suscriptor) > 0) {
                    if (strpos($request_suscriptor, '@@output') !== false) {
                        $request = str_replace('"', '\"', $request);
                        $request = str_replace('@@output', $request, $request_suscriptor);
                    }
                }

                $request = json_decode($request);

                $seguridad = new SeguridadIntegracion();
                $config = $seguridad->getConfigRest($idSeguridad, $server, 30);

                Log::info("Llamando a suscriptor URL: " . $uri);

                $headers = array(
                    'Content-Type: application/json',
                );

                try {

                    $client = new GuzzleHttp\Client($config); //GuzzleHttp\Client

                    $result = $client->post($uri, [
                        GuzzleHttp\RequestOptions::JSON => $request
                    ]);

                    $response = $result->getBody();

                } catch (Exception $exception) {
                    Log::error('Error Notificando a suscriptor ' . $suscriptor->institucion . $exception->getMessage());
                    $response = $exception->getBody();
                    $error_message = $exception->getMessage();
                } finally {
                    $statusCode = $result->getStatusCode();
                }

                //Se obtiene la codigo de la cabecera HTTP
                if ($statusCode < 200 || $statusCode >= 300) {
                    // Ocurio un error en el server del Callback ## Error en el servidor externo ##
                    // Se guarda en Auditoria el error
                    $response->code = $statusCode;
                    $response->des_code = $error_message;
                    $response = json_encode($response->getContents());
                    $operacion = 'Error Notificando a suscriptor ' . $suscriptor->institucion;

                    AuditoriaOperaciones::registrarAuditoria($proceso->nombre,
                        "Error Notificando a suscriptor " . $suscriptor->institucion, $response, array());

                } else {
                    // Caso con errores
                    if (isset($result)) {
                        $result2 = get_object_vars($result);
                    } else {
                        $result2 = "SUCCESS";
                    }
                    $response = $result2;
                    AuditoriaOperaciones::registrarAuditoria($proceso->nombre,
                        "Suscriptor " . $suscriptor->institucion . " notificado exitosamente", implode(',', $response), array());
                }
            }
        }

        Log::info("Suscriptores notificados");

    }
}