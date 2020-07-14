<?php
require_once('campo.php');

use Illuminate\Http\Request;
use App\Helpers\Doctrine;

class CampoGrid extends Campo
{
    private $javascript;

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

        $columns = $this->extra->columns;


        $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<div class="grid" data-id="' . $this->id . '" style="display: block;"></div>';
        $display .= '<input type="hidden" name="' . $this->nombre . '" value=\'' . ($dato ? json_encode($dato->valor) : $valor_default) . '\' />';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){
                    var mode = "' . $modo . '";
                    var columns = ' . json_encode($columns) . ';

                    var headers = columns.map(function(c){return c.header;});

                    var data;

                    try{
                        data = JSON.parse($("[name=\'' . $this->nombre . '\']").val());
                        data = data.slice(1);
                    }catch(err){
                        data = [
                          new Array(headers.length)
                        ];
                    }


                    $(".grid[data-id=' . $this->id . ']").handsontable({
                      data: data,
                      readOnly: mode=="visualizacion",
                      minSpareRows: 1,
                      rowHeaders: false,
                      colHeaders: headers,
                      columns: columns,
                      contextMenu: true,
                      stretchH: "all",
                      autoWrapRow: true,
                      afterChange: function (change, source) {
                        var rows = this.getData().slice();
                        rows.unshift(headers);
                        var json = JSON.stringify(rows);
                        $("[name=\'' . $this->nombre . '\']").val(json);
                        //var audio = new Audio(base_url+"assets/audio/grillas.mp3");
                        //audio.play();
                      }
                    });
                  });
            </script>
        ';

        return $display;
    }

    public function backendExtraFields()
    {

        $columns = array();
        if (isset($this->extra->columns))
            $columns = $this->extra->columns;

        $output = '
            <div class="columnas">
                <script type="text/javascript">
                    $(document).ready(function(){
                        $("#formEditarCampo .columnas .nuevo").click(function(){
                            var pos=$("#formEditarCampo .columnas table tbody tr").length;
                            var html="<tr>";
                            html+="<td><input type=\'text\' name=\'extra[columns]["+pos+"][header]\' class=\'form-control\' /></td>";
                            html+="<td><select class=\'form-control\' name=\'extra[columns]["+pos+"][type]\' ><option>text</option><option>numeric</option></select></td>";
                            html+="<td><button type=\'button\' class=\'btn btn-light eliminar\'><i class=\'icon-remove\'></i> Eliminar</button></td>";
                            html+="</tr>";

                            $("#formEditarCampo .columnas table tbody").append(html);
                        });
                        $("#formEditarCampo .columnas").on("click",".eliminar",function(){
                            $(this).closest("tr").remove();
                            reindex_columns();
                        });
                    });

                    function reindex_columns(){
                        var table = $("#formEditarCampo table").first()
                        var num = -2; // la primera fila (0) es headers
                        table.find("tr").each(
                            function(tr_index, tr_ele){
                                num++;
                                $(this).find("td").each(
                                    function(td_index, td_ele){
                                        $(this).find(":input").each(function(child_index, child_ele){
                                            var old_name= $(child_ele).attr("name");
                                            if( typeof old_name === "undefined"){
                                                // this un elemento que usa name
                                                $(this).data("rownum", num);
                                                return;
                                            }
                    
                                            var new_name = old_name.substring(0, old_name.indexOf("[", old_name.indexOf("[") + 1) + 1);
                                            new_name += num;
                                            new_name += old_name.substring(old_name.indexOf("]", old_name.indexOf("]") + 1) );
                                            $(this).attr("name", new_name);
                                        })
                                    }
                                )
                            }
                        )
                    }                    
                </script>
                <h4>Columnas</h4>
                <button class="btn btn-light nuevo" type="button"><i class="material-icons">add</i> Nuevo</button>
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';

        if ($columns) {
            $i = 0;
            foreach ($columns as $key => $c) {
                $output .= '
                <tr>
                    <td><input class="form-control" type="text" name="extra[columns][' . $i . '][header]" value="' . $c->header . '" /></td>
                    <td><select class="form-control" name="extra[columns][' . $i . '][type]"><option ' . ($c->type == 'text' ? 'selected' : '') . '>text</option><option ' . ($c->type == 'numeric' ? 'selected' : '') . '>numeric</option></select></td>
                    <td><button type="button" class="btn btn-light eliminar"><i class="material-icons">close</i> Eliminar</button></td>
                </tr>
                ';
                $i++;
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

}