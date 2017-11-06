<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['check.auth'])->group(function () {
    Route::get('/uuid', [function () {
        return \Ramsey\Uuid\Uuid::uuid1();
    }, 'permission' => 'prm']);
    //用户个人信息获取接口
    Route::get('my/info', 'UserController@getUserInfo');
    //用户设置个人信息接口
    Route::post('my/info', 'UserController@updateUserInfo');
});
//登录接口
Route::get('/auth/login', 'AuthController@login');
//注册接口
Route::post('/user', 'UserController@register');