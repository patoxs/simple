<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('manager/usuarios') ?>">Usuarios Backend</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
    </ol>
</nav>

<form class="ajaxForm" method="post" action="<?= url('manager/usuarios/editar_form/' . $usuario->id) ?>">
    {{csrf_field()}}
    <fieldset>
        <legend><?= $title ?></legend>
        <div class="validacion"></div>
        <label>Correo Electrónico</label>
        <input type="text" name="email" value="<?=$usuario->email?>" class="form-control col-3"/>
        <label>Contraseña</label>
        <input type="password" name="password" value="" class="form-control col-3"/>
        <label>Confirmar contraseña</label>
        <input type="password" name="password_confirmation" value="" class="form-control col-3"/>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= $usuario->nombre?>" class="form-control col-3"/>
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?= $usuario->apellidos?>" class="form-control col-3"/>
        <label>Cuenta</label>
        <select name="cuenta_id" class="form-control col-3">
            <?php foreach($cuentas as $c):?>
            <option value="<?=$c->id?>" <?=$c->id == $usuario->cuenta_id ? 'selected' : ''?>><?=$c->nombre?></option>
            <?php endforeach ?>
        </select>
        <label>Rol</label>
        <?php
        $roles = array("super", "modelamiento", "seguimiento", "operacion", "gestion", "desarrollo", "configuracion", "reportes");
        $longitud = count($roles);

        $valores = isset($usuario->rol) ? explode(",", $usuario->rol) : [];
        ?>
        <select name="rol[]" class="form-control col-6" multiple>
            <?php
            for($o = 0; $o < $longitud; $o++){
            ?>
            <option value="<?= $roles[$o] ?>" <?=  isset($usuario) && in_array($roles[$o], $valores) ? 'selected' : ''?> > <?= $roles[$o] ?> </option>
            <?php
            }
            ?>
        </select>
        <div class="text-muted form-text">
            <ul>
                <li>super: Tiene todos los privilegios del sistema.</li>
                <li>modelamiento: Permite modelar y diseñar el funcionamiento del trámite.</li>
                <li>seguimiento: Permite hacer seguimiento de los tramites.</li>
                <li>operacion: Permite hacer seguimiento y operaciones sobre los tramites como eliminacion y edición.
                </li>
                <li>gestion: Permite acceder a reportes de gestion con privilegio de visualiación.</li>
                <li>reportes: Permite acceder y configurar reportes de gestion y uso de la plataforma.</li>
                <li>desarrollo: Permite acceder a la API de desarrollo, para la ingtegracion con plataformas externas.
                </li>
                <li>configuracion: Permite configurar los usuarios y grupos de usuarios que tienen acceso al sistema.
                </li>
            </ul>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-light" href="<?= url('manager/usuarios') ?>">Cancelar</a>
        </div>
    </fieldset>
</form>
