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
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.configuration.backend_users')}}">Usuarios</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$edit ? $form->email : 'Crear'}}</li>
                    </ol>
                </nav>

                <form action="{{$edit ?
                route('backend.configuration.backend_users.update', ['id' => $form->id]) :
                route('backend.configuration.backend_users.store')}}"
                      method="POST">

                    @if($edit)
                        {{method_field('PUT')}}
                    @endif

                    {{csrf_field()}}

                    <div class="row">
                        <div class="col-12">
                            <h4>{{$edit ? 'Editar' : 'Crear'}}</h4>
                            <hr>
                        </div>

                        <div class="col-5">
                            @if($edit)
                                @include('components.inputs.email', [
                                    'key' => 'email',
                                    'display_name' => 'Correo electrónico',
                                    'disabled' => true,
                                    'no_input' => true
                                    ])
                            @else
                                @include('components.inputs.email', ['key' => 'email', 'display_name' => 'Correo electrónico'])
                            @endif
                            @include('components.inputs.password_with_confirmation', ['key' => 'password'])
                            @include('components.inputs.text', ['key' => 'nombre'])
                            @include('components.inputs.text', ['key' => 'apellidos'])


                            <div class="form-group">
                                <label for="rol">Rol</label>
                                @php
                                    $roles = array("super", "modelamiento", "seguimiento", "operacion", "gestion", "desarrollo", "configuracion", "reportes");

                                    $valores = isset($form->rol) ? explode(",", $form->rol) : [];
                                @endphp

                                <select name="rol[]" id="rol" class="form-control {{ $errors->has('rol') ? 'is-invalid':'' }}" multiple>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol }}" <?= in_array($rol, $valores) ? 'selected' : ''?> > <?= $rol ?> </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('rol'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('rol') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="help-block">
                                <ul>
                                    <li>super: Tiene todos los privilegios del sistema.</li>
                                    <li>modelamiento: Permite modelar y diseñar el funcionamiento del trámite.</li>
                                    <li>seguimiento: Permite hacer seguimiento de los tramites.</li>
                                    <li>operacion: Permite hacer seguimiento y operaciones sobre los tramites como
                                        eliminacion y edición.
                                    </li>
                                    <li>gestión: Permite acceder a reportes de gestion con privilegio de
                                        visualización.
                                    </li>
                                    <li>reportes: Permite acceder y configurar reportes de gestión y uso de la
                                        plataforma.
                                    </li>
                                    <li>desarrollo: Permite acceder a la API de desarrollo, para la integracion con
                                        plataformas externas.
                                    </li>
                                    <li>configuracion: Permite configurar los usuarios y grupos de usuarios que tienen
                                        acceso al sistema.
                                    </li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-12">
                            <hr>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <input type="reset" class="btn btn-light" value="Cancelar">
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
