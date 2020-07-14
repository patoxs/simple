@extends('layouts.procedure')

@section('css')
    <link rel="stylesheet" href="<?= asset('js/helpers/calendar/css/calendar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/handsontable.full.min.css') ?>">
@endsection

@section('content')
    <div style="clear: both;"></div>
    @if ($etapa->Tarea->vencimiento)
        <div class="alert alert-warning">Atención. Esta etapa {{$etapa->getFechaVencimientoSinDiasAsString()}}.</div>
    @endif
    <form method="POST" class="ajaxForm dynaForm form-horizontal"
          action="/etapas/ejecutar_form/{{$etapa->id}}/{{$secuencia . ($qs ? '?' . $qs : '')}}">
        {{csrf_field()}}
        <input type="hidden" name="_method" value="post">
        <div class="validacion"></div>

        <h1 class="title">{{$paso->Formulario->nombre}}</h1>
        <hr>
        <?php
            $campos_dependientes = [];
            $campos_ocultos_extra = [];
        ?>

        <?php $existe_btn_siguiente = false; ?>
        @foreach($paso->Formulario->Campos as $c)
            <?php
                $campos_dependientes[] = $c->nombre;
                $condicion_final = "";
                if( !is_null($c->dependiente_campo) && ! array_key_exists($c->dependiente_campo, $campos_ocultos_extra)){
                    $campos_ocultos_extra[$c->dependiente_campo] = $c->getVariableUltimoValor($c->dependiente_campo, $etapa);
                }

              

               
            ?>
            @if($c->condiciones_extra_visible)
                @foreach($c->condiciones_extra_visible as $condicion)
                    <?php
                        $condicion_final .= $condicion->campo.";".$condicion->igualdad.";".$condicion->valor.";".$condicion->tipo."&&";
                        if( ! array_key_exists($condicion->campo, $campos_ocultos_extra) ){
                            $campos_ocultos_extra[$condicion->campo] = $c->getVariableUltimoValor($condicion->campo, $etapa);
                        }
                    ?>

                @endforeach
            @endif
           
            <?php $existe_btn_siguiente = $c->tipo==='btn_siguiente' ? true : false; ?>
            @if($c->tipo != 'btn_siguiente')
                <?php $condicion_final = ""; ?>
                @if($c->condiciones_extra_visible)
                    @foreach($c->condiciones_extra_visible as $condicion)
                        <?php
                            $condicion_final .= $condicion->campo.";".$condicion->igualdad.";".$condicion->valor.";".$condicion->tipo."&&";
                        ?>
                    @endforeach
                @endif
                
                <?php
                    if(!is_null($c->dependiente_campo) && !is_null($c->dependiente_valor)){
                        $condicion_final = $c->dependiente_campo.";".$c->dependiente_relacion.";".$c->dependiente_valor.";".$c->dependiente_tipo."&&".$condicion_final;
                    }
                    $condicion_final = substr($condicion_final,0,-2);
                ?>
                <div class="campo control-group" data-id="<?=$c->id?>"
                    <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo . '" data-dependiente-valor="' . $c->dependiente_valor . '" data-dependiente-tipo="' . $c->dependiente_tipo . '" data-dependiente-relacion="' . $c->dependiente_relacion . '"' : 'data-dependiente-campo="dependiente"' ?> style="display: <?= $c->isCurrentlyVisible($etapa->id) ? 'block' : 'none'?>;"
                    data-readonly="{{$paso->modo == 'visualizacion' || $c->readonly}}" <?=$c->condiciones_extra_visible ? 'data-condicion="' . $condicion_final . '"' : 'data-condicion="no-condition"'  ?> >
                    <?=$c->displayConDatoSeguimiento($etapa->id, $paso->modo)?>
                </div>
            @endif
        @endforeach
           
        <div class="form-actions mt-3 simple-btn-actions">
            
            @if($existe_btn_siguiente)
                <?php $condicion_final = ""; ?>
                @if($c->condiciones_extra_visible)
                    @foreach($c->condiciones_extra_visible as $condicion)
                        <?php
                            $condicion_final .= $condicion->campo.";".$condicion->igualdad.";".$condicion->valor.";".$condicion->tipo."&&";
                        ?>
                    @endforeach
                @endif
                <?php
                    if(!is_null($c->dependiente_campo) && !is_null($c->dependiente_valor)){
                        $condicion_final = $c->dependiente_campo.";".$c->dependiente_relacion.";".$c->dependiente_valor.";".$c->dependiente_tipo."&&".$condicion_final;
                    }
                    $condicion_final = substr($condicion_final,0,-2);
                ?>
               
                @if($secuencia > 0)
                    <span class="campo control-group">
                        <a class="btn btn-light"
                        href="{{url('etapas/ejecutar/' . $etapa->id . '/' . ($secuencia - 1) . ($qs ? '?' . $qs : ''))}}">
                            Volver
                        </a>
                    </span>
                @endif
                <span class="campo control-group <?= $c->isCurrentlyVisible($etapa->id) ? 'simple-sgt-block' : 'simple-sgt-none'?>" data-id="<?=$c->id?>"
                    <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo . '" data-dependiente-valor="' . $c->dependiente_valor . '" data-dependiente-tipo="' . $c->dependiente_tipo . '" data-dependiente-relacion="' . $c->dependiente_relacion . '"' : 'data-dependiente-campo="dependiente"' ?> style="display: <?= $c->isCurrentlyVisible($etapa->id) ? 'block' : 'none'?>;"
                    data-readonly="{{$paso->modo == 'visualizacion' || $c->readonly}}" <?=$c->condiciones_extra_visible ? 'data-condicion="' . $condicion_final . '"' : 'data-condicion="no-condition"'  ?> >
                    
                    <?=$c->displayConDatoSeguimiento($etapa->id, $paso->modo)?>
                </span>
            @else
                @if ($secuencia > 0)
                    <a class="btn btn-light"
                    href="{{url('etapas/ejecutar/' . $etapa->id . '/' . ($secuencia - 1) . ($qs ? '?' . $qs : ''))}}">
                        Volver
                    </a>
                @endif
                <button class="btn btn-danger" type="submit">Siguiente</button>
            @endif        
        </div>            
        <?php $campos_faltantes = array_diff(array_keys($campos_ocultos_extra), $campos_dependientes); ?>
        @foreach($campos_faltantes as $c_nombre)
            <input  class="camposvisibilidad" type="hidden" name="{{$c_nombre}}" value="{{$campos_ocultos_extra[$c_nombre]}}">
        @endforeach
        <input type="hidden" name="secuencia" value="{{$secuencia}}">
        <div class="ajaxLoader" style="position: fixed; left: 50%; top: 30%; display: none;">
            <img src="{{asset('img/loading.gif')}}">
        </div>
      <!--ID ANAlYTICS POR TAREA DEL TTE-->
 
       @if(!is_null($extra['analytics']))

            @push('script')
                <script>
                    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                    ga('create', '<?=$extra['analytics']['id_seguimiento'];?>', 'auto');
                      
                    const ES_FINAL = '<?=$extra['es_final'];?>';
                    
                    const GA_PARAMS = {
                        hitType: 'event',
                        eventCategory: '<?=$extra['analytics']['categoria'];?>',
                        eventAction: '<?=$extra['analytics']['nombre_marca'];?>',
                        eventLabel: '<?=$extra['analytics']['evento_enviante'];?>'
                    };

                    if (ES_FINAL=='no') {
                       ga('send', GA_PARAMS);
                    } 
                ////////////////////////  FIN 1ER HIT  envia EL INICIO DEL TTE RNT  ////////////////////////

                </script>

            @endpush
        @endif



              
      <!--ID ANAlYTICS POR TAREA DEL TTE-->
    </form>
    <div id="modalcalendar" class="modal hide modalconfg modcalejec"></div>
    <input type="hidden" id="urlbase" value="<?= URL::to('/') ?>"/>
