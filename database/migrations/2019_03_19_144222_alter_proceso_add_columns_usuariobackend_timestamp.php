<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProcesoAddColumnsUsuariobackendTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proceso', function (Blueprint $table) {
            $table->unsignedInteger('usuario_id')->after('url_informativa')->nullable();
            $table->dateTime('created_at')->after('usuario_id')->nullable();
            $table->dateTime('updated_at')->after('created_at')->nullable();

            $table->foreign('usuario_id', 'fk_proceso_usuario1')
                ->references('id')
                ->on('usuario_backend')
                ->onDelete('set null')
                ->onUpdate('CASCADE');
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
            $table->dropColumn('usuario_id');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
