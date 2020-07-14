@extends('layouts.backend')

@section('title', $title)

@section('css')
    <link rel="stylesheet" href="<?= asset('css/handsontable.full.min.css') ?>">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">


                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.tracing.index')}}">Seguimiento de Procesos</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{url('backend/seguimiento/index_proceso/'.$etapa->Tramite->proceso_id)}}">
                                <?=$etapa->Tramite->Proceso->nombre?>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{url('backend/seguimiento/ver/'.$etapa->tramite_id)}}">
                                Trámite # <?= $etapa->tramite_id ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{url('backend/seguimiento/ver_etapa/'.$etapa->id)}}">
                                <?=$etapa->Tarea->nombre?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Paso <?=$secuencia + 1?></li>

                    </ol>
                </nav>

            </div>
        </div>


        <div class="row mt-3">
            <div class="col-3">
                <div class="jumbotron">
                    <p>
                        Estado: <?= $etapa->pendiente == 0 ? 'Completado' : ($etapa->vencida() ? 'Vencida' : 'Pendiente') ?></p>
                    <p><?= $etapa->created_at ? 'Inicio: ' . $etapa->created_at : '' ?></p>
                    <p><?= $etapa->ended_at ? 'Término: ' . $etapa->ended_at : '' ?></p>
                    <p>Asignado
                        a: <?=!$etapa->usuario_id ? 'Ninguno' : !$etapa->Usuario->registrado ? 'No registrado' : '<abbr class="tt" title="' . $etapa->Usuario->displayInfo() . '">' . $etapa->Usuario->displayUsername() . '</abbr>'?> <?php if($etapa->pendiente):?>
                        (<a id="reasignarLink" href="<?=url('seguimiento/reasignar')?>">Reasignar</a>)<?php endif?>
                    </p>
                    <form id="reasignarForm" method="POST"
                          action="<?=url('backend/seguimiento/reasignar_form/' . $etapa->id)?>"
                          class="ajaxForm hide">
                        <div class="validacion"></div>
                        <label>¿A quien deseas asignarle esta etapa?</label>
                        <select name="usuario_id">
                            <?php foreach($etapa->getUsuariosFromGruposDeUsuarioDeCuenta() as $u):?>
                            <option value="<?=$u->id?>" <?=$u->id == $etapa->usuario_id ? 'selected' : ''?>><?=$u->open_id ? $u->nombres . ' ' . $u->apellido_paterno : $u->usuario?></option>
                            <?php endforeach?>
                        </select>
                        <button class="btn btn-primary" type="submit">Reasignar</button>
                    </form>
                    <?php if (!in_array('seguimiento', explode(',', Auth::user()->rol)) &&
                    ((count($etapa->Tramite->Etapas) > 1 && $etapa->pendiente) || $etapa->isFinal())):?>
                    <p><a href="#" onclick="return auditarRetrocesoEtapa(<?php echo $etapa->id; ?>)">Retroceder
                            etapa</a></p>
                    <?php endif?>
                </div>
            </div>
            <div class="col-9">
                <form class="form-horizontal dynaForm" onsubmit="return false;">
                    <fieldset>
                        <div class="validacion"></div>
                        @if ($paso)
                            <legend><?= $paso->Formulario->nombre ?></legend>
                            @foreach ($paso->Formulario->Campos as $c)
                                @if($c->tipo != 'btn_siguiente')
                                    <div class="control-group campo" data-id="<?= $c->id ?>"
                                         <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo . '" data-dependiente-valor="' . $c->dependiente_valor . '" data-dependiente-tipo="' . $c->dependiente_tipo . '" data-dependiente-relacion="' . $c->dependiente_relacion . '"' : '' ?> style="display: <?= $c->isCurrentlyVisible($etapa->id) ? 'block' : 'none'?>;"
                                         data-readonly="<?=$paso->modo == 'visualizacion' || $c->readonly?>">
                                        <?=$c->displayConDatoSeguimiento($etapa->id, $paso->modo)?>
                                    </div>
                                @endif
                            @endforeach
                            <div class="form-actions mb-4">
                                @if ($secuencia > 0)
                                    <a class="btn btn-light"
                                       href="<?= url('backend/seguimiento/ver_etapa/' . $etapa->id . '/' . ($secuencia - 1)) ?>">
                                        <i class="material-icons">chevron_left</i> Volver
                                    </a>
                                @endif
                                @if ($secuencia + 1 < count($etapa->getPasosEjecutables()))
                                    <a class="btn btn-primary"
                                       href="<?= url('backend/seguimiento/ver_etapa/' . $etapa->id . '/' . ($secuencia + 1)) ?>">
                                        Siguiente
                                    </a>
                                @endif
                            </div>
                        @else
                            <legend>No tiene formulario</legend>
                        @endif
                    </fieldset>
                </form>
            </div>
        </div>

        <div id="auditar" class="modal hide">

        </div>
    </div>
@endsection
@section('script')

    <script src="{{asset('js/helpers/handsontable.full.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $("#reasignarLink").click(function () {
                $("#reasignarForm").show();
                return false;
            });
        });

        function auditarRetrocesoEtapa(etapaId) {
            $("#auditar").load("/backend/seguimiento/ajax_auditar_retroceder_etapa/" + etapaId);
            $("#auditar").modal();
            return false;
        }
        $(".dynaForm :input").prop("disabled", true);
        $(".dataTables_wrapper .btn_grid_action").prop("disabled", true);
    </script>
@endsection