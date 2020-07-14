<?php
require_once('campo.php');

use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use App\Rules\GrillaDatosExternos;

class CampoGridDatosExternos extends Campo
{
    private $javascript;

    public $requiere_datos = false;
    private $cell_text_max_length_default = 50;

    private $cell_text_max_length;
    private $columns;
    private $agregable;
    private $eliminable;
    private $editable;
    private $export_as;
    private $input_is_array;
    private $tiene_acciones;
    private $botones;
    private $botones_position;
    private $ayuda;
    private $field_types = ['string'=>'String', 'integer'=>'Entero',
                           'float' => 'Flotante', 'boolean' => 'Booleano'];
    private $field_types_html;

    protected function display($modo, $dato, $etapa_id = false)
    {
        $this->load_extra_config( $modo );

        $display_modal = '
        <div class="modal fade modalgrid" id="table_alter_modal_'.$this->id.'" tabindex="-1" role="dialog" aria-labelledby="add_to_table_modal_label_'.$this->id.'" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="add_to_table_modal_label_'.$this->id.'">Nuevo registro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="ajax-alert_'.$this->id.'" class="alert alert-danger" style="display:none;">
                    </div>
                    <div class="modal-body" id="modal-body-'.$this->id.'">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="modal_accept_button_'.$this->id.'" class="btn btn-outline-secondary">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        ';

        $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<input type="hidden" name="'.$this->nombre.'" id="'.$this->id.'">';
        $display .= '<div class="controls grid-Cls">
                        <div data-id="' . $this->id . '" >
                            <div class="container">
                                <div class="row">
                                    <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="grilla-'.$this->id.'" data-grilla_id="'.$this->id.'">

                                    </table>
                                    </div>
                                    <div class="col-auto colautogrid" style="transform:translateY(+50%);">
                                    <!-- Al lado -->
                                        '.($this->botones_position == "right_side" ? implode("<br /><br />", $this->botones) : '').'
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="">
                                        '.($this->botones_position == "bottom" ? implode("\n", $this->botones) : '').'
                                    </div>
                                    <div class="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';

        $data = [];
        if($dato && ! empty($dato->valor) ){
            if(is_string($dato->valor))
                $data = json_decode($dato->valor, true);
            else
                $data = $dato->valor;
            if( ! is_null($data) && is_array($data) && ! $this->is_array_associative($data) ){
                // hay que corregir llenando con vacios cuando la columna no sea exportable
                for($i=0; $i<count($data);$i++){
                    for( $j=0; $j < count($this->columns); $j++){
                        if( $this->columns[$j]->is_exportable == 'false'){
                            array_splice($data[$i], $j, 0, '');

                        }
                    }
                }
            }
        }else if ($etapa_id ){
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $data = $regla->getExpresionParaOutput($etapa->id);
        }
        if( is_string($data))
            $data = json_decode($data, true);

        $display .='
        <script>
                $(document).ready(function(){
                    var data = '.json_encode($data).';
                    var is_array = '.(count($data) > 0 ? "Array.isArray(data[0])" : var_export($this->input_is_array, true) ).';
                    var columns = '.json_encode($this->columns).';
                    grillas_datatable['.$this->id.'] = {};

                    grillas_datatable['.$this->id.'].tiene_acciones = '.($this->tiene_acciones ? 'true': 'false').';

                    grillas_datatable['.$this->id.'].cantidad_columnas = columns.length;
                    grillas_datatable['.$this->id.'].export_as = "'.(isset($this->export_as) ? $this->export_as: 'array').'".toLowerCase();

                    if('.($this->tiene_acciones ? 'true': 'false').'){
                        grillas_datatable['.$this->id.'].cantidad_columnas++;
                    }

                    init_tables('.$this->id.', "'.$modo.'",columns,'.$this->cell_text_max_length.',is_array, '.json_encode($this->editable).','.json_encode($this->eliminable).');
                    grillas_datatable['.$this->id.'].table.draw(true);
                    if(data != null && data.length > 0){
                        if(is_array){
                            grilla_populate_arrays('.$this->id.', data);
                        }else{
                            grilla_populate_objects('.$this->id.', data);
                        }
                        store_data_in_hidden('.$this->id.');
                    }
                    grillas_datatable['.$this->id.'].table.draw(true);
                    grillas_datatable['.$this->id.'].table.columns.adjust();
                });
            </script>
        ';
        if( $modo != 'visualizacion' )
          $display .= $display_modal;

        return $display;
    }

