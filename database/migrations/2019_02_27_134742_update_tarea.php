<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTarea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tarea', function (Blueprint $table) {
            $table->string('paso_confirmacion_titulo')->nullable()->after('paso_confirmacion');
            $table->text('paso_confirmacion_contenido')->nullable()->after('paso_confirmacion_titulo');
            $table->string('paso_confirmacion_texto_boton_final')->nullable()->after('paso_confirmacion_contenido');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tarea', function (Blueprint $table) {
            $table->dropColumn('paso_confirmacion_titulo');
            $table->dropColumn('paso_confirmacion_contenido');
            $table->dropColumn('paso_confirmacion_texto_boton_final');
        });
    }
}
