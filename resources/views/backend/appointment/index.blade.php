@extends('layouts.backend')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">Agendas</li>
                    </ol>
                </nav>


                <input type="hidden" id="base_url" value="<?= env('APP_URL') ?>"/>

                <div class="validacion"></div>
                <form id="frmsearch" method="POST" action="<?= url('backend/agendas/buscar') ?>">
                    {{csrf_field()}}
                    <a class="btn btn-success" href="#" onclick="nuevaagenda();">
                        <i class="material-icons">add</i> Nueva
                    </a>
                    <a href="/ayuda/simple/backend/agenda-agregar.html" target="_blank">
                        <i class="material-icons align-middle">help</i>
                    </a>
                    <!-- <label class='lpertenece'>Pertenece a:</label> -->
                    <div class="form-inline mt-3 zona-search clearfix">
                        <div class="clearfix"><input type="text" name="pertenece" class="js-pertenece form-control"/>
                        </div>
                        <div class="clearfix">
                            <a class="btn btn-light js-btn-buscar" onclick="buscarAgenda();" href="#"
                               data-toggle="modal">
                                <i class="material-icons">search</i> Buscar
                            </a>
                        </div>
                    </div>
                </form>

                <table class="table mt-3">
                    <thead>
                    <tr>
                        <th>Nombre
                            <a href="/ayuda/simple/backend/agenda-listar.html" target="_blank">
                                <i class="material-icons">help</i>
                            </a>
                        </th>
                        <th>Pertenece a:</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    if(isset($paginador)){
                    $inicio = $paginador['inicio'];
                    $registros = $paginador['registros'];
                    $finregistro = $inicio + $registros;
                    if(count($agendas) > 0){
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
                        <td colspan="3">No se encontraron registros</td>
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
                        <li><a href="<?=url('backend/agendas/pagina/1')?>">&laquo;</a></li>
                        <?php
                        for ($i = $pagina_desde; $i <= $pagina_hasta; $i++) {
                            if ($i > 0 && $i <= $total_paginas) {
                                echo '<li><a href="' . url('backend/agendas/pagina/' . $i) . '">' . $i . '</a></li>';
                            }
                        }
                        ?>
                        <li><a href="<?=url('backend/agendas/pagina/' . $total_paginas)?>">&raquo;</a></li>
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

                <div id="modal" class="modal hide"></div>
                <div id="modalnuevaagenda" class="modal hide"></div>

            </div>
        </div>
    </div>
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
    </script>
@endsection