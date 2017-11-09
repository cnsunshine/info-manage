<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->increments('eid');
            $table->string('name');
            $table->string('status');
            $table->integer('total_time');
            $table->dateTime('start_date_time');
            $table->dateTime('end_date_time');
            $table->integer('pass_score');
            $table->string('description');
            $table->boolean('is_auto');
            $table->string('create_uid');
            $table->dateTime('create_time');
            $table->string('update_uid');
            $table->dateTime('update_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam');
    }
}
