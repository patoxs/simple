<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConexionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conexion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tarea_id_origen');
            $table->unsignedInteger('tarea_id_destino')->nullable();
            $table->enum('tipo', ['secuencial', 'evaluacion', 'paralelo', 'paralelo_evaluacion', 'union']);
            $table->string('regla', 256)->nullable();

            $table->unique(['tarea_id_origen', 'tarea_id_destino'], 'tarea_origen_destino');
            $table->index('tarea_id_origen', 'fk_ruta_tarea');
            $table->index('tarea_id_destino', 'fk_ruta_tarea1');

            $table->foreign('tarea_id_origen', 'conexion_ibfk_1')
                ->references('id')
                ->on('tarea')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('tarea_id_destino', 'conexion_ibfk_2')
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
        Schema::dropIfExists('conexion');
    }
}
