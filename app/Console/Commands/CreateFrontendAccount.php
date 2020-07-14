<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateFrontendAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:frontend {email} {password} {cuenta_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear usuario frontend';

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

        $this->warn("Creando usuario {$email} en frontend!");

        User::insert([
            'usuario' => $email,
            'nombres' => $email,
            'apellido_paterno' => NULL,
            'apellido_materno' => NULL,
            'registrado' => 1,
            'cuenta_id' => $cuenta_id,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Usuario frontend creado exitósamente!");
    }
}
