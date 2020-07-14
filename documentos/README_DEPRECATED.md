# SIMPLE 2.0

## Requerimientos

* NodeJS >= 8.11.3 
* NPM >= 5.6.0
* MySQL 5.7 ó MariaDB 10.2
* PHP 7.1
* Librerías PHP necesarias:
    * OpenSSL
    * PDO
    * PDO_MYSQL
    * Mbstring
    * Tokenizer
    * curl
    * mcrypt
    * Ctype
    * XML
    * JSON
    * GD
    * SOAP
    * bcmath

## Instalación


### Mysql >= 5.7
Si estas usando una versión mayor o igual a MySQL 5.7, deberas desactivar el only_full_group_by, para eso en el sql mode deberás tener las siguientes lineas (my.cnf).

    sql-mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"


### Permisos de directorio

Es posible que deba configurar algunos permisos. Los directorios dentro de `storage` y `bootstrap/cache` deben ser editables por su servidor web o Laravel no se ejecutará.

### Variables de entorno

El siguiente paso es copiar el archivo .env.example a .env y editar las variables de configuración de acuerdo a tu servidor:

```
cp .env.example .env
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

### Instalar las dependencias con composer

Laravel utiliza `Composer` para administrar sus dependencias. Entonces, antes de usar este proyecto desarrollado en Laravel, 
asegúrese de tener Composer instalado en su máquina. Y ejecute el siguiente comando.
 
```
composer install
```

Luego, la instalación de las librerías JS necesarias:

```
npm install
```

Compilación de JS

```
npm run prod
```

Luego, Migración y Semillas de la base de datos:

```
php artisan migrate --seed
```

## Actualizaciones

Cada vez que se realice un pull del proyecto, este deberá ser acompañado de la siguiente lista de ejecución de comandos.

```
npm install
npm run production
composer install
php artisan migrate --force
vendor/bin/phpunit
```

## Elasticsearch

Para crear el índice:

```
php artisan elasticsearch:admin create
```

Para indexar todo (Realizar esto en instalación inicial):

```
php artisan elasticsearch:admin index
```

Para indexar solo páginas:

```
php artisan elasticsearch:admin index pages
```

## Creación de usuarios en Frontend, Backend y Manager

Para crear un usuario perteneciente a Frontend, basta con ejecutar este comando especificando email, contraseña y opcionalmente la cuenta:

```
php artisan simple:frontend {email} {password} {cuenta?}
php artisan simple:frontend mail@example.com 123456 1
```

Para crear un usuario perteneciente al Backend, basta con ejecutar este comando especificando email y contraseña:

```
php artisan simple:backend {email} {password}
php artisan simple:backend mail@example.com 123456
```

Y para crear un usuario perteneciente al Manager,

```
php artisan simple:manager {user} {password}
php artisan simple:manager siturra qwerty
```

## Generar la llave de aplicación

```
php artisan key:generate
```

## Tests con PHPUnit

Listado de Tests:

- Verificar que las librerías de PHP requeridas por SIMPLE, estan habilitadas (VerifyLibrariesAvailableTest)
- Validación de Reglas Customizadas (CustomValidationRulesTest)
- Creación de Usuarios (Front, Backend, Manager) (CreateUsersTest)
- Motor de Reglas SIMPLE BPM (RulesTest)

Para ejecutar los Tests solo debes ejecutar el siguiente comando:

```
vendor/bin/phpunit
```

## Adicionales 

Si desea poder utilizar una acción de tipo Soap, debe tener habilitada la librería Soap en su php.ini

## Queue worker para indexar contenido de trámites
Para indexar el contenido de los trámites cada vez que se avanza dentro del flujo, es necesario dejar corriendo el worker con el siguiente comando:

```
php artisan queue:work --timeout=0
```

## Tareas programadas
Configurar por cada instancia el siguiente path para ser programado y que ejecute las tareas de limpieza de trámites sin avanzar, usuarios no registrados sin actividad y notificación de etapas por vencer

```
/schedule
ejemplo: http://simple.cl/schedule
```
