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

        <a class="btn btn-success" href="<?=route('backend.subscribers.create', [$proceso->id])?>">
            <i class="material-icons">insert_drive_file</i> Nuevo</a>
        <a onclick="ga('cuenta.send','event','Mejora UX','Ayuda_suscriptores_externos');" href="/ayuda/simple/backend/modelamiento-del-proceso/suscriptores-externos.html" target="_blank">
            <i class="material-icons align-middle">help</i>
        </a>
        <table class="table mt-3">
            <thead>
            <tr>
                <th>Institución</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($suscriptores as $p)
                <tr>
                    <td><?=$p->institucion?></td>
                    <td>
                        <a href="<?=route('backend.subscribers.edit', [$p->id])?>" class="btn btn-primary">
                            <i class="material-icons">edit</i> Editar</a>
                        <a href="<?=route('backend.subscribers.delete', [$p->id])?>" class="btn btn-danger"
                           onclick="return confirm('¿Esta seguro que desea eliminar?')">
                            <i class="material-icons">delete</i> Eliminar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
@section('script')
    <script src="{{asset('/js/helpers/modelador-seguridad.js')}}" type="text/javascript"></script>
@endsection