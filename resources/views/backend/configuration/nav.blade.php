<div class="col-md-3">
    <div class="nav flex-column nav-pills">
        <a class="nav-link disabled" href="#">GENERAL</a>
        <a class="nav-link {{Request::path() == 'backend/configuracion' ? 'active' : ''}}"
           href="{{route('backend.configuration.my_site')}}">Mi Sitio</a>
        <!--
        <a class="nav-link {{strstr(Request::path(), 'backend/configuracion/plantilla') ? 'active' : ''}}"
           href="{{route('backend.configuration.template')}}">Plantillas de Simple</a>
        -->
        <a class="nav-link {{strstr(Request::path(), 'backend/configuracion/modelador') ? 'active' : ''}}"
           href="{{route('backend.configuration.modeler')}}">Configuración Modelador</a>
        <a class="nav-link {{strstr(Request::path(), 'backend/configuracion/firmas_electronicas') ? 'active' : ''}}"
           href="{{route('backend.configuration.electronic_signature')}}">Firmas Electrónicas</a>

         <a class="nav-link {{strstr(Request::path(), 'backend/configuracion/categorias') ? 'active' : ''}}"
       href="{{route('backend.configuration.list_categoria')}}">Categorías</a>   

        <!--
        <a class="nav-link" href="#">Configuración Modelador</a>
        -->
        <a class="nav-link {{strstr(Request::path(), 'backend/configuracion/estilo') ? 'active' : ''}}"
           href="{{route('backend.configuration.my_style')}}">Estilos (Personalización)</a>
    </div>
    <div class="nav flex-column nav-pills">
        <a class="nav-link disabled" href="#">ACCESOS FRONTEND</a>
        <a class="nav-link {{strstr(Request::path(), 'configuracion/usuarios')  || strstr(Request::path(), 'configuracion/usuario_editar')  ? 'active' : ''}}"
           href="{{route('backend.configuration.frontend_users')}}">
            Usuarios
        </a>
        <a class="nav-link {{strstr(Request::path(), 'configuracion/grupos_usuarios') ||  strstr(Request::path(), 'configuracion/grupo_usuarios_editar') ? 'active' : ''}}"
           href="{{route('backend.configuration.group_users')}}">
            Grupos de Usuarios
        </a>
    </div>
    <div class="nav flex-column nav-pills">
        <a class="nav-link disabled" href="#">ACCESOS BACKEND</a>
        <a class="nav-link {{strstr(Request::path(), 'backend_usuarios') ||  strstr(Request::path(), 'backend_usuario_editar') ? 'active' : ''}}"
           href="{{route('backend.configuration.backend_users')}}">
            Usuarios
        </a>
    </div>
</div>