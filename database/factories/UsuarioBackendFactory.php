<?php

use Faker\Generator as Faker;
use App\Models\UsuarioBackend;

$factory->define(UsuarioBackend::class, function (Faker $faker) {
    return [
        'nombre' => $faker->name,
        'apellidos' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('123456'),
        'cuenta_id' => 1,
        'rol' => 'super',
    ];
});