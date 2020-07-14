<?php

use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;

class ProcesoTable extends Doctrine_Table
{

    public function findProcesosDisponiblesParaIniciar($usuario_id, $cuenta = 'localhost', $orderby = 'id', $direction = 'desc')
    {
        $usuario = Doctrine::getTable('Usuario')->find($usuario_id);

        $cuenta = Doctrine::getTable('Cuenta')->find(Cuenta::cuentaSegunDominio()->id); // UsuarioBackendSesion::usuario()->cuenta_id);

        $query = Doctrine_Query::create()
            ->from('Proceso p, p.Cuenta c, p.Tareas t')
            ->where('p.activo = 1 AND t.inicial = 1')
            //Si el usuario tiene permisos de acceso
            //->andWhere('(t.acceso_modo="grupos_usuarios" AND u.id = ?) OR (t.acceso_modo = "registrados") OR (t.acceso_modo = "claveunica") OR (t.acceso_modo="publico")',$usuario->id)
            //Si la tarea se encuentra activa
            ->andWhere('1!=(t.activacion="no" OR ( t.activacion="entre_fechas" AND ((t.activacion_inicio IS NOT NULL AND t.activacion_inicio>NOW()) OR (t.activacion_fin IS NOT NULL AND NOW()>t.activacion_fin) )))')
            ->orderBy($orderby . ' ' . $direction);

        if ($cuenta != 'localhost')
            $query->andWhere('c.nombre = ?', $cuenta->nombre);

        $procesos = $query->execute();

        Log::debug('$cuenta->nombre [' . $cuenta->nombre . ']');

        // Chequeamos los permisos de acceso
        foreach ($procesos as $key => $p)
            if (!$p->canUsuarioListarlo($usuario_id))
                unset($procesos[$key]);

        return $procesos;
    }

    public function findProcesosDisponiblesParaIniciarByCategoria($usuario_id, $categoria_id, $cuenta = 'localhost', $orderby = 'id', $direction = 'desc')
    {

        $usuario = Doctrine::getTable('Usuario')->find($usuario_id);

        $query = Doctrine_Query::create()
            ->from('Proceso p, p.Cuenta c, p.Tareas t')
            ->where('t.inicial = 1 AND p.categoria_id = ' . $categoria_id)
            // Si el usuario tiene permisos de acceso
            // ->andWhere('(t.acceso_modo="grupos_usuarios" AND u.id = ?) OR (t.acceso_modo = "registrados") OR (t.acceso_modo = "claveunica") OR (t.acceso_modo="publico")',$usuario->id)
            // Si la tarea se encuentra activa
            ->andWhere('1!=(t.activacion="no" OR ( t.activacion="entre_fechas" AND ((t.activacion_inicio IS NOT NULL AND t.activacion_inicio>NOW()) OR (t.activacion_fin IS NOT NULL AND NOW()>t.activacion_fin) )))')
            ->orderBy($orderby . ' ' . $direction);

        if ($cuenta != 'localhost')
            $query->andWhere('c.nombre = ?', $cuenta->nombre);

        Log::debug('$cuenta->nombre [' . $cuenta->nombre . ']');

        $procesos = $query->execute();

        // Chequeamos los permisos de acceso
        foreach ($procesos as $key => $p)
            if (!$p->canUsuarioListarlo($usuario_id))
                unset($procesos[$key]);

        return $procesos;
    }

