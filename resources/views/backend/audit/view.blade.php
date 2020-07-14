@extends('layouts.backend')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.audit')}}">Auditoría</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
                    </ol>
                </nav>

                <h2 class="text-center">{{$title}}</h2>

                <div class="jumbotron">
                    <dl class="dl-horizontal">
                        <dt> Operacion</dt>
                        <dd> {{$registro->operacion}} </dd>
                        <dt> Usuario</dt>
                        <dd> {{htmlspecialchars($registro->usuario)}}</dd>
                        <dt> Fecha</dt>
                        <dd> {{$registro->fecha}}</dd>
                        <dt> Motivo</dt>
                        <dd style="word-wrap: break-word;"> {{$registro->motivo != '' ? $registro->motivo : ' '}}</dd>
                    </dl>
                </div>

                <div class="row">
                    @foreach($registro->detalles as $elemento => $detalle)
                        <div class="col-6">
                            <h4><?=str_replace('_', ' ', ucfirst($elemento))?></h4>
                            <div class="jumbotron">
                                <dl class="dl-horizontal">
                                    @if (count($detalle) > 0)
                                        @foreach($detalle as $key=>$value)

                                            <dt><?=str_replace('_', ' ', ucfirst($key))?></dt>
                                            @if(is_array($value))
                                                <dl class="dl-horizontal">
                                                    @foreach($value as $key=>$value)
                                                        <dt><?=str_replace('_', ' ', ucfirst($key))?></dt>
                                                        <dd><?=is_array($value) ? json_encode($value) : $value?></dd>
                                                    @endforeach
                                                </dl>
                                            @else

                                                <dd><?=$value?></dd>
                                            @endif

                                        @endforeach
                                    @else
                                        <p>Sin datos</p>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
@endsection