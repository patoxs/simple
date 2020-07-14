<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTareaHasGrupoUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarea_has_grupo_usuarios', function (Blueprint $table) {
            $table->unsignedInteger('tarea_id');
            $table->unsignedInteger('grupo_usuarios_id');

            $table->primary(['tarea_id', 'grupo_usuarios_id']);

            $table->foreign('grupo_usuarios_id', 'fk_tarea_has_grupo_usuarios_grupo_usuarios1')
                ->references('id')
                ->on('grupo_usuarios')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('tarea_id', 'fk_tarea_has_grupo_usuarios_tarea1')
                ->references('id')
                ->on('tarea')
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
        Schema::dropIfExists('tarea_has_grupo_usuarios');
    }
}
