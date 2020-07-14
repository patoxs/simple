<nav class="navbar navbar-expand-lg navbar-light custom-nav">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('backend.home') }}">
            <img src="{{asset('/img/logo.png')}}" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav">
                @if (Auth::guest())
                    <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">{{__('auth.login')}}</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('backend.home') }}"
                           class="nav-link {{Request::path() == 'backend' ? 'active' : ''}}">
                            {{__('nav.home')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href=""
                           class="nav-link {{strstr(Request::path(), 'backend/agenda') ? 'active' : ''}}">
                            {{__('nav.diary')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('backend.procesos.index')}}"
                           class="nav-link {{strstr(Request::path(), 'backend/procesos') ? 'active' : ''}}">
                            {{__('nav.bpm')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('backend.tracing.index')}}"
                           class="nav-link {{strstr(Request::path(), 'backend/seguimiento') ? 'active' : ''}}">
                            {{__('nav.tracing')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('backend.report')}}"
                           class="nav-link {{strstr(Request::path(), 'backend/reportes') ? 'active' : '' }}">
                            {{__('nav.management')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('backend.audit')}}"
                           class="nav-link {{strstr(Request::path(), 'backend/auditoria') ? 'active' : ''}}">
                            {{__('nav.audit')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('backend.api')}}"
                           class="nav-link {{strstr(Request::path(), 'backend/api') ? 'active' : ''}}">
                            {{__('nav.api')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('backend.configuration.my_site') }}"
                           class="nav-link {{strstr(Request::path(), 'backend/configuracion') ? 'active' : ''}}">
                            {{__('nav.config')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a target="_blank" href="{{asset('/ayuda/simple/index.html')}}" class="nav-link">
                            {{__('nav.help')}}
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink"
                           data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            Bienvenido {{ Auth::user()->email }}
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
                @endif
            </ul>
        </div>

    </div>
</nav>