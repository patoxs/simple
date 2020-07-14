<?php

use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author msilva
 */
class Swagger
{
    //put your code here
    /**
     * @param $formulario array con campos del formulario de entrada para iniciar el proceso
     * @return string
     * @version 1.0
     */
    public function generar_swagger($formulario, $id_tramite, $id_etapa)
    {
        $proceso = Doctrine::getTable('Proceso')->find($id_tramite);
        if (!isset($formulario)) {
            throw new ApiException("Formulario no se puede recuper", 404);
        }
        //Log::info("Input Generar Swagger: ".$this->varDump($formulario));
        Log::info("Id trámite: " . $id_tramite);
        Log::info("Id tarea: " . $id_etapa);

        $output_vars = $this->getOuputVarsSwagger($formulario, $id_tramite);

        if (isset($formulario) && count($formulario) > 0) {
            //Log::info("Formulario recuperado: ".$this->varDump($formulario));
            $data_entrada = "";

            $form = $formulario[0];
            $campos = $form["form"];

            foreach ($campos["campos"] as $campo) {
                Log::debug($campo['nombre'] . " -> " . $campo['direccion']);
                if ($campo['direccion'] == 'OUT') {
                    continue;  //Se debe especificar directamente si es de salida. 
                }
                //Campo tipo file será tratado como string asumiendo que el archivo viene en base64
                if ($data_entrada != "")
                    $data_entrada .= ",";

                if ($campo["tipo"] == "string") {
                    $data_entrada .= "\"" . $campo["nombre"] . "\": {\"type\": \"string\",\"format\": \"".implode(",",$campo["format"])."\",\"description\": \"".$campo["descripcion"]."\"}";
                } else if ($campo["tipo"] == "base64") {
                    $data_entrada .= "\"" . $campo["nombre"] . "\":" . json_encode(array('type' => 'string', 'format' => 'base64')); //{\"type\": \"string\"}";
                } else if ($campo["tipo_control"] == "checkbox") {
                    $data_entrada .= "\"" . $campo["nombre"] . "\": {\"type\": \"array\",\"items\": {\"type\": \"string\"}}";
                } else if ($campo["tipo"] == "date") {
                    $data_entrada .= "\"" . $campo["nombre"] . "\": {\"type\": \"string\",\"format\": \"date\"}";
                } else if ($campo["tipo"] == "grid") {
                    $data_entrada .= "";

                    $columnas = array();
                    $columnas = $campo["dominio_valores"];

                    $nombres_columnas = "";
                    foreach ($columnas["columns"] as $column) {
                        if ($nombres_columnas != "") $nombres_columnas .= ",";
                        $nombres_columnas .= "'" . $column["header"] . "'";
                    }

                    $data_entrada .= "\"" . $campo["nombre"] . "\": {
                        \"description\": \"Formato de arreglo\n\nPrimera fila corresponde a nombres de columnas, los cuales son: [" . $nombres_columnas . "]\n\nFilas siguientes corresponden a los valores\nEjemplo:\n[\n  [nombre_columna_1, nombre_columna_2, .., nombre_columna_N],\n  [valor_1, valor_2, .., valor_N], .., [valor_1, valor_2, .., valor_N]\n]\n\",
                        \"type\": \"array\",\"items\": {\"type\": \"array\",\"items\": {\"type\": \"string\"}},
                        \"default\": \"[[" . $nombres_columnas . "]]\",";

                    $data_entrada .= "\"minItems\": 1,";
                    $data_entrada .= "\"maxItems\": " . count($columnas["columns"]) . "}";


                }
            }
        }
        $data_salida = "";
        //Agregar las variables globales de salida
        if (isset($output_vars)) {
            //VARIABLES DE ACCION
            if (isset($output_vars['accionvar'])) {
                $data_salida = $this->crearOutputVars($output_vars['accionvar'], $data_salida);
            }
            if (isset($output_vars['formvar'])) {
                $data_salida = $this->crearOutputVars($output_vars['formvar'], $data_salida);
            }
        }

        $swagger = "";

        // $nombre_host = gethostname();
        $nombre_host = request()->getHttpHost();
        //($_SERVER['HTTPS'] ? $protocol = 'https://' : $protocol = 'http://');

        Log::info("HOST: " . $nombre_host);

        if ($file = fopen(public_path('start_swagger.json'), "r")) {
            Log::debug("Formulario recuperado");
            while (!feof($file)) {
                $line = fgets($file);
                $line = str_replace("-PROCESO-", $proceso->nombre, $line);
                $line = str_replace("-DESCRIPCION-", $proceso->descripcion, $line);
                $line = str_replace("-DATA_ENTRADA-", $data_entrada, $line);
                $line = str_replace("-OUTPUT-", $data_salida, $line);
                $line = str_replace("-HOST-", $nombre_host, $line);
                $line = str_replace("-AUTH-", $this->estrucAutorizacion($id_etapa), $line);
                $line = str_replace("-id_tramite-", $id_tramite, $line);
                $line = str_replace("-id_tarea-", $id_etapa, $line);
                $swagger .= $line;
            }
            fclose($file);
        }

        return $swagger;

    }