    public function backendExtraFields()
    {
        $this->load_extra_config();
        $precarga = isset($this->extra->precarga) ? $this->extra->precarga : null;

        $hidden_arr[] = '<input type="hidden" name="extra[agregable]" value="'.($this->agregable  ? 'true': 'false').'" />';
        $hidden_arr[] = '<input type="hidden" name="extra[eliminable]" value="'.($this->eliminable ? 'true': 'false').'"/>';
        $hidden_arr[] = '<input type="hidden" name="extra[editable]" value="'.($this->editable ? 'true': 'false').'"/>';
        $output = implode("\n", $hidden_arr);

        $column_template_html = "
                    <tr>
                        <td>
                            <input type='text' name='extra[columns][{{column_pos}}][header]' class='form-control' value='{{header}}' />
                        </td>
                        <td>
                            <input class='form-control' type='input' name='extra[columns][{{column_pos}}][modal_add_text]' value='{{modal_add_text}}'/>
                        </td>
                        <td>
                            <input class='form-control' type='input' name='extra[columns][{{column_pos}}][object_field_name]' value='{{object_field_name}}'/>
                        </td>
                        <td>
                            <select class='form-control' type='input' name='extra[columns][{{column_pos}}][field_type]' value='{{object_field_name}}'>
                                {{select_field_types}}
                            </select>
                        </td>
                        <td>
                            <input class='form-control' type='checkbox' {{is_input_checked}} onclick='return cambiar_estado_entrada(this, {{column_pos}});'>
                            <input type='hidden' name='extra[columns][{{column_pos}}][is_input]' value='{{is_input}}' />
                        </td>
                        <td>
                            <input class='form-control' type='checkbox' onclick='return cambiar_exportable(this,{{column_pos}});' {{is_exportable_checked}}>
                            <input type='hidden' name='extra[columns][{{column_pos}}][is_exportable]' value='{{is_exportable}}' />
                        </td>
                        <td>
                            <input type='text' name='extra[columns][{{column_pos}}][validacion]' class='validacion-columna form-control' value='{{validacion}}' />
                        </td>
                        <td>
                            <button type='button' class='btn btn-outline-secondary eliminar'><i class='material-icons'>close</i> Eliminar</button>
                        </td>
                    </tr>";


        $field_types_no_selected = str_replace("{{selected}}", "", join("", $this->field_types_html));
        $column_template_html = str_replace("\n", "", $column_template_html);

        $html_column_template = str_replace('{{select_field_types}}', $field_types_no_selected, $column_template_html);
        $html_column_template = str_replace("\n", "", $html_column_template);

        $output .= '
            <br />
            <div class="input-group controls">
                <label class="controls-label-inputt" for="grilla_datos_externos_table_text_max_length">Largo m&aacute;ximo del texto en las celdas:&nbsp;</label>
                <input class="form-control col-1" type="text" name="extra[cell_text_max_length]" id="grilla_datos_externos_table_text_max_length" value="'.$this->cell_text_max_length.'"/>
            </div>
            <div class="input-group controls">
                <label class="controls-label-inputt" for="grilla_formato_entrada">Formato de entrada</label>
                <select class="form-control col-2" name="extra[grilla_import_as]">
                    <option value="object" '.( ! $this->input_is_array ? "selected": "" ).'>Objetos</option>
                    <option value="array" '.( $this->input_is_array ? "selected": "" ).'>Arreglos</option>
                </select>
            </div>
            <div class="input-group controls">
                <label class="controls-label-inputt" for="grilla_formato_salida">Formato de salida</label>
                <select class="form-control col-2" name="extra[grilla_export_as]">
                    <option value="object" '.($this->export_as == "object" ? "selected": "").'>Objetos</option>
                    <option value="array" '.($this->export_as == "array" ? "selected": "").'>Arreglos</option>
                </select>
            </div>
            <div class="input-group controls">
                <label for="grilla_agregable">Se puede Agregar</label>
                <input class="controls-inputchk" type="checkbox" id="grilla_agregable" onclick="toggle_checkbox(\'agregable\', this)" '.($this->agregable ? "checked": "").'/>
            </div>
            <div class="input-group controls">
                <label for="grilla_eliminable">Se puede Eliminar</label>
                <input class="controls-inputchk" type="checkbox" id="grilla_eliminable" onclick="toggle_checkbox(\'eliminable\', this)" '.($this->eliminable ? 'checked': "").'/>
            </div>
            <div class="input-group controls">
                <label for="grilla_eliminable">Se puede Editar</label>
                <input class="controls-inputchk" type="checkbox" id="grilla_editable" onclick="toggle_checkbox(\'editable\', this)" '.($this->editable ? 'checked': "").'/>
            </div>
            <div class="input-group controls">
                <label class="controls-label-inputt" for="grilla_datos_externos_posicion_botones">Posicion botones</label>
                <select class="form-control col-2" name="extra[buttons_position]">
                    <option value="bottom" '.($this->botones_position == "bottom" ? 'selected=selected': '').'>Abajo</option>
                    <option value="right_side" '.($this->botones_position == "right_side" ? 'selected=selected': '').'>Al lado</option>
                </select>
            </div>

            <div class="columnas">
                <script type="text/javascript">

                    var column_template = "'.$html_column_template.'";

                    $(document).ready(function(){
                        $(".modal-dialog.modal-lg").removeClass("modal-lg").addClass("modal-xl");

                        $("#modal").on("hide.bs.modal", function () {
                            $(".modal-dialog.modal-xl").removeClass("modal-xl").addClass("modal-lg");
                        });

                        $("#formEditarCampo .columnas .nuevo").click(function(){
                            var pos=$("#formEditarCampo .columnas table tbody tr").length;
                            var new_col = column_template.replace(/{{column_pos}}/g, pos);
                            new_col = new_col.replace(/{{([^}]+)\}}/g, "");
                            $("#formEditarCampo .columnas table tbody").append(
                                new_col
                            );
                        });
                        $("#formEditarCampo .columnas").on("click",".eliminar",function(){
                            var table = $(this).closest("table");
                            $(this).closest("tr").remove();
                            reindex_columns(table);
                        });
                    });
                    $("#grilla_datos_externos_table_text_max_length").keydown(function(evt){
                        var key_code = evt.which;
                        // solo numeros
                        if( key_code != 13 && key_code != 9 && key_code != 8 && ( key_code < 48 || key_code > 57 ) ) {
                            // 13 enter, 9 tab, 8 backspace
                            evt.preventDefault();
                            evt.stopPropagation();
                            return false;
                        }

                    });
                </script>
                <h4>Columnas</h4>
                <button class="btn btn-light nuevo" type="button"><i class="material-icons">add</i> Nuevo</button>
                <table class="table mt-3 table-striped">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Texto al agregar</th>
                            <th>Nombre del campo</th>
                            <th>Tipo de dato</th>
                            <th>Es entrada</th>
                            <th>Exportable</th>
                            <th>Validaci&oacute;n</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';