@endsection


@push('script')
    <script src="{{asset('js/helpers/s3_upload.js')}}"></script>
    <script src="{{asset('js/helpers/fileuploader.js')}}"></script>

    <script src="{{asset('js/helpers/handsontable.full.min.js')}}"></script>

    <script type="text/javascript"
            src="<?= asset('js/helpers/calendar/components/underscore/underscore-min.js') ?>"></script>
    <script type="text/javascript"
            src="<?= asset('js/helpers/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
    <script type="text/javascript" src="<?= asset('js/helpers/calendar/js/language/es-CO.js') ?>"></script>
    <script type="text/javascript" src="<?= asset('js/helpers/calendar/js/calendar.js?v=0.3') ?>"></script>
    <script src="{{asset('js/helpers/collapse.js')}}"></script>
    <script src="{{asset('js/helpers/transition.js')}}"></script>
    <script>
        $(function () {
            $.each($('.js-data-cita'), function () {
                if (jQuery.trim($(this).val()) != "") {
                    var id = $(this).attr('id');
                    var arrdat = $(this).val().split('_');
                    $('#codcita' + id).val(arrdat[0]);
                    var feho = arrdat[1].split(' ');
                    var fe = feho[0].split('-');
                    var d = new Date(fe[0] + '/' + fe[1] + '/' + fe[2] + ' ' + feho[1]);
                    var h = '';
                    if (d.getHours() <= 9) {
                        h = '0' + d.getHours();
                    } else {
                        h = d.getHours();
                    }
                    var m = '';
                    if (d.getMinutes() <= 9) {
                        m = '0' + d.getMinutes();
                    } else {
                        m = d.getMinutes();
                    }
                    var fecha = d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + h + ':' + m;
                    var lab = moment(d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate()).format("LL");
                    $('#txtresult' + id).html(lab + ' a las ' + h + ':' + m + " horas");
                }
            });
        });
    </script>

    <script src="{{asset('js/helpers/common.js')}}"></script>
    <script src="{{ asset('js/helpers/grilla_datos_externos.js') }}"></script>
    <script src="{{ asset('js/helpers/jquery-ensure-max-length.min.js') }}"></script>
    <script src="{{ asset('js/helpers/popper.min.js') }}"></script>
@endpush
