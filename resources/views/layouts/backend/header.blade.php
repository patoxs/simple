<nav class="navbar navbar-expand-lg navbar-light custom-nav">
    <div class="container-fluid container-backend">
        <div class="barra-gob">
            <div class="blue"></div>
            <div class="red"></div>
        </div>
        <a class="navbar-brand" href="{{ route('backend.home') }}">
            <img src="{{asset('/img/logo.png')}}" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="row">
            <div class="col-12">
                <ul class="navbar-nav float-right">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink"
                           data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            Bienvenido, <strong>{{ Auth::user()->email }}</strong>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a href="{{route('backend.cuentas')}}" class="dropdown-item">
                                {{__('auth.my_account')}}
                            </a>
                            <a href="{{ route('backend.logout') }}" class="dropdown-item">
                                {{__('auth.close_session')}}
                            </a>

                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-12">
                <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        @if (Auth::guest())
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="nav-link">
                                    {{__('auth.login')}}
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('backend.home') }}"
                                   class="nav-link {{Request::path() == 'backend' ? 'active' : ''}}">
                                    <i class="material-icons">dashboard</i> {{__('nav.home')}}
                                </a>
                            </li>
                            @can('agenda')
                                <!-- <li class="nav-item">
                                    <a href="{{route('backend.appointment.index')}}"
                                       class="nav-link {{strstr(Request::path(), 'backend/agenda') ? 'active' : ''}}">
                                        <i class="material-icons">date_range</i> {{__('nav.diary')}}
                                    </a>
                                </li> -->
                            @endcan

                            @can('proceso')
                                <li class="nav-item">
                                    <a href="{{route('backend.procesos.index')}}"
                                       class="nav-link {{strstr(Request::path(), 'backend/procesos') ||
                                       strstr(Request::path(), 'backend/formularios') ||
                                       strstr(Request::path(), 'backend/acciones') ||
                                       strstr(Request::path(), 'backend/Admseguridad') ||
                                       strstr(Request::path(), 'backend/suscriptores') ||
                                       strstr(Request::path(), 'backend/documentos') ?
                                       'active' : ''}}">
                                        <i class="material-icons">create_new_folder</i> {{__('nav.bpm')}}
                                    </a>
                                </li>
                            @endcan
                            @can('seguimiento')
                            <li class="nav-item">
                                <a href="{{route('backend.tracing.index')}}"
                                   class="nav-link {{strstr(Request::path(), 'backend/seguimiento') ? 'active' : ''}}">
                                    <i class="material-icons">search</i> {{__('nav.tracing')}}
                                </a>
                            </li>
                            @endcan
                            @can('gestion')
                                <li class="nav-item">
                                    <a href="{{route('backend.report')}}"
                                       class="nav-link {{strstr(Request::path(), 'backend/reportes') ? 'active' : '' }}">
                                        <i class="material-icons">library_books</i> {{__('nav.management')}}
                                    </a>
                                </li>
                            @endcan
                            @can('auditoria')
                                <li class="nav-item">
                                    <a href="{{route('backend.audit')}}"
                                       class="nav-link {{strstr(Request::path(), 'backend/auditoria') ? 'active' : ''}}">
                                        <i class="material-icons">assignment</i> {{__('nav.audit')}}
                                    </a>
                                </li>
                            @endcan
                            @can('api')
                                <li class="nav-item">
                                    <a href="{{route('backend.api')}}"
                                       class="nav-link {{strstr(Request::path(), 'backend/api') ? 'active' : ''}}">
                                        <i class="material-icons">code</i> {{__('nav.api')}}
                                    </a>
                                </li>
                            @endcan
                            @can('configuracion')
                                <li class="nav-item">
                                    <a href="{{ route('backend.configuration.my_site') }}"
                                       class="nav-link {{strstr(Request::path(), 'backend/configuracion') ? 'active' : ''}}">
                                        <i class="material-icons">settings</i> {{__('nav.config')}}
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item">
                                <a target="_blank" onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_nav_index');"
                                   href="http://atencioninstitucional.digital.gob.cl" class="nav-link">
                                    <i class="material-icons">help</i> {{__('nav.help')}}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

    </div>
</nav>
