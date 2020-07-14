<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigGeneralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_general', function (Blueprint $table) {
            $table->string('componente', 45);
            $table->integer('cuenta');
            $table->string('llave', 80);
            $table->string('valor', 256)->nullable();

            $table->primary(['componente', 'cuenta', 'llave']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_general');
    }
}
