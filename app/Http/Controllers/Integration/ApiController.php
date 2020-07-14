<?php

namespace App\Http\Controllers\Integration;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception as ApiException;

class ApiController extends Controller
{
    public function tramites_post(Request $request, $id_proceso, $id_tarea)
    {
        Log::info("inicio proceso");

        if (!isset($id_proceso)
            || !isset($id_tarea)) {
            return response()->json(array('message' => 'Parámetros insuficientes', "code" => 400), 400);
        }
        try {
            $this->checkIdentificationHeaders($request, $id_tarea);

            $mediator = new \IntegracionMediator();

            $bodyContent = $request->getContent();

            $this->registrarAuditoria($request, $id_tarea, "Iniciar Tramite", "Tramites", $bodyContent);

            $data = $mediator->iniciarProceso($id_proceso, $id_tarea, $bodyContent);

            $this->eliminarDataSeguimiento($data['idInstancia']);

            return response()->json($data);
        } catch (Exception $e) {
            Log::info("Recupera exception: " . $e->getMessage());
            Log::info("Recupera getCode: " . $e->getCode());

            return response()->json(
                array("message" => $e->getMessage(),
                    "code" => $e->getCode()), $e->getCode());
        }
    }

    public function tramites_put(Request $request, $id_tramite, $id_etapa, $id_paso)
    {

        if (!isset($id_tramite)
            || !isset($id_etapa)
            || !isset($id_paso)) {
            return response()->json(array('message' => 'Parámetros insuficientes', "code" => 400), 400);
        }

        //Recuperar los valores
        $etapa_id = $id_etapa;
        $tramite_id = $id_tramite;
        $secuencia = $id_paso;

        try {

            $mediator = new \IntegracionMediator();

            $etapa = Doctrine::getTable('Etapa')->findOneById($etapa_id);

            if ($etapa == null) {
                return response()->json(array("message" => "Etapa no existe"), 400);
            }

            $bodyContent = $request->getContent();
            $this->checkIdentificationHeaders($request, $etapa->tarea_id);
            $this->registrarAuditoria($request, $etapa->id, "Continuar Tramite", "Tramites", $bodyContent);

            $data = $mediator->continuarProceso($tramite_id, $etapa_id, $secuencia, $bodyContent);
        } catch (Exception $e) {
            return response()->json(array("message" => $e->getMessage(), "code" => $e->getCode()), $e->getCode());
        }
        return response()->json($data);
    }

    /**
     *
     * @param type $tipo
     * @param type $id_tramite
     * @param type $id_paso
     */
    public function status_get()
    {

        Log::info("Status proceso");

        try {
            if (!isset($this->get()['tramite']) && !isset($this->get()['rut']) && !isset($this->get()['user'])) {
                return response()->json(array('message' => 'Parámetros insuficientes', "code" => 400), 400);
            }

            if (isset($this->get()['tramite'])) {
                $status = $this->obtenerStatusPorTramite($this->get()['tramite']);
            } else if (isset($this->get()['rut']) || isset($this->get()['user'])) {
                $status = $this->obtenerStatusPorUsuario($this->get()['rut'], $this->get()['user']);
            }

            return response()->json($status);
        } catch (Exception $e) {
            return response()->json(
                array("message" => $e->getMessage(),
                    "code" => $e->getCode()), $e->getCode());
        }

    }

