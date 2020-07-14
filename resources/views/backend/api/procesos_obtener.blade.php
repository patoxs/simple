@extends('layouts.backend')

@section('title', 'Procesos: obtener')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="col-9">
                <h2>Procesos: obtener</h2>

                <p>Obtiene un procesos.</p>

                <h3>Request HTTP</h3>

                <pre>GET {{url('/backend/api/procesos/{procesoId}?token={token}')}}</pre>

                <h3>Parámetros</h3>

                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <th>Nombre del Parámetro</th>
                        <th>Valor</th>
                        <th>Descripción</th>
                    </tr>
                    <tr>
                        <td>procesoId</td>
                        <td>int</td>
                        <td>Identificador único de un proceso en SIMPLE.</td>
                    </tr>
                    </tbody>
                </table>

                <h3>Response HTTP</h3>

                <p>Si el request es correcto, se devuelve un <a href="{{route('backend.api.procesos_recurso')}}">recurso
                        proceso</a>.</p>
            </div>

        </div>
    </div>
@endsection