<nav class="navbar navbar-expand-lg navbar-light custom-nav">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('backend.home') }}">
            <img src="{{asset('/img/logo.png')}}" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        @if( Auth::user()->email != null )
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
            </div>
        @endif
    </div>
</nav>
