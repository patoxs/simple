<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToTermsAndConditionOnUsuarioBackend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuario_backend', function (Blueprint $table) {
            $table->boolean('acepta_terminos')
                ->after('remember_token')
                ->default(false)
                ->nullable(true);
            $table->timestamp('fecha_aceptacion_terminos')
                ->after('acepta_terminos')
                ->nullable(true);
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
            $table->dropColumn('acepta_terminos');
            $table->dropColumn('fecha_aceptacion_terminos');
        });

    }
}
