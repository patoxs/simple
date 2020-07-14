@extends('layouts.backend')

@section('title', $title)

@section('css')
    @if(env('js_diagram') == 'gojs')
        <link href="{{asset('css/diagrama-procesos2.css')}}" rel="stylesheet">
    @else
        <link href="{{asset('css/diagrama-procesos.css')}}" rel="stylesheet">
    @endif

@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= route('backend.procesos.index') ?>">
                                Listado de Procesos
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('backend.process.nav')

        <div id="areaDibujo">
            <div class="row">
                <div class="col-12 mt-3">
                    <h1 style="float: left;">
                        <?= $proceso->nombre ?>
                        <a href="#" title="Editar"><i class="material-icons">edit</i></a>
                    </h1>
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_disenador');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html" target="_blank"
                       style="float:left;">
                        <i class="material-icons">help</i>
                    </a>
                </div>
            </div>
            <div class="botonera btn-toolbar">
                <div class="btn-group">
                    <button class="btn btn-light createBox" title="Crear tarea">
                        <img src="{{asset('img/tarea.png')}}"/>
                    </button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-light createConnection" data-tipo="secuencial"
                            title="Crear conexión secuencial">
                        <img src="{{asset('img/secuencial-bar.gif')}}"/>
                    </button>
                    <button class="btn btn-light createConnection" data-tipo="evaluacion"
                            title="Crear conexión por evaluación">
                        <img src="{{asset('img/evaluacion.gif')}}"/>
                    </button>
                    <button class="btn btn-light createConnection" data-tipo="paralelo" title="Crear conexión paralela">
                        <img src="{{asset('img/paralelo.gif')}}"/>
                    </button>
                    <button class="btn btn-light createConnection" data-tipo="paralelo_evaluacion"
                            title="Crear conexión paralela con evaluación">
                        <img src="{{asset('img/paralelo_evaluacion.gif')}}"/>
                    </button>
                    <button class="btn btn-light createConnection" data-tipo="union" title="Crear conexión de unión">
                        <img src="{{asset('img/union.gif')}}"/>
                    </button>
                </div>
            </div>
        </div>
        <div id="drawWrapper">
            <div id="draw"></div>
        </div>
    </div>

    <div class="modal hide" id="modal"></div>
    <div class="modal hide" id="modalSelectIcon"></div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <script src="{{asset('js/helpers/jquery.ui.livedraggable/jquery.ui.livedraggable.js')}}" type="text/javascript"></script>

    @if(env('js_diagram') == 'gojs')
        <script src="{{asset('js/go/go.js')}}" type="text/javascript"></script>
        <script type="text/javascript" src="{{asset('js/helpers/diagrama-procesos2.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/helpers/modelador-procesos2.js')}}"></script>
    @else
        <script src="{{asset('js/helpers/jquery.jsplumb/jquery.jsPlumb-1.3.16-all-min.js')}}" type="text/javascript"></script>
        <script type="text/javascript" src="{{asset('js/helpers/diagrama-procesos.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/helpers/modelador-procesos.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/helpers/editar.js')}}"></script>
    @endif

    <script type="text/javascript">
        $(document).ready(function () {
            procesoId = {{$proceso->id}};

                <?php
                $conector = env('CONECTOR');
                $config = \App\Helpers\Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(2, Cuenta::cuentaSegunDominio()->id);
                if ($config) {
                    $config = \App\Helpers\Doctrine::getTable('Config')->findOneByIdAndIdpar($config->config_id, $config->idpar);
                    $conector = $config->nombre;
                }
                ?>

            var conector = '<?= $conector; ?>';

            drawFromModel(<?= $proceso->getJSONFromModel()?>, "<?=$proceso->width?>", "<?=$proceso->height?>", conector);
            jsPlumb.repaintEverything();
        });
    </script>

    <script>
        $(function () {
            $.fn.modal.Constructor.prototype.enforceFocus = function () {
            };
            $(document).on('click', '#SelectIcon', function () {
                $("#modalSelectIcon").load("<?= url('backend/procesos/seleccionar_icono') ?>");
                $("#modalSelectIcon").modal();
            });
        });

        $(document).on('click', "input[name=asignacion]", function () {
            if ($(this).val() == "usuario")
                $("#optionalAsignacionUsuario").removeClass("hide");
            else
                $("#optionalAsignacionUsuario").addClass("hide");
        });
    </script>
@endsection