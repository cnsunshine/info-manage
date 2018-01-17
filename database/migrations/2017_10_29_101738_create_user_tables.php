<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid');
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('real_name')->nullable();
            $table->string('email')->nullable();
            $table->string('tel')->nullable();
            $table->dateTime('last_login')->nullable();
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
        Schema::dropIfExists('users');
    }
}
