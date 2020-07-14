<footer class="footer mt-auto py-3">
    <div class="container">
        <div class="row">
            <div class="col-6 mt-1">
                <ul>
                    <li style="list-style: none">
              <span class="splogoinstfooter">
                <a class="logoinstfooter" href="#">
                  <img class="align-self-center mr-3 logo"
                       src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logofADesplegar : asset('assets/img/logo.png') }}"
                       alt="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : env('APP_NAME') }}"/>
                </a>
              </span>
                        <span class="float-left splogosuperfooter">
                <a class="logosuperfooter" href="#"><img src="{{ asset('img/logo_super.svg') }}"></a>
              </span>
                    </li>
                    <li style="list-style: none">
                        <a href="http://www.bienesnacionales.cl/">MINISTERIO DE BIENES NACIONALES</a>
                    </li>
                    <!--<li><a href="http://www.minsegpres.gob.cl/">Otros trámites de []</a></li>
                    <li><a href="#">Políticas de privacidad</a></li>
                    <li><a href="#">Término de uso</a></li>-->
                </ul>
                <br>
            </div>
            <div class="col-6 mt-1 text-right">
                @if ( (isset($metadata->contacto_email) && $metadata->contacto_email!='') &&
                (isset($metadata->contacto_link) && $metadata->contacto_link!=''))
                    <p style="font-size: 14px;line-height: 26px;">
                        Si el sistema presenta problemas comuníquese con nosotros escribiendo al siguiente correo
                        {{ $metadata->contacto_email }}, o bien ingresando en el siguiente
                        <a href="{{ $metadata->contacto_link }}" target="_blank">
                            link
                        </a>
                    </p>
                @elseif (isset($metadata->contacto_email) && $metadata->contacto_email!='')
                    <p style="font-size: 14px;line-height: 26px;">
                        Si el sistema presenta problemas comuníquese con nosotros escribiendo al siguiente correo
                        {{ $metadata->contacto_email }}
                    </p>
                @elseif(isset($metadata->contacto_link) && $metadata->contacto_link!='')
                    <p style="font-size: 14px;line-height: 26px;">
                        Si el sistema presenta problemas comuníquese con nosotros ingresando en el siguiente
                        <a href="{{ $metadata->contacto_link }}">link</a>
                    </p>
                @endif
            </div>
        </div>

        <div class="bicolor">
            <span class="azul"></span>
            <span class="rojo"></span>
        </div>
    </div>
</footer>