@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.procesos.index') }}">Listado de Procesos</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Historial de Procesos</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if($historial_cambios->count() > 0)

            <div class="row">
                <div class="col-12">
                    <h2>{{ $proceso->nombre }}</h2>
                </div>
            </div>

            <table class="table">
                <thead>
                <tr>
                    <th>Usuario</th>
                    <th>
                        Fecha
                        <a href="{{ route('backend.procesos.historial', [
                                $proceso->id
                            ]).'?order_by_fecha='.(null==$order_by_fecha ? 'asc':$order_by_fecha) }}"
                           class="arrow-order">
                            <i class="material-icons top-arrow {{ ($order_by_fecha && $order_by_fecha == 'asc') ?
                                'active':'' }}">
                                arrow_drop_up
                            </i>
                            <i class="material-icons bottom-arrow {{ ($order_by_fecha && $order_by_fecha == 'desc') ?
                                'active':'' }}">
                                arrow_drop_down
                            </i>
                        </a>
                    </th>
                    <th>Descripción</th>
                </tr>
                </thead>
                <tbody>
                @foreach($historial_cambios as $historial)
                    <tr>
                        <td>
                            @if($historial->usuario)
                                {{ $historial->usuario->email }}
                            @else
                                Usuario eliminado
                            @endif

                        </td>
                        <td>{{ $historial->getDate() }}</td>
                        <td>{{ $historial->description }}</td>
                        {{--                    <td>{{ $historial->created_at }}</td>--}}
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $historial_cambios->links('vendor.pagination.bootstrap-4') }}
        @else
            <p>
                No se registran modificaciones a la fecha.
            </p>
        @endif
    </div>


@endsection
@section('script')

@endsection