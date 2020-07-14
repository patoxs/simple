<?php

namespace App\Console\Commands;

use App\Models\UsuarioBackend;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateBackendAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:backend {email} {password} {cuenta_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'How to create Backend User';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $cuenta_id = $this->argument('cuenta_id');

        $this->warn("Creating user {$email} in the Backend!");

        UsuarioBackend::insert([
            'nombre' => '',
            'apellidos' => '',
            'rol' => 'super',
            'cuenta_id' => $cuenta_id,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Successfully created user!");

    }
}
