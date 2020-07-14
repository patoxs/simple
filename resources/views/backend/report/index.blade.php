@extends('layouts.backend')

@section('title', 'Gestión')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">Gestión</li>
                    </ol>
                </nav>
                <table class="table">
                    <thead>
                    <th width="90%">Proceso</th>
                    <th width="10%"></th>
                    </thead>
                    <tbody>
                    @foreach($procesos as $p)
                        @if ($p->activo == '1')
                            @if(is_null((Auth::user()->procesos)))
                                <tr>
                                    <td>{{$p->nombre}}</td>
                                    <td>
                                        <a href="{{route('backend.report.list', [$p->id])}}"
                                           class="btn btn-primary">
                                            <i class="material-icons">remove_red_eye</i> Ver Reportes
                                        </a>
                                    </td>
                                </tr>
                            @elseif( in_array( $p->id,explode(',',Auth::user()->procesos)))
                                <tr>
                                    <td>{{$p->nombre}}</td>
                                    <td>
                                        <a href="{{route('backend.report.list', [$p->id])}}"
                                           class="btn btn-primary">
                                            <i class="material-icons">remove_red_eye</i> Ver Reportes
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection