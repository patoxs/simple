<?php

namespace App\Providers;

use App\Models\Cuenta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\ScoutEngines\Elasticsearch\ElasticsearchEngine;
use Laravel\Scout\EngineManager;
use Elasticsearch\ClientBuilder as ElasticBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (\Schema::hasTable('cuenta')) {
            setlocale(LC_ALL, env('PHP_LOCALE'));

            Schema::defaultStringLength(191);

            $this->bootClaveUnicaSocialite();

            $this->bootElasticsearch();

            $this->bootValidatorExtend();

            $this->bootSetEmailConfigs();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDoctrineOrmServiceProvider();
        $this->registerRollbarServiceProvider();
    }

    public function registerDoctrineOrmServiceProvider()
    {
        $this->app->register(DoctrineOrmServiceProvider::class);
    }

    public function registerRollbarServiceProvider()
    {
        if ($this->app->environment('production')) {
            $this->app->register(RollbarServiceProvider::class);
        }
    }

    public function bootClaveUnicaSocialite()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'claveunica',
            function ($app) use ($socialite) {
               $redirect = env('APP_MAIN_DOMAIN') == 'localhost' ?
                    env('APP_URL') . '/login/claveunica/callback' :
                    secure_url('login/claveunica/callback');

                $config = [
                    'client_id' => \Cuenta::cuentaSegunDominio()->client_id,
                    'client_secret' => \Cuenta::cuentaSegunDominio()->client_secret,
                    'redirect' => $redirect
                ];

                return $socialite->buildProvider(\App\Socialite\Two\ClaveUnicaProvider::class, $config);
            }
        );
    }

    private function bootElasticsearch()
    {
        app(EngineManager::class)->extend('elasticsearch', function ($app) {
            return new ElasticsearchEngine(ElasticBuilder::create()
                ->setHosts(config('scout.elasticsearch.hosts'))
                ->build(),
                config('scout.elasticsearch.index')
            );
        });
    }

    private function bootValidatorExtend()
    {

        Validator::extend('date_prep', function ($attribute, $value, $parameters, $validator) {
            //Convierte una fecha en humano al formato mysql.
            return strftime('%Y-%m-%d', strtotime($value));
        });

        Validator::extend('alpha_space', function ($attribute, $value, $parameters, $validator) {
            return (!preg_match('/^[\pL\pN\s]{0,75}$/u', $value)) ? FALSE : TRUE;
        });

        Validator::extend('is_natural_no_zero', function ($attribute, $value, $parameters, $validator) {
            if (!preg_match('/^[0-9]+$/', $value)) {
                return FALSE;
            }

            if ($value == 0) {
                return FALSE;
            }

            return TRUE;
        });

        Validator::extend('emails', function ($attribute, $value, $parameters, $validator) {

            $value = str_replace(' ', '', $value);
            $array = explode(',', $value);

            foreach ($array as $email) //loop over values
            {
                $email_to_validate['email'][] = $email;
            }

            $rules = array('email.*' => 'email');

            $messages = array(
                'email.*' => trans('validation.emails')
            );
            $validator = Validator::make($email_to_validate, $rules, $messages);


            if ($validator->passes()) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('valid_email', function ($attribute, $value, $parameters, $validator) {
            $rules = array('*' => 'email');

            $value = ['email' => $value];

            $validator = Validator::make($value, $rules);

            if ($validator->passes()) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('rut', function ($attribute, $value, $parameters, $validator) {
            $rut_con_dv = explode('-', $value);
            if (count($rut_con_dv) == 2) {
                $rut = str_replace('.', '', $rut_con_dv[0]);
                $dv = strtolower($rut_con_dv[1]);
                /* Con las lineas anteriores le asignanos a las variables $rut y $dv, lo ingresado por formulario en la página anterior, solo utilizaremos el rut. El digito verificador, lo usaremos al final */
                $rutin = strrev($rut);
                /* Invertimos el rut con la funcion “strrev” */
                $cant = strlen($rutin);
                /* Contamos la cantidad de numeros que tiene el rut */
                $c = 0;
                /* Creamos un contador con valor inicial cero */
                while ($c < $cant) {
                    $r[$c] = substr($rutin, $c, 1);
                    $c++;
                }
                /* Hacemos un ciclo en el que se creara un array o arreglo que se llamara $r, en el cual se le asignara a cada valor del array, el valor correspodiente del rut, Por ej: para el rut 12346578, que invertido sería 87654321, el valor de $r[0] es 8, de $r[5] es 3 y asi sucesiva y respectivamente. */
                $ca = count($r);
                /* Contamos la cantidad de valores que tiene el arreglo con la función “count” */
                $m = 2;
                $c2 = 0;
                $suma = 0;
                /* En las lineas anteriores creamos 3 cosas, un multiplicador con el nombre $m y que su valor inicial es 2, ya que por formula es el primero que necesitamos, creamos tambien un segundo contador con el nombre $c2 y valor inicial cero y por ultimo creamos un acumulador de nombre $suma en el cual se guardara el total luego de multiplicar y sumar como manda la formula */
                while ($c2 < $ca) {
                    $suma = $suma + ($r[$c2] * $m);
                    if ($m == 7) {
                        $m = 2;
                    } else {
                        $m++;
                    }
                    $c2++;
                }
                /* Hacemos un nuevo ciclo en el cual a $suma se le suma (valga la redundancia) su propio valor (que inicialmente es cero) más el resultado de la multiplicación entre el valor del array correspondiente por el multiplicador correspondiente, basandonos en la formula */
                $resto = $suma % 11;
                /* Calculamos el resto de la división usando el simbolo % */
                $digito = 11 - $resto;
                /* Calculamos el digito que corresponde al Rut, restando a 11 el resto obtenido anteriormente */
                if ($digito == 10) {
                    $digito = 'k';
                } else {
                    if ($digito == 11) {
                        $digito = '0';
                    }
                }
                /* Creamos dos condiciones, la primero dice que si el valor de $digito es 11,
                lo reemplazamos por un cero (el cero va entre comillas. De no hacerlo así,
                el programa considerará “nada” como cero, es decir si la persona no ingresa Digito Verificado y
                este corresponde a un cero, lo tomará como valido, las comillas, al considerarlo texto, evitan eso).
                El segundo dice que si el valor de $digito es 10, lo reemplazamos por una K, de no cumplirse ninguno
                de las condiciones, el valor de $digito no cambiará. */
                if ($dv == $digito) {
                    //return $rut . '-' . $dv;
                    return true;
                }
                /* Por ultimo comprobamos si el resultado que obtuvimos es el mismo que ingreso la persona, de ser así se muestra el mensaje “Valido”, de no ser así se muestra el mensaje “No Valido” */
            }

            return false;
        });

    }

    /**
     * Seteamos las variables de 'address' y 'name' correspondientes al email dependiendo de la cuenta segun el dominio.
     */
    public function bootSetEmailConfigs()
    {
        if (class_exists('Cuenta')) {
            $cuenta = \Cuenta::cuentaSegunDominio();

            $mail_from = env('MAIL_FROM_ADDRESS');
            if(empty($mail_from)) {
                $mail_from = $cuenta['nombre'] . '@' . env('APP_MAIN_DOMAIN', 'localhost');
            }

            $data = [
                'address' => $mail_from,
                'name' => $cuenta['nombre_largo'],
            ];

            config(['mail.from' => $data]);
        }
    }

}
