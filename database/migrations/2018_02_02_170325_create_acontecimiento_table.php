<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcontecimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acontecimiento', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('estado');
            $table->unsignedInteger('evento_externo_id');
            $table->unsignedInteger('etapa_id');

            $table->foreign('etapa_id', 'ac_etapa_foreign_key')
                ->references('id')
                ->on('etapa')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->foreign('evento_externo_id', 'ac_evento_externo_foreign_key')
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
        Schema::dropIfExists('acontecimiento');
    }
}
