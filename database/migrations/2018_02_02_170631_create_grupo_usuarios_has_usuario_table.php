<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGrupoUsuariosHasUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_usuarios_has_usuario', function (Blueprint $table) {
            $table->unsignedInteger('grupo_usuarios_id');
            $table->unsignedInteger('usuario_id');

            $table->primary(['grupo_usuarios_id', 'usuario_id']);
            $table->index('usuario_id', 'fk_grupo_usuarios_has_usuario_usuario1');
            $table->index('grupo_usuarios_id', 'fk_grupo_usuarios_has_usuario_grupo_usuarios1');

            $table->foreign('grupo_usuarios_id', 'grupo_usuarios_has_usuario_ibfk_1')
                ->references('id')
                ->on('grupo_usuarios')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table
                ->foreign('usuario_id', 'grupo_usuarios_has_usuario_ibfk_2')
                ->references('id')
                ->on('usuario')
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
        Schema::dropIfExists('grupo_usuarios_has_usuario');
    }
}
