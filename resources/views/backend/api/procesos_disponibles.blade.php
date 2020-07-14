@extends('layouts.backend')

@section('title', 'Trámites disponibles')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="span9">
                <h2>Trámites disponibles como servicios</h2>
                <table class="table">
                    <tbody>
                    <tr>
                        <th>Nombre del Proceso</th>
                        <th>Tarea</th>
                        <th>Descripción</th>
                        <th>Url</th>
                    </tr>
                    @foreach($json as $item)
                        <tr>
                            <td>{{$item['nombre']}}</td>
                            <td>{{$item['tarea']}}</td>
                            <td></td>
                            <td>
                                <a class="btn btn-light" target="_blank"
                                   href="{{url("integracion/especificacion/servicio/proceso/{$item['id']}/tarea/{$item['id_tarea']}")}} ">
                                    <i class="material-icons">file_download</i>Swagger
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection