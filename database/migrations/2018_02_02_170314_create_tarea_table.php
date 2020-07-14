<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTareaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarea', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identificador', 32);
            $table->tinyInteger('inicial')->default(0);
            $table->string('nombre', 128);
            $table->unsignedInteger('posx')->default(0);
            $table->unsignedInteger('posy')->default(0);
            $table->enum('asignacion', ['ciclica', 'manual', 'autoservicio', 'usuario'])->default('ciclica');
            $table->string('asignacion_usuario', 128)->nullable();
            $table->tinyInteger('asignacion_notificar')->default(0);
            $table->unsignedInteger('proceso_id');
            $table->tinyInteger('almacenar_usuario')->default(0);
            $table->string('almacenar_usuario_variable', 128)->nullable();
            $table->enum('acceso_modo', ['grupos_usuarios', 'publico', 'registrados', 'claveunica'])->default('grupos_usuarios');
            $table->enum('activacion', ['si', 'no', 'entre_fechas'])->default('si');
            $table->date('activacion_inicio')->nullable();
            $table->date('activacion_fin')->nullable();
            $table->tinyInteger('vencimiento')->default(0);
            $table->unsignedInteger('vencimiento_valor')->default(5);
            $table->enum('vencimiento_unidad', ['D', 'W', 'M', 'Y']);
            $table->tinyInteger('vencimiento_habiles')->default(0);
            $table->tinyInteger('vencimiento_notificar')->default(0);
            $table->string('vencimiento_notificar_email', 255)->nullable();
            $table->unsignedInteger('vencimiento_notificar_dias')->default(1);
            $table->text('grupos_usuarios')->nullable();
            $table->tinyInteger('paso_confirmacion')->default(1);
            $table->text('previsualizacion')->nullable();
            $table->tinyInteger('externa')->default(0);
            $table->tinyInteger('es_final')->default(0);
            $table->tinyInteger('exponer_tramite')->nullable();

            $table->unique(['identificador', 'proceso_id'], 'identificador_proceso');
            $table->index('proceso_id', 'fk_tarea_proceso1');

            $table->foreign('proceso_id', 'tarea_ibfk_1')
                ->references('id')
                ->on('proceso')
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
        Schema::dropIfExists('tarea');
    }
}
