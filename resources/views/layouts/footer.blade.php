<footer id="footer-simple">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="logos-container">
                            <p class="iniciativa">Iniciativa de:</p>
                            <a href="http://digital.gob.cl/" target="_blank">
                                <img class="ft-logo-dgd" src="{{ asset('img/footer/logo-blanco.svg') }}"
                                     alt="Logo Gob Digital">
                            </a>
                            <a href="http://www.minsegpres.gob.cl/" target="_blank">
                                <img class="ft-logo-segpres" src="{{ asset('img/footer/logo-segpres-blanco.svg') }}"
                                     alt="Logo SEGPRES">
                            </a>
                        </div>
                        @if ( (isset($metadata->contacto_email) && $metadata->contacto_email!='') ||
                       (isset($metadata->contacto_link) && $metadata->contacto_link!=''))
                            <div class="barra-vertical"></div>
                        @endif
                    </div>
                    <div class="col-lg-7">
                        @if ( (isset($metadata->contacto_email) && $metadata->contacto_email!='') &&
                        (isset($metadata->contacto_link) && $metadata->contacto_link!=''))
                            <p class="ft-text-info">
                                Si el sistema presenta problemas comuníquese con nosotros escribiendo al siguiente correo
                                {{ $metadata->contacto_email }}, o bien ingresando en el siguiente
                                <a href="{{ $metadata->contacto_link }}" target="_blank">
                                    link
                                </a>
                            </p>
                        @elseif (isset($metadata->contacto_email) && $metadata->contacto_email!='')
                            <p class="ft-text-info">
                                Si el sistema presenta problemas comuníquese con nosotros escribiendo al siguiente correo
                                {{ $metadata->contacto_email }}
                            </p>
                        @elseif(isset($metadata->contacto_link) && $metadata->contacto_link!='')
                            <p class="ft-text-info">
                                Si el sistema presenta problemas comuníquese con nosotros ingresando en el siguiente
                                <a href="{{ $metadata->contacto_link }}">link</a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="row" id="footer-bootom">
                    <div class="col-lg-5">
                        <p class="text-segpres">
                            <img class="ft-logo-cc" src="{{ asset('img/footer/cc.svg') }}" alt="cc dgd">
                            <a href="http://www.minsegpres.gob.cl/" target="_blank">
                                Ministerio Secretaría General de la Presidencia
                            </a>
                        </p>
                    </div>
{{--                    <div class="col-lg-4 align-self-star">--}}
{{--                        <a class="link-terms" href="{{route('front.terminos')}}">--}}
{{--                            Términos y Condiciones--}}
{{--                        </a>--}}
{{--                    </div>--}}
                    <div class="col-lg-7 text-right">
                        Powered by:  <img class="ft-logo-powered" src="{{ asset('img/footer/powered-simple-chico.svg') }}"
                                          alt="Logo Gob Digital">
                    </div>
                    <div class="col-lg-12">
                        <div class="gob-lines">
                            <div class="blue"></div>
                            <div class="red"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
