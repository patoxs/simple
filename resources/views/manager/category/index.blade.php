<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
    </ol>
</nav>

<p><a class="btn btn-primary" href="<?=url('manager/categorias/editar')?>">Crear Categoría</a></p>

<table class="table">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Descripción</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($categorias as $c):?>
    <tr>
        <td><?=$c->nombre?></td>
        <td><?=$c->descripcion?></td>
        <td>
            <a class="btn btn-primary" href="<?=url('manager/categorias/editar/' . $c->id)?>">
                <i class="material-icons">edit</i> Editar
            </a>
            <a class="btn btn-danger" href="<?=url('manager/categorias/eliminar/' . $c->id)?>"
               onclick="return confirm('¿Está seguro que desea eliminar esta categoria?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>