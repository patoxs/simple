<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('queue');
            $table->dropColumn('payload');
            $table->dropColumn('attempts');
            $table->dropColumn('reserved_at');
            $table->dropColumn('available_at');
            
            $table->integer('user_id')->index();
            $table->enum('user_type', ['frontend', 'backend']);
            $table->text('extra')->nullable();
            
            $table->string('filename', 255)->nullable();
            $table->string('filepath', 255)->nullable();

            $table->mediumText('arguments')->nullable();
            $table->enum('status', ['created', 'running', 'error', 'finished'])->nullable();
            $table->integer('downloads')->default(0);
            $table->dateTime('updated_at')->nullable();
        });
        DB::statement('ALTER TABLE `jobs` MODIFY created_at DATETIME DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('user_type');
            $table->dropColumn('extra');
            $table->dropColumn('arguments');
            $table->dropColumn('status');
            $table->dropColumn('updated_at');
            $table->dropColumn('filename');
            $table->dropColumn('filepath');
            $table->dropColumn('downloads');

            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
        });
        DB::statement('ALTER TABLE `jobs` MODIFY created_at int(10) NOT NULL');
    }
}
