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
    Route::get('/index', function () {
        return \Ramsey\Uuid\Uuid::uuid1();
    });
    Route::get('/info/{id}', 'UserController@info')
        ->where('id', '[0-9]+');
});
//登录接口
Route::get('/auth/login', 'AuthController@login');
//注册接口
Route::post('/user', 'UserController@register');