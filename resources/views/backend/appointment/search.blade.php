@extends('layouts.backend')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">


                <input type="hidden" id="base_url" value="<?= url()->current() ?>"/>
                <ul class="breadcrumb">
                    <li>
                        Agendas
                    </li>
                </ul>
                <div class="validacion"></div>
                <form id="frmsearch" method="POST" action="<?= url('backend/agendas/buscar') ?>">
                    {{csrf_field()}}
                    <a class="btn btn-success" href="#" onclick="nuevaagenda();"><i class="material-icons">add</i>
                        Nuevo</a>
                    <br><br>
                    <b>Pertenece a:</b>

                    <div class="form-inline zona-search clearfix">
                        <div class="clearfix">
                            <input type="text" name="pertenece" value="<?= $buscar ?>"
                                   class="form-control js-pertenece"/>
                        </div>
                        <div class="clearfix">
                            <a class="btn btn-light js-btn-buscar" onclick="buscarAgenda();"
                                                 href="#"
                                                 data-toggle="modal">
                                <i class="material-icons">search</i> Buscar
                            </a>
                        </div>
                    </div>
                </form>
                <table class="table mt-3">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Pertenece</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(isset($paginador)){
                    $finregistro = count($agendas);
                    $inicio = 0;
                    if($finregistro > 0){
                    for($i = $inicio;$i < $finregistro;$i++){
                    if(isset($agendas[$i])){
                    $item = $agendas[$i];
                    ?>
                    <tr class="js-del-<?=$item['id']?>">
                        <td><?=$item['nombre']?></td>
                        <td><?=$item['pertenece']?></td>
                        <td>
                            <a class="btn btn-primary" href="#" onclick="editar(<?=$item['id']?>);"><i
                                        class="icon-white icon-edit"></i> Editar</a>
                            <a class="btn btn-danger" href="#"
                               onclick="eliminarAgenda(<?=$item['id']?>,'<?=$item['nombre']?>','<?=$item['pertenece']?>');"><i
                                        class="icon-white icon-remove"></i> Eliminar</a>
                        </td>
                    </tr>
                    <?php
                    }
                    }
                    }else{
                    ?>
                    <tr>
                        <td colspan="3">No se encontraron registro</td>
                    </tr>
                    <?php
                    }
                    }
                    ?>
                    </tbody>
                </table>
                <form id="frmfieldsedit" action="<?= url('backend/agendas/ajax_back_eliminar_agenda') ?>">
                    <input type="hidden" id="txtid" name="id"/>
                    <input type="hidden" id="txtnombre" name="nombre"/>
                    <input type="hidden" id="txtpertenece" name="pertenece"/>
                </form>
                <div class="container-menu clearfix">
                    <?php
                    if(isset($paginador) && $paginador['pagina'] <= $paginador['total_paginas']){
                    $total_paginas = $paginador['total_paginas'];
                    $pagina = $paginador['pagina'];
                    $total_registros = $paginador['total_registros'];
                    $registros = $paginador['registros'];
                    $inicio = $paginador['inicio'];
                    $finregistro = $inicio + $registros;
                    $pagina_desde = $paginador['pagina_desde'];
                    $pagina_hasta = $paginador['pagina_hasta'];
                    ?>
                    <ul class="pagination">
                        <li><a data-url="<?= url('backend/agendas/buscar/1') ?>" onclick="paginar(this);" href="#">&laquo;</a>
                        </li>
                        <?php
                        for ($i = $pagina_desde; $i <= $pagina_hasta; $i++) {
                            if ($i > 0 && $i <= $total_paginas) {
                                echo '<li><a data-url="' . url('backend/agendas/buscar/' . $i) . '" onclick="paginar(this);" href="#">' . $i . '</a></li>';
                            }
                        }
                        ?>
                        <li><a data-url="<?= url('backend/agendas/buscar/' . $total_paginas) ?>"
                               onclick="paginar(this);"
                               href="#">&raquo;</a></li>
                    </ul>
                    <?php
                    }
                    ?>
                </div>

                <div id="modalImportar" class="modal hide fade">
                    <form method="POST" enctype="multipart/form-data" action="<?=url('backend/procesos/importar')?>">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h3>Importar Proceso</h3>
                        </div>
                        <div class="modal-body">
                            <p>Cargue a continuación el archivo .simple donde exportó su proceso.</p>
                            <input type="file" name="archivo"/>
                        </div>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Importar</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <div id="modal" class="modal hide"></div>
@endsection
@section('script')
    <script src="{{asset('js/helpers/common.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('.container-menu').css({'position': 'absolute', 'left': '50%', 'margin-left': '-113px'});
        });

        function nuevaagenda() {
            $("#modalnuevaagenda").load("/backend/agendas/ajax_back_nueva_agenda/");
            $("#modalnuevaagenda").modal();
        }

        function editar(id) {
            $("#modalnuevaagenda").load("/backend/agendas/ajax_back_editar_agenda/" + id);
            $("#modalnuevaagenda").modal();
        }

        function eliminarAgenda(id, nombre, pertenece) {
            $('#txtid').val(id);
            $('#txtnombre').val(nombre);
            $('#txtpertenece').val(pertenece);
            var param = $('#frmfieldsedit').serialize();
            $("#modalnuevaagenda").load("/backend/agendas/ajax_back_eliminar_agenda?" + param);
            $("#modalnuevaagenda").modal();
        }

        function paginar(obj) {
            $('#frmsearch').attr('action', $(obj).attr('data-url'));
            $('#frmsearch').submit();
        }
    </script>
    <div id="modalnuevaagenda" class="modal hide"></div>
@endsection