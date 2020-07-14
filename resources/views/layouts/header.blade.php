<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="" href="{{ url('/') }}">
            <div class="media">
                <img class="align-self-center mr-3 logo"
                     src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logoADesplegar : asset('assets/img/logo.png') }}"
                     alt="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : env('APP_NAME') }}"/>
                <div class="media-body align-self-center name-institution">
                    <h5 class="mt-1">{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : ''}}</h5>
                    <p>{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->mensaje : ''}}</p>
                </div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav mt-2">
                @if (Auth::guest() || !Auth::user()->registrado)
                    <li class="nav-item login-default mr-3">
                        <a href="{{route('login')}}" class="nav-link">
                            Ingreso funcionarios
                        </a>
                    </li>
                    <li class="nav-item login">
                        <a href="{{route('login.claveunica')}}" class="nav-link">
                            <span class="icon-claveunica"></span> {{__('auth.login_claveunica')}}
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown login">
                        <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink"
                           data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <span class="icon-claveunica"></span> Bienvenido/a, {{ Auth::user()->nombres }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right login" aria-labelledby="navbarDropdownMenuLink">
                            <a href="{{ route('logout') }}" class="dropdown-item" id="cierreSesion" >
                                <i class="material-icons">exit_to_app</i> {{__('auth.close_session')}}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                  style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @endif
            </ul>
            <ul class="simple-list-menu mt-1 list-group d-block d-sm-none">
                <a class="list-group-item list-group-item-action  {{isset($sidebar) && $sidebar == 'disponibles' ? 'active' : ''}}"
                   href="{{route('home')}}">
                    <i class="material-icons">insert_drive_file</i> Iniciar trámite
                </a>

                @if(Auth::user()->registrado)
                    @php
                        $npendientes = \App\Helpers\Doctrine::getTable('Etapa')
                            ->findPendientes(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                        $nsinasignar = count(\App\Helpers\Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio()));
                        $nparticipados = \App\Helpers\Doctrine::getTable('Tramite')->findParticipadosALL(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                    @endphp
                    <a class="list-group-item list-group-item-action {{isset($sidebar) && $sidebar == 'inbox' ? 'active' : ''}}"
                       href="{{route('stage.inbox')}}">
                        <i class="material-icons">inbox</i> Bandeja de Entrada ({{$npendientes}})
                    </a>
                    @if ($nsinasignar)
                        <a class="list-group-item list-group-item-action {{ isset($sidebar) && $sidebar == 'sinasignar' ? 'active' : '' }}"
                           href="{{route('stage.unassigned')}}">
                            <i class="material-icons">assignment</i> Sin asignar ({{$nsinasignar}})
                        </a>
                    @endif
                    <a class="list-group-item list-group-item-action {{isset($sidebar) && $sidebar == 'participados' ? 'active' : ''}}"
                       href="{{route('tramites.participados')}}">
                        <i class="material-icons">history</i> Historial de Trámites ({{$nparticipados}})
                    </a>
                <!--  <a class="list-group-item list-group-item-action {{isset($sidebar) && strstr($sidebar, 'miagenda') ? 'active' : ''}}"
                           href="{{route('agenda.miagenda')}}">
                            <i class="material-icons">date_range</i> Mi Agenda
                        </a> -->
                @endif
            </ul>
        </div>

    </div>
</nav>
