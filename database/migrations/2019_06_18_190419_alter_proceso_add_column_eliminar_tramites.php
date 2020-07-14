<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProcesoAddColumnEliminarTramites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Columna que permite que el proceso tenga la opción de que sus trámites puedan ser eliminados(logicamente) por parte del usuario
        Schema::table('proceso', function (Blueprint $table) {
            $table->boolean('eliminar_tramites')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proceso', function (Blueprint $table) {
            $table->dropColumn('eliminar_tramites');
        });
    }
}
