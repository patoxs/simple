@extends('layouts.backend')

@section('title', 'Configuración de Firmas')

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
                            <a href="{{route('backend.configuration.electronic_signature')}}">Firmas</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$edit ? $form->email : 'Crear'}}</li>
                    </ol>
                </nav>

                <form action="{{$edit ?
                route('backend.configuration.electronic_signature.update', ['id' => $form->id]) :
                route('backend.configuration.electronic_signature.store')}}"
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

                        <div class="col-4">
                            <!--@if($edit)
                                @include('components.inputs.email', ['key' => 'email', 'display_name' => 'Correo electrónico', 'disabled' => true])
                            @else
                                @include('components.inputs.email', ['key' => 'email', 'display_name' => 'Correo electrónico'])
                            @endif
                            @include('components.inputs.password_with_confirmation', ['key' => 'password'])-->
                            @include('components.inputs.text', ['key' => 'rut'])
                            @include('components.inputs.text', ['key' => 'nombre'])
                            
                            <!--@include('components.inputs.text', ['key' => 'entidad'])-->
                            <label>Entidad</label>
                            <input name="entidad" id="entidad" type="text" class="entidad form-control" value="{{$entidad1}}" disabled>   
                            <!--{{$entidad1}}-->
                            <!--@include('components.inputs.text', ['key' => 'proposito'])-->
                            <label>Próposito</label>
                            <input name="proposito" id="proposito" type="text" class="proposito form-control" value="{{$proposito}}" >
                            <!--<select name="proposito" class="proposito form-control">
                                <option value="Próposito General">Propósito General</option>
                                <option value="Desatendido">Desatendido</option>
                            </select>-->
                            <label>Estado</label>
                            <select name="estado" class="proposito form-control">
                                <option value="1">Activo</option>
                                <option value="0">No Activo</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="help-block">
                                <ul>
                                    <li>Propósito General: El usuario debe Firmar el documento.</li>
                                    <li>Desatendida: La firma de los documentos se realiza automaticamente. Solo se permite una firma.</li>
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