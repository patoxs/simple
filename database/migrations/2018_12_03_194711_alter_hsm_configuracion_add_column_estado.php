<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHsmConfiguracionAddColumnEstado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('hsm_configuracion', function (Blueprint $table) {
            $table->enum('estado',['0', '1'])->default('1');

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
            $table->dropColumn(estado);
        });
    }
}
