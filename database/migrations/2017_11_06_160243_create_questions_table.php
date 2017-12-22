<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('qid');
            $table->string('detail')->comment('题干');
            $table->string('description')->nullable()->comment('备注');
            $table->string('status');
            $table->string('classification')->comment('题目分类');
            $table->string('type')->comment('题型');
            $table->json('option')->comment("[\"选项一","选项二\"]");
            $table->json('answer')->comment("[0]");
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
        Schema::dropIfExists('questions');
    }
}
