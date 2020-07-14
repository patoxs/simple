@extends('layouts.procedure')

@section('content')
    <form method="GET" class="ajaxForm dynaForm"
          action="{{route('home', [$etapa->id])}}/{{$qs ? '?' . $qs : ''}}">
        {{csrf_field()}}
        <fieldset>
            <div class="validacion"></div>
            <legend>Información</legend>
            <p>Estimado usuario/a en este momento no podemos cursar su solicitud, intente más tarde</p>
            <div class="form-actions">
                <a class="btn btn-success"
                   href="<?= url('home') ?>">
                    Volver al inicio
                </a>
            </div>
        </fieldset>
        <div class="ajaxLoader" style="position: fixed; left: 50%; top: 30%; display: none;">
            <img src="{{asset('img/loading.gif')}}">
        </div>
    </form>
@endsection