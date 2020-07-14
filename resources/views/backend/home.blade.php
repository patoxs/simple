@extends('layouts.backend')

@section('title', $title)

@section('css')
    <style>
        .clearfix {
            *zoom: 1;
        }

        .clearfix:before,
        .clearfix:after {
            display: table;
            content: "";
            line-height: 0;
        }

        .clearfix:after {
            clear: both;
        }

        .hide-text {
            font: 0/0 a;
            color: transparent;
            text-shadow: none;
            background-color: transparent;
            border: 0;
        }

        .input-block-level {
            display: block;
            width: 100%;
            min-height: 30px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        #dashboard .widget {
            position: relative;
            width: 100%;
            height: 460px;
            float: left;
            margin-right: 10px;
            margin-bottom: 20px;
            perspective: 1000px;
            -webkit-perspective: 1000px;
            -moz-perspective: 1000px;
            -o-perspective: 1000px;
            -ms-perspective: 1000px;
        }

        #dashboard .widget .front,
        #dashboard .widget .back {
            -webkit-transition: 1s;
            -moz-transition: 1s;
            -o-transition: 1s;
            transition: 1s;
            -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            width: 100%;
            height: 460px;
            position: absolute;
            top: 0;
            left: 0;
            overflow-y: auto;
        }

        #dashboard .widget .front .cabecera,
        #dashboard .widget .back .cabecera {
            background: #0054AB;
            color: #fff;
            padding: 10px 5px 5px 13px;
            cursor: move;
        }

        #dashboard .widget .front .cabecera h3,
        #dashboard .widget .back .cabecera h3 {
            font-size: 14px;
            line-height: 1;
        }

        #dashboard .widget .front .contenido,
        #dashboard .widget .back .contenido {
            padding: 5px;
        }

        #dashboard .widget .front {
            background: #fff;
            transform: rotateY(0deg);
            -webkit-transform: rotateY(0deg);
            -moz-transform: rotateY(0deg);
            -o-transform: rotateY(0deg);
            -ms-transform: rotateY(0deg);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            -o-backface-visibility: hidden;
            -ms-backface-visibility: hidden;
            z-index: 900;
        }

        #dashboard .widget .front .config {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        #dashboard .widget .back {
            background: #eee;
            transform: rotateY(-180deg);
            -webkit-transform: rotateY(-180deg);
            -moz-transform: rotateY(-180deg);
            -o-transform: rotateY(-180deg);
            -ms-transform: rotateY(-180deg);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            -o-backface-visibility: hidden;
            -ms-backface-visibility: hidden;
            z-index: 800;
        }

        #dashboard .widget .back .volver {
            position: absolute;
            top: 5px;
            right: 10px;
        }

        #dashboard .widget.flip .front {
            transform: rotateY(180deg);
            -webkit-transform: rotateY(180deg);
            -moz-transform: rotateY(180deg);
            -o-transform: rotateY(180deg);
            -ms-transform: rotateY(180deg);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            -o-backface-visibility: hidden;
            -ms-backface-visibility: hidden;
            z-index: 900;
        }

        #dashboard .widget.flip .back {
            transform: rotateY(0deg);
            -webkit-transform: rotateY(0deg);
            -moz-transform: rotateY(0deg);
            -o-transform: rotateY(0deg);
            -ms-transform: rotateY(0deg);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            -moz-backface-visibility: hidden;
            -o-backface-visibility: hidden;
            -ms-backface-visibility: hidden;
            z-index: 1000;
        }

        .invalido {
            border: 1px solid #AA0909;
        }

        .spanError {
            font-size: 10px;
            color: #AA0909;
            margin-left: 1px;

        }

        .AlignButton {
            margin-top: 0px;
        }

        .AlignText {
            margin-top: 9px;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <p>
                <div class="btn-group">
                    <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="material-icons">add</i> Nuevo widget <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item"
                               href="<?=route('backend.management.widget_create', ['tramite_etapas'])?>">
                                Tramite por etapas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="<?=route('backend.management.widget_create', ['tramites_cantidad'])?>">
                                Tramites realizados
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="<?=route('backend.management.widget_create', ['etapa_usuarios'])?>">
                                Carga de Usuarios por Etapa
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="<?=route('backend.management.widget_create', ['etapa_grupo_usuarios'])?>">
                                Carga de Grupos de Usuarios por Etapa
                            </a>
                        </li>
                    </ul>
                </div>
                <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_Widget');"href="/ayuda/simple/backend/widgets.html" target="_blank">
                    <i class="material-icons align-middle">help</i>
                </a>
                </p>
            </div>
        </div>
        <div id="dashboard">
            <div class="row">
                @if(!is_null($widgets))
                    @foreach($widgets as $w)
                        <div class="col-md-4">
                            <div class="widget" data-id="{{$w->id}}">
                                @php
                                    $data['widget'] = $w;
                                @endphp
                                @include('backend.management.widget_load', $data)
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $("#dashboard").sortable({
                items: ".widget",
                handle: ".cabecera",
                revert: true,
                stop: widgetChangePositions
            });
        });

        function widgetChangePositions() {
            var widgets = new Array();
            $("#dashboard .widget").each(function (i, e) {
                widgets.push($(e).data('id'));
            });
            var json = JSON.stringify(widgets);

            $.post("/backend/gestion/widget_change_positions/", "posiciones=" + json);
        }

        function widgetConfig(button) {
            var widget = $(button).closest(".widget");
            $(widget).addClass('flip');
            return false;
        }

        function widgetConfigOk(form) {
            var widget = $(form).closest(".widget");
            var widgetId = $(widget).data("id");
            $(widget).removeClass('flip');

            //Damos tiempo para que termine la animacion
            setTimeout(function () {
                $(widget).load("/backend/gestion/widget_load/" + widgetId)
            }, 1000);
        }
    </script>
@endsection
