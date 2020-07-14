<div class="col-md-3">
    <div class="nav flex-column nav-pills">
        <a class="nav-link {{Request::path() == 'backend/api' ? 'active' : ''}}"
           href="{{route('backend.api')}}">Introduccion</a>
    </div>
    <div class="nav flex-column nav-pills">
        <a class="nav-link disabled" href="#">AUTORIZACIÓN</a>
        <a class="nav-link {{strstr(Request::path(), 'api/token')  || strstr(Request::path(), 'configuracion/usuario_editar')  ? 'active' : ''}}"
           href="{{route('backend.api.token')}}">
            Código de Acceso
        </a>
    </div>
    <div class="nav flex-column nav-pills">
        <a class="nav-link disabled" href="#">TRAMITES</a>
        <a class="nav-link {{strstr(Request::path(), 'tramites_obtener') ? 'active' : ''}}"
           href="{{route('backend.api.tramites_obtener')}}">
            Obtener
        </a>
        <a class="nav-link {{(Request::path() =='backend/api/tramites_listar') ? 'active' : ''}}"
           href="{{route('backend.api.tramites_listar')}}">
            Listar
        </a>
        <a class="nav-link {{strstr(Request::path(), 'tramites_listarporproceso') ? 'active' : ''}}"
           href="{{route('backend.api.tramites_listarporproceso')}}">
            Listar Por Proceso
        </a>
    </div>
    <div class="nav flex-column nav-pills">
        <a class="nav-link disabled" href="#">Procesos</a>
        <a class="nav-link {{strstr(Request::path(), 'procesos_obtener') ? 'active' : ''}}"
           href="{{route('backend.api.procesos_obtener')}}">
            Obtener
        </a>
        <a class="nav-link {{strstr(Request::path(), 'procesos_listar') ? 'active' : ''}}"
           href="{{route('backend.api.procesos_listar')}}">
            Listar
        </a>
        <a class="nav-link {{strstr(Request::path(), 'procesos_disponibles') ? 'active' : ''}}"
           href="{{route('backend.api.procesos_disponibles')}}">
            Trámites disponibles
        </a>
    </div>
</div>