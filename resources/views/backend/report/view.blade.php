@extends('layouts.backend')

@section('title', $title)

@php
    $col_size = count($reporte_tabla[0]);
    $row_size = count($reporte_tabla);
@endphp

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
                            <a href="{{route('backend.report.list', [$reporte->Proceso->id])}}">{{$reporte->Proceso->nombre}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$reporte->nombre}}</li>
                    </ol>
                </nav>


                <form class='form-horizontal' id="filtroForm">
                    <input type='hidden' name='busqueda_avanzada' value='1'/>
                    <div id="filtro" class='jumbotron' style='display: <?= $filtro ? 'block' : 'none'?>;'>
                        <div class="col-12">
                            <div class='row'>
                                <div class="col-4">
                                    <div class='control-group'>
                                        <div class='controls'>
                                            <input name="query" value="<?= $query ?>" type="text"
                                                   class="search-query form-control" placeholder="Término a buscar"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class='control-group'>
                                        <label class='control-label'>Estado del trámite</label>
                                        <div class='controls'>
                                            <label class='radio'><input type='radio' name='pendiente'
                                                                        value='-1' <?= $pendiente == -1 ? 'checked' : '' ?>>
                                                Cualquiera</label>
                                            <label class='radio'><input type='radio' name='pendiente'
                                                                        value='1' <?= $pendiente == 1 ? 'checked' : '' ?>>
                                                En curso</label>
                                            <label class='radio'><input type='radio' name='pendiente'
                                                                        value='0' <?= $pendiente == 0 ? 'checked' : '' ?>>
                                                Completado</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class='control-group'>
                                        <label class='control-label'>Fecha de creación</label>
                                        <div class='input-group'>
                                            <input type='text' name='created_at_desde' placeholder='Desde'
                                                   class='datepicker form-control' value='<?= $created_at_desde ?>'/>
                                            <input type='text' name='created_at_hasta' placeholder='Hasta'
                                                   class='datepicker form-control' value='<?= $created_at_hasta ?>'/>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <dl class="dl-horizontal">
                                <dt>Duración promedio</dt>
                                <dd> <?=$promedio_tramite ? abs($promedio_tramite) . ' días' : 'No hay tramites finalizados'?></dd>
                                <dt>Cantidad de trámites</dt>
                                <dd> {{$tramites_completos + $tramites_pendientes}}</dd>
                                <dt>Completos</dt>
                                <dd> <?=$tramites_completos?></dd>
                                <dt>En curso</dt>
                                <dd><?=$tramites_pendientes?></dd>
                                <dt>En curso vencidos</dt>
                                <dd><?=$tramites_vencidos?></dd>
                            </dl>
                            </dl>
                        </div>
                        <div class="col-4 text-right">
                            <a id="toggleFiltroBtn" class="btn btn-light" href='#'
                               onclick="toggleFiltro()">
                                <i class="material-icons">arrow_downward</i> Filtro
                            </a>
                            <button type="submit" name="formato" value="xls"
                                    class="btn btn-primary">
                                <i class="material-icons">insert_drive_file</i> XLS
                            </button>
                            <button type="submit" name="formato" value="pdf"
                                    class="btn btn-primary">
                                <i class="material-icons">picture_as_pdf</i> PDF
                            </button>
                        </div>
                    </div>
                </form>

                <table class="table table-striped">
                    <thead>

                    <tr>
                        @for($col = 0;$col < $col_size;$col++)
                            <th>{{$reporte_tabla[0][$col]}}</th>
                        @endfor
                    </tr>

                    </thead>

                    @for($row = 1;$row < $row_size;$row++)
                        <tr>
                            @for($col = 0;$col < $col_size;$col++)
                                <td>{{$reporte_tabla[$row][$col]}}</td>
                            @endfor
                        </tr>
                    @endfor
                </table>

                {{$reporte_tabla->links('vendor.pagination.bootstrap-4', ['created_at_desde' => $created_at_desde, 'created_at_hasta' => $created_at_hasta])}}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            $(":input").change(function (event) {
                $("#filtroForm").submit();
            });
        });

        function toggleFiltro() {
            $("#filtro").slideToggle();

            var icon = $("#toggleFiltroBtn").find("i");
            if (icon.text() == "arrow_downward") {
                icon.text('arrow_upward');
            } else {
                icon.text('arrow_downward');
            }

            return false;
        }
    </script>
@endsection