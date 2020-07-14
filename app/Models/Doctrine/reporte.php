<?php

use \Illuminate\Support\Facades\Log;

class Reporte extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('campos');
        $this->hasColumn('proceso_id');
    }

    function setUp()
    {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Campo as Campos', array(
            'local' => 'id',
            'foreign' => 'reporte_id'
        ));
    }

    public function setCampos($campos)
    {
        $this->_set('campos', json_encode($campos));
    }

    public function getCampos()
    {
        return json_decode($this->_get('campos'));
    }

    public function getReporteAsMatrix($params = array(), $limit = null, $offset = null)
    {

        Log::debug('class reporte.php -> getReporteAsMatrix; $params: ' . json_encode($params));

        set_time_limit(600);

        $campos = $this->getCampos();

        $etiquetas = $campos;
        foreach ($etiquetas as &$etiqueta) {

            $etiqueta = explode(' ', $etiqueta);

            $var_etiqueta = explode("->", $etiqueta[0]);
            if (count($var_etiqueta) > 1) {
                unset($etiqueta[0]);
                unset($etiqueta[1]);
                $etiqueta = $var_etiqueta[1] . ' (' . implode(' ', $etiqueta) . ')';
            } else {
                unset($etiqueta[0]);
                unset($etiqueta[1]);

                $etiqueta = implode(' ', $etiqueta);
            }
        }

        $nombres_variables = $campos;
        foreach ($nombres_variables as &$variable) {
            $variable = explode(' ', $variable)[0];
            Log::debug('$variable: ' . $variable);
        }

        $header_variables = array_merge(array('id'), $nombres_variables);
        $header = array_merge(array('Tramite Id'), $etiquetas);
        Log::debug('$header_variables: ' . json_encode($header_variables));
        Log::debug('$header: ' . json_encode($header));
        $excel[] = $header;
        ini_set('memory_limit', '-1');
        $query = Doctrine_Query::create()
            ->from('Tramite t, t.Proceso p, t.Etapas e, e.DatosSeguimiento d')
            ->where('p.id = ?', $this->proceso_id)
            ->andWhere('t.deleted_at is NULL');

        foreach ($params as $p) {
            Log::debug('Parametro p: ' . $p);
            $query = $query->andWhere($p);
        }

        if ($limit) {
            $query = $query->limit($limit);
        }

        if ($offset) {
            $query = $query->offset($offset);
        }

        $tramites = $query->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')//Mostramos solo los que se han avanzado o tienen datos
        ->groupBy('t.id')
            ->orderBy('t.id desc')
            ->execute();

        foreach ($tramites as $t) {
            $etapas_actuales = $t->getEtapasActuales();
            $etapas_actuales_arr = array();
            $vtos_etapas_arr = array();
            $dias_vtos_arr = array();
            foreach ($etapas_actuales as $e) {
                $etapas_actuales_arr[] = $e->Tarea->nombre;
                $vtos_etapas_arr[] = $e->vencimiento_at ? $e->vencimiento_at : "N/A";
                if ($e->vencimiento_at && ($dias_vencidos = ceil((strtotime($e->vencimiento_at) - time()) / 60 / 60 / 24)) < 0) {
                    $dias_vtos_arr[] = abs($dias_vencidos);
                } else {
                    $dias_vtos_arr[] = 'N/A';
                }
            }
            $etapas_actuales_str = implode(',', $etapas_actuales_arr);
            $vtos_etapas_str = implode(',', $vtos_etapas_arr);
            $dias_vtos_str = implode(',', $dias_vtos_arr);

            $t = $t->toArray(false);

            $t['etapa_actual'] = $etapas_actuales_str;
            $t['vencimiento_at'] = $vtos_etapas_str;
            $t['dias_vencidos'] = $dias_vtos_str;
            $t['pendiente'] = $t['pendiente'] ? 'En curso' : 'Completado';

            $row = array();

            /*
             foreach($t as $key => $value) {
                 if (in_array($key, $header_variables)) {
                     if ($key == 'pendiente'){
                         $value = $value ? 'En curso' : 'Completado';
                     }

                     $colindex = array_search($key, $header_variables);
                     $row[$colindex] = $value;
                 }
             }
             */

            $datos = Doctrine_Core::getTable('DatoSeguimiento')->findByTramite($t['id']);

            foreach ($datos as $d) {
                $val = $d->valor;
                if (!is_string($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $t[$d->nombre] = strip_tags($val);
            }

            // Rellenamos con espacios en blanco los campos que no existen.
            foreach ($header_variables as $h) {
                $var_find = explode("->", $h);
                if (count($var_find) > 1) {
                    $row[] = isSet($t[$var_find[0]]) ? json_decode($t[$var_find[0]])->$var_find[1] : '';
                } else {
                    $row[] = isSet($t[$h]) ? $t[$h] : '';
                }
            }
            $excel[] = $row;
        }

        return $excel;
    }

    public function getHeaderVariables(){

        $campos = $this->getCampos();
        $etiquetas = $campos;
        foreach ($etiquetas as &$etiqueta) {

            $etiqueta = explode(' ', $etiqueta);
            $var_etiqueta = explode("->", $etiqueta[0]);
            if (count($var_etiqueta) > 1) {
                unset($etiqueta[0]);
                unset($etiqueta[1]);
                $etiqueta = $var_etiqueta[1] . ' (' . implode(' ', $etiqueta) . ')';
            } else {
                unset($etiqueta[0]);
                unset($etiqueta[1]);
                $etiqueta = implode(' ', $etiqueta);
            }
        }

        $nombres_variables = $campos;
        foreach ($nombres_variables as &$variable) {
            $variable = explode(' ', $variable)[0];
        }

        $header_variables = array_merge(array('id'), $nombres_variables);
        return $header_variables;
    }

    public function getArregloInicial(){

        $campos = $this->getCampos();
        $etiquetas = $campos;
        foreach ($etiquetas as &$etiqueta) {

            $etiqueta = explode(' ', $etiqueta);

            $var_etiqueta = explode("->", $etiqueta[0]);
            if (count($var_etiqueta) > 1) {
                unset($etiqueta[0]);
                unset($etiqueta[1]);
                $etiqueta = $var_etiqueta[1] . ' (' . implode(' ', $etiqueta) . ')';
            } else {
                unset($etiqueta[0]);
                unset($etiqueta[1]);

                $etiqueta = implode(' ', $etiqueta);
            }
        }

        $nombres_variables = $campos;
        foreach ($nombres_variables as &$variable) {
            $variable = explode(' ', $variable)[0];
        }

        $header_variables = array_merge(array('id'), $nombres_variables);
        $header = array_merge(array('Tramite Id'), $etiquetas);
        $excel[] = $header;
        return $excel;
    }
}
