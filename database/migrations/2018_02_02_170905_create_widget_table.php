<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWidgetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo', 32);
            $table->string('nombre', 128);
            $table->unsignedInteger('posicion');
            $table->text('config')->nullable();
            $table->unsignedInteger('cuenta_id');

            $table->index('cuenta_id', 'fk_widget_cuenta1');

            $table->foreign('cuenta_id', 'widget_ibfk_1')
                ->references('id')->on('cuenta')->onDelete('CASCADE')
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
        Schema::dropIfExists('widget');
    }
}
