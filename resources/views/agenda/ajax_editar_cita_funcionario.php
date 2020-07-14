<!-- <link rel="stylesheet" href= "<?= base_url('assets/calendar/css/calendar.css') ?>" >
<script src= "<?= base_url('/assets/js/jquery-ui/js/jquery-ui.js') ?>"></script>
<script src= "<?= base_url('/assets/js/moment.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/underscore/underscore-min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/bootstrap2/js/bootstrap.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/language/es-CO.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/calendar.js') ?>"></script>
<script src="<?= base_url() ?>assets/js/collapse.js"></script>
<script src="<?= base_url() ?>assets/js/transition.js"></script>
<script src="<?= base_url() ?>assets/js/bootstrap-datetimepicker.min.js"></script> -->
<style>
    .tooltip {
        z-index: 9999;
    }
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <i class="icon-close icon--top"></i>
    </button>
    <h3 id="myModalLabel">Calendario</h3>
</div>
    <div class="modal-body">
        <div class="validacion"></div>
        <input type="hidden" id="daysel" >
        <div class="containter-calendar calendar-ciud">
            <input type="hidden" id="urlbase" value="<?= base_url() ?>" />
            <div class="page-header">
                <div class="pull-right form-inline">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-calendar-nav="prev"><< Anterior</button>
                        <button class="btn" data-calendar-nav="today">Hoy</button>
                        <button class="btn btn-primary" data-calendar-nav="next">Siguiente >></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-warning" data-calendar-view="year">A&ntilde;o</button>
                        <button class="btn btn-warning active" data-calendar-view="month">Mes</button>
                        <!-- <button class="btn btn-warning" data-calendar-view="week">Semana</button> -->
                        <button class="btn btn-warning" data-calendar-view="day">Dia</button>
                    </div>
                </div>
                <h3></h3>
            </div>
            <div id="calendareditfunc" class="calendar"></div>
        </div>
        
    </div>
<div class="modal-footer">
    <button class="button button--lightgray js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="#" onclick="" class="button">Escojer fecha</a>
