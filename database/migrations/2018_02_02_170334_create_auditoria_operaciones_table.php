<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditoriaOperacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditoria_operaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('fecha');
            $table->string('motivo', 512)->nullable();
            $table->text('detalles');
            $table->string('operacion', 128)->nullable();
            $table->string('usuario', 390);
            $table->string('proceso', 128);
            $table->unsignedInteger('cuenta_id');

            $table->foreign('cuenta_id', 'fk_cuenta')
                ->references('id')
                ->on('cuenta')
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
        Schema::dropIfExists('auditoria_operaciones');
    }
}
