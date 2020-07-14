@extends('layouts.backend')

@section('title', 'Edición de Reporte')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.report')}}">Gestión</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.report.list', [$proceso->id])}}">{{$proceso->nombre}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Crear reporte</li>
                    </ol>
                </nav>

                <form class="ajaxForm" method="POST"
                      action="{{$edit ?
                      route('backend.report.update', [$reporte->id]) :
                      route('backend.report.store')}}">

                    {{csrf_field()}}

                    <fieldset>
                        <legend>Crear Reporte</legend>
                        <div class="validacion"></div>
                        @if(!$edit)
                            <input type="hidden" name="proceso_id" value="{{$proceso->id}}">
                        @endif
                        <label>Nombre</label>
                        <input type="text" class="form-control col-2" name="nombre"
                               value="{{$edit ? $reporte->nombre : ''}}"/>
                        <label>Campos</label>
                        <div class="form-inline">
                            <select id="disponibles" class="form-control col-3" style="height: 240px;" multiple>

                                @php
                                    $tramiteHeaders = Tramite::getReporteHeaders();
                                    $camposHeaders = $proceso->getCamposReporteHeaders();
                                    $variablesHeaders = $proceso->getVariablesReporteHeaders();
                                @endphp

                                <optgroup label="Datos de Trámite">
                                    @foreach($tramiteHeaders as $rh)
                                        @if (!($edit && in_array($rh, $reporte->campos)))
                                            <option value="{{$rh}}" name="Datos de Trámite">{{$rh}}</option>
                                        @endif
                                    @endforeach
                                </optgroup>

                                <optgroup label="Campos de Formularios">
                                    @foreach($camposHeaders as $c)
                                        @if (!($edit && in_array($c, $reporte->campos)))
                                            <option value="{{$c}}" name="Campos de Formularios">{{$c}}</option>
                                        @endif
                                    @endforeach
                                </optgroup>

                                <optgroup label="Variables">
                                    @foreach ($variablesHeaders as $v)
                                        @if (!($edit && in_array($v, $reporte->campos)))
                                            <option value="{{$v}}" name="Variables">{{$v}}</option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            </select>

                            <div class="btn-group-vertical" role="group">
                                <button class="btn btn-primary" type="button" onclick="seleccionarHeader()">
                                    <i class="material-icons">chevron_right</i>
                                </button>
                                <button class="btn btn-primary" type="button" onclick="eliminarHeader()">
                                    <i class="material-icons">chevron_left</i>
                                </button>
                            </div>

                            <select id="seleccionados" class="form-control col-3" name="campos[]" style="height: 240px;"
                                    multiple>
                                @if(isset($reporte->campos))
                                    @foreach($reporte->campos as $c)
                                        <option value="{{$c}}" name="<?php
                                        if (in_array($c, $tramiteHeaders))
                                            echo "Datos de Trámite";
                                        else if (in_array($c, $camposHeaders))
                                            echo "Campos de Formularios";
                                        else if (in_array($c, $variablesHeaders))
                                            echo "Variables";

                                        ?>">{{$c}}</option>
                                    @endforeach
                                @endif
                            </select>

                            <div class="btn-group-vertical" role="group">
                                <button class="btn btn-primary" type="button" onclick="subirOrden()">
                                    <i class="material-icons">keyboard_arrow_up</i>
                                </button>
                                <button class="btn btn-primary" type="button" onclick="bajarOrden()">
                                    <i class="material-icons">keyboard_arrow_down</i>
                                </button>
                            </div>
                        </div>

                        <div class="form-actions">
                            <hr>
                            <a class="btn btn-light"
                               href="{{route('backend.report.list', [$proceso->id])}}">
                                Cancelar
                            </a>
                            <input class="btn btn-primary" type="submit" onclick="selectAll();" value="Guardar"/>
                        </div>
                    </fieldset>
                </form>

            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">

        function seleccionarHeader() {
            $("#disponibles").find(":selected").each(function (i, el) {
                $(el).detach().appendTo($("#seleccionados"));
            });
        }

        function eliminarHeader() {
            $("#seleccionados").find(":selected").each(function (i, el) {
                $(el).detach().appendTo($("#disponibles").find("[label='" + $(el).attr("name") + "']"));
            });
        }

        function subirOrden() {
            $("#seleccionados").find(":selected").each(function (i, el) {
                var anterior = $(el).prev();
                if ($(anterior).length > 0 && !($(anterior).prop("selected"))) {
                    $(el).detach().insertBefore($(anterior));
                }
            });
        }

        function bajarOrden() {
            jQuery.fn.reverse = [].reverse;
            $("#seleccionados").find(":selected").reverse().each(function (i, el) {
                var anterior = $(el).next();

                if ($(anterior).length > 0 && !($(anterior).prop("selected"))) {
                    $(el).detach().insertAfter($(anterior));
                }
            });
        }

        function selectAll() {
            $("#seleccionados").find("*").prop("selected", true);
        }
    </script>
@endsection