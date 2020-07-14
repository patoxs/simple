<?php

namespace App\Console\Commands;

use App\Models\UsuarioManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateManagerAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:manager {user} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'How to create Manager User';

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
        $user = $this->argument('user');
        $password = $this->argument('password');

        $this->warn("Creating user {$user} in the Manager!");

        UsuarioManager::insert([
            'nombre' => '',
            'apellidos' => '',
            'usuario' => $user,
            'password' => Hash::make($password),
        ]);

        $this->info("Successfully created user!");
    }
}
