<?php

class EtapaTable extends Doctrine_Table {
    
    //busca las etapas que no han sido asignadas y que usuario_id se podria asignar
    public function findSinAsignar($usuario_id, $cuenta='localhost',$matches="0",$query="0",$limite=2000, $inicio=0){
        $usuario = \App\Helpers\Doctrine::getTable('Usuario')->find($usuario_id);
        if(!$usuario->open_id){
            $grupos =  DB::table('grupo_usuarios_has_usuario')
                ->select('grupo_usuarios_id')
                ->where('usuario_id',$usuario->id)
                ->get()
                ->toArray();
            $grupos = json_decode(json_encode($grupos), true);

            if($grupos){
                $tareas = DB::table('etapa')

                ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite.id',
                'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at','etapa.vencimiento_at')
                ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
                ->where('cuenta.nombre',$cuenta->nombre)
                ->whereIn('tarea.grupos_usuarios',$grupos)
               /* ->where(function($query) use($grupos){
                    foreach ($grupos as $grupo){
                        $query->orWhere('tarea.grupos_usuarios', $grupo['grupo_usuarios_id']);
                    }
                })*/
                ->whereNull('etapa.usuario_id')
                ->whereNull('tramite.deleted_at')
                ->limit($limite)
                ->offset($inicio)
                ->orderBy('etapa.tarea_id', 'ASC')
                ->get()->toArray();

                //se buscan etapas cuyas tareas que por nivel de acceso esten configuradas por nombre de grupo como variables @@
                $tareas_aa = DB::table('etapa')
                    ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite.id',
                        'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at','etapa.vencimiento_at')
                    ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                    ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                    ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                    ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
                    ->where('cuenta.nombre',$cuenta->nombre)
                    ->where('tarea.grupos_usuarios','LIKE','%@@%')
                    ->whereNull('etapa.usuario_id')
                    ->whereNull('tramite.deleted_at')
                    ->limit($limite)
                    ->offset($inicio)
                    ->orderBy('etapa.tarea_id', 'ASC')
                    ->get()->toArray();
                if(count($tareas_aa)){
                    foreach($tareas_aa as $key=>$t)
                        if(!$this->canUsuarioAsignarsela($usuario_id,$t->acceso_modo,$t->grupos_usuarios,$t->etapa_id))
                            unset($tareas_aa[$key]);

                    //se agregan al listado original de etapas solo las que cumplen los nombres de grupo como variables @@
                    foreach($tareas_aa as $tarea)
                        array_push($tareas,$tarea);
                }
            }
            else{
                $tareas = array();
            }
        }else{
            $tareas = array();
        }


        return $tareas;
    }

   public function findSinAsignarMatch($usuario_id, $cuenta='localhost',$matches="0",$query="0"){
       $usuario = \App\Helpers\Doctrine::getTable('Usuario')->find($usuario_id);
       if(!$usuario->open_id){
            $grupos =  DB::table('grupo_usuarios_has_usuario')
                        ->select('grupo_usuarios_id')
                        ->where('usuario_id',$usuario->id)
                        ->get()
                        ->toArray();
            $grupos = json_decode(json_encode($grupos), true);

            if($grupos){
                $tareas = DB::table('etapa')
                ->select('etapa.id as etapa_id','tarea.acceso_modo as acceso_modo','grupos_usuarios','tramite.id',
                'previsualizacion','proceso.nombre as p_nombre','tarea.nombre as t_nombre','etapa.updated_at','etapa.vencimiento_at')
                ->leftJoin('tarea', 'etapa.tarea_id', '=', 'tarea.id')
                ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                ->leftJoin('cuenta', 'proceso.cuenta_id', '=', 'cuenta.id')
                ->where('cuenta.nombre',$cuenta->nombre)
                ->whereIn('tarea.grupos_usuarios',[$grupos])
                ->whereIn('tramite.id',[$matches])
                ->whereNull('etapa.usuario_id')
                ->orderBy('etapa.tarea_id', 'ASC')
                ->get()->toArray();
            }
            else{
                $tareas = array();
            }
        }else{
            $tareas = array();
        }  
        return $tareas;
    }
    
    //busca las etapas donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id,$cuenta='localhost',$orderby='updated_at',$direction='desc',$matches="0",$buscar="0", $limite=0, $inicio=0){        
        $query=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Usuario u, e.Tramite t, t.Etapas hermanas, t.Proceso p, p.Cuenta c')
                ->select('e.*,COUNT(hermanas.id) as netapas, p.nombre as proceso_nombre, tar.nombre as tarea_nombre')
                ->groupBy('e.id') 
                //Si la etapa se encuentra pendiente y asignada al usuario
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                //Si la tarea se encuentra activa
                ->andWhere('1!=(tar.activacion="no" OR ( tar.activacion="entre_fechas" AND ((tar.activacion_inicio IS NOT NULL AND tar.activacion_inicio>NOW()) OR (tar.activacion_fin IS NOT NULL AND NOW()>tar.activacion_fin) )))')
                ->andWhere('t.deleted_at is NULL')
                ->limit($limite)
                ->offset($inicio)
                ->orderBy($orderby.' '.$direction);

        if($buscar){ 
            $query->whereIn('t.id',$matches);
        }

        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);
        
        return $query->execute();
    }

    public function findPendientesALL($usuario_id, $cuenta='localhost', $orderby='updated_at',$direction='desc',$matches="0",$buscar="0"){        
        $query=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso.Cuenta c, t.Etapas e, e.Usuario u')
                ->where('u.id = ?',$usuario_id)
                ->andWhere('e.pendiente=1')
                ->limit(3000)
                ->andWhere('t.deleted_at is NULL')
                ->orderBy('t.updated_at desc');
        
        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);        
        return $query->execute();
    }

    public function canUsuarioAsignarsela($usuario_id, $acceso_modo, $grupos_usuarios, $etapa_id)
    {
        static $usuario;

        if (!$usuario || ($usuario->id != $usuario_id)) {
            $usuario = \App\Helpers\Doctrine::getTable('Usuario')->find($usuario_id);
        }

        if ($acceso_modo == 'publico' || $acceso_modo == 'anonimo')
            return true;

        if ($acceso_modo == 'claveunica' && $usuario->open_id)
            return true;

        if ($acceso_modo == 'registrados' && $usuario->registrado)
            return true;

        if ($acceso_modo == 'grupos_usuarios') {
            $r = new Regla($grupos_usuarios);
            $grupos_arr = explode(',', $r->getExpresionParaOutput($etapa_id));
            foreach ($usuario->GruposUsuarios as $g)
                if (in_array($g->id, $grupos_arr))
                    return true;
        }

        return false;
    }
    
}