        if ($this->columns) {
            foreach ($this->columns as $key => $c) {
                $text = isset($c->modal_add_text) ? $c->modal_add_text: "";

                $column = str_replace('{{column_pos}}', $key, $column_template_html);
                $column = str_replace('{{header}}', $c->header, $column);
                $column = str_replace('{{modal_add_text}}', $text, $column);
                $column = str_replace('{{is_input}}', $c->is_input, $column);
                $column = str_replace('{{object_field_name}}', (isset($c->object_field_name) ? $c->object_field_name : ''), $column);
                if(isset($c->is_input) && $c->is_input=="true"){
                    $column = str_replace('{{is_input}}', 'true', $column);
                    $column = str_replace('{{is_input_checked}}', 'checked', $column);
                }else{
                    $column = str_replace('{{is_input}}', 'false', $column);
                    $column = str_replace('{{is_input_checked}}', '', $column);
                }

                if(isset($c->is_exportable) && $c->is_exportable=="true"){
                    $column = str_replace('{{is_exportable}}', 'true', $column);
                    $column = str_replace('{{is_exportable_checked}}', 'checked', $column);
                }else{
                    $column = str_replace('{{is_exportable}}', 'false', $column);
                    $column = str_replace('{{is_exportable_checked}}', '', $column);
                }

                if(isset($c->validacion)){
                    $column = str_replace('{{validacion}}', $c->validacion, $column);
                }else{
                    $column = str_replace('{{validacion}}', '', $column);
                }

                if(isset($c->field_type) && array_key_exists($c->field_type, $this->field_types_html)){
                    $types = $this->field_types_html;
                    $types[$c->field_type] = str_replace('{{selected}}', 'selected' , $types[$c->field_type]);
                    $s = str_replace('{{selected}}', '', join("\n", $types));
                    $column = str_replace('{{select_field_types}}', $s, $column);
                }else{
                    $column = str_replace('{{select_field_types}}',
                                    str_replace('{{selected}}', '', join("\n", $this->field_types_html)), $column);
                }

                $output .= $column;
            }
        }

