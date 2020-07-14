<?php

namespace App\Providers;

use App\Models\Proceso;
use App\Policies\ProcessPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        Proceso::class => ProcessPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->registerGates();

        //
    }

    public function registerGates()
    {
        Gate::define('api', function ($user) {
            return $user->isSuper() || $user->isDesarrollo();
        });

        Gate::define('configuracion', function ($user) {
            return $user->isSuper() || $user->isconfiguracion();
        });

        Gate::define('auditoria', function ($user) {
            return $user->isSuper();
        });

        Gate::define('gestion', function ($user) {
            return $user->isSuper() || $user->isGestion();
        });

        Gate::define('agenda', function ($user) {
            return $user->isSuper() || $user->isAgenda();
        });

        Gate::define('proceso', function ($user) {
            return $user->isSuper() || $user->isModelamiento();
        });

        Gate::define('seguimiento', function ($user) {
            return $user->isSuper() || $user->isSeguimiento() || $user->isOperacion();
        });
    }
}
