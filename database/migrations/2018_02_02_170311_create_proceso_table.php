<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcesoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proceso', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 128)->nullable();
            $table->string('width', 8)->default('100%');
            $table->string('height', 8)->default('800px');
            $table->unsignedInteger('cuenta_id');
            $table->integer('proc_cont')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->unsignedInteger('categoria_id')->nullable();
            $table->integer('destacado')->nullable();
            $table->string('icon_ref', 256)->nullable();
            $table->integer('version')->nullable();
            $table->integer('root')->nullable();
            $table->string('estado', 255)->default('public');

            $table->index('cuenta_id', 'fk_proceso_cuenta1');
            $table->index('categoria_id', 'fk_categoria');

            $table->foreign('cuenta_id', 'proceso_ibfk_1')
                ->references('id')->on('cuenta')
                ->onDelete('CASCADE')
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
        Schema::dropIfExists('proceso');
    }
}
