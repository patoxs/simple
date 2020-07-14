<?php

namespace App\Http\Controllers\Backend;

use App\Models\DatoSeguimiento;
use App\Models\Etapa;
use App\Models\Tramite;
use App\Models\Proceso;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Manager;
use Doctrine_Query;
use Doctrine_Core;

class TracingController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function index()
    {
        $data ['procesos'] = Doctrine_Query::create()->from('Proceso p, p.Cuenta c')
            ->where('p.activo=1 AND c.id = ?', Auth::user()->cuenta_id)
            ->orderBy('p.nombre asc')->execute();

        return view('backend.tracing.index', $data);
    }

    /**
     * @param Request $request
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function indexProcess(Request $request, $proceso_id) {

        Log::info("Detalle de seguimiento para proceso id: " . $proceso_id);
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        if (Auth::user()->cuenta_id != $proceso->cuenta_id) {
            abort(403);
        }

        if (!is_null(Auth::user()->procesos) && !in_array($proceso_id, explode(',', Auth::user()->procesos))) {
            abort(403);
        }

        // query a evaluar
        $search_option = $request->input('search_option');
        $query_tramite_id = $request->input('query_tramite_id');
        $query_asignado = $request->input('query_asignado');
        $query_ref = $request->input('query_ref');
        $query_name = $request->input('query_name');

        $order = $request->input('order', 'updated_at');
        $direction = $request->input('direction', 'desc');
        $created_at_desde = $request->input('created_at_desde');
        $created_at_hasta = $request->input('created_at_hasta');
        $updated_at_desde = $request->input('updated_at_desde');
        $updated_at_hasta = $request->input('updated_at_hasta');
        $pendiente = $request->has('pendiente') &&
            is_numeric($request->input('pendiente')) ? $request->input('pendiente') : -1;

        $page = $request->input('page', 1); // Get the ?page=1 from the url
        $per_page = 50;
        $busqueda_avanzada = $request->input('busqueda_avanzada');
        $offset = ($page * $per_page) - $per_page;

        Log::info("Creando query");

        // obtiene los tramites cuyo proceso este activo, y ademas de tener por lo menos una etapa creada
        $preg = Tramite::where('p.activo', true)
            ->select(
                'tramite.id as id',
                'tramite.pendiente as estado',
                'e.id as etapa_id',
                'e.usuario_id as etapa_usuario_id',
                'tramite.created_at',
                'tramite.updated_at'
            )
            ->leftjoin('proceso as p', 'tramite.proceso_id', '=', 'p.id')
            ->leftjoin('etapa as e', 'tramite.id', '=', 'e.tramite_id')
            ->leftjoin('dato_seguimiento as ds', 'e.id', '=', 'ds.etapa_id')
            ->where('p.id', $proceso_id)
            ->groupBy('tramite.id')
            ->havingRaw('count(ds.id) > 0 or count(e.id) > 1')
            ->orderBy('tramite.'.$order, $direction);

        if ($created_at_desde) {
            $preg->where('tramite.created_at', '>=', date('Y-m-d', strtotime($created_at_desde)));
        }
        if ($created_at_hasta) {
            $preg->where('tramite.created_at', '<=', date('Y-m-d', strtotime($created_at_hasta)));
        }
        if ($updated_at_desde) {
            $preg->where('tramite.updated_at', '>=', date('Y-m-d', strtotime($updated_at_desde)));
        }
        if ($updated_at_hasta) {
            $preg->where('tramite.updated_at', '<=', date('Y-m-d', strtotime($updated_at_desde)));
        }
        if ($pendiente != -1) {
            $preg->where('tramite.pendiente', $pendiente );
        }

        if ($search_option) {
            switch ($search_option) {
                case 'option1':
                    // fltro por id de tramite
                    $preg->where('tramite.id', $query_tramite_id);
                    break;
                case 'option2':
                    // filtro por aignado rut, email, nombre usuario, etc (pending)
                    break;
                case 'option3':
                    // filtro por tramite_ref
                    $arrayDatos = [];
                    $datosNombre = DatoSeguimiento::where('nombre', 'tramite_ref')
                        ->select('dato_seguimiento.etapa_id')
                        ->where('valor', 'like', '%'.DatoSeguimiento::addFormatNames(strtolower($query_ref)).'%')
                        ->get()
                        ->toArray();

                    foreach ($datosNombre as $dato) {
                        $arrayDatos[] = $dato['etapa_id'];
                    }

                    $preg->whereIn('ds.etapa_id', $arrayDatos);
                    break;
                case 'option4':
                    // filtro por tramite_descripcion ($query_name)
                    $arrayDatos = [];
                    $datosNombre = DatoSeguimiento::where('nombre', 'tramite_descripcion')
                        ->select('dato_seguimiento.etapa_id')
                        ->where('valor', 'like', '%'.urldecode(DatoSeguimiento::addFormatNames(strtolower($query_name))).'%')
                        ->get()
                        ->toArray();

                    foreach ($datosNombre as $dato) {
                        $arrayDatos[] = $dato['etapa_id'];
                    }

                    $preg->whereIn('ds.etapa_id', $arrayDatos);
                    break;
            }
        }

        $tramitesTotal = $preg->get()->count();
        $preg->limit($per_page)->offset($offset);
        $tramitesResult = $preg->get();

        $tramites = [];
        foreach ($tramitesResult as $tr) {

            $item = [
                'id' => $tr->id,
                'asignado' => 'Ninguno',
                'ref' => null,
                'nombre' => null,
                'estado' => $tr->estado,
                'etapas' => [],
                'created_at' => Carbon::parse($tr->created_at)->format('d-m-Y H:i:s'),
                'updated_at' => Carbon::parse($tr->updated_at)->format('d-m-Y H:i:s')
            ];

            // obtiene el valor para el usuario asignado
            $ultimaEtapa = Etapa::where('tramite_id', $tr->id)
                ->leftjoin('usuario as u', 'etapa.usuario_id', '=', 'u.id')
                ->orderBy('etapa.id', 'DESC')
                ->first();

            $item['asignado'] = $ultimaEtapa->usuario;

            if ($ultimaEtapa->open_id) {
                $item['asignado']= $ultimaEtapa->rut;
            }

            if (!$ultimaEtapa->registrado) {
                $item['asignado'] = 'No registrado';
            }

            // si cumple con la restriccion, se obtienen los valores para ref y nombre
            $item['ref'] = $item['nombre'] = 'N/A';

            $etapasTramite = Etapa::where('tramite_id', $tr->id)->get();
            $etapasTramiteIds = [];

            foreach ($etapasTramite as $etapaTramite) {
                $etapasTramiteIds[] = $etapaTramite->id;
            }

            $datosSeguimiento = DatoSeguimiento::whereIn('etapa_id', $etapasTramiteIds)->get();


            foreach ($datosSeguimiento as $datoSeg) {
                if ($datoSeg->nombre == 'tramite_ref') {
                    $item['ref'] = DatoSeguimiento::removeFormatNames($datoSeg->valor);
                }
                if ($datoSeg->nombre == 'tramite_descripcion') {
                    $item['nombre'] = DatoSeguimiento::removeFormatNames($datoSeg->valor);
                }
            }

            // obtiene todas las etapas actuales (pendientes) de c/tarea
            $tareasList = Etapa::where('pendiente', true)
                ->join('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                ->where('tramite_id', $tr->id);


            $tareas = $tareasList->get();

            foreach ($tareas as $tarea) {
                $item['etapas'][] = $tarea->nombre;
            }

            $tramites[] = $item;
        }

        $tramites = new LengthAwarePaginator(
            $tramites, // Only grab the items we need
            $tramitesTotal, // Total items
            $per_page, // Items per page
            $page, // Current page
            // We need this so we can keep all old query parameters from the url
            ['path' => $request->url(), 'query' => $request->query()]
        );


        $data = [
            'search_option' => $search_option,
            'query_tramite_id' => $query_tramite_id,
            'query_asignado' => $query_asignado,
            'query_ref' => $query_ref,
            'query_name' => $query_name,
            'order' => $order,
            'direction' => $direction,
            'created_at_desde' => $created_at_desde,
            'created_at_hasta' => $created_at_hasta,
            'updated_at_desde' => $updated_at_desde,
            'updated_at_hasta' => $updated_at_hasta,
            'pendiente' => $pendiente,
            'busqueda_avanzada' => $busqueda_avanzada,
            'proceso' => $proceso,
            'tramites' => $tramites,
            'title' => 'Seguimiento de ' . $proceso->nombre
        ];

        return view('backend.tracing.index_process', $data);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajaxIdProcedure()
    {
        $max = Doctrine_Query::create()->select('MAX(id) as max')->from("Tramite")->fetchOne();
        $data ['max'] = $max->max;

        return view('backend.tracing.ajaxUpdateIdProcedure', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Doctrine_Manager_Exception
     */
    public function ajaxUpdateIdProcedure(Request $request)
    {
        $max = Doctrine_Query::create()->select('MAX(id) as max')->from("Tramite")->fetchOne();
        $max = $max->max + 1;

        $this->validate($request, [
            'id' => 'required|numeric|min:' . $max
        ]);

        $id = $request->input('id');

        $stmt = Doctrine_Manager::getInstance()->connection();
        $sql = "ALTER TABLE tramite AUTO_INCREMENT = " . $id . ";";
        $stmt->execute($sql);


        return redirect()->route('backend.tracing.index');
    }

    /**
     * @param $tramite_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_auditar_eliminar_tramite($tramite_id)
    {

        $tramite = Doctrine::getTable("Tramite")->find($tramite_id);
        $data['tramite'] = $tramite;

        return view('backend.tracing.ajax_auditar_eliminar_tramite', $data);
    }

    /**
     * @param Request $request
     * @param $tramite_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function borrar_tramite(Request $request, $tramite_id)
    {

        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);

        $request->validate(['descripcion' => 'required']);

        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (!is_null(Auth::user()->procesos) && !in_array($tramite->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if (Auth::user()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }
        $fecha = new \DateTime ();
        $proceso = $tramite->Proceso;
        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Trámite';
        $registro_auditoria->motivo = $request->input('descripcion');
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;


        // Detalles
        $tramite_array['proceso'] = $proceso->toArray(false);

        $tramite_array['tramite'] = $tramite->toArray(false);
        unset($tramite_array['tramite']['proceso_id']);

        $registro_auditoria->detalles = json_encode($tramite_array);
        $registro_auditoria->save();

        $tramite = Tramite::find($tramite_id);
        if($tramite)
            $tramite->delete();

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/index_proceso/' . $proceso->id)
        ]);
    }

    /**
     * @param $proceso_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function reset_proc_cont($proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        $proceso->proc_cont = 0;
        $proceso->save();

        return redirect($_SERVER['HTTP_REFERER']);
    }


    /**
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_auditar_limpiar_proceso($proceso_id)
    {

        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $data['proceso'] = $proceso;

        return view('backend.tracing.ajax_auditar_limpiar_proceso', $data);

    }

    /**
     * @param Request $request
     * @param $proceso_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function borrar_proceso(Request $request, $proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);


        $request->validate(['descripcion' => 'required']);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if (!is_null(Auth::user()->procesos) && !in_array($proceso_id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para el seguimiento del tramite';
            exit;
        }

        if (Auth::user()->cuenta_id != $proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }
        $fecha = new \DateTime ();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Todos los Trámites';
        $registro_auditoria->motivo = $request->input('descripcion');
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;


        // Detalles
        $proceso_array['proceso'] = $proceso->toArray(false);

        foreach ($proceso->Tramites as $tramite) {
            $tramite_array = $tramite->toArray(false);
            unset($tramite_array['proceso_id']);
            $proceso_array['tramites'][] = $tramite_array;

        }


        $registro_auditoria->detalles = json_encode($proceso_array);
        $registro_auditoria->save();

        $proceso->Tramites->delete();

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/index_proceso/' . $proceso_id)
        ]);

    }

    /**
     * @param $tramite_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function ver($tramite_id)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (!is_null(Auth::user()->procesos) && !in_array($tramite->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para ver el tramite';
            exit;
        }

        if (Auth::user()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }

        $data ['tramite'] = $tramite;
        $data ['etapas'] = Doctrine_Query::create()->from('Etapa e, e.Tramite t')->where('t.id = ?', $tramite->id)->orderBy('id desc')->execute();

        $data ['title'] = 'Seguimiento - ' . $tramite->Proceso->nombre;

        return view('backend.tracing.view', $data);
    }

    /**
     * @param $tramite_id
     * @param $tarea_identificador
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function ajax_ver_etapas($tramite_id, $tarea_identificador)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (Auth::user()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }

        $etapas = Doctrine_Query::create()->from('Etapa e, e.Tarea tar, e.Tramite t')->where('t.id = ? AND tar.identificador = ?', array(
            $tramite_id,
            $tarea_identificador
        ))->execute();

        $data ['etapas'] = $etapas;

        return view('backend.tracing.ajax_ver_etapas', $data);
    }

    /**
     * @param $etapa_id
     * @param int $secuencia
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ver_etapa($etapa_id, $secuencia = 0)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $paso = $etapa->getPasoEjecutable($secuencia);

        if (!is_null(Auth::user()->procesos) && !in_array($etapa->Tramite->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para ver el tramite';
            exit;
        }

        if (Auth::user()->cuenta_id != $etapa->Tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit ();
        }

        $data ['etapa'] = $etapa;
        $data ['paso'] = $paso;
        $data ['secuencia'] = $secuencia;

        $data ['title'] = 'Seguimiento - ' . $etapa->Tarea->nombre;

        return view('backend.tracing.view_stage', $data);
    }

    /**
     * @param $etapa_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_auditar_retroceder_etapa($etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $data ['etapa'] = $etapa;

        return view('backend.tracing.ajax_auditar_retroceder_etapa', $data);
    }

    /**
     *
     * Vuelve a la/s etapa/s anterior/es
     * En caso de ser la última etapa(ya finalizada), vuelve a dejar el trámite en curso
     *
     * @param unknown $etapa_id
     */
    public function retroceder_etapa(Request $request, $etapa_id)
    {
        $request->validate(['descripcion' => 'required']);

        $fecha = new \DateTime ();

        $etapa = Doctrine::getTable("Etapa")->find($etapa_id);
        $tramite = Doctrine::getTable("Tramite")->find($etapa->tramite_id);
        if ($etapa->pendiente == 1) {
            // Tarea anterior de la actual, ordenada por las etapas
            $tareas_anteriores = Doctrine_Query::create()->select("c.tarea_id_origen as id, c.tipo, e.id as etapa_id, e.etapa_ancestro_split_id as origen_paralelo")->from("Conexion c, c.TareaOrigen to, to.Etapas e")->where("c.tarea_id_destino = ?", $etapa->tarea_id)->andWhere("e.tramite_id = ?", $tramite->id)->andWhere("e.id != ?", $etapa->id)->orderBy("e.id DESC")->fetchOne();

            if (count($tareas_anteriores) > 0) {
                // Eliminamos la etapa actual
                $id_etapa_actual = $etapa->id;
                $id_tarea_actual = $etapa->tarea_id;
                $etapa->delete();

                $tipo_conexion = $tareas_anteriores->tipo;

                // Si no es union, debe retroceder solo a la ultima etapa de la tarea anterior
                if ($tipo_conexion != 'union') {
                    $tareas_anteriores = array(
                        $tareas_anteriores
                    );
                } else {
                    $tareas_anteriores = Doctrine_Query::create()->select("c.tarea_id_origen as id, c.tipo, e.id as etapa_id")->from("Conexion c, c.TareaOrigen to, to.Etapas e")->where("c.tarea_id_destino = ?", $etapa->tarea_id)->andWhere("e.tramite_id = ?", $tramite->id)->andWhere("e.id != ?", $etapa->id)->andWhere("e.etapa_ancestro_split_id = ?", $tareas_anteriores->origen_paralelo)->orderBy("e.id DESC")->execute();
                }

                // Si es union va retroceder a todas las etapas de dicha union, sino tareas_anteriores tendra un solo elemento
                foreach ($tareas_anteriores as $tarea_anterior) {
                    if ($etapa_anterior = Doctrine::getTable("Etapa")->find($tarea_anterior->etapa_id)) {
                        // Auditoría de la etapa a la cual se regresa
                        $registro_auditoria = new \AuditoriaOperaciones ();
                        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
                        $registro_auditoria->motivo = $request->input('descripcion');
                        $registro_auditoria->operacion = 'Retroceso a Etapa';
                        $registro_auditoria->proceso = $etapa_anterior->Tramite->Proceso->nombre;
                        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

                        $usuario = Auth::user();
                        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';

                        /* Formatear detalles */
                        $etapa_array['proceso'] = $etapa_anterior->Tramite->Proceso->toArray(false);
                        $etapa_array ['tramite'] = $etapa_anterior->Tramite->toArray(false);

                        $etapa_array['etapa'] = $etapa_anterior->toArray(false);
                        unset ($etapa_array ['etapa']['tarea_id']);
                        unset ($etapa_array ['etapa']['tramite_id']);
                        unset ($etapa_array ['etapa']['usuario_id']);
                        unset ($etapa_array ['etapa']['etapa_ancestro_split_id']);

                        $etapa_array ['tarea'] = $etapa_anterior->Tarea->toArray(false);
                        $etapa_array ['usuario'] = $etapa_anterior->Usuario->toArray(false);
                        unset ($etapa_array ['usuario'] ['password']);
                        unset ($etapa_array['usuario']['salt']);

                        $etapa_array ['datos_seguimiento'] = Doctrine_Query::create()
                            ->from("DatoSeguimiento d")
                            ->where("d.etapa_id = ?", $etapa_anterior->id)
                            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                        $registro_auditoria->detalles = json_encode($etapa_array);
                        $registro_auditoria->save();
                    }

                    $etapas_otra_rama = array();
                    if ($tipo_conexion == 'paralelo' || $tipo_conexion == 'paralelo_evaluacion') {
                        // Select de otras ramas para evitar inconsistencias
                        $etapas_otra_rama = Doctrine_Query::create()->select("c.tarea_id_destino as id")->from("Conexion c, c.TareaDestino to, to.Etapas e")->where("c.tarea_id_origen = ?", $tarea_anterior->id)->andWhere("c.tarea_id_destino != ?", $id_tarea_actual)->andWhere("c.tarea_id_destino != c.tarea_id_origen")->andWhere("e.etapa_ancestro_split_id = ?", $tarea_anterior->etapa_id)->execute();
                    }
                    // Si es en paralelo, y hay etapas en otras ramas, no se pone en pendiente aun
                    if (count($etapas_otra_rama) == 0) {

                        Doctrine_Query::create()->update("Etapa")->set(array(
                            'pendiente' => 1,
                            'ended_at' => null
                        ))->where("id = ?", $tarea_anterior->etapa_id)->execute();


                    }
                }
            }
        } else {

            // Auditoría
            $registro_auditoria = new \AuditoriaOperaciones ();
            $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
            $registro_auditoria->motivo = $request->input('descripcion');
            $registro_auditoria->operacion = 'Retroceso a Etapa';
            $registro_auditoria->proceso = $etapa->Tramite->Proceso->nombre;
            $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

            $usuario = Auth::user();
            $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';

            /* Formatear detalles */

            $etapa_array ['proceso'] = $etapa->Tramite->Proceso->toArray(false);
            $etapa_array ['tramite'] = $etapa->Tramite->toArray(false);

            $etapa_array['etapa'] = $etapa->toArray(false);
            unset ($etapa_array ['etapa']['tarea_id']);
            unset ($etapa_array ['etapa']['tramite_id']);
            unset ($etapa_array ['etapa']['usuario_id']);
            unset ($etapa_array ['etapa']['etapa_ancestro_split_id']);


            $etapa_array ['tarea'] = $etapa->Tarea->toArray(false);

            $etapa_array ['usuario'] = $etapa->Usuario->toArray(false);
            unset ($etapa_array ['usuario'] ['password']);

            $etapa_array ['datos_seguimiento'] = Doctrine_Query::create()->from("DatoSeguimiento d")->where("d.etapa_id = ?", $etapa->id)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            $registro_auditoria->detalles = json_encode($etapa_array);
            $registro_auditoria->save();

            $etapa->pendiente = 1;
            $etapa->ended_at = null;
            $etapa->save();
            if ($tramite->pendiente == 0) {
                $tramite->pendiente = 1;
                $tramite->ended_at = null;
                $tramite->save();
            }
        }

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/ver/' . $tramite->id)
        ]);
    }

    /**
     * @param Request $request
     * @param $etapa_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reasignar_form(Request $request, $etapa_id)
    {
        $request->validate(['usuario_id' => 'required']);

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $etapa->asignar($request->input('usuario_id'));

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/ver_etapa/' . $etapa->id)
        ]);
    }

    /**
     * @param $data
     * @return string
     */
    public function varDump($data)
    {
        ob_start();
        //var_dump($data);
        print_r($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }

    public function ajax_editar_vencimiento($etapa_id){
        $etapa = Doctrine::getTable("Etapa")->find($etapa_id);
        $data['etapa'] = $etapa;
        return view('backend.tracing.ajax_editar_vencimiento', $data);
    }

    public function editar_vencimiento_form(Request $request, $etapa_id) {
        $request->validate(['descripcion' => 'required', 'vencimiento' => 'required']);
        
        $fecha = new \DateTime ();
        $etapa = Doctrine::getTable("Etapa")->find($etapa_id);
        $tramite = Doctrine::getTable("Tramite")->find($etapa->tramite_id);
        // Auditoría
        $registro_auditoria = new \AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->motivo = $request->input('descripcion');
        $registro_auditoria->operacion = 'Cambio de Fecha de Vencimiento';
        $registro_auditoria->proceso = $etapa->Tramite->Proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';

        /* Formatear detalles */

        $etapa_array ['proceso'] = $etapa->Tramite->Proceso->toArray(false);
        $etapa_array ['tramite'] = $etapa->Tramite->toArray(false);

        $etapa_array['etapa'] = $etapa->toArray(false);
        unset ($etapa_array ['etapa']['tarea_id']);
        unset ($etapa_array ['etapa']['tramite_id']);
        unset ($etapa_array ['etapa']['usuario_id']);
        unset ($etapa_array ['etapa']['etapa_ancestro_split_id']);


        $etapa_array ['tarea'] = $etapa->Tarea->toArray(false);

        $etapa_array ['usuario'] = $etapa->Usuario->toArray(false);
        unset ($etapa_array ['usuario'] ['password']);

        $etapa_array ['datos_seguimiento'] = Doctrine_Query::create()->from("DatoSeguimiento d")->where("d.etapa_id = ?", $etapa->id)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $registro_auditoria->detalles = json_encode($etapa_array);
        $registro_auditoria->save();

        $etapa->vencimiento_at = date ( 'Y-m-d', strtotime ( $request->input('vencimiento') ) );
        $etapa->save ();

        return response()->json([
            'validacion' => true,
            'redirect' => url('backend/seguimiento/index_proceso/' . $tramite->Proceso->id)
        ]);
    }
}
