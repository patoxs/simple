var grillas_datatable = {};

var nombre_columna_acciones = '_acciones_';
var grid_accion_eliminar = "<input type='checkbox' onclick='selectToDelete(event, this)' \
style='margin:0;vertical-align:middle;' />\
<button type='button' class='btn btn-outline-secondary btn_grid_action' onclick='deleteRow(event, this)' \
style='margin:0;'>\
<i class='material-icons'>delete</i></button>";

var grid_accion_editar = "<button type='button' class='btn btn-outline-secondary btn_grid_action' \
style='margin:0;' onclick='edit_row(event, this)'>\
<i class='material-icons'>edit</i></button>";

var grid_acciones = "<div>{{accion_eliminar}}{{accion_editar}}</div>";

var grilla_datos_externos_eliminar = function(grilla_id){
    if(grillas_datatable[grilla_id].table.rows('.to_delete').data().length <= 0){
        return;
    }
    if(!confirm('Va a eliminar las filas seleccionadas. ¿Desea continuar?')) return;
    grillas_datatable[grilla_id].table.rows('.to_delete').remove().draw(true);
    store_data_in_hidden(grilla_id);
}

var modal_agregar_a_grilla = function(grilla_id){
    var modal_grande = $('#table_alter_modal_'+grilla_id);
    var tiene_acciones = grillas_datatable[grilla_id].tiene_acciones;
    var is_array = grillas_datatable[grilla_id].is_array;
    var to_add = is_array ? []: {};
    var headers = (is_array ? grillas_datatable[grilla_id].headers_array : grillas_datatable[grilla_id].headers_object);
    var some_count = 0;

    $('#modal-body-'+grilla_id, 'form').find('.modal_input').each(function(idx, elemento) {
        var el = $(elemento);
        if(el.val().toString().length > 0)
            some_count++;

        if(is_array){
            to_add.push(el.val());
        }else{
            var header_key = headers[el.data('column')].data;
            if(header_key === undefined)
                header_key = headers[el.data('column')].title;
            to_add[header_key] = el.val();
        }
    });

    modal_grande.modal("hide");

    if( some_count <= 0  )
        return;

    if(tiene_acciones){
        if(is_array){
            to_add.push( grillas_datatable[grilla_id].grid_acciones);
        }else{
            to_add[nombre_columna_acciones] = grillas_datatable[grilla_id].grid_acciones;
        }
    }

    grillas_datatable[grilla_id].table.row.add( to_add ).draw( true );
    store_data_in_hidden(grilla_id);
}

var deleteRow = function(evt, obj){
    evt.stopPropagation();
    evt.preventDefault();
    evt.cancelBubble = true;
    if(!confirm('Va a eliminar la fila. ¿Desea continuar?')) return;
    var c_tr = $(obj).closest('tr:parent');
    var grilla_id = $(obj).closest('table:parent').data('grilla_id');
    grillas_datatable[grilla_id].table.row(c_tr).remove().draw(true);
    store_data_in_hidden(grilla_id);
    return false;
}

var selectToDelete = function(evt, obj){
    var cls = 'to_delete';
    var c_tr = $(obj).closest('tr:parent');
    var c_checkbox = $(obj).prev('input');
    var status = c_tr.hasClass(cls);
    c_checkbox.prop('checked', !!! status);
    if( ! status ){
        c_tr.addClass(cls);
    }else{
        c_tr.removeClass(cls);
    }
    evt.stopPropagation();
    evt.cancelBubble = true;
    return false;
}

var grilla_populate_objects = function(grilla_id, data){
    // debe coincidir con la cantidad de columnas en la tabla, pero no viene ese campo ya que es un checkbox
    var tiene_acciones = grillas_datatable[grilla_id].tiene_acciones;
    var headers_obj = grillas_datatable[grilla_id].headers_object;

    var headers = headers_obj.map(function(c){return c.data;});

    if(tiene_acciones){
        grillas_datatable[grilla_id].cantidad_columnas--;
    }

    for(var i=0; i<data.length;i++){
        for(var key in data[i]){
            if(data[i].hasOwnProperty(key) && headers.indexOf(key) == -1 ){
                delete data[i][key];
            }
        }
        for (var key in headers) {
            if (! headers.hasOwnProperty(key)) {
                continue;
            }
            if(!(data[i].hasOwnProperty( headers[key] ))){
                // agregamos lo que falta
                data[i][ headers[key] ] = '';
            }
        }

        if(tiene_acciones)
            data[i][nombre_columna_acciones] = grillas_datatable[grilla_id].grid_acciones;
    }

    grillas_datatable[grilla_id].data = data;
    grillas_datatable[grilla_id].table.rows.add( data ).draw( true );
}

