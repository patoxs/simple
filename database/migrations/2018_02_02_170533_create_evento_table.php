<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->increments('id');
            $table->string('regla', 512);
            $table->enum('instante', ['antes', 'despues']);
            $table->unsignedInteger('tarea_id');
            $table->unsignedInteger('accion_id');
            $table->unsignedInteger('paso_id')->nullable();
            $table->unsignedInteger('evento_externo_id')->nullable();

            $table->index('accion_id', 'fk_evento_accion1');
            $table->index('paso_id', 'paso_id');

            $table->foreign('accion_id', 'evento_ibfk_1')
                ->references('id')
                ->on('accion')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table
                ->foreign('paso_id', 'evento_ibfk_2')
                ->references('id')
                ->on('paso')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table
                ->foreign('tarea_id', 'fk_evento_tarea1')
                ->references('id')
                ->on('tarea')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table
                ->foreign('evento_externo_id', 'fke_evento_externo_foreign_key')
                ->references('id')
                ->on('evento_externo')
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
        Schema::dropIfExists('evento');
    }
}
