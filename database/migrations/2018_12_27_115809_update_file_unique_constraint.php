<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFileUniqueConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file', function(Blueprint $table)
        {
            $table->dropUnique('filename_tipo');
            $table->unique(['tipo', 'tramite_id', 'filename'], 'tipo_tramiteid_filename');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file', function(Blueprint $table)
        {
            
            $table->dropUnique('tipo_tramiteid_filename');
            $table->unique(['filename', 'tipo'], 'filename_tipo');
        });
    }
}
