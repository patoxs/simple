@extends('layouts.backend')

@section('title', 'Listado de Procesos')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">


                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.tracing.index')}}">Seguimiento de
                                Procesos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
                    </ol>
                </nav>

            </div>
        </div>


        <div class="row-fluid">
            <div class="float-right">
                <a href='#' onclick='toggleBusquedaAvanzada()'>Opciones de Búsqueda</a>
            </div>
            @if(in_array('super', explode(',', Auth::user()->rol)))
                <div class="btn-group float-left">
                    <a class="btn btn-light dropdown-toggle" data-toggle="dropdown" href="#">
                        Operaciones
                        <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                        <a href="<?= url('backend/seguimiento/reset_proc_cont/' . $proceso->id) ?>"
                           class="dropdown-item"
                           onclick="return confirm('¿Esta seguro que desea reiniciar el contador de Proceso?');">
                            Reiniciar contador de Proceso
                        </a>

                        @if ($proceso->Cuenta->ambiente != 'prod')
                            <a href="<?= url('backend/seguimiento/borrar_proceso/' . $proceso->id) ?>"
                               class="dropdown-item"
                               onclick="return borrarProceso(<?=$proceso->id?>);">Borrar todo</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <br>

        <div id='busquedaAvanzada' class='row mt-5' style='display: <?=$busqueda_avanzada ? 'block' : 'none'?>;'>
            <div class='col-12'>
                <div class='jumbotron'>
                    <form class='form-horizontal'>
                        <input type='hidden' name='busqueda_avanzada' value='1'/>
                        <div class='row'>
                            <div class='col-6'>
                                <div class="row">
                                    <div class="col-12">
                                        <label class='col-form-label'>Seleccione tipo de búsqueda:</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input search-selector" type="radio"
                                                   name="search_option" id="inlineRadio5" value="option5">
                                            <label class="form-check-label" for="inlineRadio5">Sin filtro</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input search-selector" type="radio"
                                                   name="search_option" id="inlineRadio1" value="option1">
                                            <label class="form-check-label" for="inlineRadio1">Buscar por Id</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input search-selector" type="radio"
                                                   name="search_option" id="inlineRadio3" value="option3">
                                            <label class="form-check-label" for="inlineRadio3">
                                                Buscar por Referencia
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input search-selector" type="radio"
                                                   name="search_option" id="inlineRadio4" value="option4">
                                            <label class="form-check-label" for="inlineRadio4">Buscar por Nombre</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class='control-group seg-input-search' id="input1">
                                            <label class='col-form-label'>Ingrese Id:</label>
                                            <input name="query_tramite_id" value="<?= $query_tramite_id ?>"
                                                   type="text" class="form-control"/>
                                        </div>
                                        <div class='control-group seg-input-search' id="input3">
                                            <label class='col-form-label'>Ingrese Valor de referencia:</label>
                                            <input name="query_ref" value="<?= $query_ref ?>" type="text"
                                                   class="form-control"/>
                                        </div>
                                        <div class='control-group seg-input-search' id="input4">
                                            <label class='col-form-label'>Ingrese nombre:</label>
                                            <input name="query_name" value="<?= $query_name ?>" type="text"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-2'>
                                <div class='control-group'>
                                    <label class='col-form-label'>Estado del trámite</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type='radio' name='pendiente' id="cualquiera"
                                               value='-1' <?= $pendiente == -1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cualquiera">
                                            Cualquiera
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type='radio' name='pendiente' id="encurso"
                                               value='1' <?= $pendiente == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="encurso">
                                            En Curso
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type='radio' name='pendiente' id="completado"
                                               value='0' <?= $pendiente == 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="completado">
                                            Completado
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='col-4'>
                                <div class='form-group row'>
                                    <label class='col-sm-5 col-form-label'>Fecha de creación</label>
                                    <div class='col-sm-6'>
                                        <input type='text' name='created_at_desde' placeholder='Desde'
                                               class='datetimepicker form-control' value='<?= $created_at_desde ?>'/>
                                        <input type='text' name='created_at_hasta' placeholder='Hasta'
                                               class='datetimepicker form-control' value='<?= $created_at_hasta ?>'/>
                                    </div>
                                </div>
                                <div class='form-group row'>
                                    <label class='col-sm-5 col-form-label'>Fecha de último cambio</label>
                                    <div class='col-sm-6'>
                                        <input type='text' name='updated_at_desde' placeholder='Desde'
                                               class='datetimepicker form-control' value='<?= $updated_at_desde ?>'/>
                                        <input type='text' name='updated_at_hasta' placeholder='Hasta'
                                               class='datetimepicker form-control' value='<?= $updated_at_hasta ?>'/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div style='text-align: right;'>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{$tramites->links('vendor.pagination.bootstrap-4')}}

        <table class="table mt-3">
            <thead>
            <tr>
                <th>
                    <a href="<?= url()->current() . '?search_option='. $search_option . '&query_tramite_id=' . $query_tramite_id . '&query_ref='. $query_ref. '&query_name='.$query_name. '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=id&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Id <?= $order == 'id' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?></a>
                </th>
                <th>Asignado a.</th>
                <th>Ref.</th>
                <th>Nombre</th>
                <th>
                    <a href="<?= url()->current() . '?search_option='. $search_option . '&query_tramite_id=' . $query_tramite_id . '&query_ref='. $query_ref. '&query_name='.$query_name. '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=pendiente&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Estado <?= $order == 'pendiente' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?></a>
                </th>
                <th>Etapa actual</th>
                <th>
                    <a href="<?= url()->current() . '?search_option='. $search_option . '&query_tramite_id=' . $query_tramite_id . '&query_ref='. $query_ref. '&query_name='.$query_name. '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=created_at&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Fecha
                        de
                        creación <?= $order == 'created_at' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?>
                </th>
                <th>
                    <a href="<?= url()->current() . '?search_option='. $search_option . '&query_tramite_id=' . $query_tramite_id . '&query_ref='. $query_ref. '&query_name='.$query_name. '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=updated_at&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Fecha
                        de Último
                        cambio <?= $order == 'updated_at' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?></a>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                @foreach ($tramites as $t)
                <tr>
                    <td>
                        {{ $t['id'] }}
                    </td>
                    <td>
                        {{ $t['asignado'] }}
                    </td>
                    <td class="name">
                        {{ trim($t['ref'], "\"") }}
                    </td>
                    <td class="name">
                        {{ trim($t['nombre'], "\"") }}
                    </td>
                    <td>
                        {{ $t['estado'] ? 'En curso' : 'Completado' }}
                    </td>
                    <td>
                        @foreach ($t['etapas'] as $etapaNombre)
                            {{ $etapaNombre }}  
                            <?php
                                $tr = \App\Helpers\Doctrine::getTable('Tramite')->find($t['id']);
                                $etapas_vencimiento = array();
                                foreach ($tr->getEtapasActuales() as $e)
                                    $etapas_vencimiento[] = ($e->vencimiento_at ? ' <a href="#" onclick="return editarVencimiento(' . $e->id . ')" title="Cambiar fecha de vencimiento">(' . $e->getFechaVencimientoSinDiasAsString() . ')</a>' : '');
                                echo implode(', ', $etapas_vencimiento);

                            ?>
                            @if (!$loop->last),@endif
                        @endforeach
                    </td>
                    <td>{{ $t['created_at'] }}</td>
                    <td>{{ $t['updated_at'] }}</td>
                    <td style="text-align: right;">
                        <a class="btn btn-primary" href="{{ url('backend/seguimiento/ver/' . $t['id']) }}">
                            <i class="material-icons">remove_red_eye</i> Seguimiento</a>
                        @if(in_array('super', explode(',', Auth::user()->rol)))
                            <a class="btn btn-danger" href="#" onclick="return eliminarTramite({{ $t['id'] }});">
                                <i class="material-icons">delete</i> Borrar</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{$tramites->links('vendor.pagination.bootstrap-4')}}

    </div>

    <div id="modal" class="modal hide"></div>

