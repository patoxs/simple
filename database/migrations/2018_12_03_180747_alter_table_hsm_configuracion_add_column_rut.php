<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableHsmConfiguracionAddColumnRut extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hsm_configuracion', function (Blueprint $table) {
            $table->addColumn('integer', 'rut', ['unsigned' => true, 'length' => 8])->after('id');
            
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
            $table->dropColumn('rut');
        });
    }
}
