<div class="row">
    <div class="col-12 mb-3">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{strstr(Request::path(), 'backend/procesos') ? 'active' : ''}}"
                   href="{{route('backend.procesos.edit', [$proceso->id])}}">
                    Diseñador
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{strstr(Request::path(), 'backend/formularios') ? 'active' : ''}}"
                   href="{{route('backend.forms.list', [$proceso->id])}}">
                    Formularios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{strstr(Request::path(), 'backend/documentos') ? 'active' : ''}}"
                   href="{{route('backend.document.list', [$proceso->id])}}">
                    Documentos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{strstr(Request::path(), 'backend/acciones') ? 'active' : ''}}"
                   href="{{route('backend.action.list', [$proceso->id])}}">
                    Acciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{strstr(Request::path(), 'backend/Admseguridad') ? 'active' : ''}}"
                   href="{{route('backend.security.list', [$proceso->id])}}">
                    Seguridad
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{strstr(Request::path(), 'backend/suscriptores') ? 'active' : ''}}"
                   href="{{route('backend.subscribers.list', [$proceso->id])}}">
                    Suscriptores Externos
                </a>
            </li>
        </ul>
    </div>
</div>