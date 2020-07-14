@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1 class="title">Listado de trámites disponibles</h1>
    {{--<div class="date"><i class="material-icons red">date_range</i></div>--}}
    <hr>
    <br>

    <div class="row">
        <div class="col-sm-12">
            @include('home.tramites', ['login' => false])
        </div>
    </div>
@endsection