    /**
     * Realiza un check de los headers para degerminar a quien están asignados
     *
     * @param type $etapa
     * @param type $id_tarea
     * @return boolean
     */
    private function checkIdentificationHeaders(Request $request, $id_tarea)
    {
        Log::info('checkIdentificationHeaders');
        try {
            $tarea = Doctrine::getTable('Tarea')->findOneById($id_tarea);

            if ($tarea == NULL) {
                error_log("etapa debe ser una instancia de Etapa");
                throw new ApiException("Etapa no fue encontrada", 404);
            }

            $bodyContent = $request->getContent();

            $body = json_decode($bodyContent, false);

            Log::debug('Check modo');

            switch ($tarea->acceso_modo) {
                case 'claveunica':
                    if (!isset($body->identificacion)) {
                        throw new ApiException('Identificación Clave Unica no enviada', 403);
                    }
                    $mediator = new \IntegracionMediator();
                    $mediator->registerUserFromHeadersClaveUnica($body->identificacion);
                    if (Auth::user() == NULL) {
                        Log::error('No se pudo registrar el usuario Open ID');
                        throw new ApiException('No se pudo registrar el usuario Open ID', 500);
                    }
                    break;
                case 'registrados':
                case 'grupos_usuarios':
                    Log::debug("No existe el usuario o no viene el header " . $this->varDump($body->identificacion->user));
                    if (!isset($body->identificacion) || !\Usuario::registrarUsuario($body->identificacion->user)) {
                        Log::debug("No existe el usuario o no viene el header " . $this->varDump($body));
                        throw new ApiException('No se ha enviado el usuario', 403);
                    }
                    Log::debug('recuperando usuarios');
                    if ($tarea->acceso_modo === 'grupos_usuarios') {
                        Log::debug($tarea->id);
                        $usuarios = $tarea->getUsuariosFromGruposDeUsuarioDeCuenta($id_tarea);
                        foreach ($usuarios as $user) {

                            if ($body->identificacion->user === $user->usuario) {
                                Log::debug('Validando usuario clave unica: ' . $user->usuario);
                                return TRUE;
                            }
                        }
                    } else {
                        return TRUE;
                    }
                    throw new ApiException('Usuario no existe', 403);
                case 'anonimo':
                case 'publico':
                    if (!Auth::user()) {
                        //crear un usuario para sesion anonima
                        \Usuario::createAnonymousSession();
                    }
                    break;
            }
        } catch (Exception $e) {
            throw new ApiException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     *
     * @param type $etapa_id
     * @param type $operacion
     * @param type $nombre_proceso
     */
    public function registrarAuditoria(Request $request, $etapa_id, $operacion, $nombre_proceso = NULL, $body = NULL)
    {
        try {
            $nombre_etapa = $nombre_proceso;
            $etapa = NULL;
            if ($etapa_id != NULL) {
                $etapa = Doctrine::getTable('Tarea')->findOneById($etapa_id);
                $nombre_etapa = ($etapa != NULL) ? $etapa->nombre : "Catalogo";

            }

            $headers = \getallheaders();

            $new_headers = array('host' => $headers['Host'],
                'Origin' => isset($headers['Origin']) ? $headers['Origin'] : '',
                'largo-mensaje' => isset($headers['content-length']) ? $headers['content-length'] : '',
                'Content-type' => isset($headers['Content-Type']) ? $headers['Content-Type'] : '',
                'http-Method' => $request->getMethod());

            $data['headers'] = $new_headers;

            if (isset($body) && isset($body->identificacion) && $nombre_etapa != NULL) { //Comprobar que exista identificacion y etapa

                $data['Credenciales'] =
                    array("Metodo de acceso" => $etapa->acceso_modo,
                        "Username" =>
                            ($etapa->acceso_modo == 'claveunica')
                                ? $body->identificacion->rut : $body->identificacion->user);
            }
            //Recuperar el nombre para el regisrto
            Log::debug("Recuperando credencial de identificación para auditoría");

            \AuditoriaOperaciones::registrarAuditoria($nombre_etapa, $operacion,
                "Auditoria de llamados a API REST", json_encode($data));
        } catch (Exception $e) {
            throw new ApiException($e->errorMessage(), 500);
        }
    }

    private function obtenerStatusPorTramite($id_tramite)
    {

        Log::info('Obteniendo estado para trámite: ' . $id_tramite);

        try {
            $tramite = Doctrine::getTable('Tramite')->find($id_tramite);

            if (isset($tramite) && is_object($tramite)) {

                Log::info('Tramite recuperado');

                $status = $this->obtenerInfoTramite($tramite);

            } else {
                throw new ApiException("Trámite no encontrado", 412);
            }

            Log::info('Status: ' . $this->varDump($status));
            return $status;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }

    }

    private function obtenerStatusPorUsuario($rut = null, $nombre_usuario = null)
    {

        Log::info('Obteniendo estado trámites para rut: ' . $rut);

        try {

            $user = new Usuario();
            if ($rut != null) {
                $usuario = $user->findUsuarioPorRut($rut);
            } else {
                $usuario = $user->findUsuarioPorUser($nombre_usuario);
            }

            if (isset($usuario) && is_array($usuario) && count($usuario) > 0) {

                Log::info('Usuario recuperado: ' . $usuario[0]["id"]);

                $tramites = Doctrine::getTable('Tramite')->tramitesPorUsuario($usuario[0]["id"]);

                Log::info('Tramites recuperados: ' . $tramites);

                if (isset($tramites) && (is_object($tramites) || is_array($tramites))) {
                    $statusTramites = array();
                    foreach ($tramites as $tramite) {
                            $status = $this->obtenerInfoTramite($tramite);
                        array_push($statusTramites, $status);
                    }
                }

            } else {
                throw new ApiException("Usuario no encontrado", 412);
            }

            return $statusTramites;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
    }

    private function obtenerInfoTramite($tramite)
    {


        try {

            $etapa = $tramite->getEtapasActuales()->get(0);

            $proceso = Doctrine::getTable('Proceso')->find($tramite->proceso_id);

            if (isset($etapa) && is_object($etapa)) {

                Log::info('Etapa recuperada');
                Log::info('Id usuario: ' . $etapa->usuario_id);

                $usuario = Doctrine::getTable('Usuario')->find($etapa->usuario_id);

                $rut = "No existe información";
                if (isset($usuario) && isset($usuario->rut) && strlen($usuario->rut) > 0) {
                    Log::info('Usuario rut: ' . $usuario->rut);
                    $rut = $usuario->rut;
                }

                Log::info('Nombre proceso: ' . $proceso->nombre);

                $tarea = Doctrine::getTable('Tarea')->find($etapa->tarea_id);

                $status = array("idTramite" => $tramite->id,
                    "nombreTramite" => $proceso->nombre,
                    "estado" => $tramite->pendiente == 1 ? "Pendiente" : "Completado",
                    "rutUsuario" => $rut,
                    "nombreEtapaActual" => $tarea->nombre);

            } else {
                $status = array("idTramite" => $tramite->id,
                    "nombreTramite" => $proceso->nombre,
                    "estado" => "Completado",
                    "rutUsuario" => "No existe información",
                    "nombreEtapaActual" => "No existe información");
            }
            return $status;
        } catch (Exception $e) {
            throw new ApiException("Problema al recuperar información del trámite", 500);
        }
    }


    private function varDump($data)
    {
        ob_start();
        //var_dump($data);
        print_r($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }

    private function eliminarDataSeguimiento($tramite_id){
        Log::info("eliminación data tramite_id: " . $tramite_id);
        $etapas = \App\Models\Etapa::where('tramite_id',(int) $tramite_id)->get();
        foreach($etapas as $etapa){
            $datos = Doctrine::getTable('DatoSeguimiento')->findByEtapaId($etapa->id);
            foreach($datos as $dato){
                $dato->delete();
            }
        }
    }

}