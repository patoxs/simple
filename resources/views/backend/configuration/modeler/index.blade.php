@extends('layouts.backend')

@section('title', 'Selección de Conector')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.configuration.nav')

            <div class="col-md-9">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.configuration.my_site')}}">Configuración</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Selección Conector</li>
                    </ol>
                </nav>

                <table class="table">
                    <tr>
                        <th>Conector</th>
                        <th>Vista previa</th>
                        <th>Actual en uso</th>
                        <th>Acciones</th>
                    </tr>
                    @foreach($config as $p)
                        <tr>
                            <td>{{$p->nombre_visible}}</td>
                            <td><img class="theme" height="240" width="280"
                                     src="{{asset('uploads/connectors/' . $p->nombre . '.png')}}" alt="connectors"/>
                            </td>
                            <?php
                            $condicion = "No";
                            if ($config_id == $p->id) {
                                $condicion = "Si";
                            }
                            ?>
                            <td><?=$condicion?></td>
                            <td>
                                <a class="btn btn-primary"
                                   href="{{route('backend.configuration.modeler', [$p->id])}}">
                                    <i class="icon-edit icon-white"></i> Seleccionar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection
