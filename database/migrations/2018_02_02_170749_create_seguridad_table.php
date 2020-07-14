<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeguridadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seguridad', function (Blueprint $table) {
            $table->increments('id');
            $table->string('institucion', 128)->nullable();
            $table->string('servicio', 128)->nullable();
            $table->text('extra')->nullable();
            $table->integer('proceso_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seguridad');
    }
}
