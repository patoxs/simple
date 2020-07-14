<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 32);
            $table->tinyInteger('readonly')->default(0);
            $table->text('valor_default');
            $table->unsignedInteger('posicion');
            $table->string('tipo', 32);
            $table->unsignedInteger('formulario_id');
            $table->text('etiqueta');
            $table->text('ayuda');
            $table->string('validacion', 128);
            $table->enum('dependiente_tipo', ['string', 'regex'])->nullable()->default('string');
            $table->string('dependiente_campo', 64)->nullable();
            $table->string('dependiente_valor', 256)->nullable();
            $table->text('datos')->nullable();
            $table->unsignedInteger('documento_id')->nullable();
            $table->text('extra')->nullable();
            $table->enum('dependiente_relacion', ['==', '!='])->nullable()->default('==');
            $table->bigInteger('agenda_campo')->nullable();
            $table->integer('exponer_campo')->default(0);

            $table->index('formulario_id', 'fk_campo_formulario1');
            $table->index('documento_id', 'fk_campo_documento1');

            $table->foreign('formulario_id', 'campo_ibfk_1')
                ->references('id')
                ->on('formulario')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('documento_id', 'campo_ibfk_2')
                ->references('id')
                ->on('documento')
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
        Schema::dropIfExists('campo');
    }
}
