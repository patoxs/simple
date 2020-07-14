<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnumAnonimoToAccesoModoOnTarea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE tarea MODIFY COLUMN acceso_modo ENUM('grupos_usuarios', 'publico', 'registrados' , 'claveunica' , 'anonimo') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE tarea MODIFY COLUMN acceso_modo ENUM('grupos_usuarios', 'publico', 'registrados' , 'claveunica') NOT NULL");
    }
}
