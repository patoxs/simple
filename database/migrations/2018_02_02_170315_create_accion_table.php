<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->string('tipo', 32);
            $table->text('extra')->nullable();
            $table->unsignedInteger('proceso_id');
            $table->integer('exponer_variable')->default(0);

            $table->foreign('proceso_id', 'fk_trigger_proceso1')
                ->references('id')->on('proceso')
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
        Schema::dropIfExists('accion');
    }
}
