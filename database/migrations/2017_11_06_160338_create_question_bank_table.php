<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_bank', function (Blueprint $table) {
            $table->increments('qb_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('status');
            $table->string('create_uid');
            $table->dateTime('create_time');
            $table->string('update_uid');
            $table->dateTime('update_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_bank');
    }
}
