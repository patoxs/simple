<?php

use \Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use \App\Models\UsuarioBackend;
use \App\Models\UsuarioManager;
use \App\Models\Cuenta;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() == 0) {
            /*User::create([
                'nombres' => 'Admin Asimov',
                'usuario' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'salt' => '',
            ]);*/
        }

        if (Cuenta::count() == 0) {
            //Un usuario backend pertenece a una 'cuenta', por eso se crea la cuenta primero.
            $cuenta = Cuenta::create([
                'nombre' => 'Admin',
                'nombre_largo' => 'Gobierno Digital',
                'mensaje' => 'mensaje de prueba',
                'api_token' => '',
            ]);

            /*UsuarioBackend::create([
                'nombre' => 'Admin',
                'apellidos' => 'Asimov',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'cuenta_id' => $cuenta->id,
                'rol' => 'super',
            ]);*/
        }
/*
        if (UsuarioManager::count() == 0) {
            UsuarioManager::create([
                'usuario' => 'admin@admin.com',
                'password' => Hash::make('123456'),
                'nombre' => 'Manager',
                'apellidos' => 'Asimov',
                'salt' => '',
            ]);
        }
*/
    }
}
