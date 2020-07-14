@extends('layouts.backend')

@section('title', 'Configurar Código de Acceso')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="col-9">
                <form class="form" method="post" action="{{route('backend.api.token.update')}}">
                    {{csrf_field()}}

                    <h4>Configurar Código de Acceso</h4>
                    <hr>
                    <div class="validacion"></div>
                    <p>Para poder acceder a la API deberas configrar un código de acceso (token). Si dejas en blanco
                        el token no se podra acceder a la API.</p>
                    <div class="form-group"><label>token</label>
                        <input type="text" name="api_token" class="form-control col-3"
                               value="{{Auth::user()->cuenta->api_token}}">
                        <div class="help-block">Especificar un código aleatorio de máximo 32 caracteres.</div>
                    </div>
                    <hr>
                    <div class="form-actions">
                        <a class="btn btn-light" href="{{route('backend.api')}}">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection