<?php if ((isset($num_destacados) && $num_destacados > 0) || $sidebar == 'categorias'): ?>
<section id="simple-destacados">
    <div class="section-header">
        <?php if ($sidebar == 'disponibles'): ?>
        <h2>Trámites destacados</h2>
        <?php else: ?>
        <h2>Trámites - <?= $categoria->nombre ?></h2>
        <a href="<?=route('home')?>" class="btn btn-primary preventDoubleRequest"
           style="float: right;">
            <i class="material-icons align-middle">keyboard_backspace</i> Volver
        </a>
        <?php endif ?>
    </div>
    <div class="row">
        <?php foreach ($procesos as $p): ?>
        <?php if ($p->destacado == 1 || $sidebar == 'categorias'): ?>
        @if(is_null($p->ocultar_front) || !$p->ocultar_front)
        <div class="{{$login ? 'col-md-6' : 'col-md-4' }} item">
            <div class="card text-center">
                <div class="card-body">
                    <div class="media">
                        @if($p->icon_ref)
                            <img src="<?= asset('img/icon/' . $p->icon_ref) ?>" class="img-service">
                        @else
                            <i class="icon-archivo"></i>
                        @endif
                        <div class="media-body">
                            <p class="card-text">
                                {{$p->nombre}}
                            </p>
                            <p>{{$p->descripcion}}</p>
                            @if(!is_null($p->url_informativa))
                                <p><a href="{{$p->url_informativa}}" target="_blank" style="text-decoration: underline;font-size: 14px;">Mas información</a></p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($p->ficha_informativa)
                    <a href="{{ route('tramites.pre_inicio', [$p->id]) }}"></a>
                @else
                    <a href="{{
                             $p->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$p->id]) :
                            (
                                $p->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$p->id]) :
                                route('login').'?redirect='.route('tramites.iniciar', $p->id)
                            )
                            }}"
                       class="card-footer {{$p->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
                        @if ($p->canUsuarioIniciarlo(Auth::user()->id))
                            Iniciar
                        @else
                            @if ($p->getTareaInicial()->acceso_modo == 'claveunica')
                                <i class="icon-claveunica"></i> Iniciar con Clave Única
                            @else
                                Autenticarse
                            @endif
                        @endif
                        <span>&#8594;</span>
                    </a>
                @endif
            </div> <!-- fin div card -->
        </div>
        @endif
        <?php endif ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif ?>

<?php if (isset($categorias) && count($categorias) > 0): ?>
<section id="simple-categorias">
    <div class="section-header">
        <h2>Categorías</h2>
    </div>
    <div class="row">
        @foreach ($categorias as $c)
            <div class="col-lg-4 col-md-4 item">
                <a href="<?=url('home/procesos/' . $c->id)?>">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="media">
                                @if($c->icon_ref)
                                    <img src="<?= asset('uploads/logos/' . $c->icon_ref) ?>" class="img-service">
                                @else
                                    <i class="icon-archivo"></i>
                                @endif
                                <div class="media-body">
                                    <p class="card-text">
                                        {{$c->nombre}}
                                        <font size="3">
                                            <br/>{{$c->descripcion}}
                                        </font>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>
<?php endif ?>

@if (isset($num_otros) && $num_otros > 0 && $sidebar != 'categorias')
    <section id="simple-destacados">
        <div class="section-header">
            @if (count($categorias) > 0 || $num_destacados > 0)
                <h2>Otros trámites</h2>
            @endif
        </div>
        <div class="row">
            @foreach ($procesos as $p)
                @if(is_null($p->ocultar_front) || !$p->ocultar_front)
                @if($p->destacado == 0 || $p->categoria_id == 0)
                    <div class="{{$login ? 'col-md-6' : 'col-md-4' }} item">

                        <div class="card text-center">
                            <div class="card-body">
                                <div class="media">
                                    @if($p->icon_ref)
                                        <img src="<?= asset('img/icon/' . $p->icon_ref) ?>"
                                             class="img-service">
                                    @else
                                        <i class="icon-archivo"></i>
                                    @endif
                                    <div class="media-body">
                                        <p class="card-text">
                                            {{$p->nombre}}
                                        </p>
                                        <p>{{$p->descripcion}}</p>
                                        @if(!is_null($p->url_informativa))
                                            <p><a href="{{$p->url_informativa}}" target="_blank" style="text-decoration: underline;font-size: 14px;">Mas información</a></p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($p->ficha_informativa)
                                <a class="card-footer" href="{{ route('tramites.pre_inicio', [$p->id]) }}">
                                    Iniciar trámite
                                </a>
                            @else
                                <a href="{{
                                         $p->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$p->id]) :
                                        (
                                            $p->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$p->id]) :
                                            route('login').'?redirect='.route('tramites.iniciar', $p->id)
                                        )
                                        }}"
                                   class="card-footer {{$p->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
                                    @if ($p->canUsuarioIniciarlo(Auth::user()->id))
                                        Iniciar trámite
                                    @else
                                        @if ($p->getTareaInicial()->acceso_modo == 'claveunica')
                                            <i class="icon-claveunica"></i> Iniciar con Clave Única
                                        @else
                                            <i class="material-icons">person</i> Autenticarse
                                        @endif
                                    @endif
                                    <span class="float-right">&#8594;</span>
                                </a>
                            @endif
                        </div>

                    </div>
                @endif
                @endif
            @endforeach
        </div>
    </section>
@endif