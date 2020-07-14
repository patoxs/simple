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
                            <a href="{{route('backend.configuration.frontend_users')}}">Usuarios</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$edit ? $form->usuario : 'Crear'}}</li>
                    </ol>
                </nav>

                <form action="{{$edit ?
                route('backend.configuration.frontend_users.update', ['id' => $form->id]) :
                route('backend.configuration.frontend_users.store')}}"
                      method="POST">

                    @if($edit)
                        {{method_field('PUT')}}
                    @endif

                    {{csrf_field()}}

                    <div class="row">
                        <div class="col-12">
                            <h4>{{$edit ? 'Editar' : 'Crear Usuario'}}</h4>
                            <hr>
                        </div>

                        <div class="col-5">

                            @if($edit)
                                @include('components.inputs.text', ['key' => 'usuario', 'disabled' => true])
                            @else
                                @include('components.inputs.text', ['key' => 'usuario'])
                            @endif

                            @include('components.inputs.password_with_confirmation', ['key' => 'password'])
                            @include('components.inputs.text', ['key' => 'nombres'])
                            @include('components.inputs.text', ['key' => 'apellido_paterno', 'display_name' => 'Apellido Paterno'])
                            @include('components.inputs.text', ['key' => 'apellido_materno', 'display_name' => 'Apellido Materno'])


                            @include('components.inputs.email', ['key' => 'email', 'display_name' => 'Correo electrónico'])


                            <div class="form-group">
                                <label for="grupos_usuarios">Grupo de Usuarios</label>
                                <select class="chosen form-control" name="grupos_usuarios[]"
                                        data-placeholder="Seleccione los grupos de usuarios" multiple>
                                    @foreach($grupos as $grupo)
                                        <option value="{{$grupo->id}}"
                                                {{$edit && in_array($grupo->id, $grupos_selected) ? 'selected' : ''}}>
                                            {{$grupo->nombre}}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('grupos_usuarios'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('grupos_usuarios') }}</strong>
                                    </div>
                                @endif
                            </div>


                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="vacaciones"
                                       {{$form->vacaciones ? 'checked' : ''}}
                                       id="vacaciones">
                                <label class="form-check-label" for="vacaciones">
                                    ¿Fuera de oficina?
                                </label>
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
@section('script')
    <script>
        $(".chosen").chosen({disable_search_threshold: 10});
    </script>
@endsection
