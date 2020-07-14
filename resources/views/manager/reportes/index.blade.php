<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<table class="table">
    <thead>
    <tr>
        <th>N°</th>
        <th>Nombre Reporte</th>
        <th>Fecha Solicitud</th>
        <th>Email Solicitante</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Acción</th>
    </tr>
    </thead>
     <tbody>
    <?php foreach($reportes as $rep):?>
    <tr>
        <td><?=$rep->id?></td>
        <td><?=$rep->nombre_reporte?></td>
        <td><?=$rep->created_at?></td>
        <td><?=$rep->solicitante?></td>
        <td><?=$rep->rol?></td>
        <td><?=$rep->status?></td>
        <td>
            <a class="btn btn-danger" href="<?=url('manager/reportes/eliminar/' . $rep->id)?>"
               onclick="return confirm('¿Está seguro que eliminar este registro?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>