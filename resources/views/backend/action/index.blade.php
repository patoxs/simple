@extends('layouts.backend')

@section('title', $title)

@section('content')
    <div class="container-fluid">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('backend.procesos.index')}}">Listado de Procesos</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
            </ol>
        </nav>

        @include('backend.process.nav')

        <a class="btn btn-success" href="#" onclick="return seleccionarAccion(<?=$proceso->id?>);">
            <i class="material-icons">insert_drive_file</i> Nuevo
        </a>
        <a class="btn btn-light" href="#modalImportarAccion" data-toggle="modal">
            <i class="material-icons">file_upload</i>
            Importar
        </a>

        <table class="table mt-3">
            <thead>
            <tr>
                <th>Accion
                    <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_Acciones')"; href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html" target="_blank">
                        <i class="material-icons">help</i>
                    </a>
                </th>
                <th>Tipo</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($acciones as $p)
                <tr>
                    <td><?=$p->nombre?></td>
                    <td><?=$p->tipo?></td>
                    <td>
                        <a href="<?=route('backend.action.edit', [$p->id])?>" class="btn btn-primary">
                            <i class="material-icons">edit</i> Editar
                        </a>
                        <a class="btn btn-light" href="<?=route('backend.action.export', [$p->id])?>">
                            <i class="material-icons">file_download</i> Exportar
                        </a>
                        <a href="<?=route('backend.action.eliminar', [$p->id])?>" class="btn btn-danger"
                           onclick="return confirm('¿Esta seguro que desea eliminar?')">
                            <i class="material-icons">delete</i>
                            Eliminar
                        </a>
                        @if($p->extra && isset($p->extra->crt) && $p->extra->crt)
                        <a href="<?=route('backend.action.eliminar_certificado', [$p->id])?>" class="btn btn-danger"
                           onclick="return confirm('¿Esta seguro que desea eliminar el certificado?')">
                            <i class="material-icons">delete</i>
                            Eliminar certificado
                        </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div id="modalImportarAccion" class="modal hide fade">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data" action="<?=route('backend.action.import')?>">
                        {{csrf_field()}}
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Accion</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Cargue a continuación el archivo .simple donde exportó su acción.</p>
                            <input type="file" name="archivo" class="form-control"/>
                            <input type="hidden" name="proceso_id" value="<?= $proceso->id ?>"/>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Importar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="modal" class="modal hide"></div>
@endsection
@section('script')
    <script src="{{asset('js/helpers/modelador-acciones.js')}}" type="text/javascript"></script>
@endsection