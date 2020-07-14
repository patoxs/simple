<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use function GuzzleHttp\json_decode;

class CampoFileS3 extends Campo
{
    public $requiere_datos = false;
    public $min_validations = [];
    public $block_size = 5242880; // 5 MB
    public $max_size = 5242880;

    protected function display($modo, $dato, $etapa_id = false)
    {
        $set_default = true;
        if(isset($this->validacion) && is_array($this->validacion) && in_array('required', $this->validacion)){
            $set_default = false;
        }

        $saved_url = null; // string
        $saved_info = null; // json
        
        if($dato && isset($dato->valor) && isset($dato->valor->URL) && strlen($dato->valor->URL) > 1){
            $saved_url = isset($dato->valor->URL) ? $dato->valor->URL: $saved_url; // string
            $saved_info_obj = isset($dato->valor->info) ? $dato->valor->info: $saved_info; // obj
            $saved_info = json_encode($saved_info_obj);
        }else if ($etapa_id ){
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $data = $regla->getExpresionParaOutput($etapa->id);
            if($data)
                $data = json_decode($data, false);
            
            $saved_url = isset($data->URL) ? $data->URL: $saved_url; // string
            $saved_info_obj = isset($data->info) ? $data->info: $saved_info; // obj
            $saved_info = json_encode($saved_info_obj);
        }

        if(isset($this->extra->block_size)&& is_numeric($this->extra->block_size)){
            $this->block_size = intval($this->extra->block_size);
        }

        if(isset($this->extra->max_size)&& is_numeric($this->extra->max_size)){
            $this->max_size = intval($this->extra->max_size)*1048576;
        }else{
            $this->max_size = env('AWS_S3_MAX_SINGLE_PART', 5242880);
        }

        if (!$etapa_id) {
            $display = '<div class="campos3">';
            $display .= '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
            $display .= '<div class="controls">';
            $display .= '<input id="' . $this->id . '" type="hidden" name="' . $this->nombre . '" value="" />';
            $display .= '<button type="button" class="btn btn-light">Subir archivo</button>';

            if ($this->ayuda) {
                $display .= '<span class="form-text text-muted">' . $this->ayuda . '</span>';
            }

            $display .= '</div>';
            $display .= '</div>';

            return $display;
        }

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        $display = '<div class="campos3">';
        $display .= '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '
        <div class="controls">
            <input id="' . $this->id . '" type="hidden" name="' . $this->nombre . '" value=""  />
            ';
        if ($modo != 'visualizacion'){
            $display .= '
            <label for="file_input_'.$this->id.'">Archivo:</label>
            <input id="file_input_'.$this->id.'" type="file">
            <div id="parts_div_'.$this->id.'" style="display:none;" class="parts_div">
                <progress id="progress_file_'.$this->id.'" value=0 max=1></progress>
                <label id="segments_sent_'.$this->id.'">0</label><span>de</span><label id="total_segments_'.$this->id.'">0</label><span>partes subidas.</span>
            </div>
            <div>
                <button id="but_send_file_'.$this->id.'" type="button" disabled="true"
                    class="btn btn-secondary"/>
                    <i class="material-icons">file_upload</i>
                    Subir archivo
                </button>
                <input id="but_stop_'.$this->id.'" type="button" value="Detener subida" disabled="true"
                    class="btn btn-secondary"/>
            </div>';
        } // fin if visualizacion
        $display .= '
            <script>
                $(document).ready(function() {
                    var token = $(\'meta[name="csrf-token"]\').attr(\'content\');
                    set_up('.$this->id.', "'.url("uploader/datos_s3/" . $this->id . "/" . $etapa->id) .'", 
                            token, '.$this->block_size.','. $this->max_size.','. env('AWS_S3_MAX_SINGLE_PART', 5242800).');
                    '.($set_default ? 'set_default_s3_hidden('.$this->id.');': '');
            if( ! is_null($saved_url)){
                $display .= 'set_s3_hidden('.$this->id.',"'.$saved_url.'",'.$saved_info.');';
            }
            $display .= '
                });</script>';
        
        if(is_null($saved_url)){
            $display .= '<p class="link"><a id="link_to_file_'.$this->id.'" href="#"></a></p>';
        } else {
            $etapa = Doctrine::getTable('Etapa')->findOneById($etapa_id);
            $url_pieces = explode('/', $saved_url);
            $file_key = $url_pieces[ count($url_pieces) - 2 ];
            $file = Doctrine::getTable('File')->findOneByLlaveAndTipoAndTramiteId($file_key, \App\Helpers\FileS3Uploader::$file_tipo, $etapa->tramite_id);
            $display .= '<p class="link"><a id="link_to_file_'.$this->id.'" href="' . $saved_url . '" target="_blank">'.(isset($file->filename) ? $file->filename: 'Archivo').'</a>';
        }

        if ($this->ayuda)
            $display .= '<span class="form-text text-muted">' . $this->ayuda . '</span>';
            $display .= '</div>';
            $display .= '</div>';
        return $display;
    }

    public function extraForm()
    {
        $filetypes = array();
        if (isset($this->extra->filetypes)) {
            $filetypes = $this->extra->filetypes;
        }

        $info_s3 = '';
        if (isset($this->extra->info_s3)) {
            $info_s3 = ($this->extra->info_s3);
        }
        
        if(isset($this->extra->block_size)&& is_numeric($this->extra->block_size)){
            $this->block_size = intval($this->extra->block_size);
        }

        if(isset($this->extra->max_size) && is_numeric($this->extra->max_size)){
            $this->max_size = intval($this->extra->max_size);
        }

        $block_sizes = [ 5242880 => '5MB', 6291456 => '6MB', 8388608 => '8MB'];
        $output = '<div class="controls s3_upload_size">
                        <label class="control-label">Tamaño de cada bloque</label>
                        <select name="extra[block_size]" class="form-control">';
        foreach ($block_sizes as $block_size => $text) {
            $output .= "<option value='".$block_size."' ".(($this->block_size == $block_size) ? 'selected="selected"': '')." >".$text."</option>\n";
        }

        $output .= '</select>
                </div>';
        $output .= '<div class="controls s3_extra_files">';
        $output .= '<label class="control-label">Tipos de archivos por extensión</label>';
        $output .= '<select name="extra[filetypes][]" class="form-control" multiple>';
        $output .= '<option name="jpg" ' . (in_array('jpg', $filetypes) ? 'selected' : '') . '>jpg</option>';
        $output .= '<option name="png" ' . (in_array('png', $filetypes) ? 'selected' : '') . '>png</option>';
        $output .= '<option name="gif" ' . (in_array('gif', $filetypes) ? 'selected' : '') . '>gif</option>';
        $output .= '<option name="pdf" ' . (in_array('pdf', $filetypes) ? 'selected' : '') . '>pdf</option>';
        $output .= '<option name="doc" ' . (in_array('doc', $filetypes) ? 'selected' : '') . '>doc</option>';
        $output .= '<option name="docx" ' . (in_array('docx', $filetypes) ? 'selected' : '') . '>docx</option>';
        $output .= '<option name="xls" ' . (in_array('xls', $filetypes) ? 'selected' : '') . '>xls</option>';
        $output .= '<option name="xlsx" ' . (in_array('xlsx', $filetypes) ? 'selected' : '') . '>xlsx</option>';
        $output .= '<option name="mpp" ' . (in_array('mpp', $filetypes) ? 'selected' : '') . '>mpp</option>';
        $output .= '<option name="vsd" ' . (in_array('vsd', $filetypes) ? 'selected' : '') . '>vsd</option>';
        $output .= '<option name="ppt" ' . (in_array('ppt', $filetypes) ? 'selected' : '') . '>ppt</option>';
        $output .= '<option name="pptx" ' . (in_array('pptx', $filetypes) ? 'selected' : '') . '>pptx</option>';
        $output .= '<option name="zip" ' . (in_array('zip', $filetypes) ? 'selected' : '') . '>zip</option>';
        $output .= '<option name="rar" ' . (in_array('rar', $filetypes) ? 'selected' : '') . '>rar</option>';
        $output .= '<option name="odt" ' . (in_array('odt', $filetypes) ? 'selected' : '') . '>odt</option>';
        $output .= '<option name="odp" ' . (in_array('odp', $filetypes) ? 'selected' : '') . '>odp</option>';
        $output .= '<option name="ods" ' . (in_array('ods', $filetypes) ? 'selected' : '') . '>ods</option>';
        $output .= '<option name="odg" ' . (in_array('odg', $filetypes) ? 'selected' : '') . '>odg</option>';
        $output .= '</select>';
        $output .= '</div>';
        $output .= '<div class="controls s3_max_file_size">
                        <label class="control-label">Tamaño máximo del archivo (MB)</label>
                        <input type="text" name="extra[max_size]" class="form-control" value="' . ($this->extra && property_exists($this->extra,'max_size') ? $this->extra->max_size : '') . '">
                        </div>';
        $min_vals = json_encode($this->min_validations); // solo expande variables, no evalua
        $output .= "
        <script>

        $('#expire_time').keydown(function(evt){
            var key_code = evt.which;
            if( key_code != 13 && key_code != 9 && ( key_code < 48 || key_code > 57 ) ) {
                // 13 enter, 9 tab
                evt.preventDefault();
                evt.stopPropagation();
                return false;
            }

        });

        $('input.validacion[type=text]').keydown(function(evt){
            var min_vals = $min_vals;
            var old_vals = $(this).val().replace(/\s*/g, '').split('|');
            var pos = 0;

            if( evt.which == 13 || evt.which == 32) {
                // 13 enter, 32 espacio
                evt.preventDefault();
                evt.stopPropagation();
                return false;
            }

            for(i=0;i<min_vals.length;i++){
                if(old_vals.indexOf(min_vals[i]) === -1){
                    old_vals.splice(pos++, 0,min_vals[i]);
                }
            }
            console.log(old_vals.join('|'));
            $(this).val(old_vals.join('|'));
        });
        </script>
    ";

        return $output;
    }
}
