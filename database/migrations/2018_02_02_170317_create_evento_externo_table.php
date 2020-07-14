<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventoExternoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evento_externo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->enum('metodo', ['GET', 'POST', 'PUT'])->nullable();
            $table->string('url', 256);
            $table->text('mensaje');
            $table->text('regla');
            $table->unsignedInteger('tarea_id');
            $table->text('opciones')->nullable();

            $table->foreign('tarea_id', 'eetarea_foreign_key')
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
        Schema::dropIfExists('evento_externo');
    }
}
