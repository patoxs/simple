# SIMPLE 2.0

El siguiente ejemplo esta enfocado para levantar la aplicación SIMPLE en un ambiente de desarrollo local. 
Esta instalación se lleva a cabo bajo un conjunto de contenedores los cuales hacen referencia a cada uno de los 
diferentes servicios que necesita la aplicación.

* Sitio web - Laravel 5.5
* MySql 5.7
* Elastic Search 5.6
* Redis
* Rabbit
 

## Instalación
Como requerimiento excluyente se debe contar con docker instalado en tu equipo. En el siguiente 
[link](https://docs.docker.com/install/linux/docker-ce/ubuntu/) podrás encontrar un ejemplo de instalación para el 
sistema operativo Ubuntu, para otras distribuciones consultar la documentación oficial y seguir las 
instrucciones:


### (Consideración)
Para levantar el ambiente de desarrollo las variables o comandos a considerar son los definidos dentro de este
directorio `setup/`

### Variables de entorno

El siguiente paso es, dentro del directorio `setup/` crear un archivo llamado `.env` y copiar el contenido del archivo
`env.example` dentro de el, luego ahí puedes editar las variables de configuración de  acuerdo a tu necesidad, algunas 
variables ya vienen predefinidas dentro del archivo docker-compose.yml, tales como las variables de host o ip referentes
a los demás servicios, como elasticsearch, base de datos, puertos, etc.

```
cd setup/

cp env.example .env
```

Descripción de variables de entorno a utilizar

```
APP_NAME: Nombre de la aplicación.
APP_ENV: Entorno de ejecución.
APP_KEY: llave de la aplicacion, se auto genera con php artisan key:generate.
APP_DEBUG: true o false.
APP_LOG_LEVEL: Nivel de log (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
APP_URL: URL de tu aplicación incluir http.
APP_MAIN_DOMAIN: Dominio de tu aplicación, incluir http.
ANALYTICS: Código de Seguimiento de google analytics

DB_CONNECTION: Tipo de conexión de tu Base de datos, para este proyecto por defecto se usa mysql.
DB_HOST: host donde se aloja tu Base de Datos.
DB_PORT: puerto por donde se esta disponiendo tu Base De Datos en el Host.
DB_DATABASE: Nombre Base de datos (Debe estar previamente creada).
DB_USERNAME: Usuario Base de datos.
DB_PASSWORD: Contraseña Base de datos.

MAIL_DRIVER: soporta ("smtp", "sendmail", "mailgun", "mandrill", "ses", "sparkpost", "log", "array").
MAIL_HOST: Aquí puede proporcionar la dirección de host del servidor.
MAIL_PORT: Este es el puerto utilizado por su aplicación para entregar correos electrónicos a los usuarios de la aplicación.
MAIL_ENCRYPTION: Aquí puede especificar el protocolo de cifrado que se debe usar cuando la aplicación envía mensajes de correo electrónico.
MAIL_USERNAME: Si su servidor requiere un nombre de usuario para la autenticación, debe configurarlo aquí.
MAIL_PASSWORD: Si su servidor requiere una contraseña para la autenticación, debe configurarlo aquí.

ROLLBAR_TOKEN: Token de acceso proporcionado por Rollbar.

RECAPTCHA_SECRET_KEY: reCaptcha secret key, proporcionado por Google.
RECAPTCHA_SITE_KEY: reCaptcha site key, proporcionado por Google.

BASE_SERVICE: URL del microservicio de agendas.
CONTEXT_SERVICE: Contexto de aplicación del servicio de agendas. 
AGENDA_APP_KEY: Identificado de aplicación o cuenta para acceder al microservicio de agendas.
RECORDS: Cantidad de registros que se mostrarán por pagina.
TIEMPO_CONFIRMACION_CITA: Minutos para eliminar una cita si no ha sido confirmada.

JS_DIAGRAM: Libreria que se va a utilizar para hacer los diagramas de flujo, default: jsplumb (Gratuita y libre uso).

MAP_KEY: Key de acceso a Google Maps.

SCOUT_DRIVER: driver para agregar búsquedas de texto completo a sus modelos Eloquent.
ELASTICSEARCH_INDEX: Nombre lógico que interpretara elasticsearch como índice.
ELASTICSEARCH_HOST: Aquí puede proporcionar la dirección de host de elasticsearch.

AWS_S3_MAX_SINGLE_PART: Al superar este límite en bytes, los archivos se subirán a Amazon S3 usando multipartes.

DOWNLOADS_FILE_MAX_SIZE: Al momento de descargar trámites que no posean archivos subidos a Amazon S3, se compara el total a descargar con esta variable en Mega bytes, si es mayor que la variable, se usará un JOB para empaquetar y luego enviar el enlace de descarga por correo electrónico a la dirección registrada para ese nombre de usuario. Si es menor que esta variable, se descargará de forma directa sin un Job. Si no se especifica usa por omisión 500 MB.
DOWNLOADS_MAX_JOBS_PER_USER: Cantidad máxima de JOBS de archivos a descargar simultáneos permitidos por cada usuario.
DESTINATARIOS_CRON: Listado de correos separados por comas que serán destinatarios de recibir el estado de las tarea de cron
```


## Docker-compose

** Antes de instalar asegúrese de que los siguientes puertos se encuntran disponibles en su máquina:
* 8000 -> Sitio web
* 9200 -> Elasticsearch
* 3306 -> MySql
* 6379 -> Redis
* 5672 -> RabbitMq
* 15672 -> Manager de RabbitMq

Si no puedes disponibilizarlos, debes modificar los puertos en el archivo `.env`
o eventualmente modificar los puertos que esten mapeados directamente en el docker-compose.yml, como por ej: 
`elasticsearch`.

Recuerda estar dentro del directorio `setup/`
```bash
$ cd setup/
```

Simplemente ejecutamos el bash `install.sh`
```
$ bash install.sh
```

Luego comenzaran a levantar la aplicación tomando como base el Dockerfile definido
dentro del directorio `setup/`

Y continuará descargando y instalando los diferentes servicios, elasticsearch, MySql, redis y rabbit

Esto tomará algunos minutos, dependiendo de tu conección a internet ya que tendrá que descargar las diferentes imágenes de cada servicio 
(en el caso de que no las tengas instaladas). Cuando la instalación termine puedes ejecutar:
```bash
$ docker ps
```

Y se listaran los siguientes contenedores

```bash
CONTAINER ID        IMAGE                   COMMAND                  CREATED             STATUS              PORTS                                                                                        NAMES
2b3dd0042bd0        simple_app              "entrypoint"             32 minutes ago      Up 32 minutes       3000/tcp, 9000/tcp, 0.0.0.0:8000->80/tcp                                                     simple2_web
4ca2d8744acc        redis                   "docker-entrypoint.s…"   32 minutes ago      Up 32 minutes       0.0.0.0:6379->6379/tcp                                                                       simple2_redis
b59a61d19f6e        elasticsearch:5.6       "/docker-entrypoint.…"   32 minutes ago      Up 32 minutes       0.0.0.0:9200->9200/tcp, 9300/tcp                                                             simple2_elastic
1f5d24cb9da3        mysql:5.7               "docker-entrypoint.s…"   32 minutes ago      Up 32 minutes       0.0.0.0:3306->3306/tcp, 33060/tcp                                                            simple2_db
736f5da549ae        rabbitmq:3-management   "docker-entrypoint.s…"   32 minutes ago      Up 32 minutes       4369/tcp, 5671/tcp, 0.0.0.0:5672->5672/tcp, 15671/tcp, 25672/tcp, 0.0.0.0:15672->15672/tcp   simple2_rabbit
```

```bash
- simple2_web
- Simple2_db
- simple2_elastic
- simple2_redis
- simple2_rabbit
```

Cada uno mapeado a sus respectivos puertos desde 127.0.0.1 (localhost) hacia cada contenedor.

Para acceder a un contenedor puedes ejecutar el siguiente comando:
```bash
$ docker exec -it <nombre_contenedot> bash

$ docker exec -it simple2_web bash
```

```bash
$ docker exec -it simple2_db bash

Y luego ya puedes entrar con: 

mysql -u root -p
```

Para ejecutar un comando artisan dentro del contenedor, puedes acceder directamente al contenedor `simple2_web`
y dentro ejecutar:
``` bash
$ php artisan <commando>
```
o bien puedes hacerlo ejecutando el comando de la siguiwnte forma:

```bash
$ docker exec simple2_web bash -c "php artisan <comando>"
```

Este ejemplo aplica para cualquier comando ejecutado dentro de la aplicación laravel.

### Comandos de Utilidad
1.- Ejecutar el modo watch para los assets (el watch quedará esperando en la terminal)
```bash
$ docker exec simple2_web bash /var/www/simple/setup/watch_assets.sh
```

2.- Ejecutar el modo prod_assets para mimificar y procesar los assets para un eventual ambiente productivo
```bash
$ docker exec simple2_web bash /var/www/simple/setup/prod_assets.sh
```

3.- Instalar dependenias con composer
```bash
$ docker exec simple2_web bash /var/www/simple/setup/composer_install.sh
```

4.- Detener los servicios, `docker-compose`
```bash
(no elimina los contenedores)
$ docker-compose stop
```

5.- Bajar los servicios, `docker-compose`
```bash
(elimina los contenedores)
$ docker-compose down
```

6.- Levantar los servicios
```bash
$ docker-compose up -d
```


### Errores o fallos al instalar:

Para ejecutar una reinstlación desde cero, es recomendable considerar lo siguiente:

- Eliminar la imagen de simple que pudo no quedar bien instalada.
```bash
> listar imagenes
$ docker images

> Copiar el nombre de la imagen simple "simple_app" y eliminar
$ docker image rm simple_app

> Eliminar los contenedores (si estan activos)
$ docker-compose down

$ docker container rm <nombre_contenedor>
```

1) Uno o más de los puertos requeridos estaba utilizado, no me di cuenta y mi instalación se interrumpió.
    
    Esto pobablemente haya interrumpido el levantamiento de alguno de los contenedores,
    puedes revisar cuáles de los contenedores fueron levantados, si al ejecutar `docker ps` sólo se ve uno o no los 
    vez, es recomendable realizar una reinstalación, ejecutando nuevamente `bash install.sh`. Si alguno de los 
    contenedores alcanzó a ser creado, puedes probar tambien ejecutando ahora en la raiz del proyecto un comando de 
    `docker-compose` (estos a diferencia de los bash debe ejecutarse en la raiz del proyecto).
    
    `$ docker-compose down` &&
    `docker-compose up -d`
    
    Opcionalmente si alguno de los puertos requeridos se encuentra ocupado, siempre puedes modificarlo en el archivo 
    `.env`
    
