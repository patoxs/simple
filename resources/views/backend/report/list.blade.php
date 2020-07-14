@extends('layouts.backend')

@section('title', 'Documentos')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.report')}}">Gestión</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
                    </ol>
                </nav>

                @if(!in_array('gestion', explode(",", $rol)) )
                    <a class="btn btn-success" href="{{route('backend.report.create', [$proceso->id])}}">
                        <i class="material-icons">note_add</i> Nuevo
                    </a><br><br>
                @endif

                <table class="table">
                    <thead>
                    <tr>
                        <th width="10%">Reporte
                            <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_generación_reportes');" href="/ayuda/simple/backend/modelamiento-del-proceso/reportes.html"
                               target="_blank">
                                <i class="material-icons">help</i>
                            </a>
                        </th>
                        <th width="90%">Filtro</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reportes as $p)

                        <tr>
                            <td>{{$p->nombre}}</td>
                            <td>
                                <form method="GET" class="form-inline"
                                      action="{{route('backend.report.view', ['id' => $p->id, 'busqueda_avanzada' => '1', 'query' => '', 'pendiente' => '-1'])}}">
                                    {{csrf_field()}}

                                    <div class="input-group col-4">
                                        <input type='text' name='created_at_desde' placeholder='Desde'
                                               class='datetimepicker form-control col-5'/>
                                        <input type='text' name='created_at_hasta' placeholder='Hasta'
                                               class='datetimepicker form-control col-5'/>
                                    </div>

                                    <div class="report-action-form">
                                        <button type="submit" name="formato" value="xls" class="btn btn-info">
                                            <i class="material-icons">insert_drive_file</i> XLS
                                        </button>
                                        @if(!in_array('gestion', explode(",", $rol)) )
                                            <a href="{{route('backend.report.edit', [$p->id])}}"
                                               class="btn btn-primary">
                                                <i class="material-icons">edit</i> Editar
                                            </a>
                                            <a href="{{route('backend.report.delete', [$p->id])}}"
                                               class="btn btn-danger"
                                               onclick="return confirm('¿Esta seguro que desea eliminar?')">
                                                <i class="material-icons">close</i> Eliminar
                                            </a>
                                        @endif
                                    </div>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(function () {
            $('.datetimepicker').datetimepicker({
                //inline: true,
                //sideBySide: true,
                format: 'DD-MM-YYYY',
                icons: {
                    previous: "glyphicon glyphicon-chevron-left",
                    next: "glyphicon glyphicon-chevron-right"
                },
                locale: 'es'
            });
        });
    </script>
@endsection