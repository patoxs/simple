@extends('layouts.procedure')

@section('content')
    <form class="form-horizontal dynaForm" onsubmit="return false;">
        <fieldset>
            <div class="validacion"></div>
            <legend><?= $paso->Formulario->nombre ?></legend>
            @foreach ($paso->Formulario->Campos as $c)
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
            <div class="form-actions mb-4">
                @if ($secuencia > 0)
                    <a class="btn btn-light" href="<?= url('etapas/ver/' . $etapa->id . '/' . ($secuencia - 1)) ?>">
                        <i class="material-icons align-middle">chevron_left</i> Volver
                    </a>
                @endif
                @if ($secuencia + 1 < count($etapa->getPasosEjecutables()))
                    <a class="btn btn-primary" href="<?= url('etapas/ver/' . $etapa->id . '/' . ($secuencia + 1)) ?>">
                        Siguiente
                    </a>
                @endif
            </div>
        </fieldset>
    </form>
@endsection
@push('script')
    <script src="<?= asset('/calendar/js/moment-2.2.1.js') ?>"></script>
    <script>
        $(function () {
            moment.lang('es');
            $.each($('.js-data-cita'), function () {
                if ($(this).is('[readonly]')) {
                    var id = $(this).attr('id');
                    var arrdat = $(this).val().split('_');
                    var d = new Date(arrdat[1]);
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
@endpush