<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Doctrine_Manager_Exception;
use App\Helpers\Doctrine;
use Doctrine_Connection_Exception;
use Doctrine_Exception;
use Doctrine_Manager;
use Doctrine_Core;

class DoctrineOrmServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // params connections
        $dsn = env('DB_CONNECTION') . //connection
            '://' . env('DB_USERNAME') . //username
            ':' . env('DB_PASSWORD') . //password
            '@' . env('DB_HOST') . //hostname
            '/' . env('DB_DATABASE'); //database

        try {
            // established connection
            Doctrine_Manager::connection($dsn, 'default')->setCharset('UTF8');

            // this will allow Doctrine to load Model classes automatically
            Doctrine::loadModels(app_path('Models/Doctrine'));

            // this will allow us to use "mutators"
            Doctrine_Manager::getInstance()->setAttribute(
                Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

            Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_USE_NATIVE_ENUM, true);

        } catch (Doctrine_Manager_Exception $exception) {
        } catch (Doctrine_Exception $exception) {
        } catch (\Exception $exception) {
        }
    }
}
