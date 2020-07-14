@extends('layouts.terminos_y_condiciones')

@section('content')
    <div class="container terminos-condiciones notificacion">
        <div class="row justify-content-md-center">
            <div class="col-12">
                @include('terminos.titulo')
            </div>
            <div class="col-8 text-center m-5 p-5">
                <img src="{{ asset('img/eye-terminos.svg') }}" alt="terminos"/>
                <h1 class="subtitle-title">Importante</h1>
                <p class="text">
                    Para hacer uso de la plataforma SIMPLE debes aceptar los Términos y Condiciones indicados. Si no
                    estas de acuerdo, por favor contacta al Coordinador de Transformación Digital de tu institución.
                </p>
                <p class="text">
                    En caso de consultas, favor contactar a nuestra Mesa de Ayuda, a través del <br>Teléfono: 600 397 0000
                    o
                    en el siguiente <a href="https://gd.policomp.com/WebContact/Default.aspx" class="item-link"
                                       target="_blank">formulario</a>.
                </p>
                <a href="{{ route('backend.terminos') }}" class="btn btn-simple btn-primary btn-lg">Volver a los
                    Términos y
                    Condiciones</a>
            </div>
        </div>
    </div>
@endsection
