<nav class="navbar navbar-expand-lg navbar-light custom-nav">
    <div class="container-fluid container-manager">
        <div class="barra-gob">
            <div class="blue"></div>
            <div class="red"></div>
        </div>
        <a class="navbar-brand" href="{{ route('manager.home') }}">
            <img src="{{asset('/img/logo.png')}}" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink"
                       data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        Bienvenido, <b>{{ Auth::user()->usuario }}</b>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                        <a href="{{ route('manager.logout') }}" class="dropdown-item">
                            {{__('auth.close_session')}}
                        </a>
                    </div>
                </li>
            </ul>
        </div>

    </div>
</nav>
