@extends('layouts.backend')

@section('title', $title)

@section('content')
    <div class="container-fluid mt-3">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('backend.procesos.index')}}">Listado de Procesos</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
            </ol>
        </nav>

        @include('backend.process.nav')

        <form id="plantillaForm" class="ajaxForm" method="POST"
              action="<?=route('backend.security.edit_form', ($edit ? [$seguridad->id] : ''))?>">
            {{csrf_field()}}
            <fieldset>
                @if(!$edit)
                    <legend> Regitrar métodos de seguridad
                        <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda-Registrar-medotos-seguridad');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#webservice-seguridad"
                           target="_blank">
                            <i class="material-icons">help</i>
                        </a>
                    </legend>
                @endif
                @if($edit)
                    <legend> Editar métodos de seguridad
                        <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda-editar-medotos-seguridad');" href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#webservice-seguridad"
                           target="_blank">
                            <i class="material-icons">help</i>
                        </a>
                    </legend>
                @endif
                <div class="validacion"></div>
                @if(!$edit)
                    <input type="hidden" name="proceso_id" value="<?=$proceso->id?>"/>
                @endif
                <label>Nombre de la Institución</label>
                <input type="text" class="form-control col-2" name="institucion"
                       value="<?=$edit ? $seguridad->institucion : ''?>"/>
                <label>Nombre del Servicio</label>
                <input type="text" class="form-control col-2" name="servicio"
                       value="<?=$edit ? $seguridad->servicio : ''?>"/>
                <?= $seguridad->displayForm() ?>
                <div class="form-actions">
                    <a class="btn btn-light" href="<?=route('backend.security.list', [$proceso->id])?>">Cancelar</a>
                    <button class="btn btn-primary" value="Guardar" type="submit">Guardar</button>
                </div>
            </fieldset>
        </form>
    </div>
@endsection
@section('script')
    <script>
        function CambioSelect() {
            console.log($("#tipoSeguridad").val());
            switch ($("#tipoSeguridad").val()) {
                case "HTTP_BASIC":
                    $("#DivBasic").show();
                    $("#DivKey").hide();
                    $("#DivAuth").hide();
                    $(".key").val("");
                    $(".oauth").val("");
                    break;
                case "API_KEY":
                    $("#DivBasic").hide();
                    $("#DivKey").show();
                    $("#DivAuth").hide();
                    $(".basic").val("");
                    $(".oauth").val("");

                    break;
                case "OAUTH2":
                    console.log("entre en oauth2");
                    $("#DivBasic").hide();
                    $("#DivKey").hide();
                    $("#DivAuth").show();
                    $(".basic").val("");
                    $(".key").val("");
                    break;
                default:
                    $("#DivBasic").hide();
                    $("#DivKey").hide();
                    $("#DivAuth").hide();
                    $(".basic").val("");
                    $(".key").val("");
                    $(".oauth").val("");
                    break;
            }
        }

        $(document).ready(function () {
            CambioSelect();
            $("#tipoSeguridad").change(function () {
                CambioSelect();
            });
        });
    </script>
@endsection