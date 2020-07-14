<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paso', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orden');
            $table->enum('modo', ['edicion', 'visualizacion']);
            $table->string('regla', 512)->nullable();
            $table->unsignedInteger('formulario_id');
            $table->unsignedInteger('tarea_id');

            $table->index('formulario_id', 'fk_paso_formulario1');

            $table->foreign('tarea_id', 'fk_paso_tarea1')
                ->references('id')
                ->on('tarea')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table
                ->foreign('formulario_id', 'paso_ibfk_1')
                ->references('id')
                ->on('formulario')
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
        Schema::dropIfExists('paso');
    }
}