    public function findProcesosExpuestos($cuenta_id=false)
    {
        if ($cuenta_id && strlen($cuenta_id) > 0) {
            Log::info('Si tiene id');
            $sql = "select p.id, p.nombre, t.nombre as tarea, t.id as id_tarea, t.exponer_tramite, t.previsualizacion,c.id as id_cuenta, c.nombre_largo as nombre_cuenta from proceso p, tarea t, cuenta c where p.id = t.proceso_id and p.cuenta_id=c.id and t.exponer_tramite=1 and p.activo=1 and p.cuenta_id=" . $cuenta_id . ";";
        } else {
            Log::info('No tiene id');
            $sql = "select p.id, p.nombre, t.nombre as tarea, t.id as id_tarea, t.exponer_tramite, t.previsualizacion,c.id as id_cuenta, c.nombre_largo as nombre_cuenta from proceso p, tarea t, cuenta c where p.id = t.proceso_id and p.cuenta_id=c.id and t.exponer_tramite=1 and p.activo=1 ;";
        }
        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)
            ->fetchAll();
        return $result;
    }

    public function findVariblesFormularios($proceso_id, $tarea_id)
    {
        $sql = "select f.nombre as nombre_formulario, c.id as variable_id, c.nombre as nom_variables, "
            . "c.exponer_campo from proceso p,tarea t, paso pa, formulario f, campo c "
            . "where p.id=t.proceso_id and p.id=" . $proceso_id . ""
            . " and t.id=" . $tarea_id . " and t.id=pa.tarea_id and pa.formulario_id=f.id "
            . " and f.id=c.formulario_id and p.activo=1 and c.tipo not "
            . " in('title','paragraph','subtitle','recaptcha','javascript')  "
            . " GROUP by f.nombre, c.id, c.nombre, c.exponer_campo;";
        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)
            ->fetchAll();
        return $result;
    }

    public function findVariblesProcesos($proceso_id)
    {
        $sql = "select a.id as variable_id, a.nombre as nombre_variable, a.extra, a.exponer_variable, p.nombre as nombre_proceso from accion a, proceso p, tarea t where a.proceso_id=p.id and a.tipo='variable' and p.activo=1 and a.proceso_id=" . $proceso_id . " and p.id=t.proceso_id group by a.id, a.nombre, a.extra, a.exponer_variable, p.nombre;";
        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)->fetchAll();
        return $result;
    }

    public function updateVaribleExposed($varForm, $varPro, $proceso_id, $tarea_id)
    {
        $stmn = Doctrine_Manager::getInstance()->connection();
        if ($varForm) {
            $varForm = implode(",", $varForm);
            $sql1 = "update campo set exponer_campo=1 where id in (" . $varForm . ");";
            $result1 = $stmn->prepare($sql1);
            $result1->execute();
            $sql2 = "UPDATE  campo c
            INNER JOIN formulario f on c.formulario_id=f.id
            INNER JOIN proceso p on f.proceso_id = p.id
            INNER JOIN tarea t on t.proceso_id = p.id
            INNER JOIN paso pa on t.id=pa.tarea_id
            SET exponer_campo = 0
            WHERE  f.proceso_id=" . $proceso_id . " and p.activo=1 and c.tipo<>'title' and p.id=t.proceso_id and pa.formulario_id=f.id and t.id=" . $tarea_id . " and c.id not in (" . $varForm . ");";
            $result2 = $stmn->prepare($sql2);
            $result2->execute();
        } else {
            $sql2 = "UPDATE  campo c
            INNER JOIN formulario f on c.formulario_id=f.id
            INNER JOIN proceso p on f.proceso_id = p.id
            INNER JOIN tarea t on t.proceso_id = p.id
            INNER JOIN paso pa on t.id=pa.tarea_id
            SET exponer_campo = 0
            WHERE  f.proceso_id=" . $proceso_id . " and p.activo=1 and c.tipo<>'title' and p.id=t.proceso_id and pa.formulario_id=f.id and t.id=" . $tarea_id . ";";
            $result2 = $stmn->prepare($sql2);
            $result2->execute();

        }

        if ($varPro) {
            $varPro = implode(",", $varPro);
            $sql3 = "update accion set exponer_variable=1 where proceso_id=" . $proceso_id . " and id in (" . $varPro . ");";
            $result3 = $stmn->prepare($sql3);
            $result3->execute();
            $sql4 = "update accion set exponer_variable=0 where proceso_id=" . $proceso_id . " and id not in (" . $varPro . ");";
            $result4 = $stmn->prepare($sql4);
            $result4->execute();
        } else {
            $sql4 = "update accion set exponer_variable=0 where proceso_id=" . $proceso_id . ";";
            $result4 = $stmn->prepare($sql4);
            $result4->execute();
        }
    }

    public function findVaribleCallback($etapa_id)
    {
        $valor = 0;
        $sql = "select tramite_id from etapa where id=" . $etapa_id . ";";
        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)->fetchAll(PDO::FETCH_COLUMN);
        $sql2 = "select id as etapa_id from etapa where tramite_id=" . $result[0] . ";";
        $result2 = $stmn->execute($sql2)->fetchAll(PDO::FETCH_COLUMN);
        $etapa_id = implode(",", $result2);
        $sql3 = "select * from dato_seguimiento where etapa_id in (" . $etapa_id . ");";
        $result3 = $stmn->execute($sql3)->fetchAll();
        foreach ($result3 as $res) {
            if ($res['nombre'] == 'callback') {
                if (strlen($res['valor']) > 5) {
                    $valor = 1;
                }
            }
        }
        $salida['valor'] = $valor;
        $salida['data'] = $result3;
        return $salida;
    }

    public function findProceso($proceso_id)
    {
        $sql = "select * from proceso p where p.id=" . $proceso_id . ";";
        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)->fetchObject();
        return $result;
    }

    public function findCallbackProceso($id_proceso)
    {
        Log::info('En findCallbackProceso con id: ' . $id_proceso);
        $sql = "select t.id as id_tarea, t.nombre from accion a, tarea t, evento e where a.proceso_id = " . $id_proceso . " and a.tipo = 'callback' and a.id = e.accion_id and e.tarea_id = t.id";
        Log::info('SQL: ' . $sql);

        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)->fetchAll();

        Log::info('Result: ' . $this->varDump($result));

        return $result;
    }

    public function findTareasProceso($id_proceso)
    {
        Log::info('En findTareasProceso con id: ' . $id_proceso);
        $sql = "select t.id, t.nombre from tarea t where t.proceso_id = " . $id_proceso;
        Log::info('SQL: ' . $sql);

        $stmn = Doctrine_Manager::getInstance()->connection();
        $result = $stmn->execute($sql)->fetchAll();

        //log_message('info','Result: '.$this->varDump($result), FALSE);

        return $result;
    }
}