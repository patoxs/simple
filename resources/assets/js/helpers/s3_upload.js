
var s3_fields = {};

function set_up(unique_id, url, token, block_size, max_size, single_file_max_size){
    if(! (unique_id in s3_fields) ){
        s3_fields[unique_id] = {};
    }

    var c_s3 = s3_fields[unique_id];
    c_s3.max_size = max_size;
    c_s3.single_file_max_size = single_file_max_size; // l: 16 -1 || b: 5 - 1
    c_s3.chunk_size = block_size;
    c_s3.running_chunk_size = -1;
    c_s3.XMLHttpRequest_arr = [];
    c_s3.parts_info = [];
    c_s3.algorithm = '';
    c_s3.unique_id = unique_id;
    c_s3.base_url = url;
    c_s3.url = null;
    c_s3.token = token;
    c_s3.link_to_file = $("#link_to_file_"+unique_id);
    c_s3.hidden_name_field = $('#'+unique_id);
    c_s3.file_input = $('#file_input_'+unique_id);
    c_s3.progress_file = $('#progress_file_'+unique_id);
    c_s3.but_stop = $('#but_stop_'+unique_id);
    c_s3.segments_sent = $('#segments_sent_'+unique_id);
    c_s3.total_segments = $('#total_segments_'+unique_id);
    c_s3.but_send_file = $('#but_send_file_'+unique_id);
    c_s3.parts_div = $('#parts_div_'+unique_id);

    c_s3.segments_count = -1;

    c_s3.send_start_time = -1;
    c_s3.send_end_time = -1;
    c_s3.file_load_start_time = -1;
    c_s3.file_load_end_time = -1;

    c_s3.buffer = null;
    c_s3.fileSize = -1;
    c_s3.count = -1;
    c_s3.offset = 0;
    c_s3.file = null;

    c_s3.stop_uploading = false;

    c_s3.file_input.on('change', function(c_s3){
        return function(evt){
            c_s3.but_send_file.prop('disabled', false);
            c_s3.but_stop.prop('disabled', false);
            c_s3.file = c_s3.file_input[0].files[0];
            c_s3.filename = encodeURI(c_s3.file.name);
            c_s3.fileSize = c_s3.file.size;
            if(c_s3.fileSize >= c_s3.single_file_max_size){
                // debe ser multiupload
                c_s3.running_chunk_size = c_s3.chunk_size;
                c_s3.url = c_s3.base_url + '/multi';
                c_s3.segments_count = Math.ceil(c_s3.fileSize/ c_s3.running_chunk_size);
                c_s3.running_chunk_size = c_s3.chunk_size;
            }else{
                // debe ser single file
                if(c_s3.fileSize > c_s3.max_size){
                    c_s3.but_send_file.prop('disabled', true);
                    c_s3.but_stop.prop('disabled', true);
                    alert('El archivo supera el tamaño máximo permitido.');
                    c_s3.file = null;
                    return;
                }
                c_s3.running_chunk_size = c_s3.fileSize;
                c_s3.url = c_s3.base_url + '/single';
                c_s3.segments_count = 1;
            }

            resetSend(c_s3);
            c_s3.parts_div.show();
            c_s3.total_segments.text(c_s3.segments_count);
        }
    }(c_s3)
    );
    c_s3.but_stop.on('click', function(){
        console.log('Se quiere detener la carga de archivo ' + unique_id);
        c_s3.stop_uploading = true;
        while(c_s3.XMLHttpRequest_arr.length){
            var xhr = c_s3.XMLHttpRequest_arr.pop();
            xhr.abort();
        }
    });
    c_s3.but_send_file.on('click', start_upload(c_s3));
}

function resetSend(c_s3){
    c_s3.segments_sent.text(0);
    c_s3.count = 0;
    c_s3.file_parts_status = {};
    c_s3.offset = 0;
    c_s3.parts_div.hide();
    for(var i=0;i<c_s3.segments_count;i++){
        c_s3.file_parts_status[i] = 0;
    }
}

function onProgress(c_s3) {
    return function (e) {
        c_s3.but_send_file.prop('disabled', true);
        c_s3.but_stop.prop('disabled', true);
        c_s3.progress_file.val(e.loaded / e.total);
    };
}

function start_upload(c_s3) {
    return function(e) {
        if(c_s3.file == null){
            return;
        }
        c_s3.stop_uploading = false;
        c_s3.but_stop.prop('disabled', false);
        c_s3.but_send_file.prop('disabled', true);
        c_s3.progress_file.css('display', "inline-block");
        readBlock(c_s3);
    }
}

function readBlock(c_s3) {
    var r = new FileReader();
    var blob = c_s3.file.slice(c_s3.offset, c_s3.running_chunk_size + c_s3.offset);
    console.log(c_s3.offset, c_s3.running_chunk_size + c_s3.offset);
    r.onload = onLoadHandler(c_s3);
    r.readAsArrayBuffer(blob);
}

