<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.ga')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{  \Cuenta::seo_tags()->title }}</title>
    <meta name="description" content="{{ \Cuenta::seo_tags()->description }}">
    <meta name="keywords" content="{{ \Cuenta::seo_tags()->keywords }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }} " rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">
    @yield('css')



</head>
<body class="h-100">
	<div id="app" class="h-100 d-flex flex-column">
    	@include('layouts.header')
    	<div class="main-container container pb-5">
        	@yield('content')
        	{!! isset($content) ? $content : '' !!}
    	</div>
        @include('layouts.footer', ['metadata' => \Cuenta::getAccountMetadata()])
	</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
@yield('script')
</body>
</html>
