<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatoSeguimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dato_seguimiento', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->mediumText('valor');
            $table->unsignedInteger('etapa_id');

            $table->unique(['nombre', 'etapa_id'], 'nombre_etapa');

            $table->foreign('etapa_id', 'fk_dato_seguimiento_etapa1')
                ->references('id')
                ->on('etapa')
                ->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dato_seguimiento');
    }
}
