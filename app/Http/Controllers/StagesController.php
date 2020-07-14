<?php

namespace App\Http\Controllers;
use App\Models\Tramite;
use App\Models\Job;
use App\Models\Campo;
use App\Rules\Captcha;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Manager;
use Illuminate\Support\Facades\URL;
use Cuenta;
use ZipArchive;
use App\Jobs\IndexStages;
use App\Jobs\FilesDownload;
use Carbon\Carbon;
use Doctrine_Query;
use App\Models\DatoSeguimiento;
use App\Models\Etapa;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StagesController extends Controller
{
    public function run(Request $request, $etapa_id, $secuencia = 0)
    {   
        $iframe = $request->input('iframe');
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
       
        $data = \Cuenta::configSegunDominio();
        
        $data['num_pasos'] = $etapa === false ? 0 : self::num_pasos($etapa->Tarea->id);
        $proceso_id= $etapa->Tarea->proceso_id; 
        Log::info("El Proceso_id: " . $proceso_id);
        $proceso = Doctrine::getTable('Proceso')->find($etapa->Tarea->proceso_id);
            Log::info("Se a identificado el Proceso Nº : " . $proceso);
    
        if (!$etapa) {
            return abort(404);
        }
        if ( $etapa->Tarea->acceso_modo != 'anonimo' && $etapa->usuario_id != Auth::user()->id) {
            if (!Auth::user()->registrado) {
                return redirect()->route('home');
            }
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }

        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        // if ($etapa->vencida()) {
        //     echo 'Esta etapa se encuentra vencida';
        //     exit;
        // }

        $qs = $request->getQueryString();
        $pasosEjecutables = $etapa->getPasosEjecutables();
        
        $paso = (isset($pasosEjecutables[$secuencia])) ? $pasosEjecutables[$secuencia] : null;
        Log::info("Ejecutando paso: " . $paso);
        if (!$paso) {
            Log::info("Entra en no paso: ");
            return redirect('etapas/ejecutar_fin/' . $etapa->id . ($qs ? '?' . $qs : ''));
        } else if (($etapa->Tarea->final || !$etapa->Tarea->paso_confirmacion) && $paso->getReadonly() && end($pasosEjecutables) == $paso) { // No se requiere mas input
            $respuesta = $etapa->iniciarPaso($paso);
            if(isset($respuesta)){
                return redirect($respuesta);
            }
            $respuesta = $etapa->finalizarPaso($paso);
            if(isset($respuesta)){
                return redirect($respuesta);
            }
             Log::info("El finalizar paso: " .  $etapa->finalizarPaso($paso));
            $etapa->avanzar();
            
            //Job para indexar contenido cada vez que se avanza de etapa
            $this->dispatch(new IndexStages($etapa->Tramite->id));

            if(session()->has('redirect_url')){
                return redirect()->away(session()->get('redirect_url'));
            }

            return redirect('etapas/ver/' . $etapa->id . '/' . (count($pasosEjecutables) - 1));
        } else {
            
            $respuesta = $etapa->iniciarPaso($paso);
            if(isset($respuesta)){
                return redirect($respuesta);
            }
            if(session()->has('redirect_url')){
                return redirect()->away(session()->get('redirect_url'));
            }

            Log::info("###MARCA INICIO GA : " . $etapa->pendiente);
            $data['extra']['analytics'] = null;
            $extra_etapa = json_decode($etapa->extra, true);
            $extra_etapa = ($extra_etapa === null ) ? [] : $extra_etapa;
            if(!isset($extra_etapa['mostrar_hit'])){ //isset
                $busca_evento_analytics = DB::table('etapa') //Buscando el evento analytics por tarea iniciada
                    ->select('accion.id',
                        'accion.tipo',
                        'tarea.nombre as tarea_nombre',
                        'tarea.es_final as es_tarea_final',
                        'accion.nombre',
                        'accion.extra',
                        'evento.regla'
                    )
                    ->join('tarea','etapa.tarea_id', '=','tarea.id')
                    ->join('evento', 'evento.tarea_id', '=', 'tarea.id')
                    ->join('accion','evento.accion_id','=', 'accion.id')
                    ->where('etapa.id', $etapa->id)->where('accion.tipo','=','evento_analytics')->get();

                Log::info("###Lo que trae busca_analyiticd : " . $busca_evento_analytics);

                if (count($busca_evento_analytics) > 0) {
                    $data['extra']['analytics'] = json_decode($busca_evento_analytics[0]->extra, true);
                    $data['extra']['es_final'] = $busca_evento_analytics[0]->es_tarea_final ? 'si':'no';
                    $extra_hit =  $data['extra']['analytics'];
                    $extra_etapa['analytics']=$extra_hit;
                    $extra_etapa['mostrar_hit'] = true;
                } else {
                    $extra_etapa['mostrar_hit'] = false;
                }

                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
            }
           
            $data['secuencia'] = $secuencia;
            $data['etapa'] = $etapa;
            $data['paso'] = $paso;
            
            $data['qs'] = $request->getQueryString();
            $data['sidebar'] = Auth::user()->registrado ? 'inbox' : 'disponibles';
            $data['title'] = $etapa->Tarea->nombre;
            //$template = $request->has('iframe') ? 'template_iframe' : 'template';
            return view('stages.run', $data);
        }
    }

    public function num_pasos($tarea_id)
    {
        Log::debug('$etapa->Tarea->id [' . $tarea_id . ']');

        $stmn = Doctrine_Manager::getInstance()->connection();
        $sql_pasos = "SELECT COUNT(*) AS total FROM paso WHERE tarea_id=" . $tarea_id;
        $result = $stmn->prepare($sql_pasos);
        $result->execute();
        $num_pasos = $result->fetchAll();
        Log::debug('$num_pasos [' . $num_pasos[0][0] . ']');

        return $num_pasos[0][0];
    }

    /**
     * @internal Muestra las etapas disponibles para ejecutar asignadas al usuario logueado
     * @param Request $request
     * @return view stages.inbox
     */
    public function inbox(Request $request)
    {
        $cuenta= Cuenta::cuentaSegunDominio(); // Obtengo la cuenta del usuario logueado
        $sortValue = $request->sortValue;
        $sort = $request->sort;
        $query = $request->input('query'); // Obtengo el parametro de búsqueda
        if ($query && session('query_sinasignar') != $query) 
        {// Si el dato buscado no es vacío y es distinto al ya buscado (variable de session query_sinasignar) realizo busqueda en elasticSearch

            try{
                $request->session()->put('query_sinasignar',$request->input('query')); // Seteo variable de session para comparar en la proxima busqueda
                $result = Tramite::search($query)->get(); // Consulto en elasticSearch 
                $matches = array(); // Array donde se guardaran los id de tramite
                foreach($result as $resultado)
                { // Recorro los resultados 
                    array_push($matches, $resultado->id); // Agrego el id del tramite al array matches
                }
                $request->session()->put('matches_sinasignar', $matches); // Seteo una variable de session para los id's de tramite para la busqueda en la DB
            }catch(\Exception $e){
                \Log::error('Exception elasticsearch: '.$e->getMessage());
            }
        }
        /* Query para obtener los tramites buscados de acuerdo al filtro */
        $etapas = Etapa::where('etapa.usuario_id', Auth::user()->id)->where('etapa.pendiente', 1)
        ->whereHas('tramite', function($q) use ($query, $cuenta){
            $q->whereHas('proceso', function($q) use ($cuenta){
                $q->where('cuenta_id',$cuenta->id);         
            });
            if($query!="" && !empty(session('matches_sinasignar')))
            { // Si viene el filtro de busqueda y se obtiene datos de elasticSearch agrego where para id de tramites
                $q->whereIn('tramite_id', session('matches_sinasignar'));
            }
        })
        ->whereHas('tarea', function($q){
            $q->where('activacion', "si")
            ->orWhere(function($q){
                $q->where('activacion', "entre_fechas")
                ->where('activacion_inicio', '<=', Carbon::now())
                ->where('activacion_fin', '>=', Carbon::now());   
            }); 
        });
        if($query!="" && !empty(session('matches_sinasignar')))
        { // Si viene el filtro de busqueda y se obtiene datos de elasticSearch agrego where para id de tramites
            $etapas = $etapas->whereIn('tramite_id', session('matches_sinasignar'));
        }
        /* Order de acuerdo a lo solicitado desde los titulos de la tabla en la vista */
        if($sortValue == 'etapa')
        {// Orden por nombre de tarea
            $etapas = $etapas->join('tarea', 'tarea.id', 'etapa.tarea_id')->orderBy('tarea.nombre', $sort);
        }
        if($sortValue == 'nombre')
        { // Orden por nombre de proceso
            $etapas = $etapas->join('tarea', 'tarea.id', 'etapa.tarea_id')
            ->join('proceso', 'tarea.proceso_id', 'proceso.id')->orderBy('proceso.nombre', $sort);
        }
        if($sortValue == 'numero')
        { // Orden por id de tramite
            $etapas = $etapas->orderBy('tramite_id', $sort);
        }
        elseif($sortValue == 'modificacion')
        { // Orden por fecha de modificación 
            $etapas = $etapas->join('tramite', 'tramite.id', 'etapa.tramite_id')
            ->orderBy('tramite.updated_at', $sort);
        }
        elseif($sortValue == 'ingreso')
        { // Orden por fecha de modificación 
            $etapas = $etapas->join('tramite', 'tramite.id', 'etapa.tramite_id')
            ->orderBy('tramite.created_at', $sort);
        }
        elseif($sortValue == 'vencimiento')
        { // Orden por fecha de modificación 
            $etapas = $etapas->orderBy('vencimiento_at', $sort);
        }
        $etapas = $etapas->groupBy('etapa.id') // Agrupo por el id de la etapa
        ->paginate(50); // Pagino de 50 registros
        // Retorno la vista inbox
        return view('stages.inbox', compact('etapas', 'cuenta', 'query', 'request'));
    }

    /**
     * @internal Muestra las etapas sin asignar disponibles para el usuario logueado
     * @param Request $request
     * @return view stages.unassigned
     */
    public function sinasignar(Request $request)
    {
        $etapas = [];
        if (!Auth::user()->registrado) 
        {
            //$request->session()->put('claveunica_redirect', URL::current());//se saca el login.claveunicas
            return redirect()->route('login');
        }
        $sortValue = $request->sortValue;// Obtengo el parametro de orden de los datos 
        $sort = $request->sort;// Obtengo el parametro de dirección del orden
        $query = $request->input('query'); // Obtengo el parametro de búsqueda
        if (!Auth::user()->open_id) 
        {
            if ($query && session('query_sinasignar') != $query) 
            {// Si el dato buscado no es vacío y es distinto al ya buscado (variable de session query_sinasignar) realizo busqueda en elasticSearch
                try{
                    $request->session()->put('query_sinasignar',$request->input('query')); // Seteo variable de session para comparar en la proxima busqueda
                    $result = Tramite::search($query)->get(); // Consulto en elasticSearch 
                    $matches = array(); // Array donde se guardaran los id de tramite
                    foreach($result as $resultado)
                    { // Recorro los resultados
                        array_push($matches, $resultado->id); // Agrego el id del tramite al array matches
                    }
                    $request->session()->put('matches_sinasignar', $matches); // Seteo una variable de session para los id's de tramite para la busqueda en la DB
                }catch(\Exception $e){
                    \Log::error('Exception elasticsearch: '.$e->getMessage());
                }
            }
            $grupos = Auth::user()->grupo_usuarios()->pluck('grupo_usuarios_id'); // Obtengo los grupos al que pertenece el usuario logueado
            $cuenta= Cuenta::cuentaSegunDominio(); // Obtengo la cuenta del usuario logueado
            /* Query para obtener los tramites buscados de acuerdo al filtro */
            $etapas = Etapa::select('etapa.*')->
            whereNull('etapa.usuario_id')
            ->join('tarea', function($q) use ($grupos){
                $q->on('etapa.tarea_id','=', 'tarea.id');                
            })
            ->join('proceso', function($q) use ($cuenta){
                $q->on('tarea.proceso_id', '=', 'proceso.id');
            })
            ->where(function($q) use ($grupos){
                $q->where('grupos_usuarios','LIKE','%@@%');
                foreach($grupos as $grupo){
                    $q->orWhereRaw('CONCAT(SPACE(1), REPLACE(tarea.grupos_usuarios, ",", " "), SPACE(1)) like "% '.$grupo.' %"');
                }
            })
            ->where(function($q)  use ($cuenta){
                $q->where('cuenta_id',$cuenta->id)
                ->where('proceso.activo', 1);
            })
            ->whereHas('tramite', function($q) use ($query){
                if($query!="" && !empty(session('matches_sinasignar')))
                { // Si viene el filtro de busqueda y se obtiene datos de elasticSearch agrego where para id de tramites
                    $q->whereIn('tramite_id', session('matches_sinasignar'));
                }
            });
            /* Order de acuerdo a lo solicitado desde los titulos de la tabla en la vista */
            if($sortValue == 'etapa')
            {// Orden por nombre de tarea
                $etapas = $etapas->orderBy('tarea.nombre', $sort);
            }
            if($sortValue == 'nombre')
            { // Orden por nombre de proceso
                $etapas = $etapas->orderBy('proceso.nombre', $sort);
            }
            if($sortValue == 'numero')
            { // Orden por id de tramite
                $etapas = $etapas->orderBy('tramite_id', $sort);
            }
            elseif($sortValue == 'modificacion')
            { // Orden por fecha de modificación 
                $etapas = $etapas->join('tramite', 'tramite.id', 'etapa.tramite_id')
                ->orderBy('tramite.updated_at', $sort);
            }
            elseif($sortValue == 'ingreso')
            { // Orden por fecha de modificación 
                $etapas = $etapas->join('tramite', 'tramite.id', 'etapa.tramite_id')
                ->orderBy('tramite.created_at', $sort);
            }
            elseif($sortValue == 'vencimiento')
            { // Orden por fecha de modificación 
                $etapas = $etapas->orderBy('vencimiento_at', $sort);
            }
            $etapas=$etapas->paginate(50);
            /* Retorno vista bandeja sin asignar */ 
        }
        return view('stages.unassigned', compact('etapas', 'cuenta', 'query', 'request'));
    }

    public function ejecutar_form(Request $request, $etapa_id, $secuencia)
    {
        Log::info('ejecutar_form ($etapa_id [' . $etapa_id . '], $secuencia [' . $secuencia . '])');

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ( $etapa->Tarea->acceso_modo != 'anonimo' && $etapa->usuario_id != Auth::user()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }

        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        // if ($etapa->vencida()) {
        //     echo 'Esta etapa se encuentra vencida';
        //     exit;
        // }

        $paso = $etapa->getPasoEjecutable($secuencia);
        $formulario = $paso->Formulario;
        $modo = $paso->modo;
        $respuesta = new \stdClass();
        $validations = [];
        $tipos_no_serializados = array("checkbox","radio","comunas");
        if ($modo == 'edicion') {

            $campos_nombre_etiqueta = [];
            foreach ($formulario->Campos as $c) {

                if(!in_array($c->tipo,$tipos_no_serializados))
                    if(!$request->has($c->nombre))
                        continue;
                // Validamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                if ($c->isEditableWithCurrentPOST($request, $etapa_id)) {
                    $validate = $c->formValidate($request, $etapa->id);
                    if (!empty($validate[0]) && !empty($validate[1])) {
                        $validations[$validate[0]] = $validate[1];
                        $etiqueta = strip_tags($c->etiqueta);
                        if($c->tipo == 'select' && strpos($etiqueta, '.') !== FALSE){
                            $etiqueta = substr($etiqueta, strpos($etiqueta, '.'));
                        }

                        $campos_nombre_etiqueta[$validate[0]] = "<b>$etiqueta</b>";
                    }
                }
                if ($c->tipo == 'recaptcha') {
                    $validations['g-recaptcha-response'] = ['required', new Captcha];
                }
            }

            $request->validate( $validations, [], $campos_nombre_etiqueta );

            // Almacenamos los campos
            foreach ($formulario->Campos as $c) {
                // Almacenamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)

                if ($c->isEditableWithCurrentPOST($request, $etapa_id)) {
                    $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($c->nombre, $etapa->id);
                    if (!$dato)
                        $dato = new \DatoSeguimiento();
                    $dato->nombre = $c->nombre;
                    $dato->valor = $request->input($c->nombre) === false ? '' : $request->input($c->nombre);

                    if (!is_object($dato->valor) && !is_array($dato->valor)) {
                        if (preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)) {
                            $dato->valor = preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i", "$3-$2-$1", $dato->valor);
                        }
                    }

                    if($c->tipo=='comunas'){
                        $region_comuna = $request->input($c->nombre);
                        $region_comuna['cstateCode'] = $request->input('cstateCode_'.$c->id);
                        $region_comuna['cstateName'] = $request->input('cstateName_'.$c->id);
                        $region_comuna['ccityCode'] = $request->input('ccityCode_'.$c->id);
                        $region_comuna['ccityName'] = $request->input('ccityName_'.$c->id);
                        $dato->valor = $region_comuna;
                    }elseif($c->tipo=='provincias'){
                        $region_provincia_comuna = $request->input($c->nombre);
                        $region_provincia_comuna['pstateCode'] = $request->input('pstateCode_'.$c->id);
                        $region_provincia_comuna['pstateName'] = $request->input('pstateName_'.$c->id);
                        $region_provincia_comuna['provinciaCode'] = $request->input('provinciaCode_'.$c->id);
                        $region_provincia_comuna['provinciaName'] = $request->input('provinciaName_'.$c->id);
                        $region_provincia_comuna['pcityCode'] = $request->input('pcityCode_'.$c->id);
                        $region_provincia_comuna['pcityName'] = $request->input('pcityName_'.$c->id);
                        $dato->valor = $region_provincia_comuna;
                    }

                    $dato->etapa_id = $etapa->id;
                    $dato->save();
                }
            }
            $etapa->save();
            $respuesta->redirect = $etapa->finalizarPaso($paso);
            if(isset($respuesta->redirect)){
                return response()->json([
                    'validacion' => true,
                    'redirect' => $respuesta->redirect
                ]);
            }
            
            $respuesta->validacion = TRUE;
            $qs = $request->getQueryString();
            $prox_paso = $etapa->getPasoEjecutable($secuencia + 1);
            $pasosEjecutables = $etapa->getPasosEjecutables();
            if (!$prox_paso) {
                $respuesta->redirect = '/etapas/ejecutar_fin/' . $etapa_id . ($qs ? '?' . $qs : '');
            } else if ($etapa->Tarea->final && $prox_paso->getReadonly() && end($pasosEjecutables) == $prox_paso) { //Cerrado automatico
                $respuesta->redirect = $etapa->iniciarPaso($prox_paso);
                if(isset($respuesta->redirect)){
                    return response()->json([
                        'validacion' => true,
                        'redirect' => $respuesta->redirect
                    ]);
                }
                $respuesta->redirect = $etapa->finalizarPaso($prox_paso);
                if(isset($respuesta->redirect)){
                    return response()->json([
                        'validacion' => true,
                        'redirect' => $respuesta->redirect
                    ]);
                }
                $etapa->avanzar();
                //Job para indexar contenido cada vez que se avanza de etapa
                $this->dispatch(new IndexStages($etapa->Tramite->id));
                $respuesta->redirect = '/etapas/ver/' . $etapa->id . '/' . (count($pasosEjecutables) - 1);
            } else {
                $respuesta->redirect = '/etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1) . ($qs ? '?' . $qs : '');
            }

        } else if ($modo == 'visualizacion') {
            $respuesta->validacion = TRUE;

            $qs = $request->getQueryString();
            $prox_paso = $etapa->getPasoEjecutable($secuencia + 1);
            $pasosEjecutables = $etapa->getPasosEjecutables();
            if (!$prox_paso) {
                $respuesta->redirect = '/etapas/ejecutar_fin/' . $etapa_id . ($qs ? '?' . $qs : '');
            } else if ($etapa->Tarea->final && $prox_paso->getReadonly() && end($pasosEjecutables) == $prox_paso) { //Cerrado automatico
                $respuesta->redirect = $etapa->iniciarPaso($prox_paso);
                if(isset($respuesta->redirect)){
                    return response()->json([
                        'validacion' => true,
                        'redirect' => $respuesta->redirect
                    ]);
                }
                $respuesta->redirect = $etapa->finalizarPaso($prox_paso);
                if(isset($respuesta->redirect)){
                    return response()->json([
                        'validacion' => true,
                        'redirect' => $respuesta->redirect
                    ]);
                }
                $etapa->avanzar();
                //Job para indexar contenido cada vez que se avanza de etapa
                $this->dispatch(new IndexStages($etapa->Tramite->id));
                $respuesta->redirect = '/etapas/ver/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables()) - 1);
            } else {
                $respuesta->redirect = '/etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1) . ($qs ? '?' . $qs : '');
            }
        }

        return response()->json([
            'validacion' => true,
            'redirect' => $respuesta->redirect
        ]);
    }

    public function asignar($etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id) {
            echo 'Etapa ya fue asignada.';
            exit;
        }

        if (!$etapa->canUsuarioAsignarsela(Auth::user()->id)) {
            echo 'Usuario no puede asignarse esta etapa.';
            exit;
        }

        $etapa->asignar(Auth::user()->id);

        return redirect('etapas/inbox');
    }

    public function ejecutar_fin(Request $request, $etapa_id)
    {

        if(session()->has('redirect_url')){
            return redirect()->away(session()->get('redirect_url'));
        }

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $proceso_id= $etapa->Tarea->proceso_id; 
        $proceso = Doctrine::getTable('Proceso')->find($etapa->Tarea->proceso_id);
         
        if ( $etapa->Tarea->acceso_modo != 'anonimo' && $etapa->usuario_id != Auth::user()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

     //dd($etapa->id);

        $data = \Cuenta::configSegunDominio();
        $data['extra']['analytics'] = null;
        $data['tareas_proximas'] = $etapa->getTareasProximas();
            $extra_etapa = json_decode($etapa->extra, true);
            $extra_etapa = ($extra_etapa === null ) ? [] : $extra_etapa;
            if(!isset($extra_etapa['mostrar_hit'])){ //isset ||$extra_etapa['mostrar_hit']
                $busca_evento_analytics = DB::table('etapa') //Buscando el evento analytics por tarea iniciada
                    ->select('accion.id',
                        'accion.tipo',
                        'tarea.nombre as tarea_nombre',
                        'tarea.es_final as es_tarea_final',
                        'accion.nombre',
                        'accion.extra',
                        'evento.regla'
                    )
                    ->join('tarea','etapa.tarea_id', '=','tarea.id')
                    ->join('evento', 'evento.tarea_id', '=', 'tarea.id')
                    ->join('accion','evento.accion_id','=', 'accion.id')
                    ->where('etapa.id', $etapa->id)->where('accion.tipo','=','evento_analytics')->get();

                Log::info("###Lo que trae busca_analyitics : " . $busca_evento_analytics);

                if (count($busca_evento_analytics) > 0) {
                    $data['extra']['analytics'] = json_decode($busca_evento_analytics[0]->extra, true);   
                   $data['extra']['es_final'] = $busca_evento_analytics[0]->es_tarea_final ? 1: 0;
                    //$data['extra']['es_final'] =1 ? 0;
                    $extra_hit =  $data['extra']['analytics'];
                    $extra_etapa['analytics']=$extra_hit;
                }
                $extra_etapa['mostrar_hit'] = false;
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
            }else if( in_array($data['tareas_proximas']->estado, ['standby', 'completado', 'sincontinuacion', 'pendiente'])) {
              //  $data['extra']['es_final'] = 'si'; 
                $busca_evento_analytics = DB::table('etapa') //Buscando el evento analytics por tarea iniciada
                    ->select('accion.id',
                        'accion.tipo',
                        'tarea.nombre as tarea_nombre',
                        'tarea.es_final as es_tarea_final',
                        'accion.nombre',
                        'accion.extra',
                        'evento.regla'
                    )
                    ->join('tarea','etapa.tarea_id', '=','tarea.id')
                    ->join('evento', 'evento.tarea_id', '=', 'tarea.id')
                    ->join('accion','evento.accion_id','=', 'accion.id')
                    ->where('etapa.id', $etapa->id)->where('accion.tipo','=','evento_analytics')->get();
                // dd($busca_evento_analytics); h
                if (count($busca_evento_analytics) > 0) {
                    $data['extra']['es_final'] = $busca_evento_analytics[0]->es_tarea_final ? 1: 0;
                  //  $data['extra']['es_final'] = $busca_evento_analytics[0]->es_tarea_final ? 'si':'no';
                    $data['extra']['analytics'] = json_decode($busca_evento_analytics[0]->extra, true);
                    // TOOD: Marcar para no mostrar nunca mas
                    $extra_hit =  $data['extra']['analytics'];
                    $extra_etapa['analytics']=$extra_hit;
                    $extra_etapa['mostrar_hit'] = true;
                } else {
                    $extra_etapa['mostrar_hit'] = false;
                }

                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
            }
        // dd(in_array($data['tareas_proximas']->estado, ['standby', 'completado', 'sincontinuacion', 'pendiente']));
        $data['etapa'] = $etapa;
        
       // $data['idrnt'] = $idrnt;
       // $data['idcha'] = $idcha;
        $data['qs'] = $request->getQueryString();

        $data['sidebar'] = Auth::user()->registrado ? 'inbox' : 'disponibles';
        $data['title'] = $etapa->Tarea->nombre;
        $template = $request->input('iframe') ? 'template_iframe' : 'template_newhome';

      
        
         //fin de evento unico en etapa
       
        return view('stages.ejecutar_fin', $data);
    }

    public function ejecutar_fin_form(Request $request, $etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ( $etapa->Tarea->acceso_modo != 'anonimo' && $etapa->usuario_id != Auth::user()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        // $etapa->avanzar($request->input('usuarios_a_asignar'));
        try {
            // $agenda = new AppointmentController();
            // $appointments = $agenda->obtener_citas_de_tramite($etapa_id);
            // if (isset($appointments) && is_array($appointments) && (count($appointments) >= 1)) {
            //     $json = '{"ids":[';
            //     $i = 0;
            //     foreach ($appointments as $item) {
            //         if ($i == 0) {
            //             $json = $json . '"' . $item . '"';
            //         } else {
            //             $json = $json . ',"' . $item . '"';
            //         }
            //         $i++;
            //     }
            //     $json = $json . ']}';
            //     $agenda->confirmar_citas_grupo($json);
            //     $etapa->avanzar($request->input('usuarios_a_asignar'));
            // } else {
            //     $etapa->avanzar($request->input('usuarios_a_asignar'));
            // }
            $etapa->avanzar($request->input('usuarios_a_asignar'));

            $proximas = $etapa->getTareasProximas();



            Log::info("###Id etapa despues de avanzar: " . $etapa->id);
            Log::info("###Id tarea despues de avanzar: " . $etapa->tarea_id);
             Log::info("###MARCA FIN PARA GA,estado completado: " . $etapa->pendiente);
            $cola = new \ColaContinuarTramite();
            $tareas_encoladas = $cola->findTareasEncoladas($etapa->tramite_id);
            if ($proximas->estado === 'pendiente') {
                Log::debug("pendiente");
                foreach ($proximas->tareas as $tarea) {
                    Log::debug('Ejecutando continuar de etapa ' . $tarea->id . " en trámite " . $etapa->tramite_id);
                    $etapa->ejecutarColaContinuarTarea($tarea->id, $tareas_encoladas);
                }
            }
        } catch (Exception $err) {
            Log::error($err->getMessage());
        }

        //Job para indexar contenido cada vez que se avanza de etapa
        $this->dispatch(new IndexStages($etapa->Tramite->id));
        if ($request->input('iframe')) {
            return response()->json([
                'validacion' => true,
                'redirect' => route('stage.ejecutar_exito')
            ]);
        }
        
        //redirigir a la siguiente etapa sin pasar por el home ni la bandeja de entrada si el usuario asigado es el mismo
        $usuario_ultima_etapa = $etapa->Tramite->getEtapasActuales()->get(0)->usuario_id;
        $etapa_actual = $etapa->Tramite->getEtapasActuales()->get(0)->id;
        if(Auth::user()->id == $usuario_ultima_etapa){
            return response()->json([
                'validacion' => true,
                'redirect' => route('stage.run', [$etapa_actual]),
            ]);
        }else{
            return response()->json([
                'validacion' => true,
                'redirect' => route('home'), 
            ]);
        }
    }

    //Pagina que indica que la etapa se completo con exito. Solamente la ven los que acceden mediante iframe.
    public function ejecutar_exito()
    {
        $data = \Cuenta::configSegunDominio();
        $data['title'] = 'Etapa completada con éxito';

        return view('backend.stages.ejecutar_exito', $data);
    }

    public function ver($etapa_id, $secuencia = 0)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->Tarea->acceso_modo != 'anonimo' && $etapa->usuario_id != Auth::user()->id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $paso = $etapa->getPasoEjecutable($secuencia);

        $data = \Cuenta::configSegunDominio();
        $data['etapa'] = $etapa;
        $data['paso'] = $paso;
        $data['secuencia'] = $secuencia;

        $data['sidebar'] = 'participados';
        $data['title'] = 'Historial - ' . $etapa->Tarea->nombre;
        //$data['content'] = 'etapas/ver';

        return view('stages.view', $data);
    }

    public function descargar($tramites)
    {
        $data['tramites'] = $tramites;
        return view('stages.download', $data);
    }

    public function descargar_form(Request $request)
    {
        if (!Cuenta::cuentaSegunDominio()->descarga_masiva) {
            $request->session()->flash('error', 'Servicio no tiene permisos para descargar.');
            return redirect()->back();
        }

        if (!Auth::user()->registrado) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }
        $tramites = $request->input('tramites');
        $opcionesDescarga = $request->input('opcionesDescarga');
        $tramites = explode(",", $tramites);
        $ruta_documentos = public_path('uploads/documentos/');
        $ruta_generados = public_path('uploads/datos/');
        $ruta_tmp = public_path('uploads/tmp/');
        $fecha_obj = new \DateTime();
        $fecha = date_format($fecha_obj, "Y-m-d");
        $time_stamp = date_format($fecha_obj, "Y-m-d_His");

        $tipoDocumento = "";
        switch ($opcionesDescarga) {
            case 'documento':
                $tipoDocumento = ['documento'];
                break;
            case 'dato': // s3 son archivos subidos al igual que los dato
                $tipoDocumento = ['dato', 's3'];
                break;   
        }

        // Recorriendo los trámites
        $zip_path_filename = public_path($ruta_tmp).'tramites_'.$time_stamp.'.zip';
        $files_list = ['documento' => [], 'dato'=> [], 's3' => []];
        $non_existant_files = [];
        $docs_total_space = 0;
        $s3_missing_file_info_ids = [];
        $cuenta = null;
        foreach ($tramites as $t) {
            if (empty($tipoDocumento)) {
                $files = Doctrine::getTable('File')->findByTramiteId($t);
            } else {
                $files = \Doctrine_Query::create()->from('File f')->where('f.tramite_id=?', $t)->andWhereIn('tipo', $tipoDocumento)->execute();
            }
            $dir_tramite_id = NULL;
            if (count($files) > 0) {
                // Recorriendo los archivos
                foreach ($files as $f) {
                    $tr = Doctrine::getTable('Tramite')->find($t);
                    $participado = $tr->usuarioHaParticipado(Auth::user()->id);
                    if (!$participado) {
                        $request->session()->flash('error', 'Usuario no ha participado en el trámite.');
                        return redirect()->back();
                    }
                    if( (is_null($cuenta)|| $cuenta === FALSE) && $tr !== FALSE){
                        $cuenta = $tr->Proceso->Cuenta;
                    }
                    $nombre_documento = $tr->id;
                    $tramite_nro = '';
                    foreach ($tr->getValorDatoSeguimiento() as $tra_nro) {
                        if ($tra_nro->valor == $f->filename) {
                            $nombre_documento = $tra_nro->nombre;
                        }
                        if ($tra_nro->nombre == 'tramite_ref') {
                            $tramite_nro = $tra_nro->valor;
                        }
                    }

                    $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                    $tramite_nro = str_replace(" ", "", $tramite_nro);
                     
                    if (empty($nombre_documento)){
                        continue;
                    }
                    if ($f->tipo == 'documento') {
                        $ruta_base = $ruta_documentos;
                    } elseif ($f->tipo == 'dato') {
                        $ruta_base = $ruta_generados;
                    }else if($f->tipo == 's3'){
                        $ruta_base = 's3';
                    }


                    //verificar el nombre del archivo para obtener la etapa y verificar el nivel de acceso de la tarea
                    $tarea = DB::table('etapa')
                            ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo')
                            ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                            ->leftJoin('dato_seguimiento', 'etapa.id', '=', 'dato_seguimiento.etapa_id')
                            ->leftJoin('tramite','etapa.tramite_id', '=', 'tramite.id')
                            ->where('tramite.id',(int)$f->tramite_id)
                            ->where('dato_seguimiento.valor','LIKE', '%'.$f->filename.'%')
                            ->first();

                    $path = $ruta_base . $f->filename;
                    $proceso_nombre = str_replace(' ', '_', $tr->Proceso->nombre);
                    $proceso_nombre = \App\Helpers\FileS3Uploader::filenameToAscii($proceso_nombre);
                    $directory = "{$proceso_nombre}/{$tr->id}/{$f->tipo}";
                    if( $f->tipo == 's3' ){
                        $extra = $f->extra;
                        if( ! $extra ){
                            $s3_missing_file_info_ids[] = $f->id;
                        }else{
                            $docs_total_space += $extra->s3_file_size;
                            $files_list[$f->tipo][] = ['file_name' => $f->filename,
                                                       'bucket' => $extra->s3_bucket,
                                                       'file_path' => $extra->s3_filepath,
                                                       'tramite' => $tr->Proceso->nombre,
                                                       'proceso' => $directory,
                                                       'tramite_id' => $tr->id,
                                                       'directory' => $directory];
                        }
                    }elseif(file_exists($path) && !is_null($tarea)){

                        $nice_directory = 'generados';
                        if($f->tipo=='dato'){
                            switch ($tarea->acceso_modo){
                                case 'grupos_usuarios':
                                    $nice_directory = 'subidos_registrado';
                                    break;
                                case 'registrados':
                                    $nice_directory = 'subidos_registrado';
                                    break;
                                case 'claveunica':
                                    $nice_directory = 'subidos_claveunica';
                                    break;
                                case 'publico':
                                    $nice_directory = 'subidos_anonimo';
                                    break;
                                case 'anonimo':
                                    $nice_directory = 'subidos_anonimo';
                                    break;
                                
                            }
                        }

                        $docs_total_space += filesize($path);
                        $files_list[$tr->id][] = [
                            'ori_path' => $path,
                            'nice_name' => $f->filename,
                            'directory' => $directory,
                            'tramite_id' => $tr->id,
                            'tramite' => $tr->Proceso->nombre,
                            'nice_directory' => $nice_directory
                        ];
                    }else{
                        $non_existant_files[] = $path;
                    }
                }
            }
        }

        $max_space_before_email_link = env('DOWNLOADS_FILE_MAX_SIZE', 500 * 1024 * 1024);
        if( ( array_key_exists('s3', $files_list) && count($files_list['s3']) > 0 )
                || $docs_total_space > $max_space_before_email_link ) {
            $running_jobs = Job::where('user_id', Auth::user()->id)
                               ->whereIn('status', [Job::$running, Job::$created])
                               ->where('user_type', Auth::user()->user_type)
                               ->count();
            if($running_jobs >= env('DOWNLOADS_MAX_JOBS_PER_USER', 1)){
                $request->session()->flash('error',
                    "Ya tiene trabajos en ejecuci&oacute;n pendientes, por favor espere a que este termine.");
                return redirect()->back();
            }
            $http_host = request()->getSchemeAndHttpHost();

            if(strpos(url()->current(), 'https://') === 0){
                $http_host = str_replace('http://', 'https://', $http_host);
            }

            $email_to = Auth::user()->email;
            $validator = \Validator::make(
                [ 'email' => $email_to ], [ 'email' => 'required|email' ]
            );
            if ($validator->fails()) {
                if( empty( $email_to ) ){
                    $msg = 'No posee una direcci&oacute;n de correo electr&oacute;nico configurada.';
                }else{
                    $msg = 'Su direcci&oacute;n de correo electr&oacute;nico: '.$email_to.' no es v&aacute;lida.';
                }
                $request->session()->flash('error', $msg);
                return redirect()->back();
            }
            $name_to = Auth::user()->nombres;
            $email_subject = 'Enlace para descargar archivos.';
            $this->dispatch(new FilesDownload(Auth::user()->id, Auth::user()->user_type, $files_list, $email_to,
                                              $name_to, $email_subject, $http_host, $cuenta, $dir_tramite_id, $tramites));

            $request->session()->flash('success', "Se enviar&aacute; un enlace para la descarga de los documentos una vez est&eacute; listo a la direcci&oacute;n: {$email_to}");
            return redirect()->back();
        }

        $files_to_compress_not_empty = false;
        foreach($files_list as $tipo => $f_array ){
            if( count($files_list[$tipo]) > 0 ){
                $files_to_compress_not_empty = true;
                break;
            }
        }
        if($files_to_compress_not_empty){
            foreach($tramites as $tramite){
                $new_name = date('Ymdhis').'-'.$tramite.'.zip';
                $zip_name = public_path('uploads/tmp/async_downloader').DIRECTORY_SEPARATOR.$new_name;
                
                $zip = new ZipArchive;
                $opened = $zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                foreach($files_list[$tramite] as $file ){
                
                    $out_dir = public_path('uploads/tmp/async_downloader').DIRECTORY_SEPARATOR.date('Ymdhis').'-'.$f_array[0]['tramite_id'];
                    $dir = "{$out_dir}/{$file['nice_directory']}";
                    if( ! file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    
                    $ori_full_path = $file['ori_path'];
                    $f = $dir.DIRECTORY_SEPARATOR.$file['nice_name'];
                    if( ! copy($ori_full_path, $f) ){
                        $errors_copying[] = $file;
                    }else{
                        $copied_files[] = $f;
                    }
                    $source = realpath($out_dir);
                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::LEAVES_ONLY);
                    $start_last_dir = strrpos($source, DIRECTORY_SEPARATOR) + 1;
                    $maindir = substr($source, $start_last_dir);
                    $source = substr($source, 0, $start_last_dir );
                    $source_long_directories = strlen($source) - 1;
                    $source_long_files = strlen($source);
                    $omitted_directories = ['.', '..'];
                    foreach ($files as $file){
                        if( in_array($file->getFilename(), $omitted_directories) ){
                            continue;
                        }    
                        $file = $file->getRealPath();        
                        if (is_dir($file) === TRUE){
                            $zip->addEmptyDir(substr($file, $source_long_directories));
                        }else if (is_file($file) === TRUE ){ //&& file_exists($file)){
                            $f_name_dest = substr($file, $source_long_files);
                            try{
                                $zip->addFile($file, $f_name_dest);
                            }catch(\Exception $e){
                                $this->failed($e);
                            }
                            $zip->setCompressionName($f_name_dest, \ZipArchive::CM_STORE);
                        }
                    }
                }
                $zip->close();
            }
            // Remove directorio y archivos
            $master_directory = $out_dir;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($master_directory, \RecursiveIteratorIterator::SELF_FIRST)
            );
            $dirs_delete = [];
            foreach ($iterator as $info) {
                if( ! in_array($info->getPath(), $dirs_delete))
                    $dirs_delete[] = $info->getPath();
            }
            
            rsort($dirs_delete);
            foreach($dirs_delete as $dir){
                foreach($copied_files as $file){
                    $for_unlink = $file;
                    if( ! empty($for_unlink) && strpos($for_unlink, '..') === FALSE && trim($for_unlink) !== '.' && file_exists($for_unlink) ){
                        unlink($for_unlink);
                    }
                }
                if(file_exists($dir) && $this->is_dir_empty($dir) ){
                    @rmdir($dir);
                }else{
                    $error = "Directorio temporal '{$dir}' no existe o no esta vacio. No se puede borrar.";
                    Log::error($error); 
                }
            }
            if(count($non_existant_files)> 0)
                $request->session()->flash('warning', 'No se pudieron encontrar todos los archivos requeridos para descargar.');
            // archivo $zip tiene al menos 1 archivo
            return response()
                ->download($zip_name, $new_name, ['Content-Type' => 'application/octet-stream'])
                ->deleteFileAfterSend(true);
        }else{
            $request->session()->flash('error', 'No se encontraron archivos para descargar.');
            return redirect()->back();
        }
    }

    public function descargar_archivo(Request $request, $user_id, $job_id, $file_name){
        if (!Cuenta::cuentaSegunDominio()->descarga_masiva) {
            $request->session()->flash('error', 'Servicio no tiene permisos para descargar.');
            return redirect()->back();
        }

        if (!Auth::user()->registrado) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }

        if (Auth::user()->id != $user_id) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }

        // validar que user_id y job_id sean enteros

        $job_info = Job::where('user_id', Auth::user()->id)
                        ->where('id', $job_id)
                        ->where('filename', $file_name)->first();

        $full_path = $job_info->filepath.DIRECTORY_SEPARATOR.$job_info->filename;
        if(file_exists($full_path)){
            $job_info->downloads += 1;
            $job_info->save();

            $time_stamp = Carbon::now()->format("Y-m-d_His");
            return response()
                ->download($full_path, 'tramites_'.$time_stamp.'.zip', ['Content-Type' => 'application/octet-stream'])
                ->deleteFileAfterSend(true);
        }else{
            abort(404);
        }
    }

    public function estados($tramite_id)
    {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);
        $datos = $tramite->getValorDatoSeguimientoAll();
        foreach ($datos as $dato) {
            if ($dato->nombre == 'historial_estados') {
                $historial = $dato->valor;
            }
        }
        $data['historial'] = $historial;
        return view('stages.estados',$data);
    }

    public function validar_campos_async(Request $request){
        if( ! $request->has('campos')){
            return response()->json( [ 'status' => FALSE, 'messages' => NULL, 'code'=> -1] );
        }
        $campos = $request->input('campos');

        $data = [];
        $rules = [];
        $nicenames = [];
        $data_columnas = [];
        foreach($campos as $campo){
            if(! array_key_exists('campo_id', $campo) ){
                continue;
            }
            $campo_id = $campo['campo_id'];
            $campo_base = Campo::find($campo_id);

            $c_extra = json_decode($campo_base['extra'], TRUE);

            $columna = $campo['columna'];
            $columnas = $c_extra['columns'];
            if( ! array_key_exists('validacion', $columnas[$columna])){
                continue;
            }
            $validacion = $columnas[$columna]['validacion'];
            $etiqueta = $campo['etiqueta'];

            $data[] = $campo['valor'];
            $rules[] = str_replace(' ', '', $validacion);
            $nicenames[] = "<b>$etiqueta</b>" ;
            $data_columnas[] = $columna;
        }

        $validator = \Validator::make(
            $data, $rules, [], $nicenames
        );

        if( $validator->fails() ){
            return response()->json( [
                'status' => FALSE,
                'messages' => $validator->messages(),
                'columnas' => $data_columnas,
                'code'=>1
            ] );
        }

        return response()->json( [ 'status' => TRUE, 'code' => 0, 'columnas' => $data_columnas ] );
    }

    public function saveForm(Request $request,$etapa_id){

        //Se guardan los datos del formulario en la etapa correspondiente
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $input = $request->all();
        $protected_vars = array('_token','_method','secuencia','btn_async');
        foreach($input as $key => $value){
            if($key=='secuencia')
                $paso = $etapa->getPasoEjecutable($value);
            if($key=='btn_async'){
                $campo = Doctrine_Query::create()
                    ->from("Campo")
                    ->where("id = ?", $value)
                    ->fetchOne();
            }
            if(!in_array($key,$protected_vars) && !is_null($value)){
                $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa_id);
                if (!$dato)
                    $dato = new \DatoSeguimiento();
                $dato->nombre = $key;
                $dato->valor = $value;

                if (!is_object($dato->valor) && !is_array($dato->valor)) {
                    if (preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)) {
                        $dato->valor = preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i", "$3-$2-$1", $dato->valor);
                    }
                }
                $dato->etapa_id = $etapa_id;
                $dato->save();
            }
        }

        //se ejecutan acciones durante el paso
        $etapa->ejecutarPaso($paso,$campo);

        //se genera respuesta con los datos que la etapa tiene hasta el momento
        $datos = DatoSeguimiento::where('etapa_id',$etapa->id)
                ->select('nombre','valor')
                ->get();
        $response = $datos->toArray();

        //se genera arreglo con los datos procesados en la etapa
        $array_datos = [];
        foreach ($datos as $dato) {
            $array_datos[$dato->nombre] = $dato->valor;
        }

        //se obtienen todos los campos del formulario que está consultando
        $formulario_id = $campo->Formulario->id;
        $campos = Campo::where('formulario_id',$formulario_id)->get();

        //se obtienen todos los campos del formulario que está consultando y a la vez los nuevos hidden si es que aplica
        $campos = Campo::where('formulario_id',$formulario_id)->get();

        //se recorren los campos del formulario para verificar que existan coincidencias con los datos obtenidos en la etapa
        foreach($campos as $campo){

            //en caso que no exista valor por defecto, continua el recorrido sin agregar datos al arreglo
            if( empty($campo->valor_default) ){
                continue;
            }

            $regla = new \Regla($campo->valor_default);
            $var = $regla->getExpresionParaOutput($etapa->id);
            $response[] = ['nombre'=>$campo->nombre, 'valor' => $var ];

            //si existe el campo valor por defecto dentro de los datos de la etapa los agrega a la respuesta para setear los datos
            //se setea como valor por defecto(para los que tienen) el valor del dato para el campo del formulario
            /*if(array_key_exists($var, $array_datos)){
               $response[] = ['nombre'=>$campo->nombre, 'valor' =>$array_datos[$var] ];
            }*/

        }

        return response()->json($response);
    }

    private function is_dir_empty($dir){
        $files = scandir($dir);
        foreach($files as $file){
            if($file !== '.' && $file !== '..'){
                return false;
            }
        }

        return true;
    }

    public function ejecutar_error(Request $request, $etapa_id){
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $data = \Cuenta::configSegunDominio();
        $data['extra']['analytics'] = null;
        $data['tareas_proximas'] = $etapa->getTareasProximas();
        $extra_etapa = json_decode($etapa->extra, true);
        $extra_etapa = ($extra_etapa === null ) ? [] : $extra_etapa;
        $data['etapa'] = $etapa;
        $data['qs'] = $request->getQueryString();
        $data['sidebar'] = Auth::user()->registrado ? 'inbox' : 'disponibles';
        $data['title'] = $etapa->Tarea->nombre;
        $template = $request->input('iframe') ? 'template_iframe' : 'template_newhome';
        return view('stages.errores', $data);
    }

    public function ejecutar_carga_masiva($tramite_id)
    {
        $maxIdFileXLSX = DB::select('select max(id) as mxid from file where tipo="dato" and tramite_id = ?',[$tramite_id]);
        $fileNameXLSX = DB::select('select filename from file where id = ?',[$maxIdFileXLSX[0]->mxid]);
        $pathFileNameXLSX = public_path('uploads/datos')."/".$fileNameXLSX[0]->filename;
        $llave = "SCEmpresa";
        $files_eliminados = \App\Models\File::where('llave',$llave)->where('tramite_id',$tramite_id)->delete();
        try{
            Excel::load($pathFileNameXLSX, function ($reader) use($tramite_id,$llave) {

                $results = $reader->get();
                $cantidad_registros = count($results);
    
                /**
                 * $reader->get() nos permite obtener todas las filas de nuestro archivo
                 */
                $FechaHoy = date('Ymd');
                foreach ($results as $key => $row) {
                    $FechaHoy = date('YmdHisu');
                    $FechaHoy = sha1($FechaHoy).$FechaHoy;
                    $col_headers = array_keys($row->toArray());
                    $rut = trim(str_replace(" ","",$row[$col_headers[1]]));
                    $rut = trim(str_replace(".","",$rut));
                    $ciudadano = [
                        'nombre' => $row[$col_headers[0]],
                        'rut' => $rut,
                        'fecha_nacimiento' => \Carbon\Carbon::parse($row[$col_headers[2]])->format('d-m-Y'),
                        'domicilio' => $row[$col_headers[3]],
                        'patente_automovil' => $row[$col_headers[4]]
                        ];
    
                    /** Una vez obtenido los datos de la fila procedemos a registrarlos */
                    if (!empty($ciudadano)) {
                        // Campos Tabla file
                        $campoFileName   =  uniqid()."-".$ciudadano['rut'];
                        $campoTipo       = "documento";
                        $campoLlave      = $llave;
                        $campoLlaveFirma = $rut;
                        $campoValidez    = "15";
                        $campoTramiteId  = $tramite_id;
                        $campoCreateAt   = date('Y-m-d H:i:s');
                        $campoExtra      = json_encode($ciudadano);
                        DB::statement('INSERT INTO file (filename,tipo,llave,llave_firma,validez,tramite_id,created_at,extra) 
                                       VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)', [$campoFileName, $campoTipo, $campoLlave
                                                 , $campoLlaveFirma, $campoValidez, $campoTramiteId, $campoCreateAt, $campoExtra,]);
                        DB::commit();
                    }
                }
            });
        }catch(\Exception $e){
            $files_eliminados = \App\Models\File::where('llave',$llave)->where('tramite_id',$tramite_id)->delete();            
            return response()->json(['estado' => 'ERROR', 'mensaje' => 'El archivo excel presenta problemas de formato'], 500);
        }

        return response()->json(['estado' => 'OK'], 200);

    }

}
