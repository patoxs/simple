<div class="nav flex-column nav-pills">
    <a class="nav-link {{Request::path() == 'manager' ? 'active' : ''}}"
       href="{{route('manager.home')}}">Portada</a>
    <a class="nav-link disabled" href="#">ADMINISTRACIÓN</a>
    <a class="nav-link {{strstr(Request::path(), 'manager/cuentas')  ? 'active' : ''}}"
       href="{{route('manager.account.index')}}">Cuentas</a>
        <a class="nav-link {{strstr(Request::path(), 'manager/usermanager') ? 'active' : ''}}"
       href="{{route('manager.usermanager.index')}}">Usuarios Manager</a>   
    <a class="nav-link {{strstr(Request::path(), 'manager/usuarios') ? 'active' : ''}}"
       href="{{route('manager.users.index')}}">Usuarios Backend</a>
     <a class="nav-link {{strstr(Request::path(), 'manager/reportes') ? 'active' : ''}}"
       href="{{route('manager.reportes.index')}}">Estado Reportes</a>
    <a class="nav-link {{strstr(Request::path(), 'manager/diaferiado') ? 'active' : ''}}"
       href="{{route('manager.holiday.index')}}">Días Feriados</a>
    <a class="nav-link {{strstr(Request::path(), 'manager/categorias') ? 'active' : ''}}"
       href="{{route('manager.category.index')}}">Categorías</a>
</div>
<div class="nav flex-column nav-pills">
    <a class="nav-link disabled" href="#">CONSULTAS</a>
    <a class="nav-link {{strstr(Request::path(), 'manager/tramites_expuestos')  ? 'active' : ''}}"
       href="{{route('manager.procedures_exposed.index')}}">
        Trámites expuestos como servicios
    </a>
</div>
<div class="nav flex-column nav-pills">
    <a class="nav-link disabled" href="#">ESTADISTICAS</a>
    <a class="nav-link {{strstr(Request::path(), 'manager/estadisticas/cuentas') ? 'active' : ''}}"
       href="{{route('manager.statistics.index')}}">
        Trámites en curso
    </a>
</div>
<div class="nav flex-column nav-pills">
    <a class="nav-link disabled" href="#">ALERTAS</a>
    <a class="nav-link {{strstr(Request::path(), 'manager/anuncios') ? 'active' : ''}}"
       href="{{route('manager.anuncios.index')}}">
        Anuncios
    </a>
</div>