var grilla_populate_arrays = function(grilla_id, data){
    // debe coincidir con la cantidad de columnas en la tabla, pero no viene ese campo ya que es un checkbox
    var tiene_acciones = grillas_datatable[grilla_id].tiene_acciones;
    var cols_num = tiene_acciones ? grillas_datatable[grilla_id].cantidad_columnas -1 : grillas_datatable[grilla_id].cantidad_columnas;

    for(var i=0; i<data.length;i++){
        if(data[i].length > cols_num){
            data[i] = data[i].slice(0, grillas_datatable[grilla_id].cantidad_columnas );
        }

        for(var j=0;j <data[i].length;j++){
            if(data[i][j] == null)
                data[i][j] = '';
        }
        while(data[i].length < cols_num){
            data[i].push('');
        }

        if(tiene_acciones)
            data[i].push(grillas_datatable[grilla_id].grid_acciones);

    }

    grillas_datatable[grilla_id].data = data;

    grillas_datatable[grilla_id].table.rows.add( data ).draw( true );

}

var add_tooltips = function(grilla_id){
    var max_cell_length = grillas_datatable[grilla_id].cell_text_max_length;

    $("#grilla-"+grilla_id).find('tr').each(function(index, tr_element){
        if(index < 1) return; // es header
        var self = $(this);
        // this es tr

        var last_column_index = $(tr_element).find('td').length;
        if(grillas_datatable[grilla_id].tiene_acciones)
            --last_column_index;

        $(tr_element).find('td').each(function(index, td_element){
            if(index >= last_column_index)
                return;
            var td_jquery = $(td_element);
            var text = td_jquery.text();

            td_jquery.attr('title', text);
            if(text.length > max_cell_length){
                td_jquery.attr('data-toggle', 'tooltip');
                td_jquery.attr('data-placement', 'top');
                td_jquery.text(text.slice(0, max_cell_length) + '...');
            }else{
                // al alicarse el tooltip, title vacio y su contenido para a data-original-title
                // y lo necesitamos para editr
                td_jquery.attr('data-original-title', text);
            }
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
}

var init_tables = function(grilla_id, mode, columns, cell_text_max_length, is_array, is_editable, is_eliminable){
    // var mode = "edicion"; ejemplo
    var tr_header_obj = $("#grilla-" + grilla_id + " tr:first");
    var modal_form = $("#table_alter_modal_" + grilla_id + " .modal-body", "form");

    var thead_html = "<th scope='col'>{{text}}</th>\n";
    var modal_form_input_html = '<div class="form-group"><label for="_" class="col-form-label">{{text}}:</label>' +
                                    ' <input type="text" class="form-control modal_input" ' +
                                        ' data-campo_id="{{campo_id}}" ' +
                                        ' data-etiqueta="{{text}}" ' +
                                        ' data-field_type={{field_type}} ' +
                                        ' data-column="{{column}}" onFocusOut="modal_input_validate(this)"></div>';
    var modal_form_not_input = '<input type="hidden" class="modal_input" data-column="{{column}}" data-field_type={{field_type}}>';
    var modal_validate_errors = '<ul id="{{id}}" style="margin:0px;"></ul>';
    grillas_datatable[grilla_id].cell_text_max_length = cell_text_max_length;
    grillas_datatable[grilla_id].is_array = is_array;
    grillas_datatable[grilla_id].is_eliminable = is_eliminable;
    grillas_datatable[grilla_id].is_editable = is_editable;
    grillas_datatable[grilla_id].is_modal_valid = false;
    var accion_eliminar = is_eliminable ? grid_accion_eliminar: '';
    var accion_editar = is_editable ? grid_accion_editar: '';
    grillas_datatable[grilla_id].grid_acciones = grid_acciones.replace('{{accion_eliminar}}', accion_eliminar).replace('{{accion_editar}}', accion_editar);

    grillas_datatable[grilla_id].exportable_columns_indexes = [];
    grillas_datatable[grilla_id].exportable_columns_names = [];
    grillas_datatable[grilla_id].exportable_columns_names_flat = [];
    grillas_datatable[grilla_id].headers_object = [];
    grillas_datatable[grilla_id].headers_array = [];
    grillas_datatable[grilla_id].field_types = [];

    for(var i=0;i<columns.length;i++){
        // creamos el arreglo de cabeceras
        if(typeof columns[i].object_field_name == 'undefined' || columns[i].object_field_name == null){
            // Alertar
            columns[i].object_field_name = columns[i].header;
        }
        grillas_datatable[grilla_id].field_types.push( columns[i].field_type )
        grillas_datatable[grilla_id].headers_array.push({title: columns[i].header});
        grillas_datatable[grilla_id].headers_object.push({
            data: columns[i].object_field_name,
            title: columns[i].header
        });

        tr_header_obj.append(thead_html.replace("{{text}}", columns[i].header));
        if( columns[i].is_exportable=="true"){
            grillas_datatable[grilla_id].exportable_columns_indexes.push(i)
            grillas_datatable[grilla_id].exportable_columns_names.push({title:columns[i].header, data: columns[i].object_field_name});
            grillas_datatable[grilla_id].exportable_columns_names_flat.push(columns[i].object_field_name);
        }

        // creamos el modal para agregar y editar registros
        if( typeof columns[i].modal_add_text == 'undefined' || columns[i].modal_add_text == null)
                columns[i].modal_add_text = columns[i].header;

        var new_element;
        if(columns[i].is_input=="true"){
            new_element = modal_form_input_html;
        }else{
            new_element = modal_form_not_input;
        }
        modal_form.append(
            new_element.replace(/{{text}}/g, columns[i].modal_add_text)
                                 .replace("{{column}}", i)
                                 .replace('{{campo_id}}', grilla_id)
                                 .replace('{{field_type}}', columns[i].field_type)
        );

        $('#ajax-alert_'+grilla_id).append(modal_validate_errors.replace('{{id}}', 'ajax-alert_'+grilla_id+'_'+ i) )

    }

    if(grillas_datatable[grilla_id].tiene_acciones)
        tr_header_obj.append(thead_html.replace("{{text}}", "Acciones"));

    if(grillas_datatable[grilla_id].tiene_acciones){
        grillas_datatable[grilla_id].headers_array.push({title: 'Acciones'});
        grillas_datatable[grilla_id].headers_object.push({
            title: 'Acciones', data: nombre_columna_acciones
        });
    }

    $("#table_alter_modal_" + grilla_id).find(".form-control.modal_input").keypress(function(evt){
        // al presionar "enter" se debe "aceptar" el modal
        if ( evt.which == 13 ){
            $(this).next().focus();
            evt.preventDefault();
            $('#modal_accept_button_' + grilla_id).click();
            return false;
        }
    });

    if( grillas_datatable[grilla_id].is_array ){
        var headers = grillas_datatable[grilla_id].headers_array;
    }else{
        var headers = grillas_datatable[grilla_id].headers_object;
    }

    grillas_datatable[grilla_id].table = $("#grilla-"+grilla_id).DataTable({language:
            {"sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No hay registros que mostrar",
                "sInfo": "Mostrando desde _START_ hasta _END_ de _TOTAL_ registros",
                "sInfoEmpty": "No existen registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ líneas)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "paginate": {
                    "previous": "Anterior ",
                    "next": " Siguiente"
                    }
            },
            select: true,
            responsive: true,
            columnDefs: [{
                className: "display"
            }],
            columns: headers
    }).draw(true);

    grillas_datatable[grilla_id].table.on( 'draw', grilla_id, function (event, settings) {
        grilla_id = event.data;
        add_tooltips(grilla_id);
    });

    grillas_datatable[grilla_id].table.on("click", "tbody tr", grilla_id, function (event) {
        grilla_id = event.data;
        if( ! grillas_datatable[grilla_id].is_editable) {
            return;
        }

        if( (event.target.tagName.toLocaleLowerCase() == 'td' || event.target.tagName.toLocaleLowerCase() == 'div' ) && $(event.target).children('button').length > 0 ){
            return;
        }

        var j_row = $(this);
        var dt_row = grillas_datatable[grilla_id].table.row( this );
        var modal = $("#table_alter_modal_" + grilla_id );
        var current_values = [];
        var table_selector = 'td';

        if(j_row.has('.dataTables_empty').length > 0){
            // Se hizo click en la fila que muestra "no hay registros que mostrar"
            return;
        }

        if(grillas_datatable[grilla_id].tiene_acciones){
            table_selector += ':not(:last-child)';
        }

        j_row.children(table_selector).each(function(idx, ele){
            current_values.push($(ele).attr('data-original-title'));
        });

        modal.find('input').each(function(idx, ele){
            $(ele).val( current_values[idx] );
        });

        $('#add_to_table_modal_label_'+grilla_id).text('Editar Registro')
        var click_data = {
            grilla_id: grilla_id,
            dt_row: dt_row,
            modal: modal
        }

        $('#modal_accept_button_' + grilla_id).on("click", click_data, function(event){
            // se gatilla al hacer click en editar
            var d = event.data;
            var grilla_id = d.grilla_id;
            var dt_row = d.dt_row;
            var modal = d.modal;
            // validamos el modal
            modal_validate_multi(grilla_id).then(
                function(grilla_id, dt_row, modal){
                    return function(){
                        if(!grillas_datatable[grilla_id].is_modal_valid)
                          return;
                        modal_modificar_linea( grilla_id, dt_row, modal)
                        modal.modal("hide");
                        store_data_in_hidden(grilla_id);
                    }
                }(grilla_id, dt_row, modal)
            );
        });
        modal.modal('show');
    });
    $('#'+grilla_id).on('change', grilla_id, function(event){
        var grilla_id = event.data;
        var json_str = event.target.value;
        if(json_str.length <= 1)
            return;
        var data = JSON.parse(json_str);
        var replace_data = true;
        add_data_to_table(grilla_id, data, replace_data);
    });

    $("#table_alter_modal_" + grilla_id).on('hide.bs.modal', function(event){
        var ajax_alert = $('#ajax-alert_' + grilla_id);
        ajax_alert.find('li').remove();
        ajax_alert.hide();
        $('#modal-body-'+grilla_id, 'form').find(':input:not([type=hidden])').each(function(idx, elemento) {
            $(elemento).val("");
        });
        $('#modal_accept_button_' + grilla_id).prop("onclick", null).off("click");
    });
}

var edit_row = function(evt, obj) {
    evt.stopPropagation();
    evt.preventDefault();
    evt.cancelBubble = true;
    $(obj).parents('tr').first().click();
    var grilla_id = $(obj).closest('table:parent').data('grilla_id');
    store_data_in_hidden(grilla_id);
    return false;
}

var modal_modificar_linea = function( grilla_id, dt_row, modal){
    var table = grillas_datatable[grilla_id].table;
    modal.find('input').each(function(idx, ele){
        table.cell(dt_row, idx).data( $(ele).val() );
    });

    table.draw(true);
    add_tooltips(grilla_id);
}

var cambiar_estado_entrada = function(obj, pos){
    var columna_entrada = $('input[name="extra[columns]['+pos+'][is_input]"]');
    columna_entrada.val($(obj).prop('checked'));
}

var cambiar_exportable = function(obj, pos){
    var columna_entrada = $('input[name="extra[columns]['+pos+'][is_exportable]"]');
    columna_entrada.val($(obj).prop('checked'));
}

var toggle_checkbox = function(name, obj){
    var v = $(obj).prop("checked");
    $("input[name=\'extra[" + name + "]\']").val(v);
}

var open_add_modal = function(grilla_id) {
    var modal = $("#table_alter_modal_" + grilla_id );
    $('#add_to_table_modal_label_'+grilla_id).text('Nuevo Registro');
    $('#modal-body-'+grilla_id, 'form').find(':input:not([type=hidden])').each(function(idx, elemento) {
        $(elemento).val("");
    });

    $('#modal_accept_button_' + grilla_id).prop("onclick", null).off("click");
    $('#modal_accept_button_' + grilla_id).on("click", grilla_id, function(event){
        grilla_id = event.data;
        modal_agregar_a_grilla( grilla_id);
    });

    $('#modal_accept_button_' + grilla_id).on("click", grilla_id, function(event){
        // Agregar fila
        grilla_id = event.data;
        modal_validate_multi(grilla_id).then(
            function(grilla_id){
                return  function(){
                    if(grillas_datatable[grilla_id].is_modal_valid)
                        modal_agregar_a_grilla( grilla_id);
                }
            }(grilla_id));
    });
    modal.modal('show');
}

var reindex_columns = function(table){
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

function store_data_in_hidden(grilla_id){
    // construir arreglos
    var data = [];
    var exportable_columns_indexes = grillas_datatable[grilla_id].exportable_columns_indexes;
    var is_array = grillas_datatable[grilla_id].is_array;
    var field_types = grillas_datatable[grilla_id].field_types;
    var change_type = {
        "string": function(variable){
            return variable;
        },
        "float": function(variable){
            return parseFloat(variable);
        },
        'integer': function(variable){
            return parseInt(variable);
        },
        'boolean': function(variable){
            var v = null;
            if (variable.toLocaleLowerCase() == 'true')
                v = true;
            else if(variable.toLocaleLowerCase() == 'false')
                v = false;
        }
    }
    if(grillas_datatable[grilla_id].exportable_columns_indexes.length <= 0){
        $("#"+grilla_id).val( JSON.stringify([]) );
        return;
    }else{
        if( grillas_datatable[grilla_id].export_as === 'array' ){
            // exportar como arreglos
            var data = [];
            grillas_datatable[grilla_id].table.rows(
                function(row_index, row){
                    new_row = [];
                    if( ! is_array )
                        var columnas = grillas_datatable[grilla_id].exportable_columns_names_flat;
                    else
                        var columnas = grillas_datatable[grilla_id].exportable_columns_indexes;

                    columnas.forEach(function(ele, idx){
                        try{
                            new_row.push( change_type[field_types[idx]](row[ele]) );
                        }catch(exc){
                            console.warn('Se intento usar el tipo: ' + field_types[idx], exc);
                            new_row.push( row[ele] );
                        }
                    });

                    data.push(new_row);
                }
            );
        }else{
            // exportar como objetos
            grillas_datatable[grilla_id].table.rows(
                function(row_index, row){
                    var dd = {};
                    if( is_array ){
                        var header_names = grillas_datatable[grilla_id].exportable_columns_names_flat;
                        exportable_columns_indexes.forEach(function(ele, idx){
                            try{
                                dd[ header_names[ele] ] = change_type[field_types[ele]](row[ele]);
                            }catch(exc){
                                dd[ header_names[ele] ] = row[ele];
                            }
                        });
                    }else{
                        grillas_datatable[grilla_id].exportable_columns_names_flat.forEach(function(ele, idx){
                            try{
                                dd[ele] = change_type[field_types[idx]](row[ele]);
                            }catch(exc){
                                dd[ele] = row[ele];
                            }
                        });
                    }

                    data.push(dd);
                }
            );
        }
    }

    if( data.length > 0){
        $("#"+grilla_id).val( JSON.stringify(data) );
    }else{
        $("#"+grilla_id).val( '' );
    }
}

function add_data_to_table(grilla_id, data, replace){

    if( typeof replace !== 'undefined' && replace === true){
        grillas_datatable[grilla_id].table.clear().draw(true);
    }

    if(data.length == 0){
        console.warn('data vacia.');
        return;
    }

    if( Array.isArray( data[0] ) ){
        grilla_populate_arrays(grilla_id, data);
    }else{
        grilla_populate_objects(grilla_id, data);
    }
    store_data_in_hidden(grilla_id);
}

function modal_validate_multi(grilla_id){
    data = {};
    $('#modal-body-' + grilla_id).find('input').not(':hidden').each(function(idx, obj){
        data[ $(obj).data('column') ] = {
            campo_id: $(obj).data('campo_id'),
            columna: $(obj).data('column'),
            valor: $(obj).val(),
            etiqueta: $(obj).data('etiqueta')
        }
    });

    grillas_datatable[grilla_id].is_modal_valid = false;
    context = {
        campo_id: grilla_id,
        data: data
    }

    return modal_validate(context, data);
}

function modal_input_validate(obj){
    data = {};
    data[ 0 ] = {
        campo_id: $(obj).data('campo_id'),
        columna: $(obj).data('column'),
        valor: $(obj).val(),
        etiqueta: $(obj).data('etiqueta')
    }

    grillas_datatable[$(obj).data('campo_id')].is_modal_valid = false;
    context = {
        campo_id: $(obj).data('campo_id'),
        data: data
    }
    modal_validate(context, data);
}

function modal_validate(context, data){
    return $.ajax({
        url: '/etapas/validar_campos_async',
        type: 'POST',
        dataType: 'JSON',
        context: context,
        data: {campos: data} // para evitar
    }).then(function(data){
        if(data['code'] == -1){
            console.warn(data);
            return;
        }

        var grilla_id = this.campo_id; // son iguales
        var ajax_alert = $('#ajax-alert_' + grilla_id);
        if( data.hasOwnProperty('messages') ){
            // hay errores
            var messages = data['messages'];
            for (var index in messages) {
                if ( ! messages.hasOwnProperty(index)) {
                    continue;
                }
                var column = data['columnas'][index];
                var ul = ajax_alert.find('#ajax-alert_' + grilla_id + '_' + column);
                ul.find('li').remove();

                messages[index].forEach(function(msg){
                    ul.append('<li>' + msg + '</li>');
                });

                grillas_datatable[grilla_id].is_modal_valid = false;
                ajax_alert.show();
            }
        }else if( ! data.hasOwnProperty('messages') ){
            // exito
            for (var index in data['columnas']) {
                if ( ! data['columnas'].hasOwnProperty(index)) {
                    continue;
                }
                var ul = ajax_alert.find('#ajax-alert_' + grilla_id + '_' + data['columnas'][index]);
                ul.find('li').remove();
            }
        }
        if( ajax_alert.find('li').length == 0){
            ajax_alert.hide();
            grillas_datatable[grilla_id].is_modal_valid = true;
        }
    });
}
