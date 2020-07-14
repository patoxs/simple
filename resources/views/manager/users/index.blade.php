<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p><a class="btn btn-primary" href="<?=url('manager/usuarios/editar')?>">Crear Usuario</a></p>

<table class="table">
    <thead>
    <tr>
        <th>Correo Electrónico</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Cuenta</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($usuarios as $c):?>
    <tr>
        <td><?=$c->email?></td>
        <td><?=$c->nombre?></td>
        <td><?=$c->apellidos?></td>
        <td><?=$c->Cuenta->nombre?></td>
        <td><?=$c->rol?></td>
        <td>
            <a class="btn btn-primary" href="<?=url('manager/usuarios/editar/' . $c->id)?>">
                <i class="material-icons">edit</i> Editar
            </a>
            <a class="btn btn-danger" href="<?=url('manager/usuarios/eliminar/' . $c->id)?>"
               onclick="return confirm('¿Está seguro que desea eliminar este usuario?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>