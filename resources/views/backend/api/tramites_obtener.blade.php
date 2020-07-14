@extends('layouts.backend')

@section('title', 'Trámites: obtener')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="col-9">
                <h2>Tramites: obtener</h2>

                <p>Obtiene un trámite.</p>

                <h3>Request HTTP</h3>

                <pre>GET {{url('/backend/api/tramites/{tramiteId}?token={token}')}}</pre>

                <h3>Parámetros</h3>

                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <th>Nombre del Parámetro</th>
                        <th>Valor</th>
                        <th>Descripción</th>
                    </tr>
                    <tr>
                        <td>tramiteId</td>
                        <td>int</td>
                        <td>Identificador único de un trámite en SIMPLE.</td>
                    </tr>
                    </tbody>
                </table>

                <h3>Response HTTP</h3>

                <p>Si el request es correcto, se devuelve un <a href="{{route('backend.api.tramites_recurso')}}">recurso
                        tramite</a>.</p>
            </div>

        </div>
    </div>
@endsection