<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filename', 255);
            $table->enum('tipo', ['dato', 'documento']);
            $table->string('llave', 12);
            $table->string('llave_copia', 40)->nullable();
            $table->string('llave_firma', 12)->nullable();
            $table->unsignedInteger('validez')->nullable();
            $table->unsignedInteger('tramite_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->tinyInteger('validez_habiles')->nullable();

            $table->unique(['filename', 'tipo'], 'filename_tipo');

            $table->foreign('tramite_id', 'fk_file_tramite1')
                ->references('id')
                ->on('tramite')
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
        Schema::dropIfExists('file');
    }
}
