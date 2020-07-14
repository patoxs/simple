<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use RollbarNotifier;
use Rollbar;

class RollbarServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Don't register rollbar if it is not configured.
        if (!getenv('ROLLBAR_ACCESS_TOKEN') and !$this->app['config']->get('services.rollbar')) {
            return;
        }

        $app = $this->app;

        $config['access_token'] = getenv('ROLLBAR_ACCESS_TOKEN') ?: $app['config']->get('services.rollbar.access_token');

        if (is_null($config['access_token'])) {
            return false;
        }

        if (empty($config['access_token'])) {
            throw new InvalidArgumentException('Rollbar access token not configured');
        }

        \Rollbar\Rollbar::init(config('services.rollbar'));

    }
}
