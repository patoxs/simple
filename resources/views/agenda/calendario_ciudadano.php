<style>
    .tooltip {
        z-index: 9999;
    }
</style>
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Calendario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <input type="hidden" id="validarferiado" value="0">
        <div class="modal-body mod-cal_ciu">
            <div class="validacion valcal"></div>
            <input type="hidden" id="daysel">
            <div class="containter-calendar calendar-ciud">
                <input type="hidden" id="urlbase" value="<?= URL::to('/') ?>"/>
                <div class="page-header">
                    <div class="float-right form-inline">
                        <div class="btn-group">
                            <button class="btn btn-light" data-calendar-nav="prev">&lt;&lt;</button>
                            <button class="btn btn-primary" data-calendar-nav="today">Hoy</button>
                            <button class="btn btn-light" data-calendar-nav="next">&gt;&gt;</button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-light" data-calendar-view="year">A&ntilde;o</button>
                            <button class="btn btn-light active" data-calendar-view="month">Mes</button>
                            <!-- <button class="btn" data-calendar-view="week">Semana</button> -->
                            <button class="btn btn-light" data-calendar-view="day">D&iacute;a</button>
                        </div>
                    </div>
                    <h3></h3>
                </div>
                <div id="calendar" class="calendar"></div>
                <div></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" data-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>

