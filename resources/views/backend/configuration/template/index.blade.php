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
                        <li class="breadcrumb-item active" aria-current="page">Selección de plantilla</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12">
                        <a href="{{route('backend.configuration.template.add')}}"
                           class="btn btn-success">
                            <i class="material-icons">note_add</i> Nueva Plantilla
                        </a>
                    </div>
                    <br>
                    <br>
                    <div class="col-12">
                        <table class="table">
                            <thead>
                            <th>Plantilla</th>
                            <th>Vista Previa</th>
                            <th>Actual en uso</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody>
                            @foreach($config as $template)
                                <tr>
                                    <td>{{$template->nombre_visible}}</td>
                                    <td>
                                        <img class="theme" height="140" width="280"
                                             src="{{asset(
                                             ($template->nombre == 'default') ?
                                                 'uploads/themes/' . $template->nombre . '/preview.png' :
                                                 'uploads/themes/' . $template->cuenta_id . '/' . $template->nombre . '/preview.png'
                                             )}}"
                                             alt="theme"/>
                                    </td>
                                    <td>{{ ($config_id == $template->id) ? 'Si': 'No'}}</td>
                                    <td>
                                        <a href="{{route('backend.configuration.template', $template->id)}}"
                                           class="btn btn-primary">
                                            <i class="material-icons">select_all</i> Seleccionar
                                        </a>
                                        <a href="{{route('backend.configuration.template.delete', $template->id)}}"
                                           onclick="if(!confirm('¿Está seguro que desea eliminar?')) return false;"
                                           class="btn btn-danger">
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
