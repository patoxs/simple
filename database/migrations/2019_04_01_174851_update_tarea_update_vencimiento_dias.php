<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTareaUpdateVencimientoDias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE tarea MODIFY COLUMN vencimiento_valor VARCHAR(128) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE tarea MODIFY COLUMN vencimiento_valor INT(10) UNSIGNED NOT NULL DEFAULT 5');
    }
}
