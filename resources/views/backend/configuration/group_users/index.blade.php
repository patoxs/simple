@extends('layouts.backend')

@section('title', 'Configuración de Usuarios')

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
                        <li class="breadcrumb-item active" aria-current="page">Grupo de Usuarios</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12">
                        <a href="{{route('backend.configuration.group_users.add')}}"
                           class="btn btn-success">
                            <i class="material-icons">note_add</i> Nuevo
                        </a>
                    </div>
                    <br>
                    <br>
                    <div class="col-12">
                        <table class="table">
                            <thead>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Usuarios</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody>
                            @foreach($group_users as $group)
                                <tr>
                                    <td>{{$group->id}}</td>
                                    <td>{{$group->nombre}}</td>
                                    <td>{{$group->users->implode('nombres', ', ')}}</td>
                                    <td>
                                        <a href="{{route('backend.configuration.group_users.edit', $group->id)}}"
                                           class="btn btn-primary">
                                            <i class="material-icons">edit</i> Editar
                                        </a>
                                        <form id="form-<?= $group->id ?>" method="post"
                                              action="{{route('backend.configuration.group_users.delete', $group->id)}}"
                                              style="display: inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE"/>
                                            <a class="btn btn-danger"
                                               onclick="if(confirm('¿Está seguro que desea eliminar?')) document.querySelector('#form-<?= $group->id ?>').submit(); return false;"
                                               href="#">
                                                <i class="material-icons">close</i> Eliminar
                                            </a>
                                        </form>
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
