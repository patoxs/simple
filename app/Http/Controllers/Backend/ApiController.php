<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use DatoSeguimiento;
use Doctrine_Query;
use Doctrine_Core;
use stdClass;
use Cuenta;
use Regla;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function updateToken(Request $request)
    {
        $this->validate($request, [
            'api_token' => 'required|max:32'
        ]);

        $cuenta = Auth::user()->Cuenta;
        $cuenta->api_token = $request->input('api_token');
        $cuenta->save();

        $request->session()->flash('status', 'API Token editado con éxito');

        return redirect()->route('backend.api');
    }

    public function procesos_disponibles()
    {
        $data['title'] = 'Trámites disponibles como servicios';
        $data['content'] = 'backend/api/tramites_disponibles';
        $data['json'] = Doctrine::getTable('Proceso')->findProcesosExpuestos(Auth::user()->cuenta_id);

        return view('backend.api.procesos_disponibles', $data);
    }

    /*
     * Llamadas de la API
     */
    public function tramites(Request $request, $tramite_id = null)
    {
        $api_token = $request->input('token');
        $api_dato_key = $request->input('dato', null);


        $cuenta = Cuenta::cuentaSegunDominio();

        if (!$cuenta->api_token)
            return abort(404);

        if ($cuenta->api_token != $api_token) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }

        $respuesta = new stdClass();
        if ($tramite_id) {
            $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

            if (!$tramite)
                return abort(404);

            if ($tramite->Proceso->Cuenta != $cuenta) {
                echo 'Usuario no tiene permisos para ejecutar esta etapa.';
                exit;
            }

            $respuesta->tramite = $tramite->toPublicArray();
        } else {
            $offset = $request->input('pageToken') ? 1 * base64_decode(urldecode($request->input('pageToken'))) : null;
            $limit = ($request->input('maxResults') && $request->input('maxResults') <= 50) ? 1 * $request->input('maxResults') : 10;

            $query = Doctrine_Query::create()
                ->from('Tramite t, t.Proceso.Cuenta c')
                ->where('c.id = ?', array($cuenta->id))
                ->andWhere('t.deleted_at is NULL')
                ->orderBy('id desc');
            if ($offset)
                $query->andWhere('id < ?', $offset);

            $ntramites_restantes = $query->count() - $limit;

            $query->limit($limit);
            $tramites = $query->execute();

            $nextPageToken = null;
            if ($ntramites_restantes > 0)
                $nextPageToken = urlencode(base64_encode($tramites[count($tramites) - 1]->id));

            $respuesta->tramites = new stdClass();
            $respuesta->tramites->titulo = 'Listado de Trámites';
            $respuesta->tramites->tipo = '#tramitesFeed';
            $respuesta->tramites->nextPageToken = $nextPageToken;
            $respuesta->tramites->items = null;
            foreach ($tramites as $t)
                $respuesta->tramites->items[] = $t->toPublicArray();
        }

        $coincidence = false;

        if ($api_dato_key && !empty($respuesta->tramite['datos'])) {
            // searching for key
            foreach ($respuesta->tramite['datos'] as $dato) {
                // extract the value of key
                reset($dato);
                $first_key = key($dato);

                // verify if key really exist
                if ($first_key == $api_dato_key) {
                    $coincidence = true;
                    $respuesta = $dato;
                }
            }
        }

        if ($api_dato_key && !$coincidence) {
            return response()->json('variable not found!', 404);
        }

        return response()->json($respuesta);
    }

    public function procesos(Request $request, $proceso_id = null, $recurso = null)
    {
        $api_token = $request->input('token');

        $cuenta = Cuenta::cuentaSegunDominio();

        if (!$cuenta->api_token)
            return abort(404);

        if ($cuenta->api_token != $api_token) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }

        if ($proceso_id) {
            $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

            if (!$proceso)
                return abort(404);

            if ($proceso->Cuenta != $cuenta)
                show_error('No tiene permisos para acceder a este recurso.', 401);

            if ($recurso == 'tramites') {
                $offset = $request->input('pageToken') ? 1 * base64_decode(urldecode($request->input('pageToken'))) : null;
                $limit = ($request->input('maxResults') && $request->input('maxResults') <= 50) ? 1 * $request->input('maxResults') : 10;

                $query = Doctrine_Query::create()
                    ->from('Tramite t, t.Proceso p')
                    ->where('p.activo=1 AND p.id = ?', array($proceso->id))
                    ->andWhere('t.deleted_at is NULL')
                    ->orderBy('id desc');
                if ($offset)
                    $query->andWhere('id < ?', $offset);

                $ntramites_restantes = $query->count() - $limit;

                $query->limit($limit);
                $tramites = $query->execute();

                $nextPageToken = null;
                if ($ntramites_restantes > 0)
                    $nextPageToken = urlencode(base64_encode($tramites[count($tramites) - 1]->id));

                $respuesta = new stdClass();
                $respuesta->tramites = new stdClass();
                $respuesta->tramites->titulo = 'Listado de Trámites';
                $respuesta->tramites->tipo = '#tramitesFeed';
                $respuesta->tramites->nextPageToken = $nextPageToken;
                $respuesta->tramites->items = null;
                foreach ($tramites as $t)
                    $respuesta->tramites->items[] = $t->toPublicArray();
            } else {

                $respuesta = new stdClass();
                $respuesta->proceso = $proceso->toPublicArray();
            }
        } else {

            $procesos = Doctrine::getTable('Proceso')->findByCuentaId($cuenta->id);

            $respuesta = new stdClass();
            $respuesta->procesos = new stdClass();
            $respuesta->procesos->titulo = 'Listado de Procesos';
            $respuesta->procesos->tipo = '#procesosFeed';
            $respuesta->procesos->items = null;
            foreach ($procesos as $t) {
                $respuesta->procesos->items[] = $t->toPublicArray();
            }
        }

        header('Content-type: application/json');
        echo json_indent(json_encode($respuesta));
    }

    public function notificar(Request $request, $tramite_id = null)
    {
        if (!is_numeric($tramite_id)) {
            return response()->json('ID inválido.');
        }

        $t = Doctrine::getTable('Tramite')->find($tramite_id);
        $etapa_id = $t->getUltimaEtapa()->id;
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if(!$etapa->Tarea->externa)
            return response()->json(['status' => 'La operación no es permitida',], 403);
        $pendientes = Doctrine_Core::getTable('Acontecimiento')->findByEtapaIdAndEstado($etapa_id, 1)->count();
        if ($pendientes > 0) {

            $json = json_decode($request->getContent(), true);
            if (count($json) > 0) {
                foreach ($json as $key => $value) {
                    $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa_id);
                    if (!$dato)
                        $dato = new DatoSeguimiento();
                    $key = str_replace("-", "_", $key);
                    $key = str_replace(" ", "_", $key);
                    $dato->nombre = $key;
                    $dato->valor = $value;
                    $dato->etapa_id = $etapa_id;
                    $dato->save();
                }
            }

            // Ejecutar eventos antes de la tarea
            /*
            $eventos=Doctrine_Query::create()->from('Evento e')
                    ->where('e.tarea_id = ? AND e.instante = ? AND e.paso_id IS NULL',array($e->Tarea->id,'antes'))
                    ->execute();
            foreach ($eventos as $e) {
                    $r = new Regla($e->regla);
                    if ($r->evaluar($this->id))
                        $e->Accion->ejecutar($etapa);
            }
            */

            $acontecimientos = Doctrine_Query::create()
                ->from('Acontecimiento a')
                ->where('a.etapa_id = ? AND a.estado = ?', array($etapa_id, 1))
                ->orderBy('a.id asc')
                ->execute();


            foreach ($acontecimientos as $clave => $a) {

                $evento = Doctrine::getTable('EventoExterno')->find($a->EventoExterno->id);
                $regla = new Regla($evento->regla);
                $tarea_id = $a->Etapa->Tarea->id;

                //Ejecutar eventos antes del evento externo
                $eventos = Doctrine_Query::create()->from('Evento e')
                    ->where('e.tarea_id = ? AND e.instante = ? AND e.evento_externo_id = ?', array($a->Etapa->Tarea->id, 'antes', $evento->id))
                    ->orderBy('e.id asc')
                    ->execute();
                foreach ($eventos as $e) {
                    $r = new Regla($e->regla);
                    if ($r->evaluar($etapa_id)) {
                        $e->Accion->ejecutar($etapa);
                    }
                }

                if($regla->evaluar($a->Etapa->id) && !is_null($evento->metodo)){
                    $regla = new Regla($evento->mensaje);
                    $msg = $regla->getExpresionParaOutput($a->Etapa->id);
                    $regla = new Regla($evento->url);
                    $url = $regla->getExpresionParaOutput($a->Etapa->id);
                    $regla = new Regla($evento->opciones);
                    $opciones = $regla->getExpresionParaOutput($a->Etapa->id);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    $metodos = array('POST', 'PUT');
                    if (in_array($evento->metodo, $metodos)) {
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $evento->metodo);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
                    }
                    $opciones_httpheader = array('cache-control: no-cache', 'Content-Type: application/json');
                    if (!is_null($opciones)) {
                        array_push($opciones_httpheader, $opciones);
                    }
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $opciones_httpheader);
                    $response = curl_exec($ch);
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $err = curl_error($ch);
                    curl_close($ch);

                    if (($httpcode == 200 or $httpcode == 201) && isJSON($response)) {
                        $js = json_decode($response);
                        foreach ($js as $key => $value) {
                            $key = str_replace("-", "_", $key);
                            $key = str_replace(" ", "_", $key);
                            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $a->Etapa->id);
                            if (!$dato)
                                $dato = new DatoSeguimiento();
                            $dato->nombre = $key;
                            $dato->valor = $value;
                            $dato->etapa_id = $a->Etapa->id;
                            $dato->save();
                        }
                    }
                    $a->estado = 0;
                    $a->save();

                    //Ejecutar eventos despues del evento externo
                    $eventos = Doctrine_Query::create()->from('Evento e')
                        ->where('e.tarea_id = ? AND e.instante = ? AND e.evento_externo_id = ?', array($tarea_id, 'despues', $evento->id))
                        ->orderBy('e.id asc')
                        ->execute();

                    foreach ($eventos as $e) {
                        $r = new Regla($e->regla);
                        if ($r->evaluar($etapa_id))
                            $e->Accion->ejecutar($etapa);
                    }
                }elseif($regla->evaluar($a->Etapa->id) && is_null($evento->metodo)){
                    $a->estado = 0;
                    $a->save();

                    //Ejecutar eventos despues del evento externo
                    $eventos = Doctrine_Query::create()->from('Evento e')
                        ->where('e.tarea_id = ? AND e.instante = ? AND e.evento_externo_id = ?', array($tarea_id, 'despues', $evento->id))
                        ->orderBy('e.id asc')
                        ->execute();

                    foreach ($eventos as $e) {
                        $r = new Regla($e->regla);
                        if ($r->evaluar($etapa_id))
                            $e->Accion->ejecutar($etapa);
                    }
                }
            }

            // Ejecutar eventos despues de tareas
            /*
            $eventos=Doctrine_Query::create()
                    ->from('Evento e')
                    ->where('e.tarea_id = ? AND e.instante = ? AND e.paso_id IS NULL',array($tarea_id,'despues'))
                    ->orderBy('e.id asc')
                    ->execute();
            //echo $eventos->getSqlQuery();
            //exit;
            foreach ($eventos as $e) {
                    $r = new Regla($e->regla);
                    if ($r->evaluar($etapa_id))
                        $e->Accion->ejecutar($etapa);
            }
            */

        }

        $pendientes = Doctrine_Core::getTable('Acontecimiento')->findByEtapaIdAndEstado($etapa_id, 1)->count();
        if ($pendientes == 0) {
            $tp = $etapa->getTareasProximas();
            if ($tp->estado == 'completado') {
                $ejecutar_eventos = FALSE;
                $t->cerrar($ejecutar_eventos);
            } else {
                $etapa->avanzar();
            }
        }


    }

    /*
        Generación de trámites de forma externa
    */
    public function ejecutar($proceso_id)
    {
        if ($proceso_id) {
            $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
            if (!$proceso)
                return abort(404);

            //inicio de sesión con usuario no registrado
            $usuario = new \Usuario();
            $usuario->usuario = random_string('unique');
            $usuario->password = Hash::make(random_string('alnum', 32));
            $usuario->registrado = 0;
            $usuario->save();

            UsuarioSesion::login($usuario->usuario, $usuario->password);
            $CI = &get_instance();
            $CI->session->set_userdata('usuario_id', $usuario->id);
            if (!$proceso->canUsuarioIniciarlo(UsuarioSesion::usuario()->id)) {
                echo 'Usuario no puede iniciar este proceso';
                exit;
            }

            //inicio del trámite
            $tramite = new Tramite();
            $tramite->iniciar($proceso->id);
            $etapa = Doctrine::getTable('Etapa')->find($tramite->getEtapasActuales()->get(0)->id);

            //almacenamiento de variables por POST
            $vars = $this->input->post(NULL, TRUE);
            foreach ($vars as $key => $value) {
                $key = str_replace("-", "_", $key);
                $key = str_replace(" ", "_", $key);
                $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa->id);
                if (!$dato)
                    $dato = new DatoSeguimiento();
                $dato->nombre = $key;
                $dato->valor = $value;
                if (!is_object($dato->valor) && !is_array($dato->valor)) {
                    if (preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)) {
                        $dato->valor = preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i", "$3-$2-$1", $dato->valor);
                    }
                }
                $dato->etapa_id = $etapa->id;
                $dato->save();
            }

            //pasos(formularios y campos)
            $ejecutar = true;
            $secuencia = 0;
            while ($ejecutar) {
                $paso = $etapa->getPasoEjecutable($secuencia);
                if (!$paso)
                    break;

                //ejecución de eventos antes de iniciar el paso
                $respuesta = $etapa->iniciarPaso($paso);
                //campos del paso(formulario)
                foreach ($paso->Formulario->Campos as $c) {
                    $c->displayConDatoSeguimiento($etapa->id, $paso->modo);
                }
                //ejecución de eventos después de finalizar el paso
                $etapa->finalizarPaso($paso);
                $secuencia++;
            }
            $etapa->avanzar();

            //resultado del trámite es entregar todas las variables generadas en json
            $datos = Doctrine::getTable('DatoSeguimiento')->findByEtapaId($etapa->id);
            foreach ($datos as $dato) {
                $data[$dato->nombre] = $dato->valor;
            }
            header('Content-type: application/json');
            echo json_indent(json_encode($data));
        } else {
            return abort(404);
        }
    }

    public function testjson($evento)
    {
        $this->_auth();
        $evento = Doctrine_Query::create()
            ->from('EventoExterno e')
            ->where('id = ?', array($evento))
            ->fetchOne();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $evento->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $mensaje_json = json_decode($mensaje, true);
        $mensaje_json = json_encode($mensaje_json, JSON_UNESCAPED_SLASHES);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $evento->mensaje);
        $opciones_httpheader = array("cache-control: no-cache", "Content-Type: application/json");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $opciones_httpheader);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo 'code--' . $httpcode . "<br>";
        $err = curl_error($ch);
        echo 'error--' . $err . "<br>";
        curl_close($ch);
        if (($httpcode == 200 or $httpcode == 201) && isJSON($response)) {
            $json = json_decode($response);
            print_r($json);
        }
    }

    public function completados(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $respuesta = new stdClass();
        $query = Doctrine_Query::create()
            ->from('Proceso p, p.Tramites t')
            ->select('p.nombre, COUNT(t.id) as ntramites')
            ->where('t.pendiente=0');
        if ($desde)
            $query = $query->andWhere('created_at >= ' . "'" . date('Y-m-d', strtotime($desde)) . "'");
        if ($hasta)
            $query = $query->andWhere('ended_at <= ' . "'" . date('Y-m-d', strtotime($hasta)) . "'");

        $tramites = $query->groupBy('p.id')->execute();
        foreach ($tramites as $p) {
            $respuesta->tramites[] = (object)array('cuenta' => $p->Cuenta->nombre, 'proceso_id' => $p->id, 'proceso' => $p->nombre, 'completados' => $p->ntramites);
        }
        header('Content-type: application/json');
        echo json_indent(json_encode($respuesta));
    }

    public function estados(Request $request, $tramite_id = null)
    {
        if (!is_numeric($tramite_id))
            return response()->json(['status' => 'ERROR', 'message' => 'ID inválido'], 400);

        $t = Doctrine::getTable('Tramite')->find($tramite_id);
        if(!$t)
            return response()->json(['status' => 'ERROR', 'message' => 'Trámite inválido'], 404);

        $etapa_id = $t->getUltimaEtapa()->id;
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $pendientes = Doctrine_Core::getTable('Acontecimiento')->findByEtapaIdAndEstado($etapa_id, 1)->count();
        if ($pendientes > 0) {

            $json = json_decode($request->getContent(), true);

            $historial_estados = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("historial_estados",$etapa_id);
            if(!$historial_estados)
                $array_estados = array();
            else
                $array_estados = $historial_estados->valor;

            //Token
            if(count($json)>0){
                $existe_token = false;
                foreach($json as $key => $value){
                    if($key=='token'){
                        $api_token = $value;
                        $cuenta = Cuenta::cuentaSegunDominio();

                        if (!$cuenta->api_token)
                            return response()->json(['status' => 'ERROR', 'message' => 'Usuario no tiene permisos para ejecutar esta etapa.'], 403);

                        if ($cuenta->api_token != $api_token) {
                            return response()->json(['status' => 'ERROR', 'message' => 'Usuario no tiene permisos para ejecutar esta etapa.'], 403);
                        }
                        $existe_token = true;
                    }
                }
                if(!$existe_token)
                    return response()->json(['status' => 'ERROR', 'message' => 'Usuario no tiene permisos para ejecutar esta etapa.'], 403);
            }
            //Fin token

            if (count($json) > 0) {
                $associativeArray = array();
                foreach ($json as $key => $value) {
                    $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa_id);
                    if (!$dato)
                        $dato = new DatoSeguimiento();
                    $key = str_replace("-", "_", $key);
                    $key = str_replace(" ", "_", $key);
                    $dato->nombre = $key;
                    $dato->valor = $value;
                    $dato->etapa_id = $etapa_id;
                    $dato->save();

                    switch ($key) {
                        case 'status':
                            $associativeArray['status'] = $dato->valor;
                            break;
                        case 'description':
                            $associativeArray['description'] = $dato->valor;
                            break;
                        case 'message':
                            $associativeArray['message'] = $dato->valor;
                            break;
                        case 'service_application_id':
                            $associativeArray['service_application_id'] = $dato->valor;
                            break;
                        case 'stage':
                            $associativeArray['stage'] = $dato->valor;
                            break;
                    }
                }
                $associativeArray['created_at'] = Carbon::now('America/Santiago')->format('d-m-Y H:i:s');
                array_push($array_estados, $associativeArray);
                $historial_estados = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("historial_estados",$etapa_id);
                if(!$historial_estados)
                    $historial_estados = new DatoSeguimiento();

                $historial_estados->nombre = 'historial_estados';
                $historial_estados->valor = $array_estados;
                $historial_estados->etapa_id = $etapa_id;
                $historial_estados->save();
            }

            // Ejecutar eventos antes de la tarea
            /*
            $eventos=Doctrine_Query::create()->from('Evento e')
                    ->where('e.tarea_id = ? AND e.instante = ? AND e.paso_id IS NULL',array($e->Tarea->id,'antes'))
                    ->execute();
            foreach ($eventos as $e) {
                    $r = new Regla($e->regla);
                    if ($r->evaluar($this->id))
                        $e->Accion->ejecutar($etapa);
            }
            */

            $acontecimientos = Doctrine_Query::create()
                ->from('Acontecimiento a')
                ->where('a.etapa_id = ? AND a.estado = ?', array($etapa_id, 1))
                ->orderBy('a.id asc')
                ->execute();


            foreach ($acontecimientos as $clave => $a) {

                $evento = Doctrine::getTable('EventoExterno')->find($a->EventoExterno->id);
                $regla = new Regla($evento->regla);
                $tarea_id = $a->Etapa->Tarea->id;

                //Ejecutar eventos antes del evento externo
                $eventos = Doctrine_Query::create()->from('Evento e')
                    ->where('e.tarea_id = ? AND e.instante = ? AND e.evento_externo_id = ?', array($a->Etapa->Tarea->id, 'antes', $evento->id))
                    ->orderBy('e.id asc')
                    ->execute();
                foreach ($eventos as $e) {
                    $r = new Regla($e->regla);
                    if ($r->evaluar($etapa_id)) {
                        $e->Accion->ejecutar($etapa);
                    }
                }

                if ($regla->evaluar($a->Etapa->id)) {

                    $regla = new Regla($evento->mensaje);
                    $msg = $regla->getExpresionParaOutput($a->Etapa->id);
                    $regla = new Regla($evento->url);
                    $url = $regla->getExpresionParaOutput($a->Etapa->id);
                    $regla = new Regla($evento->opciones);
                    $opciones = $regla->getExpresionParaOutput($a->Etapa->id);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    $metodos = array('POST', 'PUT');
                    if (in_array($evento->metodo, $metodos)) {
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $evento->metodo);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
                    }
                    $opciones_httpheader = array('cache-control: no-cache', 'Content-Type: application/json');
                    if (!is_null($opciones)) {
                        array_push($opciones_httpheader, $opciones);
                    }
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $opciones_httpheader);
                    $response = curl_exec($ch);
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $err = curl_error($ch);
                    curl_close($ch);

                    if (($httpcode == 200 or $httpcode == 201) && isJSON($response)) {
                        $js = json_decode($response);
                        foreach ($js as $key => $value) {
                            $key = str_replace("-", "_", $key);
                            $key = str_replace(" ", "_", $key);
                            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $a->Etapa->id);
                            if (!$dato)
                                $dato = new DatoSeguimiento();
                            $dato->nombre = $key;
                            $dato->valor = $value;
                            $dato->etapa_id = $a->Etapa->id;
                            $dato->save();
                        }
                    }
                    $a->estado = 0;
                    $a->save();

                    //Ejecutar eventos despues del evento externo
                    $eventos = Doctrine_Query::create()->from('Evento e')
                        ->where('e.tarea_id = ? AND e.instante = ? AND e.evento_externo_id = ?', array($tarea_id, 'despues', $evento->id))
                        ->orderBy('e.id asc')
                        ->execute();

                    foreach ($eventos as $e) {
                        $r = new Regla($e->regla);
                        if ($r->evaluar($etapa_id))
                            $e->Accion->ejecutar($etapa);
                    }
                }
            }

            // Ejecutar eventos despues de tareas
            /*
            $eventos=Doctrine_Query::create()
                    ->from('Evento e')
                    ->where('e.tarea_id = ? AND e.instante = ? AND e.paso_id IS NULL',array($tarea_id,'despues'))
                    ->orderBy('e.id asc')
                    ->execute();
            //echo $eventos->getSqlQuery();
            //exit;
            foreach ($eventos as $e) {
                    $r = new Regla($e->regla);
                    if ($r->evaluar($etapa_id))
                        $e->Accion->ejecutar($etapa);
            }
            */

        }

        $pendientes = Doctrine_Core::getTable('Acontecimiento')->findByEtapaIdAndEstado($etapa_id,1)->count();
        if ($pendientes == 0) {
            $tp = $etapa->getTareasProximas();
            if ($tp->estado == 'completado') {
                $ejecutar_eventos = FALSE;
                $t->cerrar($ejecutar_eventos);
            } else {
                $etapa->avanzar();
            }
        }

        if($historial_estados)
            return response()->json(['status' => 'OK',], 200);
        else
            return response()->json(['status' => 'ERROR', 'message' => 'Error al generar historial de estados'], 500);

    }

    public function progress(Request $request, $tramite_id = null)
    {
        if (!is_numeric($tramite_id))
            return response()->json(['status' => 'ERROR', 'message' => 'ID inválido'], 400);

        $t = Doctrine::getTable('Tramite')->find($tramite_id);
        if(!$t)
            return response()->json(['status' => 'ERROR', 'message' => 'Trámite inválido'], 404);

        $etapa_id = $t->getUltimaEtapa()->id;
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        
        $json = json_decode($request->getContent(), true);
        $historial_estados = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("historial_estados",$etapa_id);
        if(!$historial_estados)
            $array_estados = array();
        else
            $array_estados = $historial_estados->valor;

        //Token
        if(count($json)>0){
            $existe_token = false;
            foreach($json as $key => $value){
                if($key=='token'){
                    $api_token = $value;
                    $cuenta = Cuenta::cuentaSegunDominio();

                    if (!$cuenta->api_token)
                        return response()->json(['status' => 'ERROR', 'message' => 'Usuario no tiene permisos para ejecutar esta etapa.'], 403);

                    if ($cuenta->api_token != $api_token) {
                        return response()->json(['status' => 'ERROR', 'message' => 'Usuario no tiene permisos para ejecutar esta etapa.'], 403);
                    }
                    $existe_token = true;
                }
            }
            if(!$existe_token)
                return response()->json(['status' => 'ERROR', 'message' => 'Usuario no tiene permisos para ejecutar esta etapa.'], 403);
        }
        //Fin token

        if (count($json) > 0) {
            $associativeArray = array();
            foreach ($json as $key => $value) {
                $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa_id);
                if (!$dato)
                    $dato = new DatoSeguimiento();
                $key = str_replace("-", "_", $key);
                $key = str_replace(" ", "_", $key);
                $dato->nombre = $key;
                $dato->valor = $value;
                $dato->etapa_id = $etapa_id;
                $dato->save();

                switch ($key) {
                    case 'status':
                        $associativeArray['status'] = $dato->valor;
                        break;
                    case 'description':
                        $associativeArray['description'] = $dato->valor;
                        break;
                    case 'message':
                        $associativeArray['message'] = $dato->valor;
                        break;
                    case 'service_application_id':
                        $associativeArray['service_application_id'] = $dato->valor;
                        break;
                    case 'stage':
                        $associativeArray['stage'] = $dato->valor;
                        break;
                }
            }
            $associativeArray['created_at'] = Carbon::now('America/Santiago')->format('d-m-Y H:i:s');
            array_push($array_estados, $associativeArray);
            $historial_estados = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("historial_estados",$etapa_id);
            if(!$historial_estados)
                $historial_estados = new DatoSeguimiento();

            $historial_estados->nombre = 'historial_estados';
            $historial_estados->valor = $array_estados;
            $historial_estados->etapa_id = $etapa_id;
            $historial_estados->save();
        }

        if($historial_estados)
            return response()->json(['status' => 'OK',], 200);
        else
            return response()->json(['status' => 'ERROR', 'message' => 'Error al generar historial de estados'], 500);
    }
}
