$(document).ready(function () {

    $('.navbar-detail').hide();
    $("#main").css("margin-top", $("header").height());

    $('#sidebar_head').click(function () {
        $('.navbar-detail').toggle();
    });

    $(window).resize(function () {
        $('.navbar-detail').hide();
        $("#main").css("margin-top", $("header").height());
    });

    $("[data-toggle=popover]").popover();

    $(".chosen").chosen();

    $(".preventDoubleRequest").one("click", function () {
        $(this).click(function () {
            return false;
        });
    });

    $(".file-uploader").each(function (i, el) {
        var $parentDiv = $(el).parent();
        console.log($(el).data("action"));
        new qq.FileUploader({
            params: {_token: window._token},
            element: el,
            action: $(el).data("action"),
            method: 'post',
            onComplete: function (id, filename, respuesta) {
                if (!respuesta.error) {
                    if (typeof(respuesta.file_name) !== "undefined") {
                        $parentDiv.find(":input[type=hidden]").val(respuesta.file_name);
                        $parentDiv.find(".qq-upload-list").empty();
                        $parentDiv.find(".link").html("<a target='blank' href='/uploader/datos_get/" + respuesta.id + "/" + respuesta.llave + "'>" + respuesta.file_name + "</a> (<a href='#' class='remove'>X</a>)");
                        prepareDynaForm(".dynaForm");
                    } else {
                        $parentDiv.find(".link").html("");
                        alert("La imagen es muy grande");
                    }
                }
            }
        });
    });

    $(".file-uploader").parent().on("click", "a.remove", function () {
        var $parentDiv = $(this).closest("div");
        $parentDiv.find(":input[type=hidden]").val("");
        $parentDiv.find(".link").empty();
        $parentDiv.find(".qq-upload-list").empty();
        prepareDynaForm(".dynaForm");
    });

    $("#login .submit").click(function () {
        var form = $("#login");
        if (!$(form).prop("submitting")) {
            $(form).prop("submitting", true);
            $('#login .ajaxLoader').show();

            $.ajax({
                url: $(form).prop("action"),
                data: $(form).serialize(),
                type: $(form).prop("method"),
                dataType: "json",
                success: function (response) {
                    if (response.validacion) {
                        if (response.redirect) {
                            window.location = response.redirect;
                        } else {
                            var f = window[$(form).data("onsuccess")];
                            f(form);
                        }
                    } else {
                        if ($('#login_captcha').length > 0) {
                            if ($('#login_captcha').is(':empty')) {
                                grecaptcha.render('login_captcha', {
                                    'sitekey': site_key
                                });
                            } else {
                                grecaptcha.reset();
                            }
                        }

                        $(form).prop("submitting", false);
                        $('#login .ajaxLoader').hide();

                        $(".validacion_login").html(response.errores);
                        $('html, body').animate({
                            scrollTop: $(".validacion_login").offset().top - 10
                        });
                    }
                },
                error: function () {
                    $(form).prop("submitting", false);
                    $('#login .ajaxLoader').hide();
                }
            });
        }
        return false;
    });

    prepareDynaForm(".dynaForm");

    $(".dynaForm").on("change", ":input", function (event) {
        prepareDynaForm($(event.target).closest(".dynaForm"))
    });
});