function onLoadHandler(c_s3){
    return function(evt){
        if( evt.target.error != null){
            console.error('Ocurrio un error ', evt.target.error);
            return;
        }
        if(c_s3.stop_uploading){
            console.log('Deteniendo	! ' + c_s3.unique_id);
            return;
        }
        c_s3.offset += evt.target.result.byteLength;
        
        c_s3.count++;
        send_chunk(evt.target.result, c_s3);
    }
}

function send_chunk(chunk, c_s3) {
    var part_number = c_s3.count;
    var url = c_s3.url + '/' + part_number + '/' + c_s3.segments_count;
    var xhr = new XMLHttpRequest();
    c_s3.XMLHttpRequest_arr.push(xhr);

    var chunk = new Uint8Array(chunk);
    // el contador empieza en 1
    c_s3.progress_file.val((c_s3.count - 1) / c_s3.segments_count);

    xhr.addEventListener("progress", function(c_s3, chunk_size){
        return function(evt){
            // evt.loaded , evt.total;
        }
    }(c_s3, chunk.byteLength));

    xhr.addEventListener('load', function(c_s3, part_number, xhr){
        return function (evt) {
            try{
                var xhr_response = JSON.parse( xhr.response );
            }catch(e){
                console.error(e);
                alert('Error al cargar el archivo.');
                return;
            }
            if(! xhr_response.success) {
                c_s3.file_parts_status[part_number] = -3;
                console.error('Error al enviar', evt);
                var pos = c_s3.XMLHttpRequest_arr.indexOf(this);
                if(pos >= 0){
                    c_s3.XMLHttpRequest_arr.splice(pos, 1);
                }
                alert("Ocurrió un error al cargar el archivo.");
                return;
            }
            c_s3.file_parts_status[part_number] = 1;
            c_s3.segments_sent.text(part_number);

            c_s3.parts_info[xhr_response.part_number -1 ] = {
                'hash': xhr_response.hash, 
                'algorithm': xhr_response.algorithm
            }
            
            if(c_s3.count < c_s3.segments_count){
                readBlock(c_s3);
            }else if(xhr_response.hasOwnProperty('success')){
                // fin de enviar el archivo completo :-D
                if(xhr_response.success){

                    c_s3.progress_file.val( c_s3.progress_file.prop('max') );
                    c_s3.link_to_file.attr('href', xhr_response.URL + '/' + xhr_response.file_name);
                    c_s3.link_to_file.text(xhr_response.file_name);
                    var hidden_new_value = {
                        URL: window.location.origin + xhr_response.URL + '/' + xhr_response.file_name,
                        info: {
                            parts: c_s3.parts_info,
                            part_max_size: c_s3.running_chunk_size
                        }
                    }   
                    c_s3.hidden_name_field.val(JSON.stringify(hidden_new_value));
                }else{
                    alert(xhr_response.error);
                }
            }
            var pos = c_s3.XMLHttpRequest_arr.indexOf(this);
            if(pos >= 0){
                c_s3.XMLHttpRequest_arr.splice(pos, 1);
            }
        }
    }(c_s3, part_number, xhr));

    xhr.addEventListener("error", function(evt){
        c_s3.file_parts_status[part_number] = -1;
        console.error('Error al enviar', evt);
        var pos = c_s3.XMLHttpRequest_arr.indexOf(this);
        if(pos >= 0){
            c_s3.XMLHttpRequest_arr.splice(pos, 1);
        }
    });
    xhr.addEventListener("abort", function(evt){
        c_s3.file_parts_status[part_number] = -2;
        console.error('Abortado al enviar', evt)
        var pos = c_s3.XMLHttpRequest_arr.indexOf(this);
        if(pos >= 0){
            c_s3.XMLHttpRequest_arr.splice(pos, 1);
        }
    });
    xhr.open("POST", url, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', c_s3.token);
    xhr.setRequestHeader('filename', c_s3.filename);
    xhr.setRequestHeader('Content-Type', 'application/octet-stream');
    xhr.send(chunk);
}

function set_default_s3_hidden(unique_id){
    var c_s3 = s3_fields[unique_id];
    var hidden_default_value = {
        'URL': '',
        'info': {
            'parts': [],
            'part_max_size': -1
        }
    }
    c_s3.hidden_name_field.val(JSON.stringify(hidden_default_value));
}

function set_s3_hidden(unique_id, url, info){
    var c_s3 = s3_fields[unique_id];
    var hidden_value = {
        'URL': url,
        'info': info
    }
    c_s3.hidden_name_field.val(JSON.stringify(hidden_value));
}