    private function crearOutputVars($vars, $salida)
    {
        if (!isset($vars))
            return array();
        $data_salida = $salida;
        foreach ($vars as $var) {
            Log::debug("*****   " . $var['nombre']);
            if ($var != NULL && is_array($var)) {
                if ($data_salida != "") {
                    $data_salida .= ",";
                }
                if ($var['tipo'] == 'file' || $var['tipo'] == 'documento') {
                    $data_salida .= "\"" . $var['nombre'] . "\":" . json_encode(array('type' => 'string', 'format' => 'base64'));
                } else if ($var['tipo'] == 'date') {
                    $data_salida .= "\"" . $var['nombre'] . "\":" . json_encode(array('type' => 'date'));
                } else {
                    $data_salida .= "\"" . $var['nombre'] . "\":" . json_encode(array('type' => 'string'));
                }
                Log::debug($data_salida);
            }
        }
        return $data_salida;
    }

    private function estrucAutorizacion($id_etapa)
    {
        $auth = '';
        $tipo = $this->tipoAcceso($id_etapa);
        $auth = ",\"identificacion\" : ";
        switch ($tipo) {
            case 'registrados':
            case 'grupo_usuarios':
                $auth .= json_encode(array("\$ref" => "#/definitions/simple"));
                break;
            case 'claveunica':
                $auth .= json_encode(array("\$ref" => "#/definitions/clave_unica"));
                break;
            default:
                $auth = "";
        }
        return $auth;
    }

    private function tipoAcceso($id_tarea)
    {
        try {
            $result = Doctrine_Query::create()
                ->from('Tarea a')
                ->where('a.id = ? ', $id_tarea)
                ->execute();
            if ($result != NULL && count($result) > 0) {
                return $result[0]->acceso_modo;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return '';
        }
    }

    private function getOuputVarsSwagger($formulario, $proceso_id)
    {
        Log::debug("Recuperando variables de salida: " . $proceso_id);

        $variables = Campo::getVarsExpFromFormulario($formulario[0]['form']['idForm'], $proceso_id);
        $retval = null;
        //Estas variables son las exportables como output
        if (isset($variables)) {

            foreach ($variables as $var) {
                Log::debug('Var exportable de form: ' . $var->tipo);
                $retval['formvar'][] = array('nombre' => $var->nombre, 'tipo' => $var->tipo);
            }

        }
        $accion_vars = Campo::getVarsAccionExpFromProceso($proceso_id);

        if (isset($accion_vars)) {
            foreach ($accion_vars as $var) {
                $retval['accionvar'][] = array('nombre' => $var->extra->variable, 'tipo' => 'string');
            }
        }
        return $retval;
    }
}
