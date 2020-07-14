<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>404 Página no encontrada - {{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('/img/favicon.png')}}">

</head>
<body class="page-error">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-2">
            <h2 class="status-error">404</h2>
            <img class="error-icon" src="{{ asset('/img/error_icon.svg') }}" alt="Error 404 icon">
        </div>
        <div class="col-7">
            <h1>Página no encontrada</h1>
            <p>
                Lo sentimos, la página que buscas no existe. <br>
                Para resolver este error puedes realizar alguna de las siguientes acciones.
            </p>
            <ul>
                <li>Comprobar que la dirección (URL) sea la correcta</li>
                <li>Realizar una nueva búsqueda</li>
            </ul>
            <a href="{{ route('home') }}" class="btn btn-simple btn-error-500 btn-primary">
                <i class="material-icons">arrow_left</i>
                Volver al Home
            </a>
        </div>
    </div>
</div>
</body>
</html>