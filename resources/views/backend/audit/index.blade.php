@extends('layouts.backend')

@section('title', 'Auditoría')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">Auditoría</li>
                    </ol>
                </nav>

                {{ $registros->links('vendor.pagination.bootstrap-4') }}

                <table class="table" style="table-layout: fixed;word-wrap: break-word;">
                    <thead>
                    <tr>
                        <th>
                            <a href="{{ Request::url() . '?order=fecha&direction=' . ($direction == 'ASC' ? 'DESC' : 'ASC')}}">
                                Fecha {!! $order == 'fecha' ? $direction == 'ASC' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ Request::url() . '?order=proceso&direction=' . ($direction == 'ASC' ? 'DESC' : 'ASC')}}">
                                Proceso {!! $order == 'proceso' ? $direction == 'ASC' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ Request::url() . '?order=operacion&direction=' . ($direction == 'ASC' ? 'DESC' : 'ASC')}}">
                                Operacion {!! $order == 'operacion' ? $direction == 'ASC' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ Request::url() . '?order=usuario&direction=' . ($direction == 'ASC' ? 'DESC' : 'ASC')}}">
                                Usuario {!! $order == 'usuario' ? $direction == 'ASC' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' !!}
                            </a>
                        </th>
                        <th>Motivo</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($registros as $r)
                        <tr>
                            <td>{{\Carbon\Carbon::parse($r->fecha)->format('d-m-Y H:i:s')}}</td>
                            <td>{{$r->proceso}}</td>
                            <td>{{$r->operacion}}</td>
                            <td>{{htmlspecialchars($r->usuario)}}</td>
                            <td>{{$r->motivo}}</td>
                            <td width="10%" style="text-align: right;">
                                <a href="{{route('backend.audit.view', [$r->id])}}"
                                   class="btn btn-primary">
                                    <i class="material-icons">remove_red_eye</i> Ver detalles
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{ $registros->links('vendor.pagination.bootstrap-4') }}

            </div>
        </div>
    </div>
@endsection