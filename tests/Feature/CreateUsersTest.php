<?php

namespace Tests\Feature;

use App\Models\UsuarioBackend;
use App\Models\UsuarioManager;
use App\User;

class CreateUsersTest extends DatabaseTestCase
{
    /**
     * @test, create User in database
     */
    public function testCreateUserDatabase()
    {
        $user = factory(User::class)->create();

        $this->assertDatabaseHas('usuario', [
            'id' => $user->id
        ]);

    }

    /**
     * @test, create Backend User in database
     */
    public function testCreateBackerUserDatabase()
    {
        $user = factory(UsuarioBackend::class)->create();

        $this->assertDatabaseHas('usuario_backend', [
            'id' => $user->id
        ]);
    }

    /**
     * @test, create Manager User in database
     */
    public function testCreateManagerUserDatabase()
    {
        $user = factory(UsuarioManager::class)->create();

        $this->assertDatabaseHas('usuario_manager', [
            'id' => $user->id
        ]);

    }
}
