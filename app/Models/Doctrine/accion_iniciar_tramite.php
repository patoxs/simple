<?php
require_once('accion.php');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;

class AccionIniciarTramite extends Accion
{

    public function displaySecurityForm($proceso_id)
    {

        Log::info("En accion trámite");

        $tareas_proceso = Doctrine::getTable('Proceso')->findTareasProceso($proceso_id);

        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if (isset($this->extra->cuentaSel)) {
            $tramites_disponibles = Doctrine::getTable('Proceso')->findProcesosExpuestos($this->extra->cuentaSel);
            $cuenta = Doctrine::getTable('Cuenta')->find($this->extra->cuentaSel);
        } else {
            $tramites_disponibles = Doctrine::getTable('Proceso')->findProcesosExpuestos($proceso->cuenta_id);
            $cuenta = Doctrine::getTable('Cuenta')->find($proceso->cuenta_id);
        }

        $display = '
                <label>Cuentas</label>
                <select id="cuentaSel" name="extra[cuentaSel]" class="form-control col-2">
                    <option value="' . $cuenta->id . '">' . $cuenta->nombre . '</option>';

        $proceso_cuenta = new ProcesoCuenta();
        $cuentas_con_permiso = $proceso_cuenta->findCuentasAcceso($cuenta->id);
        if (isset($cuentas_con_permiso) && count($cuentas_con_permiso) > 0) {
            foreach ($cuentas_con_permiso as $cuentas_permiso) {
                if (isset($this->extra->cuentaSel) && $this->extra->cuentaSel == $cuentas_permiso["id"]) {
                    $display .= '<option value="' . $cuentas_permiso["id"] . '" selected>' . $cuentas_permiso["nombre"] . '</option>';
                } else {
                    $display .= '<option value="' . $cuentas_permiso["id"] . '">' . $cuentas_permiso["nombre"] . '</option>';
                }
            }
        }

        $display .= '</select>';

        $display .= '<input type="hidden" name="cuenta_actual_id" id="cuenta_actual_id" value="' . $cuenta->id . '" />';
        $display .= '<input type="hidden" name="cuenta_actual_nombre" id="cuenta_actual_nombre" value="' . $cuenta->nombre . '" />';

        $display .= '
                <label>Trámites disponibles</label>
                <select id="tramiteSel" name="extra[tramiteSel]" class="form-control col-2">
                    <option value="">Seleccione...</option>';

        foreach ($tramites_disponibles as $tramite) {
            if (!is_null($this->extra) && $this->extra->tramiteSel && $this->extra->tramiteSel == $tramite["id"]) {
                $display .= '<option value="' . $tramite["id"] . '" selected>' . $tramite["nombre"] . '</option>';
            } else {
                $display .= '<option value="' . $tramite["id"] . '">' . $tramite["nombre"] . '</option>';
            }
        }

        $display .= '</select>';

        $display .= '
                <label>Tarea desde la cual desea continuar el proceso</label>
                <select id="tareaRetornoSel" name="extra[tareaRetornoSel]" class="form-control col-2">
                    <option value="">Seleccione...</option>';

        foreach ($tareas_proceso as $tarea) {
            if (isset($this->extra->tareaRetornoSel) && $this->extra->tareaRetornoSel == $tarea["id"]) {
                $display .= '<option value="' . $tarea["id"] . '" selected>' . $tarea["nombre"] . '</option>';
            } else {
                $display .= '<option value="' . $tarea["id"] . '">' . $tarea["nombre"] . '</option>';
            }
        }
        $display .= '</select>';

        $display .= '
            <div class="" id="divObject">
                <label>Request</label>
                <textarea id="request" class="form-control col-4" name="extra[request]" rows="7" cols="70" placeholder="{ form }" class="input-xxlarge">' . ($this->extra ? $this->extra->request : '') . '</textarea>
                <br />
                <span id="resultRequest" class="spanError"></span>
                <br /><br />
            </div>';


        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.tramiteSel' => 'required',
            'extra.tareaRetornoSel' => 'required',
            'extra.request' => 'required'
        ], [
            'extra.tramiteSel.required' => 'El campo Trámite es obligatorio',
            'extra.tareaRetornoSel.required' => 'El campo Tarea retorno es obligatorio',
            'extra.request.required' => 'El campo Request es obligatorio'
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        if (is_numeric($tramite_id)) {
            $etapa = Etapa::find($tramite_id);
        } else {
            $etapa = $tramite_id;
        }

        Log::info("En ejecución accion iniciando trámite simple");

        //$CI = &get_instance();
        // Se declara el tipo de seguridad segun sea el caso
        if (isset($this->extra->request)) {
            $r = new Regla($this->extra->request);
            $request = $r->getExpresionParaOutput($etapa->id);
        }

        Log::info("Request: " . $request);
        Log::info("Id trámite: " . $this->extra->tramiteSel);
        Log::info("Id tarea retorno: " . $this->extra->tareaRetornoSel);
        Log::info("Id tramite desde etapa: " . $etapa->tramite_id);

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

            $integracion = new IntegracionMediator();

            //TODO al parecer falta indicar tarea de inicio
            $info_inicio = $integracion->iniciarProceso($this->extra->tramiteSel, $etapa->id, $request, $etapa->tramite_id, $this->extra->tareaRetornoSel);

            $response["respuesta_inicio"] = $info_inicio;

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
            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId("error_iniciar_simple", $etapa->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = "error_iniciar_simple";
            $dato->valor = $e->getCode() . ": " . $e->getMessage();
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }
    }

    private function registrarRetorno($tramite_id, $tramite_retorno, $retorno_id)
    {

        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);
        $etapa_id = $tramite->getEtapasActuales()->get(0)->id;

        $dato = new DatoSeguimiento();
        $dato->nombre = "tramite_retorno";
        $dato->valor = $tramite_retorno;
        $dato->etapa_id = $etapa_id;
        $dato->save();

        $dato = new DatoSeguimiento();
        $dato->nombre = "tarea_retorno";
        $dato->valor = $retorno_id;
        $dato->etapa_id = $etapa_id;
        $dato->save();
    }

}