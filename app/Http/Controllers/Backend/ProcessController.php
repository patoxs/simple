<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Doctrine;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Proceso;
use Carbon\Carbon;
use Doctrine_Query;
use Dompdf\Exception;
use App\Models\HistorialModificacion;
use App\Events\HistorialModificacionEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Session;

use App\Models\HistorialModificacion as Historial;



class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $process_search = $request->input('process_search', null);
        $cuenta_id = Auth::user()->cuenta_id;
        $procesos = Proceso::where('cuenta_id', $cuenta_id)->where('activo', true);

        if ($process_search) {
            $procesos->where('nombre', 'like', "%$process_search%");
        }

        $procesos = $procesos->orderBy('nombre')->paginate(20);

        $procesos_eliminados =  Proceso::where('cuenta_id', $cuenta_id)
            ->where('activo', false)
            ->orderBy('nombre')
            ->get();

        return view('backend.process.index', [
            'title' => 'Listado de Procesos',
            'procesos' => $procesos,
            'procesos_eliminados' => $procesos_eliminados,
            'process_search' => $process_search
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $proceso = new \Proceso();
        $proceso->nombre = 'Proceso';
        $proceso->cuenta_id = Auth::user()->cuenta_id;
        $proceso->usuario_id = Auth::user()->id;
        $fecha = Carbon::now('America/Santiago')->format('Y-m-d H:i:s');
        $proceso->created_at = $fecha;
        $proceso->updated_at = $fecha;
        $proceso->activo = 1;
        $proceso->save();

        return redirect()->route('backend.procesos.edit', [$proceso->id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * @param Request $request
     * @param $proceso_id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $proceso_id)
    {
        Log::info('eliminar ($proceso_id [' . $proceso_id . '])');

        $request->validate(['descripcion' => 'required']);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este proceso';
            exit;
        }

        $fecha = new \DateTime();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Proceso';
        $registro_auditoria->motivo = $request->input('descripcion');
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

        // Detalles
        $proceso_array['proceso'] = $proceso->toArray(false);

        $registro_auditoria->detalles = json_encode($proceso_array);
        $registro_auditoria->save();

        $proceso->delete_logico($proceso_id);
        $request->session()->flash('success', 'Proceso eliminado con éxito.');

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.procesos.index')
        ]);
    }

    /**
     * @param $proceso_id
     */
    public function edit($proceso_id)
    {
        Log::info('editar ($proceso_id [' . $proceso_id . '])');

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        $data['proceso'] = $proceso;
        $data['proceso_id'] = $proceso_id;
        $data['title'] = 'Modelador';
        $data['content'] = 'backend/procesos/editar';

        return view('backend.process.edit', $data);
    }

    /**
     * @param $proceso_id
     * @throws \Doctrine_Query_Exception
     */
    public function activar(Request $request, $proceso_id)
    {
        Log::info('activar ($proceso_id [' . $proceso_id . '])');

        $request->validate(['descripcion' => 'required']);

        $respuesta = new \stdClass();

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            Log::debug('Usuario no tiene permisos para activar este proceso');
            echo 'Usuario no tiene permisos para activar este proceso';
            exit;
        }
        $fecha = new \DateTime();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Activación de Proceso';
        $registro_auditoria->motivo = $request->input('descripcion');
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

        // Detalles
        $proceso_array['proceso'] = $proceso->toArray(false);

        $registro_auditoria->detalles = json_encode($proceso_array);
        $registro_auditoria->save();
        Log::debug('$registro_auditoria->usuario: ' . $registro_auditoria->usuario);

        $q = Doctrine_Query::create()
            ->update('Proceso')
            ->set('activo', 1)
            ->where("id = ?", $proceso_id);
        $q->execute();

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.procesos.index', [$proceso_id]),
        ]);
    }

    /**
     * @param $proceso_id
     */
    public function ajax_editar($proceso_id)
    {
        $proceso = Proceso::find($proceso_id);
        $categorias = Categoria::all();

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }

        $data['proceso'] = $proceso;
        $data['categorias'] = $categorias;

        return view('backend.process.ajax_editar', $data);
    }

    /**
     * @param $proceso_id
     */
    public function editar_form(Request $request, $proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }

        $validators = ['nombre' => 'required'];

        $hasFicha = $request->has('ficha_informativa') ? 1 : 0;

        if ($hasFicha) {
            $validators['ficha_titulo'] = 'required|max:128';
            $validators['ficha_contenido'] = 'required';
        }


        $request->validate($validators);

        $proceso->nombre = $request->input('nombre');

        $proceso->width = $request->input('width');
        $proceso->height = $request->input('height');
        $proceso->categoria_id = $request->input('categoria');
        $proceso->icon_ref = $request->input('logo');
        $proceso->destacado = $request->has('destacado') ? 1 : 0;
        $proceso->descripcion = $request->input('descripcion');
        $proceso->url_informativa = $request->has('url_informativa') ? $request->get('url_informativa') : NULL;
        $proceso->concurrente = $request->has('concurrente') ? 1 : 0;
        $proceso->eliminar_tramites = $request->has('eliminar_tramites') ? 1 : 0;
        $proceso->ocultar_front = $request->has('ocultar_front') ? 1 : 0;

        $proceso->updated_at = Carbon::now('America/Santiago')->format('Y-m-d H:i:s');
        $proceso->ficha_informativa = $request->has('ficha_informativa') ? 1 : 0;
        $proceso->ficha_titulo = $request->input('ficha_titulo');
        $proceso->ficha_contenido = $request->input('ficha_contenido');

        $proceso->save();

        event(new HistorialModificacionEvent(
            'Se modificó uno o más atributos de la tabla Proceso',
            $proceso_id
         ));

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.procesos.edit', [$proceso->id]),
        ]);
    }

    /**
     * @param $proceso_id
     * @param $tarea_identificador
     */
    public function ajax_crear_tarea(Request $request, $proceso_id, $tarea_identificador)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para crear esta tarea.';
            exit;
        }

        $tarea = new \Tarea();
        $tarea->proceso_id = $proceso->id;
        $tarea->identificador = $tarea_identificador;
        $tarea->nombre = $request->input('nombre');
        $tarea->posx = $request->input('posx');
        $tarea->posy = $request->input('posy');
        $tarea->vencimiento_valor = 5;
        $tarea->save();

        event(new HistorialModificacionEvent(
            'Se creó una tarea',
            $proceso_id
        ));
    }

    /**
     * @param $proceso_id
     * @param $tarea_identificador
     */
    public function ajax_editar_tarea($proceso_id, $tarea_identificador)
    {
        $tarea = Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id, $tarea_identificador);
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        if ($tarea->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar esta tarea.';
            exit;
        }
        $data['proceso_id'] = $proceso_id;
        $data['tarea'] = $tarea;
        $data['formularios'] = Doctrine::getTable('Formulario')->findByProcesoId($proceso_id);
        $data['acciones'] = Doctrine::getTable('Accion')->findByProcesoId($proceso_id);
        $data['proceso'] = $proceso;
        $data['variablesFormularios'] = Doctrine::getTable('Proceso')->findVariblesFormularios($proceso_id, $tarea['id']);
        $data['variablesProcesos'] = Doctrine::getTable('Proceso')->findVariblesProcesos($proceso_id);

        $cuentas = Doctrine::getTable('Cuenta')->findAll();

        $index = 0;
        foreach ($cuentas as $cuenta) {
            if ($tarea->Proceso->cuenta_id == $cuenta->id) {
                unset($cuentas[$index]);
                break;
            }
            $index++;
        }

        $data['cuentas'] = $cuentas;

        $proceso_cuenta = new \ProcesoCuenta();
        $data['cuentas_con_permiso'] = $proceso_cuenta->findCuentasProcesos($proceso_id);

        return view('backend.process.ajax_editar_tarea', $data);
    }

    /**
     * @param $tarea_id
     */
    public function editar_tarea_form(Request $request, $tarea_id)
    {
        $tarea = Doctrine::getTable('Tarea')->find($tarea_id);

        if ($tarea->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar esta tarea.';
            exit;
        }

        $request->validate(['nombre' => 'required']);

        if ($request->has('vencimiento')) {
            $request->validate(['vencimiento_valor' => 'required']);
            //$this->form_validation->set_rules('vencimiento_valor', 'Valor de Vencimiento', 'required|is_natural_no_zero');
            if ($request->has('vencimiento_notificar')) {
                $request->validate([
                    'vencimiento_notificar_dias' => 'required|is_natural_no_zero',
                    'vencimiento_notificar_email' => 'required',
                ]);
                //$this->form_validation->set_rules('vencimiento_notificar_dias', 'Días para notificar vencimiento', 'required|is_natural_no_zero');
                //$this->form_validation->set_rules('vencimiento_notificar_email', 'Correo electronico para notificar vencimiento', 'required');
            }
        }

        Doctrine::getTable('Proceso')
            ->updateVaribleExposed($request->input('varForm'), $request->input('varPro'), $tarea->Proceso->id, $tarea_id);

        $proceso_cuenta = new \ProcesoCuenta();
        $proceso_cuenta->deleteCuentasConPermiso($tarea->Proceso->id);
        $cuentas_con_permiso = $request->input('cuentas_con_permiso');

        if (isset($cuentas_con_permiso) && count($cuentas_con_permiso) > 0) {
            foreach ($cuentas_con_permiso as $id_cuenta) {
                $proceso_cuenta = new \ProcesoCuenta();
                $proceso_cuenta->id_proceso = $tarea->Proceso->id;
                $proceso_cuenta->id_cuenta_origen = $tarea->Proceso->cuenta_id;
                $proceso_cuenta->id_cuenta_destino = $id_cuenta;
                $proceso_cuenta->save();
            }
        }

        $tarea->nombre = $request->input('nombre');
        $tarea->inicial = $request->has('inicial') ? $request->input('inicial') : 0;
        $tarea->final = $request->has('final') ? $request->input('final') : 0;
        $tarea->asignacion = $request->input('asignacion');
        $tarea->asignacion_usuario = $request->input('asignacion_usuario');
        $tarea->asignacion_notificar = $request->has('asignacion_notificar') ? $request->has('asignacion_notificar') : 0;
        $tarea->setGruposUsuariosFromArray($request->input('grupos_usuarios'));
        $tarea->setPasosFromArray($request->input('pasos', false));
        $tarea->setEventosExternosFromArray($request->input('eventos_externos', false));
        $tarea->setEventosFromArray($request->input('eventos', false));
        $tarea->paso_confirmacion = $request->has('paso_confirmacion') ? $request->input('paso_confirmacion') : 0;
        $tarea->paso_confirmacion_titulo = $request->has('paso_confirmacion_titulo') ? $request->input('paso_confirmacion_titulo') : NULL;
        $tarea->paso_confirmacion_contenido = $request->has('paso_confirmacion_contenido') ? $request->input('paso_confirmacion_contenido') : NULL;
        $tarea->paso_confirmacion_texto_boton_final = $request->has('paso_confirmacion_texto_boton_final') ? $request->input('paso_confirmacion_texto_boton_final') : NULL;
        $tarea->almacenar_usuario = $request->has('almacenar_usuario') ? $request->input('almacenar_usuario') : 0;
        $tarea->almacenar_usuario_variable = $request->input('almacenar_usuario_variable');
        $tarea->acceso_modo = $request->input('acceso_modo');
        $tarea->activacion = $request->input('activacion');
        $tarea->activacion_inicio = strtotime($request->input('activacion_inicio'));
        $tarea->activacion_fin = strtotime($request->input('activacion_fin'));
        $tarea->vencimiento = $request->has('vencimiento') ? $request->input('vencimiento') : 0;
        $tarea->vencimiento_valor = $request->input('vencimiento_valor');
        $tarea->vencimiento_unidad = $request->input('vencimiento_unidad');
        $tarea->vencimiento_habiles = $request->has('vencimiento_habiles') ? $request->input('vencimiento_habiles') : 0;
        $tarea->vencimiento_notificar = $request->has('vencimiento_notificar') ? $request->input('vencimiento_notificar') : 0;
        $tarea->vencimiento_notificar_dias = $request->input('vencimiento_notificar_dias');
        $tarea->vencimiento_notificar_email = $request->input('vencimiento_notificar_email');
        $tarea->previsualizacion = $request->input('previsualizacion');
        $tarea->externa = $request->has('externa') ? $request->input('externa') : 0;
        $tarea->exponer_tramite = $request->input('exponer_tramite');
        $tarea->save();

        event(new HistorialModificacionEvent(
            'Se editó uno o más atributos de la tarea: '.$tarea->nombre. ' - [id: '.$tarea->id.']',
            $tarea->proceso_id
        ));

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.procesos.edit', $tarea->Proceso->id),
        ]);
    }

    /**
     * @param $tarea_id
     */
    public function eliminar_tarea($tarea_id)
    {
        $tarea = Doctrine::getTable('Tarea')->find($tarea_id);

        if ($tarea->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar esta tarea.';
            exit;
        }

        $proceso = $tarea->Proceso;

        $fecha = new \DateTime();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Tarea';
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

        // Detalles
        $tarea_array['proceso'] = $proceso->toArray(false);

        $tarea_array['tarea'] = $tarea->toArray(false);
        unset($tarea_array['tarea']['posx']);
        unset($tarea_array['tarea']['posy']);
        unset($tarea_array['tarea']['proceso_id']);

        $registro_auditoria->detalles = json_encode($tarea_array);
        $registro_auditoria->save();

        $tarea->delete();

        event(new HistorialModificacionEvent(
            'Se eliminó la tarea: '.$tarea->nombre. ' - [id: '.$tarea->id.']',
            $tarea->proceso_id
        ));

        return redirect()->route('backend.procesos.edit', [$proceso->id]);
    }

    /**
     * @param $proceso_id
     */
    public function ajax_crear_conexion(Request $request, $proceso_id)
    {

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        $tarea_origen = Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id, $request->input('tarea_id_origen'));
        $tarea_destino = Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id, $request->input('tarea_id_destino'));

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para crear esta conexion.';
            exit;
        }

        if ($tarea_origen->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para crear esta conexion.';
            exit;
        }

        if ($tarea_destino->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para crear esta conexion.';
            exit;
        }

        // El tipo solamente se setea en la primera conexion creada para esa tarea.
        $tipo = $request->input('tipo');
        if ($tarea_origen->ConexionesOrigen->count())
            $tipo = $tarea_origen->ConexionesOrigen[0]->tipo;

        $conexion = new \Conexion();
        $conexion->tarea_id_origen = $tarea_origen->id;
        $conexion->tarea_id_destino = $tarea_destino->id;
        $conexion->tipo = $tipo;
        $conexion->save();

        event(new HistorialModificacionEvent(
            'Se ha agregado una conexión entre tareas',
            $proceso_id
        ));
    }

    /**
     * @param $proceso_id
     * @param $tarea_origen_identificador
     * @param null $union
     * @throws \Doctrine_Query_Exception
     */
    public function ajax_editar_conexiones($proceso_id, $tarea_origen_identificador, $union = null)
    {
        if (!is_null($union)) {
            $conexiones = Doctrine_Query::create()
                ->from('Conexion c, c.TareaDestino t')
                ->where('t.proceso_id=? AND t.identificador=?', array($proceso_id, $tarea_origen_identificador))
                ->execute();
        } else {
            $conexiones = Doctrine_Query::create()
                ->from('Conexion c, c.TareaOrigen t')
                ->where('t.proceso_id=? AND t.identificador=?', array($proceso_id, $tarea_origen_identificador))
                ->execute();
        }

        if ($conexiones[0]->TareaOrigen->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar estas conexiones.';
            exit;
        }

        $data['proceso_id'] = $proceso_id;
        $data['conexiones'] = $conexiones;

        return view('backend.process.ajax_editar_conexiones', $data);
    }

    /**
     * @param $tarea_id
     */
    public function editar_conexiones_form(Request $request, $tarea_id)
    {
        Log::debug('method: editar_conexiones_form(' . $tarea_id . ')');

        $tarea = Doctrine::getTable('Tarea')->find($tarea_id);

        if ($tarea->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar estas conexiones.';
            exit;
        }

        $request->validate([
            'conexiones' => 'required'
        ]);

        $tarea->setConexionesFromArray($request->input('conexiones', false));

        try {
            $tarea->save();
            event(new HistorialModificacionEvent(
                'Se editó la conexión de una tarea',
                $tarea->proceso_id
            ));
        } catch (\Doctrine_Connection_Mysql_Exception $e) {
            return response()->json(['code' => 200, 'message' => 'alerta'], 400);
        }

        $proceso = $tarea->Proceso;

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.procesos.edit', [$proceso->id])
        ]);
    }

    /**
     * @param $tarea_id
     */
    public function eliminar_conexiones($tarea_id)
    {
        $tarea = Doctrine::getTable('Tarea')->find($tarea_id);

        if ($tarea->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar esta conexion.';
            exit;
        }

        $proceso = $tarea->Proceso;
        $tarea->ConexionesOrigen->delete();

        event(new HistorialModificacionEvent(
            'Se ha eliminado una conexión entre tareas',
            $proceso->id
        ));

        return redirect()->route('backend.procesos.edit', [$proceso->id]);
    }

    /**
     * @param $proceso_id
     */
    public function ajax_editar_modelo(Request $request, $proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }

        $modelo = $request->input('modelo');

        $proceso->updateModelFromJSON($modelo);
    }

    /**
     * @param $proceso_id
     */
    public function export($proceso_id)
    {

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        $json = $proceso->exportComplete();

        header("Content-Disposition: attachment; filename=\"" . mb_convert_case(str_replace(' ', '-', $proceso->nombre), MB_CASE_LOWER) . ".simple\"");
        header('Content-Type: application/json');
        echo $json;

    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function import(Request $request)
    {
        $file_path = $_FILES['archivo']['tmp_name'];

        if ($file_path) {
            $input = file_get_contents($_FILES['archivo']['tmp_name']);
            $proceso = \Proceso::importComplete($input, TRUE);

            if (!$proceso) {
                return redirect()->route('backend.procesos.index');
            }
            $proceso->usuario_id = Auth::user()->id;
            $fecha = Carbon::now('America/Santiago')->format('Y-m-d H:i:s');
            $proceso->created_at = $fecha;
            $proceso->updated_at = $fecha;
            $proceso->save();

            Log::info("Migrando configuraciones de seguridad");
            $this->migrarSeguridadAcciones($proceso);
            Log::info("Migrando configuraciones de suscriptores");
            $this->migrarSuscriptores($proceso);

            $this->migrarEventosExternos($proceso);

            Log::info("Fin migración de proceso");
        }

        $request->session()->flash('success', 'Importado con éxito.');

        return redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * @param $proceso
     * @param $cuenta
     */
    private function migrarGrupos($proceso, $cuenta)
    {
        //asignar grupos de usuario de producción por cada tarea
        Log::info("Revisando grupos para proceso id " . $proceso->id);
        $tareas = $proceso->getTareasProceso($proceso->id);

        foreach ($tareas as $tarea) {
            $idUsuarios = $tarea->grupos_usuarios;
            if (strlen($idUsuarios) > 0) {
                $ids = explode(",", $idUsuarios);
                if (count($ids) > 0) {
                    $ids_prod = "";
                    foreach ($ids as $id) {
                        $grupo = Doctrine::getTable("GrupoUsuarios")->find($id);

                        if (!false) {
                            continue;
                        }

                        Log::info("Revisando grupo: " . $grupo->nombre);
                        $grupo_prod = $grupo->existeGrupo($cuenta->vinculo_produccion);
                        if (isset($grupo_prod)) {
                            Log::info("Existe en produccion");
                            Log::info("Nombre: " . $grupo_prod->nombre);
                            if (strlen($ids_prod) > 0) {
                                $ids_prod .= "," . $grupo_prod->id;
                            } else {
                                $ids_prod = $grupo_prod->id;
                            }
                        } else {
                            Log::info("No existe en produccion");
                            $grupo_usuarios = new \GrupoUsuarios();
                            $grupo_usuarios->nombre = $grupo->nombre;
                            $grupo_usuarios->cuenta_id = $cuenta->vinculo_produccion;
                            $grupo_usuarios->save();
                            Log::info("Se crea grupo en produccion");
                            Log::info("Grupo creado: " . $grupo_usuarios->id);
                            if (strlen($ids_prod) > 0) {
                                $ids_prod .= "," . $grupo_usuarios->id;
                            } else {
                                $ids_prod = $grupo_usuarios->id;
                            }
                        }
                    }
                    Log::info("id grupos prod: " . $ids_prod);
                    if (strlen($ids_prod) > 0) {
                        $tarea->grupos_usuarios = $ids_prod;
                        $tarea->save();
                    }
                }
            }
        }
    }

    /**
     * @param $proceso
     */
    private function migrarEventosExternos($proceso)
    {
        Log::info("Revisando eventos externos para proceso id " . $proceso->id);
        $tareas = $proceso->Tareas;
        foreach ($tareas as $tarea) {
            foreach ($tarea->Eventos as $evento) {
                if (isset($evento->evento_externo_id) && strlen($evento->evento_externo_id) > 0) {
                    $evento->save();
                }
            }
        }
    }

    /**
     * @param $proceso
     */
    private function migrarSeguridadAcciones($proceso)
    {
        Log::info("Revisando seguridad para proceso id " . $proceso->id);
        $acciones = $proceso->Acciones;
        foreach ($acciones as $accion) {
            if ($accion->tipo == 'rest' || $accion->tipo == 'soap' || $accion->tipo == 'callback') {
                if (isset($accion->extra->idSeguridad) && strlen($accion->extra->idSeguridad) > 0) {
                    $extra_accion = $accion->extra;
                    $extra_accion->idSeguridad = $proceso->Admseguridad[$accion->extra->idSeguridad]->id;
                    $accion->extra = $extra_accion;
                    Log::info("Guardando accion id " . $accion->id);
                    $accion->save();
                }
            } elseif ($accion->tipo == 'iniciar_tramite') {
                if (isset($accion->extra->tareaRetornoSel) && strlen($accion->extra->tareaRetornoSel) > 0) {
                    $extra_accion = $accion->extra;
                    $extra_accion->tareaRetornoSel = $proceso->Tareas[$accion->extra->tareaRetornoSel]->id;
                    $accion->extra = $extra_accion;
                    Log::info("Guardando accion id " . $accion->id);
                    $accion->save();
                }
            }
        }
    }

    /**
     * @param $proceso
     */
    private function migrarSuscriptores($proceso)
    {
        Log::info("Revisando suscriptores para proceso id " . $proceso->id);

        $suscriptores = $proceso->Suscriptores;
        foreach ($suscriptores as $suscriptor) {
            if (isset($suscriptor->extra->idSeguridad) && strlen($suscriptor->extra->idSeguridad) > 0) {
                $extra_suscriptor = $suscriptor->extra;
                $extra_suscriptor->idSeguridad = $proceso->Admseguridad[$suscriptor->extra->idSeguridad]->id;//$new_seguridad->id;
                $suscriptor->extra = $extra_suscriptor;
                Log::info("Guardando suscriptor id " . $suscriptor->id);
                $suscriptor->save();
            }
        }

        $acciones = $proceso->Acciones;
        foreach ($acciones as $accion) {
            if ($accion->tipo == 'webhook') {
                if (isset($accion->extra->suscriptorSel) && count($accion->extra->suscriptorSel) > 0) {
                    $suscriptores_seleccionados = array();
                    foreach ($accion->extra->suscriptorSel as $suscriptor) {
                        $suscriptores_seleccionados[] = $proceso->Suscriptores[$suscriptor]->id;//$new_suscriptor->id;
                    }
                    $extra_accion = $accion->extra;
                    $extra_accion->suscriptorSel = $suscriptores_seleccionados;
                    $accion->extra = $extra_accion;
                    Log::info("Guardando accion id " . $accion->id);
                    $accion->save();
                }
            }
        }
    }

    /**
     * @param $proceso_id
     */
    public function ajax_auditar_eliminar_proceso($proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol)))
            show_error('No tiene permisos', 401);

        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $data['proceso'] = $proceso;

        return view('backend.process.ajax_auditar_eliminar_proceso', $data);
    }

    /**
     * @param $proceso_id
     */
    public function ajax_auditar_activar_proceso($proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol))) {
            return abort(401);
        }

        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $data['proceso'] = $proceso;

        return view('backend.process.ajax_auditar_activar_proceso', $data);
    }

    /**
     * @param $proceso_id
     * @throws \Doctrine_Query_Exception
     */
    public function getJSONFromModelDraw($proceso_id)
    {
        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $modelo = new \stdClass();
        $modelo->nombre = $proceso->nombre;
        $modelo->elements = array();
        $modelo->connections = array();

        $tareas = Doctrine::getTable('Tarea')->findByProcesoId($proceso_id);
        foreach ($tareas as $t) {
            $element = new \stdClass();
            $element->id = $t->identificador;
            $element->name = $t->nombre;
            $element->left = $t->posx;
            $element->top = $t->posy;
            $element->start = $t->inicial;
            $element->stop = $t->final;
            $modelo->elements[] = clone $element;
        }
        //$conexiones1=  Doctrine_Query::create()->from('Conexion c, c.TareaOrigen.Proceso p')->where('p.id = ?',$proceso_id);
        $conexiones = Doctrine_Query::create()
            ->from('Conexion c, c.TareaOrigen.Proceso p')
            ->where('p.id = ?', $proceso_id)
            ->execute();
        //echo $conexiones1->getSqlQuery();
        foreach ($conexiones as $c) {
            //$conexion->id=$c->identificador;
            $conexion = new \stdClass();
            $conexion->source = $c->TareaOrigen->identificador;
            $conexion->target = $c->TareaDestino->identificador;
            $conexion->tipo = $c->tipo;
            $modelo->connections[] = clone $conexion;
        }
        //print_r(json_encode($modelo));
        //exit;
        echo json_encode($modelo);
    }

    /**
     *
     */
    public function seleccionar_icono()
    {
        $DS = DIRECTORY_SEPARATOR;
        $directory = resource_path('assets/img/icon');
        $html = '';
        $error = '';
        $hideButton = true;
        $isImage = false;

        if (file_exists($directory)) {
            $icons = @scandir($directory);
            if ($icons !== FALSE) {
                if (count($icons) > 0) {
                    $hideButton = false;
                    foreach ($icons as $icon) {
                        if ($this->isImage($directory . $DS . $icon)) {
                            $isImage = true;
                            $html .= '<div class="item"><a class="sel-icono" href="javascript:;" rel="' . $icon . '"><img src="' . asset('img/icon/' . $icon) . '" alt="' . $icon . '" title="' . $icon . '"></a></div>';
                        }
                    }

                    if (!$isImage) {
                        $error .= '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>No hay &iacuteconos en la carpeta "assets/img/icon"</div>';
                    }
                } else {
                    $error .= '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>No hay &iacuteconos en la carpeta "assets/img/icon"</div>';
                }
            } else {
                $error .= '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>No se pudo leer la carpeta "assets/img/icon"</div>';
            }
        } else {
            $error .= '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>La carpeta "assets/img/icon" no existe</div>';
        }

        $data['hideButton'] = $hideButton;
        $data['iconos'] = $html;
        $data['error'] = $error;

        return view('backend.process.seleccionar_icono', $data);
    }

    /**
     * @param $image
     * @return bool
     */
    private function isImage($image)
    {
        return @is_array(getimagesize($image));
    }

    /**
     * @param $proceso_id
     */
    public function ajax_publicar_proceso($proceso_id)
    {
        if (!in_array('super', explode(",", Auth::user()->rol)))
            abort(401);

        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $data['proceso'] = $proceso;

        return view('backend.process.ajax_publicar_proceso', $data);
    }


    /**
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ajax_editar_proceso($proceso_id)
    {
        $proceso = Doctrine::getTable("Proceso")->find($proceso_id);
        $data['proceso'] = $proceso;
        return view('backend.process.ajax_editar_proceso', $data);

    }

    public function historial_modificaciones(Request $request, $proceso_id)
    {
        $order_by_fecha = $request->input('order_by_fecha', null);
        $historial_cambios = Historial::where('proceso_id', $proceso_id);
        $proceso = Proceso::find($proceso_id);

        if (null != $order_by_fecha) {
            $historial_cambios->orderBy('created_at', $order_by_fecha);
            $order_by_fecha = ($order_by_fecha == 'asc') ? 'desc':'asc';
        } else {
            $historial_cambios->orderBy('created_at', 'desc');
        }

        $historial_cambios = $historial_cambios->paginate(50);

        return view('backend.process.historial', [
            'historial_cambios' => $historial_cambios,
            'proceso' => $proceso,
            'order_by_fecha' => $order_by_fecha
        ]);
    }
}
