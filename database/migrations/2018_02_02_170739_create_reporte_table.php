<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReporteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reporte', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->text('campos');
            $table->unsignedInteger('proceso_id');

            $table->index('proceso_id', 'fk_reporte_proceso1');

            $table->foreign('proceso_id', 'reporte_ibfk_1')
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
        Schema::dropIfExists('reporte');
    }
}
