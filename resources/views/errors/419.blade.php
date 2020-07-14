<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>409 Página ha expirado - {{ config('app.name', 'Laravel') }}</title>

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
            <h2 class="status-error">419</h2>
            <img class="error-icon" src="{{ asset('/img/error_icon.svg') }}" alt="Error 419 icon">
        </div>
        <div class="col-7">
            <h1>La página ha expirado</h1>
            <p>
                La página ha expirado debido a inactividad. <br>
                Por favor, actualice y pruebe de nuevo.
            </p>
        </div>
    </div>
</div>
</body>
</html>