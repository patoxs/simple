<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGrupoUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->unsignedInteger('cuenta_id');

            $table->unique(['cuenta_id', 'nombre'], 'grupo_usuarios_UNIQUE');
            $table->index('cuenta_id', 'fk_grupo_usuarios_cuenta1');

            $table->foreign('cuenta_id', 'grupo_usuarios_ibfk_1')
                ->references('id')
                ->on('cuenta')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupo_usuarios');
    }
}
