@extends('layouts.backend')

@section('title', 'Introducción API')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="col-md-9">
                <h2>API</h2>
                <p>La API de SIMPLE te permite generar tus propios reportes e informes, extrayendo la información de la
                    plataforma en tiempo real.</p>

                <h3>Autorización</h3>

                <p>Para hacer llamadas a esta API se requerirá un código de acceso (token) que se enviará como parámetro
                    en cada request.</p>

                <p>El codigo de acceso de esta cuenta es: <strong>{{Auth::user()->cuenta->api_token}}</strong> <a
                            href="{{route('backend.api.token')}}">(Cambiar código de acceso)</a></p>

                <h3>Llamadas a la API</h3>

                <p>El diseño de la API de este portal sigue un modelo REST. Eso significa que se utilizan los métodos
                    estándares HTTP para obtener la información. Por ejemplo, si deseas obtener una ficha en particular,
                    deberías enviar un request HTTP como el siguiente:</p>

                <pre>GET {{url('/backend/api/tramites/{tramiteId}?token={token}')}}</pre>

                <h3>Parámetros comunes</h3>

                <p>Los diferentes métodos de esta interfaz de programación requieren distintos atributos como parte de
                    la URL, como parámetros de la consulta. Adicionalmente hay parámetros que son comunes para todos los
                    métodos:</p>

                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <th>Nombre del parámetro</th>
                        <th>Valor</th>
                        <th>Descripción</th>
                    </tr>
                    <tr>
                        <td>token</td>
                        <td>string</td>
                        <td>Código de acceso para acceder a los métodos de esta API.</td>
                    </tr>
                    <tr>
                        <td>dato</td>
                        <td>string</td>
                        <td>Nombre de la variable por la cuál desees filtrar</td>
                    </tr>

                    </tbody>
                </table>

                <h2>Formatos de los datos</h2>

                <p>Los recursos de la API de este portal vienen en formato json. Este es un ejemplo de cómo se vería un
                    trámite.</p>

                <pre>{
    "tramite":{
        "id":496,
        "estado":"completado",
        "proceso_id":11,
        "fecha_inicio":"2013-07-12 11:13:56",
        "fecha_modificacion":"2013-07-12 11:14:00",
        "fecha_termino":"2013-07-12 11:14:00",
        "etapas":[
            {
                "id":704,
                "estado":"completado",
                "usuario_asignado":{
                    "usuario":"jperez",
                    "email":"jperez@ejemplo.com",
                    "nombres":"Juan",
                    "apellido_paterno":"Perez",
                    "apellido_materno":"Cotapo"
                },
                "fecha_inicio":"2013-07-12 11:13:56",
                "fecha_modificacion":"2013-07-12 11:14:00",
                "fecha_termino":"2013-07-12 11:14:00",
                "fecha_vencimiento":null
            }
        ],
        "datos":[
            {
                "511bb6183cea8":"51e021b44939e.pdf"
            },
            {
                "materno":"COTAPO"
            },
            {
                "nombres":"JUAN"
            },
            {
                "paterno":"PEREZ"
            },
            {
                "situacion":true
            }
        ]
    }
}</pre>
                <p>
                    Si necesitas obtener el valor de una de las variables del arreglo "datos" dentro del trÁmite,
                    puedes agregar a la URL el atributo "dato", así como se muestra a continuación.
                </p>
                <pre>GET {{url('/backend/api/tramites/{tramiteId}?token={token}&dato=nombres')}}</pre>
                <pre>{
    "nombres": "JUAN"
}</pre>
                <p>
                    SI la respuesta es exitosa retornará la variable y su valor junto a un status 200 y en el caso de
                    no estar definida simplemente retornará un mensaje de error junto a un 404.
                    <br><br>
                </p>

            </div>
        </div>
    </div>
@endsection