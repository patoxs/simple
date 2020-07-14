<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuarioManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_manager', function (Blueprint $table) {
            $table->increments('id');
            $table->string('usuario', 128)->unique();
            $table->string('password', 255);
            $table->string('nombre', 128);
            $table->string('apellidos', 128);
            $table->string('salt', 32)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuario_manager');
    }
}
