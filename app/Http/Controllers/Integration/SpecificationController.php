<?php

namespace App\Http\Controllers\Integration;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use IntegracionMediator;
use Cuenta;
use Swagger;

class SpecificationController extends Controller
{

    /**
     * Operación que despliega lista de servicios.
     */
    public function procesos_get()
    {
        try {
            $tarea = Doctrine::getTable('Proceso')->findProcesosExpuestos(Cuenta::cuentaSegunDominio()->id);

            Log::debug('Recuperando procesos expuestos: ' . count($tarea));

            $result = array();
            $host = $_SERVER['HTTP_HOST'];
            //$nombre_host = gethostname();
            (isset($_SERVER['HTTPS']) ? $protocol = 'https://' : $protocol = 'http://');
            foreach ($tarea as $res) {
                array_push($result, array(
                    "id" => $res['id'],
                    "nombre" => $res['nombre'],
                    "tarea" => $res['tarea'],
                    "version" => "1.0",
                    "institucion" => $res['nombre_cuenta'],
                    "descripcion" => $res['previsualizacion'],
                    "URL" => $protocol . $host . '/integracion/especificacion/servicio/proceso/' . $res['id'] . '/tarea/' . $res['id_tarea']
                ));
            }
            $retval["catalogo"] = $result;

            return response()->json($retval);
        } catch (\Exception $e) {
            return response()->json(
                array("message" => "Problemas internos al recuperar especificación de procesos",
                    "code" => 500), 500);
        }

    }


    /**
     * Llamadas de la API
     * Tramote id es el identificador del proceso
     *
     *
     * @param type $operacion Operación que se ejecutara. Corresponde al tercer segmebto de la URL
     * @param type $id_proceso
     * @param type $id_tarea
     * @param type $id_paso
     */

    public function servicio_get($id_proceso, $id_tarea)
    {
        try {
            if ($id_proceso == NULL || $id_tarea == NULL) {
                return response()->json(array('status' => false, 'error' => 'Parámetros no validos'), 400);
            }


            $integrador = new IntegracionMediator();
            $swagger = new Swagger();
            /* Siempre obtengo el paso número 1 para generar el swagger de la opracion iniciar trámite */
            $formulario = $integrador->obtenerFormularios($id_proceso, $id_tarea, 1);
            $swagger_file = $swagger->generar_swagger($formulario, $id_proceso, $id_tarea);
            return response()->json(json_decode($swagger_file));
        } catch (Exception $e) {
            return response()->json(
                array("code" => $e->getCode(),
                    "message" => $e->getMessage()),
                $e->getCode());

        }
    }

    /**
     * Para obtener la especificación de formularios
     */
    public function formularios_get($id_proceso, $id_tarea = null, $id_paso = null)
    {

        if (!is_null($id_proceso) && !is_numeric($id_proceso)) {
            return response()->json(array("code" => 400,
                    "message" => 'Proceso debe ser un valor numérico')
                , 400);
        }

        if (!is_numeric($id_tarea)) {
            return response()->json(array("code" => 400,
                    "message" => 'Terea debe ser distinto a null y un valor numérico')
                , 400);
        }

        if (!is_numeric($id_paso) || $id_paso < 1) {
            return response()->json(array("code" => 400,
                    "message" => 'El valor de paso no es válido, debe ser número >= 1')
                , 400);
        }

        try {
            $integrador = new IntegracionMediator();
            $response = $integrador->obtenerFormularios($id_proceso, $id_tarea, $id_paso);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(array("code" => $e->getCode(), "message" => $e->getMessage()), $e->getCode());
        }
    }

}
