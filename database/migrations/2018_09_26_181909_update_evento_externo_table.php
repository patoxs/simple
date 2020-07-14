<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEventoExternoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE evento_externo MODIFY COLUMN mensaje TEXT NULL');
        DB::statement('ALTER TABLE evento_externo MODIFY COLUMN regla TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE evento_externo MODIFY COLUMN mensaje TEXT NOT NULL');
        DB::statement('ALTER TABLE evento_externo MODIFY COLUMN regla TEXT NOT NULL');
    }
}
