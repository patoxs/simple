@extends('layouts.backend')

@section('title', 'Configuración de Usuarios')

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
                        <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                    </ol>
                </nav>

                <div class="row align-items-center d-flex justify-content-between">
                    <div class="col-2 mb-4 mt-2">
                        <a href="{{ route('backend.configuration.backend_users.add') }}"
                           class="btn btn-success">
                            <i class="material-icons">note_add</i> Nuevo
                        </a>
                    </div>
                    <div class="col-6 mb-4 mt-2">
                        <form action="{{ route('backend.configuration.backend_users') }}" class="form">
                            <div class="row justify-content-end">
                                <div class="col-5">
                                    <input type="text" class="form-control" placeholder="Escribe el email a buscar aquí"
                                           name="email_search" value="{{ $email_search  }}">
                                </div>
                                <div class="col-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="material-icons">search</i>
                                        Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-12">
                        @if($users->count() >= 1)
                            <table class="table">
                                <thead>
                                <th>
                                    E-Mail
                                    <a href="{{ route('backend.configuration.backend_users').'?email_search='.$email_search.'&order_by_email='.(null==$order_by_email ? 'asc':$order_by_email) }}" class="arrow-order">
                                        <i class="material-icons top-arrow {{ ($order_by_email && $order_by_email == 'asc') ? 'active':'' }}">
                                            arrow_drop_up
                                        </i>
                                        <i class="material-icons bottom-arrow {{ ($order_by_email && $order_by_email == 'desc') ? 'active':'' }}">
                                            arrow_drop_down
                                        </i>
                                    </a>
                                </th>
                                <th>
                                    Nombre
                                    <a href="{{ route('backend.configuration.backend_users').'?email_search='.$email_search.'&order_by_name='.(null==$order_by_name ? 'asc':$order_by_name) }}" class="arrow-order">
                                        <i class="material-icons top-arrow {{ ($order_by_name && $order_by_name == 'asc') ? 'active':'' }}">
                                            arrow_drop_up
                                        </i>
                                        <i class="material-icons bottom-arrow {{ ($order_by_name && $order_by_name == 'desc') ? 'active':'' }}">
                                            arrow_drop_down
                                        </i>
                                    </a>
                                </th>
                                <th>
                                    Apellidos
                                    <a href="{{ route('backend.configuration.backend_users').'?email_search='.$email_search.'&order_by_lastname='.(null==$order_by_lastname ? 'asc':$order_by_lastname)  }}" class="arrow-order">
                                        <i class="material-icons top-arrow {{ ($order_by_lastname && $order_by_lastname == 'asc') ? 'active':'' }}">
                                            arrow_drop_up
                                        </i>
                                        <i class="material-icons bottom-arrow {{ ($order_by_lastname && $order_by_lastname == 'desc') ? 'active':'' }}">
                                            arrow_drop_down
                                        </i>
                                    </a>
                                </th>
                                <th>
                                    Rol
                                    <a href="{{ route('backend.configuration.backend_users').'?email_search='.$email_search.'&order_by_rol='.(null==$order_by_rol ? 'asc':$order_by_rol)  }}" class="arrow-order">
                                        <i class="material-icons top-arrow {{ ($order_by_rol && $order_by_rol == 'asc') ? 'active':'' }}">
                                            arrow_drop_up
                                        </i>
                                        <i class="material-icons bottom-arrow {{ ($order_by_rol && $order_by_rol == 'desc') ? 'active':'' }}">
                                            arrow_drop_down
                                        </i>
                                    </a>
                                </th>
                                <th>Acciones</th>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{$user->email}}</td>
                                        <td>{{$user->nombre}}</td>
                                        <td>{{$user->apellidos}}</td>
                                        <td>{{$user->rol}}</td>
                                        <td>
                                            <a href="{{route('backend.configuration.backend_users.edit', $user->id)}}"
                                               class="btn btn-primary">
                                                <i class="material-icons">edit</i> Editar
                                            </a>
                                            <form id="form-<?= $user->id ?>" method="post"
                                                  action="{{route('backend.configuration.backend_users.delete', $user->id)}}"
                                                  style="display: inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE"/>
                                                <a class="btn btn-danger"
                                                   onclick="if(confirm('¿Está seguro que desea eliminar?')) document.querySelector('#form-<?= $user->id ?>').submit(); return false;"
                                                   href="#">
                                                    <i class="material-icons">close</i> Eliminar
                                                </a>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{ $users->links('vendor.pagination.bootstrap-4') }}
                        @elseif($email_search != '')
                            <p>No se encontraron resultados</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
