<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documento', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('tipo', ['blanco', 'certificado'])->default('blanco');
            $table->string('nombre', 128);
            $table->text('contenido');
            $table->string('servicio', 128);
            $table->string('servicio_url', 128);
            $table->string('logo', 256);
            $table->string('timbre', 256);
            $table->string('firmador_nombre', 128);
            $table->string('firmador_cargo', 128);
            $table->string('firmador_servicio', 128);
            $table->string('firmador_imagen', 256);
            $table->unsignedInteger('validez')->nullable();
            $table->unsignedInteger('hsm_configuracion_id')->nullable();
            $table->unsignedInteger('proceso_id');
            $table->string('subtitulo', 128);
            $table->string('titulo', 128);
            $table->tinyInteger('validez_habiles')->nullable();
            $table->enum('tamano', ['letter', 'legal'])->nullable()->default('letter');

            $table->index('hsm_configuracion_id', 'hsm_configuracion_id');

            $table->foreign('hsm_configuracion_id', 'documento_ibfk_1')
                ->references('id')
                ->on('hsm_configuracion')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
            $table->foreign('proceso_id', 'fk_documento_proceso1')
                ->references('id')
                ->on('proceso')
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
        Schema::dropIfExists('documento');
    }
}
