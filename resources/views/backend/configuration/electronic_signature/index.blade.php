@extends('layouts.backend')

@section('title', 'Configuración de Firmas Electrónicas')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.configuration.nav')

            <div class="col-md-9">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.configuration.my_site')}}">Configuración</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Firmas</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12">
                        <a href="{{route('backend.configuration.electronic_signature.add')}}"
                           class="btn btn-success">
                            <i class="material-icons">note_add</i> Nuevo
                        </a>
                    </div>
                    <br>
                    <br>
                    <div class="col-12">
                        <table class="table">
                            <thead>
                            <th>Rut</th>
                            <th>Nombre</th>
                            <th>Entidad</th>
                            <th>Propósito</th>
                            <th>Estado</th>
                            <th>Procesos vinculados</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody>
                            @foreach($firmas_electronicas as $c)
                                <tr>
                                    <td>{{$c->rut}}</td>
                                    <td>{{$c->nombre}}</td>
                                    <td>{{$c->entidad}}</td>
                                    <td>{{$c->proposito}}</td>
                                    <td>{{$c->estado ? 'Activo' : 'No Activo'}}</td>
                                    <td>
                                        @if($c->documentos->count())
                                            @foreach($c->documentos as $documento)
                                                {{$documento->proceso->nombre}}<br>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{route('backend.configuration.electronic_signature.edit', $c->id)}}"
                                           class="btn btn-primary">
                                            <i class="material-icons">edit</i> Editar
                                        </a>
                                        <a class="btn btn-danger" href="{{route('backend.configuration.electronic_signature.delete', $c->id)}}"
                                        onclick="return confirm('¿Está seguro que desea eliminar esta firma? \n Los documentos asociados a esta firma serán desvinculados.')">
                                            <i class="material-icons">delete</i> Eliminar
                                        </a>
                                        <!-- <form id="form-<?= $c->id ?>" method="post"
                                              action="{{route('backend.configuration.electronic_signature.delete', $c->id)}}"
                                              style="display: inline">
                                            <?= csrf_field() ?>
                                        </form> -->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection