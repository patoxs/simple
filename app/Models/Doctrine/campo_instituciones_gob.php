<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoInstitucionesGob extends Campo
{
    public $requiere_datos = false;

    protected function display($modo, $dato)
    {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<select class="form-control" id="entidades_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[entidad]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width: 100%">';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<select class="form-control" id="instituciones_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[servicio]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . '>';
        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){
                    $("#entidades_'.$this->id.'").chosen({placeholder_text: "Por favor Seleccione el Ministerio u Organismo Principal"});
                    $("#instituciones_'.$this->id.'").chosen({placeholder_text: "Por favor Seleccione la Instituci\u00F3n"});

                    var justLoadedEntidad=true;
                    var justLoadedInstitucion=true;
                    var defaultEntidad="' . ($dato && $dato->valor ? $dato->valor->entidad : '') . '";
                    var defaultInstitucion="' . ($dato && $dato->valor ? $dato->valor->servicio : '') . '";
                        
                    updateEntidades();
                    
                    function updateEntidades(){
                        var entidades_obj = $("#entidades_'.$this->id.'");
                        
                        $.getJSON("https://apis.digital.gob.cl/instituciones/api/entidades?callback=?",function(data){
                            $(data.items).each(function(i,el){
                                entidades_obj.append("<option value=\""+el.nombre+"\" data-id=\""+el.codigo+"\">"+el.nombre+"</option>");
                            });
                            entidades_obj.change(function(event){
                                var selectedId=$(this).find("option:selected").data("id");
                                updateInstituciones(selectedId);
                            });
                            
                            if(justLoadedEntidad){
                                entidades_obj.val(defaultEntidad).change();
                                justLoadedEntidad=false;
                            }
                            entidades_obj.trigger("chosen:updated");
                        });
                    }
                    
                    function updateInstituciones(entidadId){
                        var instituciones_obj = $("#instituciones_'.$this->id.'");
                        instituciones_obj.empty();
                        $.getJSON("https://apis.digital.gob.cl/instituciones/api/entidades/"+entidadId+"/instituciones?callback=?",function(data){
                            if(data){
                                $(data.items).each(function(i,el){
                                     instituciones_obj.append("<option value=\""+el.nombre+"\">"+el.nombre+"</option>");
                                });
                            }
                            
                            if(justLoadedInstitucion){
                                instituciones_obj.val(defaultInstitucion).change();
                                justLoadedInstitucion=false;
                            }
                            instituciones_obj.trigger("chosen:updated");
                        });
                        
                    }

                });              
            </script>';

        return $display;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {
        $request->validate([
            $this->nombre . '.entidad' => implode('|', $this->validacion),
            $this->nombre . '.servicio' => implode('|', $this->validacion)
        ]);

        /*
        $CI =& get_instance();
        $CI->form_validation->set_rules($this->nombre . '[entidad]', $this->etiqueta . ' - Entidad', implode('|', $this->validacion));
        $CI->form_validation->set_rules($this->nombre . '[servicio]', $this->etiqueta . ' - Servicio', implode('|', $this->validacion));
        */
    }

}