@extends('layouts.backend')

@section('title', 'Listado de Procesos')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">Seguimiento de Procesos</li>
                    </ol>
                </nav>

                @if(in_array('super', explode(',', Auth::user()->rol)))
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Operaciones
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" onclick="return actualizarIdTramites();">Actualizar ID de
                                Trámites</a>
                        </div>
                    </div><br>
                @endif

                <table class="table">
                    <thead>
                    <tr>
                        <th>Proceso
                            <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_seguimiento_procesos');" href="/ayuda/simple/backend/seguimiento-de-procesos.html" target="_blank">
                                <i class="material-icons">help</i>
                            </a>
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($procesos as $p)
                        @if(is_null((Auth::user()->procesos)) || in_array($p->id, explode(',', Auth::user()->procesos)))
                            <tr>
                                <td><?=$p->nombre?></td>
                                <td>
                                    <a class="btn btn-primary"
                                       href="{{route('backend.tracing.list', [$p->id])}}">
                                        <i class="material-icons">remove_red_eye</i> Ver seguimiento
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>


            </div>
        </div>
    </div>
    <div id="modal" class="modal" tabindex="-1" role="dialog">
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        function actualizarIdTramites() {
            $("#modal").load('{{route('backend.tracing.ajaxIdProcedure')}}');
            $("#modal").modal();
            return false;
        }
    </script>
@endsection