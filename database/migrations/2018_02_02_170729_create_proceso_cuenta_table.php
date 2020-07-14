<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcesoCuentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proceso_cuenta', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_cuenta_origen')->nullable();
            $table->integer('id_cuenta_destino')->nullable();
            $table->integer('id_proceso')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proceso_cuenta');
    }
}
