<?php
require_once('campo.php');

use Illuminate\Http\Request;

class CampoComunas extends Campo
{

    public $requiere_datos = false;

    protected function display($modo, $dato)
    {
        $valor_default = json_decode($this->valor_default);
        if (!$valor_default) {
            $valor_default = new stdClass();
            $valor_default->region = '';
            $valor_default->comuna = '';
        }

        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<select class="form-control" id="regiones_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[region]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Regi&oacute;n</option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<select class="form-control" id="comunas_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[comuna]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Comuna</option>';
        $display .= '</select>';
        $display .= '<input type="hidden" id="cstateCode_'.$this->id.'" name="cstateCode_'.$this->id.'">';
        $display .= '<input type="hidden" id="cstateName_'.$this->id.'" name="cstateName_'.$this->id.'">';
        $display .= '<input type="hidden" id="ccityCode_'.$this->id.'" name="ccityCode_'.$this->id.'">';
        $display .= '<input type="hidden" id="ccityName_'.$this->id.'" name="ccityName_'.$this->id.'">';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){
                    var justLoadedRegion=true;
                    var justLoadedComuna=true;
                    var defaultRegion="' . ($dato && $dato->valor && property_exists($dato->valor,'region') ? $dato->valor->region : $valor_default->region) . '";
                    var defaultComuna="' . ($dato && $dato->valor && property_exists($dato->valor,'comuna') ? $dato->valor->comuna : $valor_default->comuna) . '";
                    var opcion = "'. (isset($this->extra->codigo) && $this->extra->codigo ? "codigo" : "nombre") .'";

                    $("#regiones_'.$this->id.'").chosen({placeholder_text: "Seleccione Regi\u00F3n"});
                    $("#comunas_'.$this->id.'").chosen({placeholder_text: "Seleccione Comuna"});

                    updateRegiones();
                    
                    function updateRegiones(){
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones?callback=?",function(data){
                            
                            var regiones_obj = $("#regiones_'.$this->id.'");
                            regiones_obj.empty();
                            $.each(data, function(idx, el){
                                regiones_obj.append("<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>");
                            });
                            
                            regiones_obj.change(function(event){
                                var selectedId=$(this).find("option:selected").attr("data-id");
                                updateComunas(selectedId);
                                regiones_obj.attr("cstateCode_'.$this->id.'",$(this).find("option:selected").attr("data-id"));
                                regiones_obj.attr("cstateName_'.$this->id.'",regiones_obj.val());
                                $("#cstateCode_'.$this->id.'").val($(this).find("option:selected").attr("data-id"));
                                $("#cstateName_'.$this->id.'").val(regiones_obj.val());
                            });
                            
                            if(justLoadedRegion){
                                regiones_obj.val(defaultRegion).change();
                                justLoadedRegion=false;
                            }
                            regiones_obj.trigger("chosen:updated");
                        });
                    }
                    
                    function updateComunas(regionId){
                        var comunas_obj = $("#comunas_'.$this->id.'");
                        comunas_obj.empty();
                        
                        if(typeof regionId === "undefined")
                            return;
                        
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones/"+regionId+"/comunas?callback=?",function(data){
                            if(data){
                                $.each(data, function(idx, el){
                                    var op = el[opcion];
                                    comunas_obj.append("<option data-id=\""+el.codigo+"\" value=\""+op+"\" >"+el.nombre+"</option>"); 
                                });
                            }
                            comunas_obj.trigger("chosen:updated");
                            if(justLoadedComuna){
                                comunas_obj.val(defaultComuna).change();
                                justLoadedComuna=false;
                            }
                            comunas_obj.trigger("chosen:updated");

                            $("#ccityCode_'.$this->id.'").val($(comunas_obj).find("option:selected").val());
                            $("#ccityName_'.$this->id.'").val($(comunas_obj).find("option:selected").text());

                            comunas_obj.change(function(event){
                                $("#ccityCode_'.$this->id.'").val($(this).find("option:selected").attr("data-id"));
                                $("#ccityName_'.$this->id.'").val($(this).find("option:selected").text());
                            });
                        });
                    }
                });

            </script>';

        return $display;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {
        $request->validate([
            $this->nombre . '.region' => implode('|', $this->validacion),
            $this->nombre . '.comuna' => implode('|', $this->validacion),
        ], [], [
            $this->nombre . '.region' => "<b>Región de $this->etiqueta</b>",
            $this->nombre . '.comuna' => "<b>Comuna de $this->etiqueta</b>"
        ]);
    }

    public function backendExtraFields(){
        $codigo = isset($this->extra->codigo) ? $this->extra->codigo : null;
        $html = '<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="extra[codigo]" id="checkbox_codigo"  ' . ($codigo ? 'checked' : '') . ' /> 
                    <label for="checkbox_codigo" class="form-check-label">Utilizar código en select comunas.</label>
                    </div>';
        $html .= ' </label>';
        
        return $html;
    }

}