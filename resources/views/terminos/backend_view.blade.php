@extends('layouts.terminos_y_condiciones')

@section('content')
    <div class="container terminos-condiciones backend">
        <div class="row justify-content-md-center">
            <div class="col-12">
                @include('terminos.titulo')
            </div>

            <div class="col-10">
                @include('terminos.description', ['is_backend' => true])
            </div>
        </div>
        <div class="row">
            <div class="col-11 mb-5 text-right">
                <form method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"></input>
                    <div class="form-group form-check custom-control custom-checkbox">
                        <input class="form-check-input custom-control-input simple" type="checkbox" value="1" id="acepto-terminos"
                               name="acepto_terminos">
                        <label class="form-check-label custom-control-label simple-check" for="acepto-terminos">
                            He leido y estoy de Acuerdo con los Términos y Condiciones de la plataforma SIMPLE
                        </label>
                    </div>
                    <button type="submit" class="btn btn-simple btn-light" id="btn-no-acepto-terminos">Cancelar</button>
                    <button type="submit" class="btn btn-simple btn-primary" id="btn-acepto-terminos" disabled>Aceptar y
                        Continuar
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
