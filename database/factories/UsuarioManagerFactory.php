<?php

use Faker\Generator as Faker;
use App\Models\UsuarioManager;

$factory->define(UsuarioManager::class, function (Faker $faker) {
    return [
        'usuario' => $faker->userName,
        'nombre' => $faker->name,
        'apellidos' => $faker->lastName,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
    ];
});
