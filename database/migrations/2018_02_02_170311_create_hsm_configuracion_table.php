<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHsmConfiguracionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hsm_configuracion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128);
            $table->unsignedInteger('cuenta_id');
            $table->string('entidad', 256)->nullable();
            $table->string('proposito', 64)->nullable();

            $table->unique('nombre', 'nombre_UNIQUE');

            $table->foreign('cuenta_id', 'fk_hsm_configuracion_cuenta1')
                ->references('id')
                ->on('cuenta')
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
        Schema::dropIfExists('hsm_configuracion');
    }
}