2) Es muy importante identificar en que momento de la instalación se produce el error, por ejemplo
si llegara a ocurrir algun problema de conección a mitad de la instalación lo recomendable siempre será reinstalar.
(`bash install.sh`) ya que de otro modo habría que entrar al docker de la aplicación (`docker exec -it simple2_web bash`)
e ir instalando manualmente las instrucciones del Dockerfle según hasta dónde haya llegado nuestra intalación y como lo 
muestra la terminal y realmente no queremos eso.
     
       

---
### Sección Backend y Manager
Dentro de la Instalación se creó un usuario para la sección de backend y manager

```bash
url:      localhost:8000/backend
usuario:  admin@simple.cl
password: 123456
```

```bash
url:      localhost:8000/manager
usuario:  admin@simple.cl
password: 123456
```

Puedes crear nuevo usuarios desde un comando laravel, de la siguiente manera:

```bash
$ docker exec simple2_web bash -c "php artisan simple:backend nombre_email password_min_6_caracteres"

$ docker exec simple2_web bash -c "php artisan simple:manager nombre_email password_min_6_caracteres"
```


---
### Integración Con ClaveÚnica

La integración con ClaveÚnica existe actualmente solo en Chile y para instituciones públicas.

Para permitir el funcionamiento del login con ClaveÚnica es necesario generar las credenciales
correspondientes, para ello debes dirigirte al siguiente [enlace](https://claveunica.gob.cl/institucional)
en la pestaña `Solicitar Información`. Debes completar el formulario siguiendo cuidadosamente las instrucciones, 
luego obtendrás dos pares de credenciales, `client_id` y 
`client_secret` tanto para Test/QA (Sandbox) como para producción, para este caso debes usar las de sandbox.

En la inscripción de una cuenta `institución` te pedirá una url relacionada al ambiente de Test/QA,
debes recordar lo que ingreses, sea `localhost` o `127.0.0.1`, ya que este dato lo necesitaremos mas adelante.

Ingresando a la url de la aplicación: `localhost:8000/manager` con el usuario `admin@simple.com` o un usuario válido
puedes editar la `cuenta` por defecto o crear una nueva `cuenta` y en ella agregar las credenciales en la sección `Editar Claveúnica`.

Tambien te pedirá ingresar un dominio, para efectos de prueba, puedes agregar `localhost` o `127.0.0.1`.

###### Consideración:
Como nuestra url es un `localhost` debemos aplicar un pequeño `"truco"` solo para ser aplicado en desarrollo.
Dentro de la clase `App\Providers\AppServiceProvider.php` en el método `bootClaveUnicaSocialite()`

Aquí la url vendrá definida según como lo registraste en ClaveÚnica, por ejemplo si usaste `127.0.0.1`, quedará algo
como lo siguiente.

```bash
//comentar la linea
$redirect = env('APP_MAIN_DOMAIN') == 'localhost' ?
                    env('APP_URL') . '/login/claveunica/callback' :
                    secure_url('login/claveunica/callback');
Y cambiarla por..

$redirect = 'http://127.0.0.1:8000/login/claveunica/callback';
```

Este `"truco"` jamás debe ser subido ya que sólo es para efectos de desarrollo
 

