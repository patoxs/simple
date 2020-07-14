<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoBtnAsincrono extends Campo
{

    public $requiere_datos = false;

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }

        $display = '<div class="form-group float-right">';
        $display .= '<button id="' . $this->id . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="button" class="form-control btn btn-success" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : htmlspecialchars($valor_default)) . '" data-modo="' . $modo . '" > ' . $this->etiqueta . '</button>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div><br>';

        if($etapa_id){
            $display .= '
                <script>
                    var table;
                    $(document).ready(function(){

                        //se deben ejecutar las acciones durante el paso de acuerdo al botón que está haciendo la llamada
                        
                        $("#'.$this->id.'").click(function(){
                            $("#'.$this->id.'").prop("disabled",true);
                            $(".ajaxLoader").show();
                            var myData = $("form").find("input[name],select.form-control").serializeArray();
                            var btn_asinc = {
                                  name: "btn_async",
                                  value: "'.$this->id.'"
                            };
                            myData.push(btn_asinc);
                            $.ajax({
                                url: "'.url("etapas/save/". $etapa->id ."") .'",
                                data: myData,
                                type: "POST",
                                success: function(data){
                                    $("#'.$this->id.'").prop("disabled",false);
                                    $(".ajaxLoader").hide();
                                    procesar_data(data);
                                }
                            });
                        })
                        
                    });
                    
                </script>';
        }

        return $display;
    }

}