<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p><a class="btn btn-primary" href="<?=url('manager/usermanager/editar')?>">Crear Usuario</a></p>

<table class="table">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Usuario</th>
        <th>Correo</th>
        <th>Fecha Creación</th>
        <th>Fecha Actualización</th>
        <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($users_manager as $um):?>
    <tr>
        <td><?=$um->nombre?></td>
        <td><?=$um->apellidos?></td>
        <td><?=$um->usuario?></td>
        <td><?=$um->email?></td>
        <td><?=$um->created_at?></td>
        <td><?=$um->updated_at?></td>
        <td>
            <a class="btn btn-primary" href="<?=url('manager/usermanager/editar/' . $um->id)?>">
                <i class="material-icons">edit</i> Editar
            </a>
            <a class="btn btn-danger" href="<?=url('manager/usermanager/eliminar/' . $um->id)?>"
               onclick="return confirm('¿Está seguro que desea eliminar este usuario?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>