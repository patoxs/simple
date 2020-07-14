<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCampoAddColumnCondicionesExtra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campo', function (Blueprint $table) {
            $table->text('condiciones_extra_visible')->after('dependiente_relacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campo', function (Blueprint $table) {
            $table->dropColumn('condiciones_extra_visible');
        });
    }
}
