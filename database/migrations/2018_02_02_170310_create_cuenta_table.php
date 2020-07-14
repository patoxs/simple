<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCuentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuenta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->string('nombre_largo', 256);
            $table->text('mensaje');
            $table->string('logo', 128)->nullable();
            $table->string('api_token', 32)->nullable();
            $table->tinyInteger('descarga_masiva')->default(1);
            $table->string('client_id', 64)->nullable();
            $table->string('client_secret', 64)->nullable();
            $table->string('ambiente', 255)->default('prod');
            $table->integer('vinculo_produccion')->nullable();
            $table->timestamps();

            $table->unique('nombre', 'nombre');
            $table->index('vinculo_produccion', 'vinculo_produccion_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cuenta');
    }
}
