<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>

<header class="blog-header py-3">
      <div class="row rowheader justify-content-center">
        <div class="col-10">
          <div class="row">
            <div class="col-6 pt-1">
              <a class="text-muted logosuper" href="#"><img src="{{ asset('img/logo_super.png') }}"></a>
            </div>
            <div class="col-6 d-flex justify-content-end align-items-center">
              <ul>
                <!--<li><a href="#">¿Qué es?</a></li>
                <li><a href="#">Contáctanos</a></li>-->
              </ul>
            </div>
          </div>
        </div>
      </div>


  <div class="row row2header justify-content-center">
    <div class="col-10 rcntheader">
      <div class="row">
        <div class="col-md-6">
          <ul class="ullogo">
            <li>
              <a class="logoinst" href="#">
                <img class="align-self-center mr-3 logo"
                     src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logoADesplegar : asset('assets/img/logo.png') }}"
                     alt="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : env('APP_NAME') }}"/>
              </a>
            </li>
            <li><!--<h2>Nombre del Trámite</h2>--></li>
          </ul>
        </div>
        <div class="col-md-6">
          <ul class="navlogin mt-2">
            @if (Auth::guest() || !Auth::user()->registrado)
                  <li class="nav-item login-default mr-3">
                      <a href="{{route('login')}}" class="nav-link">
                          Ingreso funcionarios
                      </a>
                  </li>
                <li class="nav-item login">
                    <a href="{{route('login.claveunica')}}" class="btn btn-sm btn-outline-secondary btnclvn nav-link">
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
                        <a href="{{ route('logout') }}" class="dropdown-item" id="cierreSesion">
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
    </div>
  </div>
</header>