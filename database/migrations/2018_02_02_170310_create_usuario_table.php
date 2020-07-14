<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id');
            $table->string('usuario', 128);
            $table->string('password', 256)->nullable();
            $table->string('rut', 16)->nullable();
            $table->string('nombres', 128)->nullable();
            $table->string('apellido_paterno', 128)->nullable();
            $table->string('apellido_materno', 128)->nullable();
            $table->string('email', 255)->nullable();
            $table->tinyInteger('registrado')->default(1);
            $table->tinyInteger('vacaciones')->default(0);
            $table->unsignedInteger('cuenta_id')->nullable();
            $table->string('salt', 32)->nullable();
            $table->tinyInteger('open_id')->default(0);
            $table->string('reset_token', 40)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['usuario', 'open_id'], 'usuario_UNIQUE');
            $table->index('cuenta_id', 'fk_usuario_cuenta1');
            $table->index(['email', 'open_id'], 'email_idx');
            $table->index('rut', 'rut_idx');

            $table->foreign('cuenta_id', 'usuario_ibfk_1')
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
        Schema::dropIfExists('usuario');
    }
}