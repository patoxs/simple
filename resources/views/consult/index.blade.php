@extends('layouts.consult')

@section('content')
    <div class="container" id="main">
        <div class="row">
            <div class="col-md-9 offset-md-3">
                <h1>{{$titulo}}</h1>
                <h2><i class="material-icons">home</i> <?= Cuenta::cuentaSegunDominio()->nombre_largo ?></h2>

                <p>
                    <i class="material-icons">help</i> En esta sección puedes dar
                    seguimiento a cualquier trámite que se ha ingresado
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form method="POST" action="">
                    {{csrf_field()}}
                    @if($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {!! $error !!}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endforeach
                    @endif
                    <div class="campo control-group">
                        <label class="control-label" style="color:#465f6e;" for="nrotramite">
                            <i class="icon icon-chevron-right"></i>Nro. de Trámite
                        </label>
                        <div class="form-group">
                            <input name="nrotramite" id="nrotramite" type="text" value="{{$nrotramite}}"
                                   class="form-control col-3"
                                   data-step="1"
                                   data-intro="Ingresá el Nro. de la Mesa de Entrada  <img src='{{asset('/js/helpdoc/ayu1.png')}}/>"
                                   data-position='center'>
                        </div>
                        
                        <div>
                            <button class="btn btn-primary" type="submit" id="buscar" name="buscar"
                                    data-step="3"
                                    data-intro="Presioná el botón, para dar seguimiento a su documento"
                                    data-position='right'>Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <?php $indice = 1; if (count($tareas) > 0 && $tareas > 0):  ?>
                <div class="responsive">
                    <table class="mt-3 table table-striped table-sm table-bordered table-hover">
                        <thead>
                        <th>Nro.</th>
                        <th>Pasos del Trámite</th>
                        <th>Finalizado en Fecha</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        </thead>
                        <tbody>
                        <?php foreach ($tareas as $d): ?>
                        <tr>
                            <td style="text-align:center;"><?= $indice++ ?></td>
                            <td><?= $d['tarea_nombre'] ?></td>
                            <td><?= $d['termino'] ?></td>
                            <td><?= $d['usuario'] ?></td>
                            <td style="text-align:center;"><?= $d['estado'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <?= $vacio ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        $(function () {
            $('#datetimepicker1').datetimepicker({
                viewMode: 'years',
                format: 'YYYY'
            });
        });

        $(document).ready(function () {
            tareas =<?= json_encode($tareas); ?>;
        });

        function doSearchEnter(e) {
            var key = e.keyCode || e.which;
            if (key === 13) {
                return true;
            } else {
                return false;
            }
        }

        function vacio(q) {
            for (i = 0; i < q.length; i++) {
                if (q.charAt(i) !== " ") {
                    return true;
                }
            }
            return false;
        }

        function validarDatos(event, dat_origen, dat_destino) {
            if (doSearchEnter(event) === true) {
                if (!vacio($(dat_origen).val())) {
                    $(dat_origen).focus();
                } else {
                    $(dat_destino).focus();
                }

            }
        }


        $(document).ready(function () {

            $('#nrotramite').on("keypress", function (e) {
                if (e.keyCode == 13) {
                    if (!vacio($('#nrotramite').val())) {
                        $('#nrotramite').focus();
                    } else {
                        var inputs = $(this).parents("form").eq(0).find(":input");
                        var idx = inputs.index(this);
                        if (idx == inputs.length - 1) {
                            inputs[0].select();
                        } else {
                            inputs[idx + 1].focus();
                            inputs[idx + 1].select();
                        }
                    }
                    return false;
                }
            });
        });
    </script>
@endsection
