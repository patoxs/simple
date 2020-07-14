@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="col-md-12">
                <h4>Cambiar contraseña</h4>
                <hr>

                <form action="{{route('backend.cuentas.save')}}" method="post">
                    {{csrf_field()}}

                    <div class="col-2">
                        @include('components.inputs.password_with_confirmation', ['key' => 'password'])
                    </div>

                    <hr>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="{{route('backend.home')}}" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
