<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsForFichaInformativaOnProcesoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proceso', function (Blueprint $table) {
            $table->boolean('ficha_informativa')->default(false);
            $table->string('ficha_titulo', 128)->nullable();
            $table->text('ficha_contenido')->nullable();
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
            $table->dropColumn('ficha_informativa');
            $table->dropColumn('ficha_titulo');
            $table->dropColumn('ficha_contenido');
        });
    }
}
