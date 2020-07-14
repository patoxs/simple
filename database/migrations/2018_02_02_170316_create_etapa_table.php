<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEtapaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etapa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tarea_id');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->tinyInteger('pendiente');
            $table->unsignedInteger('etapa_ancestro_split_id')->nullable();
            $table->date('vencimiento_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('tramite_id');

            $table->index('tramite_id', 'fk_etapa_tramite1');
            $table->index('etapa_ancestro_split_id', 'etapa_ancestro_split_id');

            $table->foreign('tramite_id', 'etapa_ibfk_1')
                ->references('id')
                ->on('tramite')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('etapa_ancestro_split_id', 'etapa_ibfk_2')
                ->references('id')
                ->on('etapa')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
            $table->foreign('tarea_id', 'fk_etapa_tarea1')
                ->references('id')
                ->on('tarea')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('usuario_id', 'fk_etapa_usuario1')
                ->references('id')
                ->on('usuario')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etapa');
    }
}
