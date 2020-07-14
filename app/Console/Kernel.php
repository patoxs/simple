<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CreateBackendAccount::class,
        Commands\CreateManagerAccount::class,
        Commands\AdminElasticsearch::class,
        Commands\CreateFrontendAccount::class,
        Commands\SendEmails::class,
        Commands\LimpiezaTramitesUsuarios::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('simple:sendmails')
        //          ->timezone('America/Santiago')
        //          ->dailyAt('07:00');
        // $schedule->command('simple:limpieza')
        //          ->timezone('America/Santiago')
        //          ->dailyAt('04:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
