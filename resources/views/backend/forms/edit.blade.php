@extends('layouts.backend')

@section('title', $title)
@section('css')
    <link rel="stylesheet" href="{{asset('css/handsontable.full.min.css')}}">
    <style>
        #areaFormulario .btn-toolbar {
            margin-bottom: 20px;
        }

        #areaFormulario .control-group {
            padding: 10px;
            border: 1px dashed transparent;
            height: 100%;
            margin: 0;
        }

        #areaFormulario .control-group:hover {
            background: #eee;
            border: 1px dashed #ccc;
            cursor: move;
        }
        #areaFormulario .btn {
            padding: .15rem .4rem;
            margin-left: 2px;
        }
        #formEditarFormulario .name-form {
            padding-left: 10px;
        }

    </style>
@endsection
@section('content')
    <div class="container-fluid mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('backend.procesos.index')}}">Listado de Procesos</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
            </ol>
        </nav>

        @include('backend.process.nav')

        <div id="areaFormulario">

            <div class="btn-toolbar">
                <div class="btn-group ml-1 mb-1">
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'title')">
                        Título
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'subtitle')">
                        Subtítulo
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'paragraph')">
                        Parrafo
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'documento')">
                        Documento
                    </button>
                </div>
                <div class="btn-group ml-1 mb-1">
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'text')">
                        Textbox
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'password')">
                        Password
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'textarea')">
                        Textarea
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'select')">
                        Select
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'radio')">
                        Radio
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'checkbox')">
                        Checkbox
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'file')">File
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'file_s3')">File Transfer
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'date')">Date
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'grid')">
                        Grilla
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'grid_datos_externos')">
                        Grilla de Datos Externos
                    </button>
                    <!-- <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'agenda')">
                        Agenda
                    </button> -->
                    <!-- <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'recaptcha')">
                        Recaptcha
                    </button> -->
                </div>
                <div class="btn-group ml-1 mb-1">
                    <button class="btn btn-secondary"
                            onclick="return agregarCampo(<?= $formulario->id ?>,'instituciones_gob')">
                        Instituciones
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'paises')">
                        Paises
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'provincias')">
                        Provincias
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'comunas')">
                        Comunas
                    </button>
                    <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'moneda')">
                        Moneda
                    </button>
                </div>
                <div class="btn-group ml-1 mb-1">
                    <button class="btn btn-secondary"
                            onclick="return agregarCampo(<?= $formulario->id ?>,'javascript')">
                        Javascript
                    </button>
                    <!-- <button class="btn btn-secondary" onclick="return agregarCampo(<?= $formulario->id ?>,'maps')">Mapa
                    </button> -->
                </div>
                <div class="btn-group ml-1 mb-1">
                    <button class="btn btn-secondary"
                            onclick="return agregarCampo(<?= $formulario->id ?>,'btn_asincrono')">
                        Botón asíncrono
                    </button>
                    <button class="btn btn-secondary"
                            onclick="return agregarCampo(<?= $formulario->id ?>,'btn_siguiente')">
                        Botón siguiente
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-10 mt-2">
                    <form id="formEditarFormulario" class="form-horizontal dynaForm debugForm" onsubmit="return false">
                        <div class="row">
                            <div class="col-10 text-left">
                                <legend class="name-form" id="leyend-edit-form"><?= $formulario->nombre ?>
                                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda-Diseno-Nuevo-Formulario');" href="/ayuda/simple/backend/modelamiento-del-proceso/diseno-de-formularios.html"
                                       target="_blank">
                                        <i class="material-icons align-middle">help</i>
                                    </a>
                                </legend>

                                <div class="float-right">
                                    <a href="#" class="btn btn-simple-light text-capitalize"
                                       onclick="return editarFormulario(<?= $formulario->id ?>)">(Cambiar Nombre y descripción)</a>&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                        <div class="edicionFormulario">
                            @foreach ($formulario->Campos as $c)
                                @if($c->tipo != 'hidden')
                                    <div class="row">
                                        <div class="col-10">
                                            <div class="control-group campo"
                                                 data-id="<?= $c->id ?>" <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo . '" data-dependiente-valor="' . $c->dependiente_valor . '" data-dependiente-tipo="' . $c->dependiente_tipo . '" data-dependiente-relacion="' . $c->dependiente_relacion . '"' : '' ?> >
                                                <div class="row align-items-start">
                                                    <div class="col-1">
                                                        <div class="buttons">
                                                            <a href="#" class="btn btn-primary"
                                                               onclick="return editarCampo(<?= $c->id ?>)">
                                                                <i class="material-icons">edit</i>
                                                            </a>
                                                            <a href="<?= route('backend.forms.delete_field', [$c->id]) ?>"
                                                               class="btn btn-danger"
                                                               onclick="return confirm('¿Esta seguro que desea eliminar?')">
                                                                <i class="material-icons">delete</i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-11">
                                                        <div class="float-left">{!!$c->displaySinDato()!!}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @endif
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal hide" id="modal"></div>
@endsection
@section('script')
    <script src="{{asset('js/helpers/bootstrap-typeahead-multiple/bootstrap-typeahead-multiple.js')}}"></script>
    <script src="{{asset('js/helpers/handsontable.full.min.js')}}"></script>
    <script>
        var formularioId = {{$formulario->id}};

        $(document).ready(function () {
            $('#areaFormulario .edicionFormulario').sortable({
                //handle: '.handler',
                revert: true,
                stop: editarPosicionCampos
            });
        });

        function editarFormulario(formularioId) {
            $("#modal").load("/backend/formularios/ajax_editar/" + formularioId);
            $("#modal").modal();
            return false;
        }

        function editarPosicionCampos() {
            var campos = new Array();
            $("#areaFormulario .edicionFormulario .campo").each(function (i, e) {
                campos.push($(e).data('id'));
            });
            var json = JSON.stringify(campos);

            $.post("/backend/formularios/editar_posicion_campos/" + formularioId, "posiciones=" + json);
        }

        function editarCampo(campoId) {
            $("#modal").load("/backend/formularios/ajax_editar_campo/" + campoId);
            $("#modal").modal();
            return false;
        }

        function agregarCampo(formularioId, tipo) {
            if (tipo == 'recaptcha') {
                if ($('#form_captcha').length) {
                    alert('Ya existe un componente Captcha dentro del formulario actual.');
                } else {
                    $("#modal").load("/backend/formularios/ajax_agregar_campo/" + formularioId + "/" + tipo);
                    $("#modal").modal();
                }
            }else if(tipo=='btn_siguiente'){
                if($('.btn_siguiente').length){
                    alert('Ya existe un componente botón siguiente dentro del formulario actual.');
                }else{
                    $("#modal").load("/backend/formularios/ajax_agregar_campo/" + formularioId + "/" + tipo);
                    $("#modal").modal();
                }
            } else {
                $("#modal").load("/backend/formularios/ajax_agregar_campo/" + formularioId + "/" + tipo);
                $("#modal").modal();
            }

            return false;
        }
    </script>
@endsection