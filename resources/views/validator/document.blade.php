<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Validador de Documentos</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('/img/favicon.png')}}">

    @yield('css')
</head>
<body>
<div class="main-container container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4 col-sm-12">
            <div class="jumbotron pt-5 pb-5">
                <form method="POST" action="{{route('validator.document')}}" autocomplete="off">
                    {{csrf_field()}}
                    <legend>Valide su documento</legend>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="id" class="col-form-label text-md-right">{{ __('Folio') }}</label>
                        <input id="id" type="text" class="form-control{{ $errors->has('id') ? ' is-invalid' : '' }}"
                               name="id" value="{{ old('id') }}" required autofocus>
                        @if ($errors->has('id'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('id') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="key" class="col-form-label text-md-right">{{ __('Código de verificación') }}</label>
                        <input id="key" type="text" class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}"
                               name="key" value="{{ old('key') }}" required autofocus>
                        @if ($errors->has('key'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('key') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="form-actions text-right mt-3">
                        <button type="submit" class="btn btn-primary">Validar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
