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
    /**
     * 个人信息
     */
    //##########用户##########
    //用户个人信息获取接口
    Route::get('/my/info', 'UserController@getUserInfo');
    //用户设置个人信息接口
    Route::post('/my/info', 'UserController@updateUserInfo');
    //修改密码
    Route::post('/my/password', 'UserController@changePassword');
    /**
     * 题库
     */
    //增加题库
    Route::post('/question/bank', [
        'uses' => 'QuestionController@createQuestionBank'
    ]);
    //获取题库
    Route::get('/question/bank/{qb_id}', [
        'uses' => 'QuestionController@getQuestionBank'
    ])->where('qb_id', '[0-9]+');
    //获取题库列表
    Route::get('/question/bank/list', [
        'uses' => 'QuestionController@getQuestionBankList'
    ]);
    //更新题库
    Route::post('/question/bank/{qb_id}', [
        'uses' => 'QuestionController@updateQuestionBank'
    ])->where('qt_id', '[0-9]+');
    //删除题库
    Route::delete('/question/bank/{qb_id}', [
        'uses' => 'QuestionController@deleteQuestionBank'
    ])->where('qt_id', '[0-9]+');
    /**
     * 题目
     */
    #####用户#####
    //获取题目列表
    Route::get('/question/list/{qb_id}', [
        'uses' => 'QuestionController@getQuestionList'
    ])->where('qb_id', '[0-9]+');
    //获取题目
    Route::get('/question/{qid}', [
        'uses' => 'QuestionController@getQuestion'
    ])->where('qid', '[0-9]+');
    //增加题目
    Route::post('/question', [
        'uses' => 'QuestionController@createQuestion'
    ]);
    //修改题目
    Route::post('/question/{qid}', [
        'uses' => 'QuestionController@updateQuestion'
    ])->where('qid', '[0-9]+');
    //删除题目
    Route::delete('/question/{qid}', [
        'uses' => 'QuestionController@deleteQuestion'
    ])->where('qid', '[0-9]+');
    /**
     * 试卷
     */
    //##########用户##########
    //获取试卷列表
    Route::get('/my/exam/list', [
        'uses' => 'ExamController@getUserExamList'
    ]);
    //获取试卷
    Route::get('/my/exam/{eid}', [
        'uses' => 'ExamController@getUserExam'
    ])->where('eid', '[0-9]+');
    //提交试卷
    Route::post('/my/exam/{eid}', [
        'uses' => 'ExamController@submitUserExam'
    ])->where('eid', '[0-9]+');
    //获取统计信息
    Route::get('my/exam/count', [
        'uses' => 'ExamController@getUserExamCount'
    ]);
    //获取试卷记录
    Route::get('my/exam/log/{id}', [
        'uses' => 'ExamController@getUserExamLog'
    ])->where('id', '[0-9]+');
    //获取试卷记录详情
    Route::get('/my/exam/detail/{id}', [
        'uses' => 'ExamController@getUserExamLogDetail'
    ])->where('id', '[0-9]+');
    //获取用户已经参加的考试
    Route::get('/my/exam', [
        'uses' => 'ExamController@getUserAttendExamList'
    ]);
    //##########后台##########
    //增加试卷
    Route::post('/exam', [
        'uses' => 'ExamController@createExam'
    ]);
    //修改试卷
    Route::post('/exam/{eid}', [
        'uses' => 'ExamController@updateExam'
    ])->where('eid', '[0-9]+');
    //删除试卷
    Route::delete('/exam/{eid}', [
        'uses' => 'ExamController@deleteExam'
    ])->where('eid', '[0-9]+');
    //获取考试列表
    Route::get('/admin/exam/list', [
        'uses' => 'ExamController@getAdminExamList'
    ]);
    //获取考试信息
    Route::get('/admin/exam/setting/{eid}', [
        'uses' => 'ExamController@getAdminExamInfo'
    ])->where('eid', '[0-9]+');
    //获取试卷信息和用户考试记录
    Route::get('/admin/exam/detail/{eid}', [
        'uses' => 'ExamController@getAdminExamLog'
    ])->where('eid', '[0-9]+');
    //获取试卷
    Route::get('/exam/{eid}', [
        'uses' => 'ExamController@getExam'
    ])->where('eid', '[0-9]+');
});
//登录接口
Route::get('/auth/login', 'AuthController@login');
//检查用户存在性
Route::get('/user/if_has/{username}', [
    'uses' => 'UserController@ifHasUser'
])->where('username', '[a-zA-Z0-9]+');
//注册接口
Route::post('/user', 'UserController@register');

##################微信接口
Route::any('/wechat', 'WechatController@serve');
//增加模板
Route::post('/wechat/template/{id}', 'WechatController@add_template');
Route::post('/wechat/template/message', 'WechatController@template_message');
Route::get('/wechat/oauth', 'WechatController@oauth');
Route::get('/wechat/oauth/callback', 'WechatController@oauth_callback');
Route::get('/wechat/rabbitmq', 'WechatController@rabbitmq');
Route::get('/wechat/rabbitmq/consumer', 'WechatController@consumer_rabbitmq');
Route::get('/wechat/rabbitmq/get', 'WechatController@get_rabbitmq');
Route::get('/wechat/menu/list', 'WechatController@get_menu_list');
Route::get('/wechat/menu/current', 'WechatController@get_menu_current');
Route::get('/wechat/menu/create', 'WechatController@create_menu');
Route::delete('/wechat/menu', 'WechatController@delete_menu');
Route::get('/wechat/template_message/private_template', 'WechatController@private_template');
Route::post('/wechat/user/bind', 'WechatController@bind_user');
//发送客服消息
Route::post('/wechat/customer/service', 'WechatController@customer_service');

//测试接口
Route::get('/test', 'WechatController@test');

Route::get('/route/info/{id}', 'OpenController@get_access_token');