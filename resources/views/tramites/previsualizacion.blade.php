@extends('layouts.tramite_informativo')

@section('content')
    <div class="content-previsualizacion mt-lg-4 mb-lg-5">
        <h1 class="title">{{ $proceso->ficha_titulo }}</h1>
        <hr>
        <p class="mb-lg-5" style="white-space: pre-line;">
            {{ $proceso->ficha_contenido }}
        </p>
        <a href="{{ url('home') }}" class="btn btn-default">Volver</a>
        <a href="{{
                 $proceso->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$proceso->id]) :
                (
                    $proceso->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$p->id]) :
                    route('login').'?redirect='.route('tramites.iniciar', $p->id)
                )
                }}"
           class="btn btn-primary {{$proceso->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
            @if ($proceso->canUsuarioIniciarlo(Auth::user()->id))
                Iniciar trámite
            @else
                @if ($proceso->getTareaInicial()->acceso_modo == 'claveunica')
                    <i class="icon-claveunica"></i> Iniciar con Clave Única
                @else
                    <i class="material-icons">person</i> Autenticarse
                @endif
            @endif
        </a>
    </div>
@endsection