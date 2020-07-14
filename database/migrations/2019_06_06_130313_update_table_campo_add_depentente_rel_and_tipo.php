<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableCampoAddDepententeRelAndTipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       DB::statement("ALTER TABLE campo CHANGE COLUMN dependiente_tipo dependiente_tipo ENUM('string', 'regex', 'numeric') NOT NULL default 'string'");

       DB::statement("ALTER TABLE campo CHANGE COLUMN dependiente_relacion dependiente_relacion ENUM('==', '!=', '>', '<', '>=', '<=') NOT NULL default '=='");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         DB::statement('ALTER TABLE campo CHANGE COLUMN dependiente_tipo NOT NULL');
         DB::statement('ALTER TABLE campo CHANGE COLUMN dependiente_relacion NOT NULL');
    }
}
