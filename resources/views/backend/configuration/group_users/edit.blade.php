@extends('layouts.backend')

@section('title', 'Configuración Grupos de Usuarios')

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
                            <a href="{{route('backend.configuration.group_users')}}">Grupos de Usuarios</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$edit ? $form->usuario : 'Crear'}}</li>
                    </ol>
                </nav>

                <form action="{{$edit ?
                route('backend.configuration.group_users.update', ['id' => $form->id]) :
                route('backend.configuration.group_users.store')}}"
                      method="POST">

                    @if($edit)
                        {{method_field('PUT')}}
                    @endif

                    {{csrf_field()}}

                    <div class="row">
                        <div class="col-12">
                            <h4>{{$edit ? 'Editar' : 'Crear Grupo de Usuario'}}</h4>
                            <hr>
                        </div>

                        <div class="col-4">
                            @include('components.inputs.text', ['key' => 'nombre'])

                            <div class="form-group">
                                <label for="usuarios">Este grupo lo componen</label>
                                <select class="chosen form-control" name="usuarios[]"
                                        data-placeholder="Seleccione los usuarios" multiple>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{$usuario->id}}"
                                                {{$edit && in_array($usuario->id, $usuarios_selected) ? 'selected' : ''}}>
                                            {{$usuario->usuario}} - {{$usuario->email}}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('usuarios'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('usuarios') }}</strong>
                                    </div>
                                @endif
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
