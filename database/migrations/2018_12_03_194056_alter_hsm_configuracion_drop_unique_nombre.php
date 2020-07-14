<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHsmConfiguracionDropUniqueNombre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hsm_configuracion', function (Blueprint $table) {
            $table->dropUnique('nombre_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hsm_configuracion', function (Blueprint $table) {
            $table->unique('nombre', 'nombre_UNIQUE');
        });
    }
}