</div>
<div id="modalconfirmar" class="modal hide fade modalconfg modcalejec"></div>
<input type="hidden" id="urlbase" value="<?= base_url() ?>" />
<script>
    window.calendar=null;
    $(function(){
        var urlbase='<?= base_url() ?>';
        //var url=urlbase+'agendas/disponibilidad/<?= $idagenda ?>';
        var url=$('#urlbase').val()+'agenda/disponibilidad/<?= $idagenda ?>';
        feriados=cargarDiasFeriados();
        var options = {
            events_source:url,
            view: 'month',
            tmpl_path: urlbase+'/assets/calendar/tmpls_ciudadano/',
            tmpl_cache: false,
            width:650,
            language:'es-CO',
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
            merge_holidays:true,
            holidays:feriados,
            onAfterEventsLoad: function(events) {
                if(!events) {
                    return;
                }
                var list = $('#eventlist');
                list.html('');

                $.each(events, function(key, val) {
                    $(document.createElement('li'))
                        .html('<a href="' + val.url + '">' + val.title + '</a>')
                        .appendTo(list);
                });
            },
            onAfterViewLoad: function(view) {
                $('.page-header h3').text(this.getTitle());
                $('.btn-group button').removeClass('active');
                $('button[data-calendar-view="' + view + '"]').addClass('active');
            }
        };
        calendar = $('#calendareditfunc').calendar(options);
        $(document).on('click','.pull-right',function(){
            eventDaysCalendar();
        });
        var fecha = new Date();
        var mesactual=fecha.getMonth() +1;
        mesvisto=calendar.getMonth();
        $('.btn-group button[data-calendar-nav]').each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.navigate($this.data('calendar-nav'));
            });
        });
        $('.btn-group button[data-calendar-view]').each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.view($this.data('calendar-view'));
                eventDaysCalendar();
            });
        });
        $('#first_day').change(function(){
            var value = $(this).val();
            value = value.length ? parseInt(value) : null;
            calendar.setOptions({first_day: value});
            calendar.view();
        });
        $('#events-in-modal').change(function(){
            var val = $(this).is(':checked') ? $(this).val() : null;
            calendar.setOptions({modal: val});
        });
        $('#show_wbn').change(function(){
            var val = $(this).is(':checked') ? true : false;
            calendar.setOptions({display_week_numbers: val});
            calendar.view();
        });
        $('#show_wb').change(function(){
            var val = $(this).is(':checked') ? true : false;
            calendar.setOptions({weekbox: val});
            calendar.view();
        });
        $('#events-modal .modal-header, #events-modal .modal-footer').click(function(e){
            //e.preventDefault();
            //e.stopPropagation();
        });
        $(document).on('click','.cal-cell',function(){
            var fe=$(this).find('span').attr('data-cal-date').split('-');
            fecha=fe[2]+'-'+fe[1]+'-'+fe[0];
            $("#agregardia").load(site_url + "backend/agendas/ajax_dia_conf_global/"+fecha);
            $("#agregardia").modal();
        })
        //$('#tabs').css({'display':'block'});
    });

    function cargarDiasFeriados(calendar){
        var urlbase='<?= base_url() ?>';
        var url=urlbase+'/agenda/diasFeriados';
        var arrdata=new Array();
        $.ajax({
            url: url,
            dataType: "json",
            async:false,
            success: function( data ) {
                if(data.code==200){
                    var items=data.daysoff;
                    $.each(items, function(index, element) {
                        var fe=element.date_dayoff.split('-');
                        arrdata[fe[0]+'-'+fe[1]]=element.name;
                    });
                }                    
            }
        });
        return arrdata;
    }
   /* function eventDaysCalendar(){
        var tmp=calendar.getDateSelect().split('/');
        var d=new Date(tmp[2],tmp[1]-1,tmp[0],1,0,0,0);
        var select=d.getDate()+'/'+d.getMonth()+'/'+d.getFullYear();
        $('#cont_cal').html('<div class="clearfix row-events-cal"><div class="hora"><strong>Hora</strong></div><div><strong>Descripci&oacute;n</strong></div></div><hr class="sep-header" />');
        $.each(calendar.getEventos(),function(index,element){
            var cita=new Date(element.start);
            var diacita=cita.getDate()+'/'+cita.getMonth()+'/'+cita.getFullYear();
            if(diacita==select){
                var min=cita.getMinutes();
                if(min<=9){
                    min='0'+min;
                }
                var hora=cita.getHours()+':'+min;
                $desc='';
                var cssdesc='';
                if(element.estado=='D'){
                    $desc='Disponible';
                    cssdesc='evdisp';
                }else{
                    if(element.estado=='R'){
                        cssdesc='evreserv';
                        $desc='Reservado';
                    }else{
                        if(element.estado=='B'){
                            cssdesc='evbloq';
                            $desc='Bloqueado';
                        }
                    }
                }
                var tmp='';
                var tmpdia=cita.getMonth()+1;
                if(tmpdia<=9){
                    tmp=cita.getDate()+'/0'+tmpdia+'/'+cita.getFullYear();
                }else{
                    tmp=cita.getDate()+'/'+tmpdia+'/'+cita.getFullYear();
                }
                var dataEvent=tmp+' '+hora;
                var $div='<div class="clearfix row-events-cal"><div class="hora">'+hora+'</div><div><div class="descevent '+cssdesc+'" data-event="'+dataEvent+'"  >'+$desc+'</div></div></div><hr class="sep-row" />';
                $('#cont_cal').append($div);
            }
        });
        $('.evdisp').click(function(){
            //var date=new Date($(this).attr('data-event'));
            var object='<?= $idobject ?>';
            var tmp=$(this).attr('data-event').split(' ');
            var tmpf=tmp[0].split('/');
            var fecha=tmpf[2]+'-'+tmpf[1]+'-'+tmpf[0];
            $("#modalconfirmar").load(site_url + "agendas/ajax_confirmar_agregar_dia/<?= $idagenda ?>/"+fecha+"/"+tmp[1]+"/"+object);
            $("#modalconfirmar").modal();
        });
    }*/
    function eventDaysCalendar(){
        var tmp=calendar.getDateSelect().split('/');
        var d=new Date(tmp[2],tmp[1]-1,tmp[0],1,0,0,0);
        var select=d.getDate()+'/'+d.getMonth()+'/'+d.getFullYear();
        var $html='';
        var i=0;
        var concurrencia=0;
        var swhaycita=false;
        var iconrow='';
        var swpuedeblock=true;
        var toltips='';
        var desctoltips='';
        $.each(calendar.getEventos(),function(index,element){
            var cita=new Date(element.start);
            var fincita=new Date(element.end);
            var diacita=cita.getDate()+'/'+cita.getMonth()+'/'+cita.getFullYear();

            var dataEvent=fecha_hora(cita);
            var dataEventFin=fecha_hora(fincita);
            var min=cita.getMinutes();
            if(min<=9){
                min='0'+min;
            }
            var hora=cita.getHours()+':'+min;
            if(diacita==select){
                swhaycita=true;
                if(i==0){
                    iconrow='<div><span onclick="block('+element.start+','+element.end+');" class="glyphicon glyphicon glyphicon-ban-circle cursor" aria-hidden="true"></span></div>';
                    $html='<div><div class="clearfix js-row-day">';
                }else{
                    if(i==element.concurrencia){
                        concurrencia=element.concurrencia;
                        $html=$html+iconrow+'</div><hr class="sep-row" /><div class="clearfix js-row-day">';
                        iconrow='<div><span onclick="block('+element.start+','+element.end+');" class="glyphicon glyphicon glyphicon-ban-circle cursor" aria-hidden="true"></span></div>';
                        i=0;
                    }
                }
                $desc='';
                toltips='';
                desctoltips='';
                var cssdesc='';
                if(element.estado=='D'){
                    $desc='Disponible';
                    cssdesc='evdisp';
                }else{
                    if(element.estado=='R'){
                        cssdesc='evreserv';
                        $desc='Reservado';
                        toltips='data-tooltips="tooltip"';
                        desctoltips=''+element.id+' '+element.correo;
                        swpuedeblock=true;
                        iconrow='';
                    }else{
                        if(element.estado=='B'){
                            cssdesc='evbloq';
                            $desc='Bloqueado';
                            swpuedeblock=true;
                            iconrow='<div><span onclick="unblock('+element.block_id+');" class="glyphicon glyphicon glyphicon-remove-circle cursor" aria-hidden="true"></span></div>';
                        }
                    }
                }
                var $div='<div class="clearfix row-events-cal"><div class="hora">'+hora+'</div><div><div class="descevent '+cssdesc+'" data-event="'+dataEvent+'" '+toltips+' title="'+desctoltips+'" data-event-fin="'+dataEventFin+'"  >'+$desc+'</div></div></div>';
                $html=$html+$div;
            }else{
                i=-1;
            }
            i++;
        });
        $html=$html+iconrow+'<hr class="sep-row" /></div></div>';
        if(swhaycita){
            $('#cont_cal').html($html);
        }else{
            $('#cont_cal').html('<div class="clearfix row-events-cal">No existe disponibilidad de citas</div>');
        }
        //data-tooltips="tooltip"
        $('[data-tooltips="tooltip"]').tooltip();
        var wcol=concurrencia*200;
        var wsep=concurrencia*153;
        $('.js-row-day').css({'width':wcol+'px'});
        $('.sep-row').css({'width':wsep+'px'});
        $('.evdisp').click(function(){
            var object='<?= $idobject ?>';
            var tmp=$(this).attr('data-event').split(' ');
            var tmpf=tmp[0].split('/');
            var fecha=tmpf[2]+'-'+tmpf[1]+'-'+tmpf[0];

            var tmp2=$(this).attr('data-event-fin').split(' ');
            var tmpf2=tmp2[0].split('/');
            var fecha2=tmpf2[2]+'-'+tmpf2[1]+'-'+tmpf2[0];


            $("#modalconfirmar").load(site_url + "agendas/ajax_confirmar_agregar_dia/<?= $idagenda ?>/"+fecha+"/"+tmp[1]+"/"+object);
            $("#modalconfirmar").modal();
        });
    }


</script>