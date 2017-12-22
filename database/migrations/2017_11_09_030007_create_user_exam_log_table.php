<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExamLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_exam_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('eid');
            $table->json('answer')->comment('[{"tid":1,"user_answer":2}]');
            $table->integer('score');
            $table->string('create_uid');
            $table->dateTime('create_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_exam_log');
    }
}