        $output .= '
        </tbody>
        </table>
        </div>

        ';

        return $output;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }

    public function backendExtraValidate(Request $request)
    {
        $request->validate(['extra.columns' => 'required']);
    }

    private function is_array_associative($arr)
    {
        if ( empty( $arr) || ! is_array($arr) ) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private function load_extra_config( $modo = 'edicion' ){
        $this->columns = [];
        $this->botones = [];
        if (isset($this->extra->columns))
            $this->columns = $this->extra->columns;

        $this->agregable = false;
        if(isset($this->extra->agregable) && $this->extra->agregable == 'true'){
            $this->agregable = true;
        }

        $this->eliminable = false;
        if(isset($this->extra->eliminable) && $this->extra->eliminable == 'true' && $modo != 'visualizacion' ){
            $this->eliminable = true;
        }

        $this->editable = false;
        if(isset($this->extra->editable) && $this->extra->editable == 'true' && $modo != 'visualizacion'){
            $this->editable = true;
        }

        $this->export_as = 'array';
        if((isset($this->extra->grilla_export_as) && $this->extra->grilla_export_as == 'object')){
            $this->export_as = 'object';
        }

        $this->input_is_array = false;
        if((isset($this->extra->grilla_import_as) && $this->extra->grilla_import_as == 'array')){
            $this->input_is_array = true;
        }

        $this->tiene_acciones = false;
        if( $this->eliminable || $this->editable ){
            $this->tiene_acciones = true;
        }
        if(isset($this->extra->agregable) && $this->extra->agregable == 'true' && $modo != 'visualizacion' ){
            $this->botones[] = '<button type="button" class="btn btn-outline-secondary" onclick="open_add_modal('.$this->id.')">Agregar</button>';
        }
        if(isset($this->extra->eliminable) && $this->extra->eliminable == 'true' && $modo != 'visualizacion' ){
            $this->botones[] = '<button type="button" class="btn btn-outline-secondary" style="" onclick="grilla_datos_externos_eliminar('.$this->id.')">Eliminar</button>';
        }

        if( isset($this->extra->buttons_position) && $this->extra->buttons_position === 'bottom' && $modo != 'visualizacion' ){
            $this->botones_position = $this->extra->buttons_position;
        }else {
            $this->botones_position = 'right_side';
        }

        $this->cell_text_max_length = $this->cell_text_max_length_default;
        if(isset($this->extra->cell_text_max_length) ){
            $this->cell_text_max_length = $this->extra->cell_text_max_length;
        }

        $this->field_types_html = [];

        foreach($this->field_types as $gd_type => $human_type){
            $this->field_types_html[$gd_type] = "<option {{selected}} value='".$gd_type."'>".$human_type."</option>";
        }

    }

    public function formValidate(Request $request, $etapa_id = null)
    {

        $validator = Validator::make($request->all(), [
           $this->nombre => $this->validacion
        ]);

        if ($validator->fails()) {
           return [$this->nombre, $this->validacion];
        }

        $validations = [];

        if( $this->extra->grilla_export_as == "array" ) {

          foreach ($this->extra->columns as $key => $column) {
            $validations[] = !empty($column->validacion) ? $column->validacion : '';
          }

        }

        if( $this->extra->grilla_export_as == "object" ) {

          foreach ($this->extra->columns as $key => $column) {
            $column_name = !empty( $column->object_field_name ) ? $column->object_field_name : $column->header;
            $validations[$column_name] = !empty($column->validacion) ? $column->validacion : '';
          }

        }

        return [ $this->nombre, new GrillaDatosExternos($validations) ];
    }
}