@endsection
@section('script')
    <script type="text/javascript">
        let SEARCH_OPT = {!! json_encode($search_option) !!};
        function checkSearchInputs(val) {
            $('.seg-input-search').hide();
            switch (val) {
                case 'option1':
                    $('#input1').show();
                    break;
                case 'option3':
                    $('#input3').show();
                    break;
                case 'option4':
                    $('#input4').show();
                    break;
            }
        }

        function editarVencimiento(etapaId) {
            $("#modal").load("/backend/seguimiento/ajax_editar_vencimiento/" + etapaId);
            $("#modal").modal();
            return false;
        }

        function eliminarTramite(tramiteId) {
            $("#modal").load("/backend/seguimiento/ajax_auditar_eliminar_tramite/" + tramiteId);
            $("#modal").modal();
            return false;

        }

        function borrarProceso(procesoId) {
            $("#modal").load("/backend/seguimiento/ajax_auditar_limpiar_proceso/" + procesoId);
            $("#modal").modal();
            return false;
        }

        function toggleBusquedaAvanzada() {
            $("#busquedaAvanzada").slideToggle();
        }

        $(document).ready(function() {
            $(function () {
                checkSearchInputs(SEARCH_OPT);
                switch (SEARCH_OPT) {
                    case 'option1':
                        $('#inlineRadio1').prop('checked', true);
                        break;
                    case 'option3':
                        $('#inlineRadio3').prop('checked', true);
                        break;
                    case 'option4':
                        $('#inlineRadio4').prop('checked', true);
                        break;
                    default:
                        $('#inlineRadio5').prop('checked', true);
                }


                $('.datetimepicker').datetimepicker({
                    format: 'DD-MM-YYYY',
                    icons: {
                        previous: "glyphicon glyphicon-chevron-left",
                        next: "glyphicon glyphicon-chevron-right"
                    },
                    locale: 'es'
                });

                $('.search-selector').on('click', function() {
                    checkSearchInputs($(this).val())
                });
            });
        });

    </script>
@endsection