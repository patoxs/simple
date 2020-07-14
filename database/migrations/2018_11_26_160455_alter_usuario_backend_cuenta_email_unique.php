<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsuarioBackendCuentaEmailUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('usuario_backend', function (Blueprint $table) {
            $table->unique(['cuenta_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuario_backend', function (Blueprint $table) {
            $table->dropUnique(['cuenta_id', 'email']);
        });
    }
}
