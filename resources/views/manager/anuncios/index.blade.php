<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p><a class="btn btn-primary" href="<?=url('manager/anuncios/editar')?>">Crear Anuncio</a></p>

<table class="table">
    <thead>
    <tr>
        <th>Texto</th>
        <th class="text-center">Tipo</th>
        <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($anuncios as $a):?>
    <tr>
        <td><?=$a->texto?></td>
        <td><?=$a->tipo?></td>
        <!-- <td class="text-center"><span class="badge badge-secondary"><?=strtoupper($a->ambiente)?></span></td> -->
        <td>
            <a class="btn btn-primary" href="<?=url('manager/anuncios/editar/' . $a->id)?>">
                <i class="material-icons">edit</i> Editar
            </a>
            @if($a->activo === 0)
                <a class="btn btn-success" href="<?=url('manager/anuncios/cambiar_estado/' . $a->id . '/1')?>"
                onclick="return confirm('¿Está seguro que desea activar este anuncio?')">
                    <i class="material-icons">done</i> Activar
                </a>
            @endif
            @if($a->activo === 1)
                <a class="btn" href="<?=url('manager/anuncios/cambiar_estado/' . $a->id)?>"
                onclick="return confirm('¿Está seguro que desea desactivar este anuncio?')">
                    <i class="material-icons">done</i> Desactivar
                </a>
            @endif
            <a class="btn btn-danger" href="<?=url('manager/anuncios/eliminar/' . $a->id)?>"
               onclick="return confirm('¿Está seguro que desea eliminar este anuncio?')">
                <i class="material-icons">delete</i> Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>