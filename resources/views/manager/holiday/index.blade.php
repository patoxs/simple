@push('scripts')
    <link rel="stylesheet" href="{{asset('js/helpers/calendar/css/calendar.css')}}">
    <script type="text/javascript"
            src="{{asset('js/helpers/calendar/components/underscore/underscore-min.js')}}"></script>
    <script type="text/javascript"
            src="{{asset('js/helpers/calendar/components/jstimezonedetect/jstz.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/helpers/calendar/js/language/es-CO.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/helpers/calendar/js/calendar.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/helpers/calendarmanager.js?v=0.5')}}"></script>
    <script src="{{asset('js/helpers/collapse.js')}}"></script>
    <script src="{{asset('js/helpers/transition.js')}}"></script>
    <script src="{{asset('js/helpers/bootstrap-datetimepicker.min.js')}}"></script>

    <script>
        window.listado = null;
        var calendars = {};
        $(function () {
            carcarObjectCalendar();

        });

        function carcarObjectCalendar() {
            moment.locale('es');
            var thisMonth = moment().format('YYYY-MM');
        }

        function getListado() {
            return listado;
        }

        function eliminarDia() {
            var swselecciono = 0;
            var fecha = '0';
            if (jQuery.trim($('#fechaaelim').val()) != '') {
                swselecciono = 1;
                fecha = $('#fechaaelim').val();
                var idelim = $('#idelim').val();
                $("#agregardia").load("/manager/diaferiado/ajax_confirmar_eliminar_dia?select=" + swselecciono + "&fecha=" + fecha + "&id=" + idelim);
                $("#agregardia").modal();
            }
        }
    </script>
@endpush

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<div class="containter-calendar container-feriados">
    <input type="hidden" id="urlbase" value="<?= env('APP_URL') ?>"/>
    <div class="page-header">
        <h3></h3>
        <div class="form-inline">
            <div class="btn-group">
                <button class="btn" data-calendar-nav="prev">&lt;&lt;</button>
                <button class="btn btn-primary" data-calendar-nav="today">Mes Actual</button>
                <button class="btn" data-calendar-nav="next">&gt;&gt;</button>
            </div>
            <div class="btn-group">
                <button class="btn anoradios" data-calendar-view="year">A&ntilde;o</button>
                <button class="btn active mesradios" data-calendar-view="month">Mes</button>
            </div>
        </div>
    </div>
    <div id="calendar" class="calendar float-left"></div>
    <div class="detallecal" style="display:none;">
        <div class="labeldetconfglob"><label>D&iacute;as Feriados Registrados</label></div>
        <div id="desccalendar" class="det col-md-5 col-sm-12 col-xs-12"></div>
        <div class="container-bot"><a class="btn btn-danger" href="#" onclick="eliminarDia();">
                <i class="material-icons">delete</i> Eliminar</a></div>
    </div>
</div>
<div id="agregardia" class="modal hide fade modalconfg"></div>
<input type="hidden" id="fechaaelim" value=""/>
<input type="hidden" id="idelim" value=""/>