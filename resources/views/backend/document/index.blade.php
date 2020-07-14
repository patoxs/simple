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

        <a class="btn btn-success" href="<?=route('backend.document.create', [$proceso->id])?>">
            <i class="material-icons">insert_drive_file</i> Nuevo
        </a>
        <a class="btn btn-light" href="#modalImportarDocumento" data-toggle="modal">
            <i class="material-icons">file_upload</i> Importar
        </a>

        <table class="table mt-3">
            <thead>
            <tr>
                <th>
                    Documento
                    <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_generacion_documentos');" href="/ayuda/simple/backend/modelamiento-del-proceso/generacion-de-documentos.html"
                       target="_blank">
                        <i class="material-icons">help</i>
                    </a>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($documentos as $p)
                <tr>
                    <td><?=$p->nombre?></td>
                    <td>
                        <a href="<?=route('backend.document.edit', [$p->id])?>" class="btn btn-primary">
                            <i class="material-icons">edit</i> Editar
                        </a>
                        <a href="<?=route('backend.document.preview', [$p->id])?>" class="btn btn-info">
                            <i class="material-icons">zoom_in</i> Previsualizar
                        </a>
                        <a class="btn btn-light" href="<?=route('backend.document.export', [$p->id])?>">
                            <i class="material-icons">file_download</i> Exportar
                        </a>
                        <a href="<?=route('backend.document.destroy', [$p->id])?>" class="btn btn-danger"
                           onclick="return confirm('¿Esta seguro que desea eliminar?')">
                            <i class="material-icons">delete</i> Eliminar
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div id="modalImportarDocumento" class="modal hide fade">
            <form method="POST" enctype="multipart/form-data" action="<?=route('backend.document.import')?>">
                {{csrf_field()}}
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Documento</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Cargue a continuación el archivo .simple donde exportó su documento.</p>
                            <input type="file" name="archivo" class="form-control"/>
                            <input type="hidden" name="proceso_id" value="<?= $proceso->id ?>"/>
                        </div>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Importar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{asset('js/helpers/modelador-acciones.js')}}" type="text/javascript"></script>
@endsection