function prepareDynaForm(form){
    $(form).find(":input[readonly]").prop("disabled", false);
    $(form).find(".file-uploader ~ input[type=hidden]").prop("type", "text");
    $(form).find(".campo[data-dependiente-campo]").each(function (i, el) {
        var tipo = $(el).data("dependiente-tipo");
        var relacion = $(el).data("dependiente-relacion");
        var campo = $(el).data("dependiente-campo");
        var valor = $(el).data("dependiente-valor");
        var existe = false;
        var visible = false;
        var imprimir = false;
        var condicion_final = $(el).data("condicion");
        if(condicion_final!='no-condition'){
            var myarr = condicion_final.split("&&");
            var resultados = [];
            for(x=0;x<myarr.length;x++){
                var evaluacion = myarr[x].split(";");
                var resultado = false;
                if(evaluacion[1]=="=="){
                    $(form).find(":input[name='"+evaluacion[0]+"']").each(function (i, el) {
                        var input = $(el).serializeArray();
                        for(var j in input){
                            if(evaluacion[3]=='string'){
                                if(input[j].value==evaluacion[2]){
                                    resultado = true;
                                }
                            }else if(evaluacion[3]=='regex'){
                                var regex = new RegExp(evaluacion[2]);
                                if (regex.test(input[j].value)) {
                                    resultado = true;
                                }
                            }
                        }
                    });
                }else if(evaluacion[1]=="!="){
                    $(form).find(":input[name='"+evaluacion[0]+"']").each(function (i, el) {
                        var input = $(el).serializeArray();
                        for(var j in input){
                            if(input[j].value!=evaluacion[2]){
                                resultado = true;
                            }else if(evaluacion[3]=='regex'){
                                var regex = new RegExp(evaluacion[2]);
                                if (regex.test(input[j].value)) {
                                    resultado = true;
                                }
                            }
                        }
                    });
                }else if (evaluacion[1]==">" || evaluacion[1]=="<" || evaluacion[1]=="<=" || evaluacion[1]==">="){ //aqui estoy evaluando los nuevos caracteres de comparacion
                     $(form).find(":input[name='"+evaluacion[0]+"']").each(function (i, el) {
                        var input = $(el).serializeArray();
                        for(var j in input){
                            if(evaluacion[1]==">" && input[j].value && Number(input[j].value) > Number(evaluacion[2])){
                                resultado = true;
                            }else if(evaluacion[1]=="<" && input[j].value && Number(input[j].value) < Number(evaluacion[2])){
                                resultado = true;
                            }else if(evaluacion[1]=="<=" && input[j].value && Number(input[j].value) <= Number(evaluacion[2])){
                                resultado = true;
                            }else if(evaluacion[1]==">=" && input[j].value && Number(input[j].value) >= Number(evaluacion[2])){
                                resultado = true;
                            }
                        }
                    });
                }
                resultados.push(resultado);
            }

            if(resultados.indexOf(false)>-1){
                $(el).hide();
            }else{
                $(el).show();
            }
        }else{
            $(form).find(":input[name='" + campo + "']").each(function (i, el){
                if (existe=false) {
                   var input = $(el).serializeArray();
                }else 
                 existe = true;
                var input = $(el).serializeArray();
                for (var j in input) {
                    if(tipo == "regex"){
                        var regex = new RegExp(valor);
                        if (regex.test(input[j].value)) {
                            visible = true;
                        }                       
                    }else if(tipo == "numeric" || tipo == "string"){
                        if (relacion == "==" && input[j].value && input[j].value == valor){
                            visible = true;
                        }else if (relacion == "!=" && input[j].value && input[j].value != valor){
                            visible = true;
                        }else if (relacion == "<=" && input[j].value && input[j].value <= valor){
                            visible = true;
                        }else if (relacion == ">=" && input[j].value && input[j].value >= valor){
                            visible = true;
                        }else if (relacion == "<" && input[j].value && input[j].value < valor){
                            visible = true;
                        }else if (relacion == ">" && input[j].value && input[j].value > valor){ 
                            visible = true;
                        }
                    }
                    if (visible){
                        break;
                    }
                }
            });
            // console.log(relacion, valor, visible, tipo, existe)
            if (existe) {
                if (visible) {
                    if ($(form).hasClass("debugForm"))
                        $(el).css("opacity", "1.0");
                    else
                        $(el).show();

                    if (!$(el).data("readonly"))
                        $(el).find(":input").addClass("enabled-temp");

                } else {
                    if ($(form).hasClass("debugForm"))
                        $(el).css("opacity", "0.5");
                    else
                        $(el).hide();

                    // $(el).find(":input").addClass("disabled-temp");
                }
            }
        }

    });

    $(form).find(":input.disabled-temp").each(function (i, el) {
        $(el).prop("disabled", true);
        $(el).removeClass("disabled-temp");
    });

    $(form).find(":input.enabled-temp").each(function (i, el) {
        $(el).prop("disabled", false);
        $(el).removeClass("disabled-temp");
    });

    $(form).find(".file-uploader ~ input[type=text]").prop("type", "hidden");
    $(form).find(":input[readonly]").prop("disabled", true);
}

function buscarAgenda() {
    if (jQuery.trim($('.js-pertenece').val()) != "") {
        var base_url = $('#base_url').val();
        if (jQuery.trim($('.js-pertenece').val()) == '%') {
            location.href = base_url + "/backend/agendas";
        } else {
            var search = $('.js-pertenece').val();
            $('#frmsearch').submit();
        }
    } else {
        $('.validacion').html('<div class="alert alert-danger" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
            '    <span aria-hidden="true">&times;</span>\n' +
            '  </button>Debe ingresar un nombre de agenda o pertence. si quiere listar todas digite \'%\'</div>');
    }
}

function calendarioFront(idagenda, idobject, idcita, tramite, etapa) {

    var site_url = $('#urlbase').val();

    if (idcita == 0) {
        if (typeof $('#codcita' + idobject) !== "undefined") {
            idcita = $('#codcita' + idobject).val();
        }
    }

    var idtramite = $('#codcita' + idobject).attr('data-id-etapa');

    if (typeof (idtramite) === "undefined" || idtramite == 0) {
        idtramite = tramite;
    }

    if (typeof(etapa) === "undefined" || etapa == 0) {
        etapa = idtramite;
    }

    $('#codcita' + idobject).attr('data-id-etapa');
    $("#modalcalendar").load(site_url + "/agenda/ajax_modal_calendar?idagenda=" + idagenda + "&object=" + idobject + "&idcita=" + idcita + "&idtramite=" + idtramite + "&etapa=" + etapa);
    $("#modalcalendar").modal();
}

var procesar_data = function(data){
    //en caso de existir los campos se setea el valor, de lo contrario se crean campos hidden para que
    //puedan realizar lo mismo y poder cumplir con las condiciones de visibilidad
    $.each(data, function(k,v) {
        if( data[k].valor && $('[name="'+data[k].nombre+'"]').length){
            var valor = JSON.parse(data[k].valor);
            if(typeof valor !== 'string'){
                valor = JSON.stringify(valor);
            }
            if($('[name="'+data[k].nombre+'"]').is('p,h3,h4')){
                $('[name="'+data[k].nombre+'"]').text(valor);
            }else{
                $('[name="'+data[k].nombre+'"]').val(valor);
                $('[name="'+data[k].nombre+'"]').trigger('change');
            }
        }else{

            if( data[k].valor &&  (Array.isArray( JSON.parse( data[k].valor ) ) || isObject( JSON.parse( data[k].valor ) ) ) )
                var valor = data[k].valor.replace(/[\r\n|\n|\r]+/g, '');
            else
                var valor = data[k].valor.replace(/['"]+/g, '');

            $('<input>').attr({
                type: 'hidden',
                name: data[k].nombre,
                value: valor,
            }).appendTo('form');
        }
    });
    prepareDynaForm(".dynaForm");
}

function isObject (value) {
  return value && typeof value === 'object' && value.constructor === Object;
}