<div id="modalconfirmar" class="modal hide fade modalconfg modcalejec"></div>
<input type="hidden" id="urlbase" value="<?= URL::to('/') ?>"/>
<script>
    window.calendar = null;
    window.hiddenfest = false;
    $(function () {
        var urlbase = '<?= URL::to('/') ?>';
        ignorar_festivo(<?= $idagenda ?>);
        var url = urlbase + 'agenda/disponibilidadCiudadano/<?= $idagenda ?>';
        feriados = cargarDiasFeriados();
        var options = {
            events_source: url,
            view: 'month',
            tmpl_path: urlbase + '/calendar/tmpls_ciudadano/',
            tmpl_cache: false,
            width: 650,
            language: 'es-CO',
            classes: {
                months: {
                    inmonth: 'cal-day-inmonth',
                    outmonth: 'cal-day-outmonth',
                    saturday: 'cal-day-weekend',
                    sunday: 'cal-day-weekend',
                    holidays: 'dia-festivo',
                    today: 'cal-hoy'
                },
                week: {
                    workday: 'cal-day-workday',
                    saturday: 'cal-day-weekend',
                    sunday: 'cal-day-weekend',
                    holidays: 'dia-festivo',
                    today: 'cal-hoy'
                }
            },
            views: {
                year: {
                    slide_events: 1,
                    enable: true
                },
                month: {
                    slide_events: 1,
                    enable: true
                },
                week: {
                    enable: 0
                },
                day: {
                    enable: true
                }
            },
            merge_holidays: true,
            holidays: feriados,
            onAfterEventsLoad: function (events) {
                if (!events) {
                    return;
                }
                var list = $('#eventlist');
                list.html('');

                $.each(events, function (key, val) {
                    $(document.createElement('li'))
                        .html('<a href="' + val.url + '">' + val.title + '</a>')
                        .appendTo(list);
                });
            },
            onAfterViewLoad: function (view) {
                $('.page-header h3').text(this.getTitle());
                $('.btn-group button').removeClass('active');
                $('button[data-calendar-view="' + view + '"]').addClass('active');
                $('.cal-cell').dblclick(function () {
                    eventDaysCalendar();
                });
                $('.cal-cell').click(function () {
                    eventDaysCalendar();
                });
                $('span[data-toggle="tooltip"]').mouseover(function (e) {
                    $("#mtooltipcustom").css({'top': (y + 20) + 'px', 'left': (x + 20) + 'px'});
                    var id = $(this).attr('aria-describedby');
                    if (typeof($("#" + id).find(".ui-tooltip-content").html()) != "undefined") {
                        var x = e.clientX,
                            y = e.clientY;
                        $('.calendar-ciud').append('<span id="mtooltipcustom" class="customtooltips">' + $("#" + id).find(".ui-tooltip-content").html() + '</span>');
                        $("#mtooltipcustom").css({'top': (y + 20) + 'px', 'left': (x + 20) + 'px'});
                    }
                });
                $('span[data-toggle="tooltip"]').mouseout(function () {
                    $("#mtooltipcustom").remove();
                });
                $('.cal-month-box .event-warning').parent().parent().find('span').addClass('styhaycita');
                $('.cal-month-box .event-warning').parent().parent().addClass('sweventhaycita');
                var ignore = 0
                if (typeof($('#validarferiado')) !== "undefined") {
                    ignore = $('#validarferiado').val();
                    if (ignore == 1) {
                        $('.event-warning').parent().parent().addClass('festivodisp');
                        $('.event-warning').parent().parent().find('span').addClass('styhaycitaspan');
                    }
                }
                var tmp = new Date();
                var $datehoy = new Date(tmp.getFullYear(), tmp.getMonth(), tmp.getDate(), 0, 0, 0, 0);
                $.each($('span[data-cal-date]'), function (index, element) {
                    var adateinview = $(this).attr('data-cal-date').split('-');
                    var $datecal = new Date(adateinview[0], adateinview[1] - 1, adateinview[2], 0, 0, 0, 0);
                    if ($datecal.getTime() < $datehoy.getTime()) {
                        $(this).addClass('diapasado');
                    }
                });
                $('.eventradocup').parent().parent().find('span').addClass('diaocupado');
            }
        };
        calendar = $('#calendar').calendar(options);
        $(document).on('click', '.float-right', function () {
            eventDaysCalendar();
        });

        var fecha = new Date();
        var mesactual = fecha.getMonth() + 1;
        mesvisto = calendar.getMonth();
        $('.btn-group button[data-calendar-nav]').each(function () {
            var $this = $(this);
            $this.click(function () {
                calendar.navigate($this.data('calendar-nav'));
                var ignore = 0
                if (typeof($('#validarferiado')) !== "undefined") {
                    ignore = $('#validarferiado').val();
                }
                var sw = false;
                var tmp = calendar.getDateSelect().split("/");
                var hoy = new Date(tmp[2], tmp[1] - 1, tmp[0], 0, 0, 0, 0);
                for (var k in feriados) {
                    if (feriados.hasOwnProperty(k)) {
                        var m = k.split('-');
                        var hol = new Date(m[2], m[1] - 1, m[0], 0, 0, 0, 0);
                        if (hoy.getTime() == hol.getTime()) {
                            if (ignore == 0) {
                                sw = true;
                            }
                        }
                    }
                }
                if (sw) {
                    hiddenfest = true;
                    //$("#cont_cal").css({'display':'none'});
                }
            });
        });
        $('.btn-group button[data-calendar-view]').each(function () {
            var $this = $(this);
            $this.click(function () {
                calendar.view($this.data('calendar-view'));
                eventDaysCalendar();
            });
        });
        $('#first_day').change(function () {
            var value = $(this).val();
            value = value.length ? parseInt(value) : null;
            calendar.setOptions({first_day: value});
            calendar.view();
        });
        $('#events-in-modal').change(function () {
            var val = $(this).is(':checked') ? $(this).val() : null;
            calendar.setOptions({modal: val});
        });
        $('#show_wbn').change(function () {
            var val = $(this).is(':checked') ? true : false;
            calendar.setOptions({display_week_numbers: val});
            calendar.view();
        });
        $('#show_wb').change(function () {
            var val = $(this).is(':checked') ? true : false;
            calendar.setOptions({weekbox: val});
            calendar.view();
        });
        $('#events-modal .modal-header, #events-modal .modal-footer').click(function (e) {
            //e.preventDefault();
            //e.stopPropagation();
        });
        $('#tabs').css({'display': 'block'});
    });

    function cargarDiasFeriados(calendar) {
        var urlbase = '<?= URL::to('/') ?>';
        var url = urlbase + 'agenda/diasFeriados';
        var arrdata = new Array();
        $.ajax({
            url: url,
            dataType: 'json',
            async: false,
            success: function (data) {
                if (data.code == '200') {
                    var items = data.daysoff;
                    $.each(items, function (index, element) {
                        arrdata[element.date_dayoff] = element.name;
                    });
                }
            }
        });
        return arrdata;
    }

    function eventDaysCalendar() {
        var tmp = calendar.getDateSelect().split('/');
        var d = new Date(tmp[2], tmp[1] - 1, tmp[0], 1, 0, 0, 0);
        var select = d.getDate() + '/' + d.getMonth() + '/' + d.getFullYear();
        var $html = '';
        var i = 0;
        var concurrencia = 1;
        var swhaycita = false;
        var timeacutal = new Date();
        $.each(calendar.getEventos(), function (index, element) {
            var cita = new Date(element.start);
            var fincita = new Date(element.end);
            var diacita = cita.getDate() + '/' + cita.getMonth() + '/' + cita.getFullYear();

            var dataEvent = fecha_hora(cita);
            var dataEventFin = fecha_hora(fincita);
            var min = cita.getMinutes();
            if (min <= 9) {
                min = '0' + min;
            }
            var hora = cita.getHours() + ':' + min;
            if (diacita == select) {
                swhaycita = true;
                if (i == 0) {
                    if (cita > timeacutal) {
                        $html = '<div><div class="clearfix js-row-day">';
                    }
                } else {
                    if (cita > timeacutal) {
                        $html = $html + '</div><hr class="sep-row" /><div class="clearfix js-row-day">';
                    }
                }
                $desc = '';
                var cssdesc = '';
                if (element.estado == 'D') {
                    $desc = 'Disponible';
                    cssdesc = 'evdisp';
                } else {
                    if (element.estado == 'R') {
                        cssdesc = 'evreserv';
                        cssdesc = 'evbloq';
                        $desc = 'Reservado';
                    } else {
                        if (element.estado == 'B') {
                            cssdesc = 'evbloq';
                            $desc = 'Bloqueado';
                        }
                    }
                }
                var $div = '<div class="clearfix row-events-cal"><div class="hora">' + hora + '</div><div><div class="descevent ' + cssdesc + '" data-event="' + dataEvent + '" data-event-fin="' + dataEventFin + '"  >' + $desc + '</div></div></div>';
                if (cita > timeacutal) {
                    $html = $html + $div;
                }
            } else {
                i = -1;
            }
            i++;
        });
        $html = $html + '<hr class="sep-row" /></div></div>';
        if (swhaycita) {
            $('#cont_cal').html($html);

            $('#cont_cal').html($html);
            if (hiddenfest) {
                $('#cont_cal').html('<div class="clearfix row-events-cal">No existe disponibilidad de citas</div>');
            }

        } else {
            $('#cont_cal').html('<div class="clearfix row-events-cal">No existe disponibilidad de citas</div>');
        }
        var wcol = concurrencia * 188;
        var wsep = concurrencia * 153;
        $('.js-row-day').css({'width': wcol + 'px'});
        $('.sep-row').css({'width': wsep + 'px'});
        $('.evdisp').click(function () {
            var object = '<?= $idobject ?>';
            var tmp = $(this).attr('data-event').split(' ');
            var tmpf = tmp[0].split('/');
            var fecha = tmpf[2] + '-' + tmpf[1] + '-' + tmpf[0];

            var tmp2 = $(this).attr('data-event-fin').split(' ');
            var tmpf2 = tmp2[0].split('/');
            var fecha2 = tmpf2[2] + '-' + tmpf2[1] + '-' + tmpf2[0];
            var idtramite =<?= $etapa ?>;
            //var etapa=<?= $etapa ?>;
            $("#modalconfirmar").load(site_url + "agenda/ajax_confirmar_agregar_dia?idagenda=<?= $idagenda ?>&fecha=" + fecha + "&hora=" + tmp[1] + "&obj=" + object + "&fechaf=" + fecha2 + "&horaf=" + tmp2[1] + "&idcita=<?= $idcita ?>&idtramite=" + idtramite);
            $("#modalconfirmar").modal();
        });
    }

    function fecha_hora(ObjectDate) {
        var min = ObjectDate.getMinutes();
        if (min <= 9) {
            min = '0' + min;
        }
        var hora = ObjectDate.getHours() + ':' + min;

        var tmp = '';
        var tmpdia = ObjectDate.getMonth() + 1;
        if (tmpdia <= 9) {
            tmp = ObjectDate.getDate() + '/0' + tmpdia + '/' + ObjectDate.getFullYear();
        } else {
            tmp = ObjectDate.getDate() + '/' + tmpdia + '/' + ObjectDate.getFullYear();
        }
        var dataEvent = tmp + ' ' + hora;
        return dataEvent;
    }

    function ignorar_festivo(idagenda) {
        var urlbase = '<?= URL::to('/') ?>';
        var url = urlbase + 'agenda/ajax_obtener_datos_agenda';
        $.ajax({
            url: url,
            dataType: "json",
            async: false,
            data: {
                id: idagenda
            },
            success: function (data) {
                if (data.code == 200) {
                    var ignorefestivo = data.calendar.ignore_non_working_days;
                    $('#validarferiado').val(ignorefestivo);
                }
            }
        });
    }
</script>
