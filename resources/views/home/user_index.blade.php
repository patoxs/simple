@extends('layouts.procedure')

@section('content')
    <h1 class="title">Listado de trámites disponibles</h1>
    <hr>
    <br>

    <div class="row">
        <div class="col-sm-12">
            @include('home.tramites', ['login' => true])
        </div>
    </div>
@endsection