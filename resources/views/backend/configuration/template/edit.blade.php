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
                        <li class="breadcrumb-item active" aria-current="page">Plantillas</li>
                    </ol>
                </nav>

                <form action="{{route('backend.configuration.template.store')}}"
                      method="POST">

                    {{csrf_field()}}

                    <h5>Cargar Nueva Plantilla</h5>
                    <hr>

                    <div class="row">
                        <div class="col-5">
                            @include('components.inputs.text', ['key' => 'nombre_visible', 'display_name' => 'Nombre Plantilla'])
                            <div class="form-group">
                                <label for="logo">Subir</label>
                                <div id="file-uploader"></div>
                                <input type="hidden" name="nombre_plantilla" value=""/>
                                <img class="logo" src="" alt=""/>
                                @if ($errors->has('nombre_plantilla'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('nombre_plantilla') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <input type="reset" class="btn btn-light" value="Cancelar">
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('js/helpers/fileuploader.js')}}"></script>
    <script>
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            params: {_token: '{{csrf_token()}}'},
            action: '/backend/uploader/themes',
            onComplete: function (id, filename, respuesta) {
                $("input[name=nombre_plantilla]").val(respuesta.file_name);
                $("img.logo").attr("src", "/logos/" + respuesta.file_name);
            }
        });
    </script>
@endsection