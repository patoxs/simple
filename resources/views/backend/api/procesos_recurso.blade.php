@extends('layouts.backend')

@section('title', 'Procesos: recurso')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="col-9">
                <h2>Procesos</h2>

                <p>Procesos es un listado de procesos de SIMPLE. Los métodos permiten obtener información de un proceso
                    o listar una serie de procesos.</p>

                <h3>Métodos</h3>

                <dl>
                    <dt><a href="{{route('backend.api.procesos_obtener')}}">obtener</a></dt>
                    <dd>Obtiene un recurso proceso.</dd>
                    <dt><a href="{{route('backend.api.procesos_listar')}}">listar</a></dt>
                    <dd>Obtiene el listado completo de procesos de la cuenta.</dd>
                </dl>

                <h3>Representación del recurso</h3>

                <p>Un recurso es representado como una estructura json. Este es un ejemplo de cómo se vería un
                    recurso.</p>

                <pre>{
    "proceso":{
        "id":10,
        "nombre":"Proceso de Inscripción a Beca Educacional"
    }
}</pre>

            </div>

        </div>
    </div>
@endsection