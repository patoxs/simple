<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('manager/usermanager') ?>">Usuarios Manager</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
    </ol>
</nav>

<form class="ajaxForm" name="f1" method="post" action="<?= url('manager/usermanager/editar_form/' . $usuario->id) ?>" >
    {{csrf_field()}}
    <fieldset>
        <legend><?= $title ?></legend>
        <div class="validacion"></div>
        <label>Nombre</label>
        <p><input type="text" name="nombre" value="<?= $usuario->nombre?>" class="form-control col-3" required/></p>
        <label>Apellidos</label>
        <p><input type="text" name="apellidos" value="<?= $usuario->apellidos?>" class="form-control col-3" required/></p>
        <label>Ingrese Nombre Usuario</label>
        <p><input type="text" name="usuario" value="<?=$usuario->usuario?>" class="form-control col-3" required/></p>
        <label>Correo Electrónico</label>
        <p><input type="email" name="email" value="<?=$usuario->email?>" class="form-control col-3" required/></p>
        <label>Contraseña</label>
        <p><input type="password" name="password" value="" class="form-control col-3" required   minlength="7" maxlength="11"/></p>
        <label>Confirmar contraseña</label>
        <p><input type="password" name="password_confirmation" value="" class="form-control col-3" required  minlength="7" maxlength="11"/></p>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit" onClick="validaUser()">Guardar</button>
            <a class="btn btn-light" href="<?= url('manager/usermanager') ?>">Cancelar</a>
        </div>
    </fieldset>
</form>
<script src="{{ asset('js/app.js') }}"></script>
@yield('script')
<script>
function validaUser(){
    password = document.f1.password.value
    password_confirmation = document.f1.password_confirmation.value

    if (password == password_confirmation)
      console.log("Usuario Creado con éxito!");
    else
       alert("Las credenciales ingresadas no coinciden, intente nuevamente")
}
</script>
@yield('script')