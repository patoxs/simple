<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuarioBackendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_backend', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 128)->unique();
            $table->string('password', 255);
            $table->string('nombre', 128);
            $table->string('apellidos', 128);
            $table->string('rol', 150)->nullable();
            $table->string('salt', 32)->nullable();
            $table->string('reset_token', 40)->nullable();
            $table->unsignedInteger('cuenta_id');
            $table->string('procesos', 150)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('cuenta_id', 'fk_usuario_backend_cuenta1');

            $table->foreign('cuenta_id', 'usuario_backend_ibfk_1')
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
        Schema::dropIfExists('usuario_backend');
    }
}
