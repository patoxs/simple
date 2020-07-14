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

        <div class="row">
            <div class="col-12 mb-3">
                <a class="btn btn-success" href="<?=route('backend.forms.create', $proceso->id)?>">
                    <i class="material-icons">insert_drive_file</i> Nuevo
                </a>
                <a class="btn btn-light" href="#modalImportarFormulario" data-toggle="modal">
                    <i class="material-icons">file_upload</i> Importar
                </a>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>Formulario
                    <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_generacion_formulario');" href="/ayuda/simple/backend/modelamiento-del-proceso/generacion-de-formularios.html"
                       target="_blank">
                        <i class="material-icons">help</i>
                    </a>
                </th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($formularios as $p)
                <tr>
                    @if(!is_null($p->descripcion))
                    <td><?=$p->nombre ." | ".$p->descripcion?></td>
                    @else
                    <td><?=$p->nombre?></td>
                    @endif
                    <td>
                        <a href="<?=route('backend.forms.edit', [$p->id])?>" class="btn btn-primary">
                            <i class="material-icons">edit</i> Editar
                        </a>
                        <a class="btn btn-light" href="<?=route('backend.forms.export', [$p->id])?>">
                            <i class="material-icons">file_download</i> Exportar
                        </a>
                        <a href="{{route('backend.forms.delete', [$p->id])}}" class="btn btn-danger"
                           onclick="return confirm('¿Esta seguro que desea eliminar?')">
                            <i class="material-icons">delete</i>
                            Eliminar
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div id="modalImportarFormulario" class="modal hide fade">
        <form method="POST" enctype="multipart/form-data" action="{{route('backend.forms.import')}}">
            {{csrf_field()}}
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Importar Formulario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Cargue a continuación el archivo .simple donde exportó su formulario.</p>
                        <input type="file" class="form-control" name="archivo"/>
                        <input type="hidden" name="proceso_id" value="{{$proceso->id}}"/>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Importar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection