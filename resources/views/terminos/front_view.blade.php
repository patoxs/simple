@extends('layouts.terminos_y_condiciones')

@section('content')
    <div class="container terminos-condiciones frontend">
        <div class="row justify-content-md-center">
            <div class="col-3">
                <div class="sticky-top">
                    <div class="indice">Índice</div>
                    <ul class="side-front-menu" id="side-items-menus">
                        <li class="active"><a href="#item-1">1. ¿Qué es SIMPLE?</a></li>
                        <li><a href="#item-2">2. ¿De quién es SIMPLE?</a></li>
                        <li>
                            <a href="#item-3">3. Sobre la utilización de SIMPLE</a>
                            <ul>
                                <li class="inner-li"><a href="#item-3.1">3.1. Software as a Service (SaaS)</a></li>
                                <li class="inner-li"><a href="#item-3.2">3.2. Licencia BSD</a></li>
                            </ul>
                        </li>
                        <li class=""><a href="#item-4">4. Sobre la modificación de estos Términos y Condiciones</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-9">
                <div class="content-terminos-front" data-spy="scroll" data-target="#navbar-terminos" data-offset="0">
                    <h1 class="title-terms-front">Téminos y condiciones</h1>
                    @include('terminos.description', ['is_backend' => false])
                </div>
            </div>
        </div>
    </div>
@endsection
