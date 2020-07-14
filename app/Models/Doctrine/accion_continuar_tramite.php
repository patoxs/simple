<?php
require_once('accion.php');

use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;

class AccionContinuarTramite extends Accion
{

    public function displaySecurityForm($proceso_id)
    {
        Log::info("En accion continuar trámite");

        $tramites_disponibles = Doctrine::getTable('Proceso')->findProcesosExpuestos("");

        $tareas_proceso = Doctrine::getTable('Proceso')->findTareasProceso($proceso_id);

        $data = Doctrine::getTable('Proceso')->find($proceso_id);
        $conf_seguridad = $data->Admseguridad;

        /*$display ='
                <label>Trámites disponibles</label>
                <select id="tramiteSel" name="extra[tramiteSel]">
                    <option value="">Seleccione...</option>';

                foreach ($tramites_disponibles as $tramite) {
                    $display.='<option value="'.$tramite["id"].'">'.$tramite["nombre"].'</option>';
                }

        $display.='</select>';*/

        /*$display.='
                <label>Tareas disponibles del trámite para retorno</label>
                <select id="tareaRetornoSel" name="extra[tareaRetornoSel]">';
        $display.='</select>';*/

        /*$display.='
                <label>Tarea desde la cual desea continuar el proceso</label>
                <select id="tareaContinuarSel" name="extra[tareaContinuarSel]">
                    <option value="">Seleccione...</option>';

                foreach ($tareas_proceso as $tarea) {
                    $display.='<option value="'.$tarea["id"].'">'.$tarea["nombre"].'</option>';
                }
        $display.='</select>';*/

        $display = '
            <div class="col-md-12" id="divObject">
                <label>Request</label>
                <textarea id="request" class="form-control" name="extra[request]" rows="7" cols="70" placeholder="{ form }" class="input-xxlarge">' . ($this->extra ? $this->extra->request : '') . '</textarea>
                <br />
                <span id="resultRequest" class="spanError"></span>
                <br /><br />
            </div>';


        return $display;
    }

    public function validateForm()
    {
        //$CI = &get_instance();
        //$CI->form_validation->set_rules('extra[tramiteSel]', 'Trámite', 'required');
    }

    public function ejecutar($tramite_id)
    {
        if (is_numeric($tramite_id)) {
            $etapa = Etapa::find($tramite_id);
        } else {
            $etapa = $tramite_id;
        }

        Log::info("En ejecución continuar trámite");

        // Se declara el tipo de seguridad segun sea el caso
        if (isset($this->extra->request)) {
            $r = new Regla($this->extra->request);
            $request = $r->getExpresionParaOutput($etapa->id);
        }

        Log::info("Request: " . $request);
        //Log::info(Id trámite: ".$this->extra->tramiteSel);

        //obtenemos el Headers si lo hay
        /*if(isset($this->extra->header)){
            $r=new Regla($this->extra->header);
            $header=$r->getExpresionParaOutput($etapa->id);
            $headers = json_decode($header);
            foreach ($headers as $name => $value) {
                $CI->rest->header($name.": ".$value);
            }
        }*/
        try {

            Log::info("Continuar desde etapa_id: " . $etapa->id);

            $tramite_id = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa("tramite_retorno", $etapa->id);
            $tarea_id = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa("tarea_retorno", $etapa->id);

            Log::info("Continuar tramite_id: " . $tramite_id->valor);
            Log::info("Continuar tarea_id: " . $tarea_id->valor);

            $etapa_continuar = new Etapa();
            $etapa_continuar = $etapa_continuar->getEtapaPorTareaId($tarea_id->valor, $tramite_id->valor);
            Log::info("id_etapa a continuar: " . $etapa->id);
            if (strlen($etapa_continuar->id) != 0) { //Existe etapa para continuar el proceso
                $integracion = new IntegracionMediator();
                $info_continuar = $integracion->continuarProceso($tramite_id->valor, $etapa_continuar->id, "0", $request);

                $response_continuar = "{\"respuesta_continuar\": " . $info_continuar . "}";

                Log::info("Response: " . $response_continuar);

                $response["respuesta_continuar"] = $response_continuar;

            } else {
                //Se encola continuar proceso hasta que etapa se cree
                $cola = new ColaContinuarTramite();
                $cola->tramite_id = $tramite_id->valor;
                $cola->tarea_id = $tarea_id->valor;
                $cola->request = $request;
                $cola->procesado = 0;
                Log::info("Se encola, ya que aun no existe etapa cola: " . $cola);
                $cola->save();
                $response["respuesta_continuar"] = "Se encola continuación trámite id:" . $tramite_id->valor . " en tarea id: " . $tarea_id->valor;
            }

            foreach ($response as $key => $value) {
                $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa->id);
                if (!$dato)
                    $dato = new DatoSeguimiento();
                $dato->nombre = $key;
                $dato->valor = $value;
                $dato->etapa_id = $etapa->id;
                $dato->save();
            }
        } catch (Exception $e) {
            Log::error($e->getCode() . ": " . $e->getMessage());
            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("error_continuar_simple", $etapa->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = "error_continuar_simple";
            $dato->valor = $e->getCode() . ": " . $e->getMessage();
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }
    }

}