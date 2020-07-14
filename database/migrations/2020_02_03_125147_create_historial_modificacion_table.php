<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistorialModificacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_modificacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description')->nullable();

            $table->integer('proceso_id')->unsigned();
            $table->integer('usuario_id')->unsigned();

            $table->foreign('proceso_id')->references('id')->on('proceso');
            $table->foreign('usuario_id')->references('id')->on('usuario_backend');

            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_modificacion');
    }
}
