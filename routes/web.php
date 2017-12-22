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
])->where('username', '[a-zA-Z]+');
//注册接口
Route::post('/user', 'UserController@register');