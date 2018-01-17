<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWeixinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_weixin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid')->nullable();
            $table->integer('subscribe')->nullable();
            $table->string('openid');
            $table->string('nickname')->nullable();
            $table->integer('sex')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('language')->nullable();
            $table->string('headimgurl')->nullable();
            $table->string('subscribe_time')->nullable();
            $table->string('unionid')->nullable();
            $table->string('remark')->nullable();
            $table->string('groupid')->nullable();
            $table->string('tagid_list')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_weixin');
    }
}
