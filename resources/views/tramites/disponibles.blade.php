<h2>Trámites disponibles a iniciar</h2>

<?php if (count($procesos) > 0): ?>

<article class="aux-box">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-8">
            <h2 class="heading-medium">Nombre</h2>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-4">
            <h2 class="heading-medium">Acciones</h2>
        </div>
    </div>
</article>

<?php foreach ($procesos as $p): ?>

<article class="aux-box">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-8">
            <?php if($p->canUsuarioIniciarlo(Auth::user()->id)):?>
            <a class="preventDoubleRequest" href="<?=url('tramites/iniciar/' . $p->id)?>"><?= $p->nombre ?></a>
            <?php else: ?>
            <?php if($p->getTareaInicial()->acceso_modo == 'claveunica'):?>
            <a href="<?=url('autenticacion/login_openid')?>?redirect=<?=url('tramites/iniciar/' . $p->id)?>"><?= $p->nombre ?></a>
            <?php else:?>
            <a href="<?=url('autenticacion/login')?>?redirect=<?=url('tramites/iniciar/' . $p->id)?>"><?= $p->nombre ?></a>
            <?php endif ?>
            <?php endif ?>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-4">
            <?php if($p->canUsuarioIniciarlo(Auth::user()->id)):?>
            <a href="<?=url('tramites/iniciar/' . $p->id)?>" class="btn btn-primary preventDoubleRequest"><i
                        class="icon-file icon-white"></i> Iniciar</a>
            <?php else: ?>
            <?php if($p->getTareaInicial()->acceso_modo == 'claveunica'):?>
            <a href="<?=url('autenticacion/login_openid')?>?redirect=<?=url('tramites/iniciar/' . $p->id)?>"><img
                        style="max-width: none;" src="<?=base_url('assets/img/claveunica-medium.png')?>"
                        alt="ClaveUnica"/></a>
            <?php else:?>
            <a href="<?=url('autenticacion/login')?>?redirect=<?=url('tramites/iniciar/' . $p->id)?>"
               class="btn btn-primary"><i class="icon-white icon-off"></i> Autenticarse</a>
            <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</article>

<?php endforeach; ?>
</tbody>
</table>

<?php else: ?>
<p>No hay trámites disponibles para ser iniciados.</p>
<?php endif; ?>
