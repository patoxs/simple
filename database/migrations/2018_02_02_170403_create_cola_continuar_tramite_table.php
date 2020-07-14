<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColaContinuarTramiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cola_continuar_tramite', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tramite_id')->nullable();
            $table->integer('tarea_id')->nullable();
            $table->text('request')->nullable();
            $table->tinyInteger('procesado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cola_continuar_tramite');
    }
}
