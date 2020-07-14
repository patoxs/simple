@extends('layouts.backend')

@section('title', $title)

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
                            <a href="{{url('backend/seguimiento/index_proceso/'.$tramite->Proceso->id)}}"><?=$tramite->Proceso->nombre?></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Trámite # <?= $tramite->id ?></li>
                    </ol>
                </nav>

            </div>
        </div>


        <div class="row mt-3">
            <div class="col-12">
                <div class="jumbotron"
                     style="position:fixed; top: 230px; right: 20px; width: 300px; height: 500px; z-index: 1000; overflow-y: scroll">
                    <h3>Registro de eventos</h3>
                    <hr/>
                    <ul>
                        <?php foreach ($etapas as $etapa): ?>
                        <li>
                            <h4><?= $etapa->Tarea->nombre ?></h4>
                            <p>
                                Estado: <?= $etapa->pendiente == 0 ? 'Completado' : ($etapa->vencida() ? 'Vencida' : 'Pendiente') ?></p>
                            <p><?= $etapa->created_at ? 'Inicio: ' . $etapa->created_at : '' ?></p>
                            <p><?= $etapa->ended_at ? 'Término: ' . $etapa->ended_at : '' ?></p>
                            <p>Asignado
                                a: <?= !$etapa->usuario_id ? 'Ninguno' : !$etapa->Usuario->registrado ? 'No registrado' : '<abbr class="tt" title="' . $etapa->Usuario->displayInfo() . '">' . $etapa->Usuario->displayUsername() . '</abbr>' ?></p>
                            <p>
                                <a href="<?= url('backend/seguimiento/ver_etapa/' . $etapa->id) ?>">
                                    Revisar detalle</a>
                            </p>
                            <?php if (!in_array('seguimiento', explode(',', Auth::user()->rol)) &&
                            ((count($etapa->Tramite->Etapas) > 1 && $etapa->pendiente) || $etapa->isFinal())):?>
                            <p><a href="#" onclick="return auditarRetrocesoEtapa(<?php echo $etapa->id; ?>)">Retroceder
                                    etapa</a></p>
                            <?php endif?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="areaDibujo">
                    <h1><?= $tramite->Proceso->nombre ?></h1>
                </div>
                <div id="drawWrapper">
                    <div id="draw"></div>
                </div>
            </div>

            <div id="auditar" class="modal hide">

            </div>

        </div>
    </div>
    </div>
@endsection
@section('script')
    @if(env('JS_DIAGRAM')=='gojs')
        <link href="{{asset('/css/diagrama-procesos2.css')}}" rel="stylesheet">
        <script src="{{asset('/js/helpers/go/go.js')}}" type="text/javascript"></script>
        <script type="text/javascript" src="{{asset('/js/helpers/diagrama-procesos2.js')}}"></script>
        <script type="text/javascript" src="{{asset('/js/helpers/seguimiento2.js')}}"></script>
    @else
        <link href="{{asset('/css/diagrama-procesos.css')}}" rel="stylesheet">
        <script src="{{asset('/js/helpers/jquery.jsplumb/jquery.jsPlumb-1.3.16-all-min.js')}}"
                type="text/javascript"></script>
        <script type="text/javascript" src="{{asset('/js/helpers/diagrama-procesos.js')}}"></script>
        <script type="text/javascript" src="{{asset('/js/helpers/seguimiento.js')}}"></script>
    @endif

    <script type="text/javascript">
        $(document).ready(function () {

                <?php
                $conector = 'Bezier';
                $config = \App\Helpers\Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(2, Cuenta::cuentaSegunDominio()->id);
                if ($config) {
                    $config = \App\Helpers\Doctrine::getTable('Config')->findOneByIdAndIdpar($config->config_id, $config->idpar);
                    $conector = $config->nombre;
                }
                ?>
            var conector = '<?= $conector; ?>';

            tramiteId =<?= $tramite->id ?>;
            drawFromModel(<?= $tramite->Proceso->getJSONFromModel() ?>, "<?=$tramite->Proceso->width?>", "<?=$tramite->Proceso->height?>", conector);
            drawSeguimiento(<?= json_encode($tramite->getTareasActuales()->toArray()) ?>,<?= json_encode($tramite->getTareasCompletadas()->toArray()) ?>, <?= json_encode($tramite->getTareasVencidas()->toArray()) ?>, <?= json_encode($tramite->getTareasVencenHoy()->toArray()) ?>);
        });

        function auditarRetrocesoEtapa(etapaId) {
            $(".popover").each(function () {
                $(this).popover("hide");
            });

            $("#auditar").load("/backend/seguimiento/ajax_auditar_retroceder_etapa/" + etapaId);
            $("#auditar").modal();
            return false;
        }
    </script>
@endsection