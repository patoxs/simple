<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoSelect extends Campo
{

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = json_decode($this->valor_default);
        }

        $display = '<div class="form-group">';
        $display .= '<label for="' . $this->id . '">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<select id="' . $this->id . '" class="form-control '.$this->id.'" name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' data-modo="' . $modo . '">';
        $display .= '<option value="">Seleccionar</option>';
        if ($this->datos) foreach ($this->datos as $d) {
            if ($dato) {
                $display .= '<option value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
            } else {
                $display .= '<option value="' . $d->valor . '" ' . ($d->valor == $valor_default ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
            }
        }

        //Para la carga masiva  en select mediante web service
         if ($this->extra && $this->extra->ws) {
            try{
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->extra->ws);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                $response = curl_exec($ch);
                if(!curl_errno($ch)){
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $err = curl_error($ch);
                    curl_close($ch);
                    $response = json_decode($response);
                    foreach ($response as $d) {
                        $display .= '<option value="' . $d->valor . '" ' . ($d->valor == $valor_default ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
                    }
                }
            }catch(Exception $e){
                Log::error("Ocurrió un error al cargar datos desde url para campo select" . $e);
            }
        }

        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
                    <script>
                        $(document).ready(function(){
                            $(".form-control-chosen").chosen();

                                $("#'.$this->id.'").chosen().change(
                                function (evt) {
                                var label = $(this.options[this.selectedIndex]).closest("optgroup").prop("label");
                
                                });
                        });
                    </script>
    
                ';

        if($modo=='visualizacion'){
            $display .= '
            <script>
                $(document).ready(function(){
                    $("#'.$this->id.'").attr("disabled",true).trigger("chosen:updated");
                });
            </script>';
        }
        if ($this->extra && $this->extra->ws){
            $display .= '
            <script>
                $(document).ready(function(){
                    var defaultValue = "' . ($dato && $dato->valor ? $dato->valor : $this->valor_default) . '";
                    if(defaultValue)
                        $("#' . $this->id . '").val(defaultValue);
                    $("#' . $this->id . '").trigger("chosen:updated");
                    $("#' . $this->id . '").chosen();
                });
            </script>';
        }

        

        return $display;
    }

    public function backendExtraFields()
    {
        $ws = isset($this->extra->ws) ? $this->extra->ws : null;

        $html = '<label>URL para cargar opciones desde webservice (Opcional)</label>';
        $html .= '<input class="form-control" name="extra[ws]" value="' . $ws . '" />';
        $html .= '<div class="help-block">
                El WS debe ser REST JSON con el siguiente formato: <a href="#" onclick="$(this).siblings(\'pre\').show()">Ver formato</a><br />
                <pre style="display:none">
[
    {
        "etiqueta": "Etiqueta 1",
        "valor": "Valor 1"
    },
    {
        "etiqueta": "Etiqueta 2",
        "valor": "Valor 2"
    },
]
                </pre>
                </div>';

        $html .= 'Para cargar registros masivos mediante archivo, en formato .CSV, separado por punto y coma(;).<br>';
        $html .= '<div class="controls">';
        // $html .= '<div id="file-uploader" data-action="'.url('uploader/masiva').'"></div>';
        $html .= '<div id="file-uploader"></div>';
        $html .= '<input id="file_carga_masiva" type="hidden" name="file_carga_masiva" /></div>';

        return $html;
    }

    public function backendExtraValidate(Request $request)
    {
        //$CI =& get_instance();
        //$CI->form_validation->set_rules('datos','Datos','required');
    }

}