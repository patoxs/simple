<?php

use App\Models\Etapa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * @internal
 *
 * @param Etapa $e
 * @return String
 */
function getPrevisualization($e)
{
   $previsualizacion = '';
    if(!empty($e->previsualizacion))
    {
        $r = new Regla($e->previsualizacion);
        $previsualizacion = $r->getExpresionParaOutput($e->etapa_id);
    }
    return $previsualizacion;
}

/**
 * @internal Devuelve el dato seguimiento de una etapa de acuerdo al nombre ($tipo) buscado
 *
 * @param Etapa $e
 * @param String $tipo
 * @return $tramite_nro
 */
function getValorDatoSeguimiento($e, $tipo)
{
    $etapas = $e->tramite->etapas;
    $tramite_nro = '';
    foreach ($etapas as $etapa )
    {
        foreach($etapa->datoSeguimientos as $dato)
        {
            if ($dato->nombre == $tipo) {
                $tramite_nro = $dato->valor == 'null' ? '' : json_decode('"'.str_replace('"','',$dato->valor).'"');
            }
        }
    }
    return $tramite_nro != '' ? $tramite_nro : $e->tramite->proceso->nombre;
}

/**
 * @internal Retorna la cuenta en la que se encuentra un usuario del sistema
 *
 * @return void
 */
function getCuenta()
{
    return \Cuenta::cuentaSegunDominio()->toArray();
}

/**
 * @internal Devuelve el total de tramites sin asignar de acuerdo a los grupos de usuario del usuario logueado
 *
 * @return Int count(Etapas)
 */
