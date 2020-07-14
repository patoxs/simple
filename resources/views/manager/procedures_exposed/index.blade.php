<script src="{{asset('/js/jquery.select2/dist/js/i18n/es.js')}}"></script> <?php //Soporte para selects con multiple choices     ?>
<script src="{{asset('js/collapse.js')}}"></script>
<script src="{{asset('js/transition.js')}}"></script>
<ul class="breadcrumb">
    <li class="active"><?=$title?></li>
</ul>

<div>
    <form method="POST" action="<?=url('manager/tramites_expuestos/buscar_cuenta')?>">
        {{csrf_field()}}
        <label>Cuenta</label>
        <div class="form-inline">
            <select id="cuenta_id" name="cuenta_id" class="AlignText form-control col-2">
                <option value="">Todas</option>
                @foreach($cuentas as $c)
                    <option value="<?=$c->id?>" <?=isset($cuenta_sel) && $c->id == $cuenta_sel ? 'selected' : ''?>><?=$c->nombre?></option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary ml-2"><i class="material-icons">search</i> Consultar</button>
        </div>
        <div>
            <table class="table mt-3">
                <tr>
                    <th>Cuenta</th>
                    <th>Nombre del Proceso</th>
                    <th>Tarea</th>
                    <th>Descripción</th>
                    <th>Url</th>
                </tr>
                @php
                    //$nombre_host = gethostname();
                    $nombre_host = $_SERVER['HTTP_HOST'];
                    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                @endphp
                @foreach ($json as $res)
                    <tr>
                        <td><?=$res['nombre_cuenta']?></td>
                        <td><?=$res['nombre']?></td>
                        <td><?=$res['tarea']?></td>
                        <td><?=$res['previsualizacion']?></td>
                        <td>
                            <a class="btn btn-light" target="_blank"
                               href="<?=$protocol . $nombre_host . '/integracion/especificacion/servicio/proceso/' . $res['id'] . '/tarea/' . $res['id_tarea']; ?> ">
                                <i class="material-icons">file_download</i>Swagger
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </form>
</div>