<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager/anuncios')?>">Anuncios</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<form class="ajaxForm" method="post" action="<?= url('manager/anuncios/editar_form/' . $anuncio->id) ?>">
    {{csrf_field()}}
    <fieldset>
        <legend><?= $title ?></legend>
        <hr>
        <div class="validacion"></div>
        <label>Texto</label>
        <input class="form-control col-6" type="text" name="texto" value="<?= $anuncio->texto ?>"/><br>
        <label>Tipo</label>
        <select name="tipo" class="form-control">
            <option value="">Seleccionar ...</option>
            <option value="critica" <?= ($anuncio->tipo == 'critica') ? 'selected' : '' ?>>Crítica</option>
            <option value="informativa" <?= ($anuncio->tipo == 'informativa') ? 'informativa' : '' ?>>Informativa</option>
            <option value="warning" <?= ($anuncio->tipo == 'warning') ? 'selected' : '' ?>>Warning</option>
        </select>  
    </fieldset>
    <br>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn btn-light" href="<?= url('manager/anuncios') ?>">Cancelar</a>
    </div>
</form>