function getTotalUnnasigned()
{
    $c=0;
    if (!Auth::user()->open_id)
    {
        $grupos = Auth::user()->grupo_usuarios()->pluck('grupo_usuarios_id');
        $cuenta=\Cuenta::cuentaSegunDominio();
        $etapas =  Etapa::select('etapa.*')
        ->whereNull('etapa.usuario_id')
        ->join('tarea', function($q) use ($grupos){
            $q->on('etapa.tarea_id','=', 'tarea.id');
        })
        ->join('proceso', function($q){
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
        ->whereHas('tramite')
        ->get();

        foreach($etapas as $etapa)
        {
            if(puedeVisualizarla($etapa))
            {
                $c++;
            }
        }
    }
    return $c;
}

/**
 * @internal Retorna el total de tramites (etapas actuales) asignados al usuario logueado
 *
 * @return Int count(Etapas)
 */
function getTotalAssigned()
{
    $cuenta=\Cuenta::cuentaSegunDominio();
    return Etapa::where('etapa.usuario_id', Auth::user()->id)->where('etapa.pendiente', 1)
        ->whereHas('tramite', function($q) use ($cuenta){
            $q->whereHas('proceso', function($q) use ($cuenta){
                $q->where('cuenta_id', $cuenta->id);
            });
        })
        ->whereHas('tarea', function($q){
            $q->where('activacion', "si")
            ->orWhere(function($q)
            {
                $q->where('activacion', "entre_fechas")
                ->where('activacion_inicio', '<=', Carbon::now())
                ->where('activacion_fin', '>=', Carbon::now());   
            });
        })
    ->count();
}

/**
 * @internal Retorna el total de tramites (etapas ya realizadas) en los que el usuario logueado a participado 
 *
 * @return Int count(tramites)
 */
function getTotalHistory()
{
    $cuenta=\Cuenta::cuentaSegunDominio();
    return Etapa::where('pendiente', 0)
        ->whereHas('tramite', function($q) use ($cuenta){
            $q->whereHas('proceso', function($q) use ($cuenta){
                $q->where('cuenta_id', $cuenta->id);
            });
        })
        ->where('usuario_id', Auth::user()->id)
        ->count();
}
/**
 * @internal Revisa el path de la url y retorna 'active' si corresponde al dato pasado en la función 
 *
 * @param String $path
 * @return String 
 */
function linkActive($path)
{
    return Request::path() == $path ? 'active':'';
}

/**
 * @internal revisa si dentro del request viene el parametro sort y de acuerdo a su valor setea el orden,
 * luego retorna el link para el ordenamiento de la query
 * @param Request $request
 * @param String $sortValue
 * @return String link
 */
function getUrlSortUnassigned($request, $sortValue)
{
    $path = Request::path();
    $sort = $request->input('sort') == 'asc' ? 'desc':'asc';
    return  "/".$path.'?query='.$request->input('query').'&sortValue='.$sortValue."&sort=".$sort;
}

/**
 * @internal Formatea una fecha y en caso de ser el campo updated_at le agrega la hora, minutos y segundos
 *
 * @param Datetime $date
 * @param string $type
 * @return Carbon date 
 */
function getDateFormat($date, $type = 'update')
{
    return $date == null || !$date ? '' : Carbon::parse($date)->format('d-m-Y '.($type == 'update' ? 'H:i:s': ''));
}

/**
 * @internal Revisa si los tramites de las etapas tiene documentos y retornta true en caso de existir
 * @param Etapa $etapas
 * @return boolean
 */
function hasFiles($etapas)
{
    foreach ($etapas as $e)      
    {
        if($e->tramite->files->count() > 0)
        {
            return true;
        }
    }
    return false;
}

/**
 * @internal Obtiene la última etapa realizada de un tramite y retorna su fecha de termino
 *
 * @param Etapa $etapa
 * @return Datetime 
 */
function getLastTask($etapa)
{

    return $etapa->tramite->etapas()->where('pendiente', 0)->orderBy('id', 'desc')->first() ? 
    getDateFormat($etapa->tramite->etapas()->where('pendiente', 0)->orderBy('id', 'desc')->first()->ended_at) : 'N/A';
}

/**
 * @internal Valida si el usuario tiene permisos para visualizar una etapa de acuerdo al modo de acceso de la tarea relacionada 
 * y al grupo de usuario al que pertenece
 * @param Etapa $e
 * @return boolean
 */
function puedeVisualizarla($e)
{
    if ($e->tarea->acceso_modo == 'publico' || $e->tarea->acceso_modo == 'anonimo')
    {
        return true;
    }

    if ($e->tarea->acceso_modo == 'claveunica' && Auth::user()->open_id)
    {
        return true;
    }

    if ($e->tarea->acceso_modo == 'registrados' && Auth::user()->registrado)
    {
        return true;
    }
    if ($e->tarea->acceso_modo == 'grupos_usuarios') 
    {
        $r = new Regla($e->tarea->grupos_usuarios);
        $grupos_arr = explode(',', $r->getExpresionParaOutput($e->id));
        foreach (Auth::user()->grupo_usuarios as $g)
        {
            if (in_array($g->id, $grupos_arr))
            {
                return true;
            }
        }
    }
    return false;
}

/**
 * @internal Retorna el estado de un trámite
 *
 * @param Tramite $tramite
 * @return String
 */
function getEstadoTramite($tramite)
{
    return $tramite->pendiente ? 'Pendiente' : 'Completado';
}
/**
 * @internal Devuelve las tareas actuales de un tramite separadas por coma ','
 *
 * @param Tramite $tramite
 * @return String
 */
function getTareasActuales($tramite)
{
    return implode(',', $tramite->etapas()->where('pendiente', 1)->join('tarea', 'tarea.id', '=','etapa.tarea_id')->pluck('tarea.nombre')->toArray());
}
/**
 * @internal Devuelve las etapas de un tramite donde el usuario logueado a participado
 * @param Etapa $etapa
 * @return Etapa $etapas
 */
function getEtapasParticipadas($etapa)
{
    return $etapa->tramite->etapas()->where('usuario_id', Auth::user()->id)->get();
}

/**
 * @internal Devulve el valor dato seguimiento 'historial_estados' de un tramite 
 *
 * @param Tramite $tramite
 * @return String $tramite_nro
 */
function getDatoSeguimientoHistorial($tramite)
{
    $etapas = $tramite->etapas;
    $tramite_nro = '';
    foreach ($etapas as $etapa )
    {
        foreach($etapa->datoSeguimientos as $dato) 
        {
            if ($dato->nombre ==  'historial_estados') {
                $tramite_nro = $dato->valor;
            }
        }
    }
    return $tramite_nro;
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

function unicode_decode($str) {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
}
