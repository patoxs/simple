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
    <link rel="stylesheet" href="{{asset('css/bootstrap-timepicker.css')}}">

    <link href="{{ asset('css/backend.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">
    <link href="{{ asset('css/component-chosen.css') }}" rel="stylesheet">

    @yield('css')
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= env('MAP_KEY') ?>&libraries=places&language=ES"></script>
    <script src="{{ asset('js/backend.js') }}"></script>

</head>
<body >
<div id="app" >
    @include('layouts.anuncios')
    @include('layouts.backend.terminos.nav')

    <div class="container-fluid pb-5">
        @include('components.messages')
    </div>

    @yield('content')

    @include('layouts.footer', ['metadata' => \Cuenta::getAccountMetadata()])
</div>

<!-- Scripts -->
@yield('script')
<script>
    var Terms = Terms || {};

    Terms.sideHandleMenuFront = function() {
        $('#side-items-menus').on('click', 'li a', function() {
            $('#side-items-menus li').removeClass('active');
            if (!$(this).parent().hasClass('inner-li')) {
                $(this).parent().addClass('active');
            } else {
                $(this).parent().parent().parent().addClass('active');
            }
        });

        $('.inner-li').on('click', 'a', function(e){
            e.stopPropagation();
            $('#side-items-menus li:not(.inner-li)').removeClass('active');
            $(this).parent().parent().parent().addClass('active');

            $('.inner-li').removeClass('active');
            $(this).parent().addClass('active');
        });
    };

    Terms.submit = function() {
        $('#acepto-terminos').on('click', function() {
            if ($(this).is(':checked')) {
                $('#btn-acepto-terminos').removeAttr('disabled');
                $('#btn-no-acepto-terminos').attr('disabled','disabled');


            } else {
                $('#btn-acepto-terminos').attr('disabled','disabled');
                $('#btn-no-acepto-terminos').removeAttr('disabled');
            }

        });
    };

    Terms.init = function() {
        this.submit();
        this.sideHandleMenuFront();
    };

    $(document).ready(function(){
        Terms.init();
    });
</script>
</body>
</html>
