<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    //获取考试
    public function getExam()
    {

    }
    //新增考试
    public function createExam(Request $request)
    {
        $jsonData = $request->all();
        if (!($jsonData['name']) && isset($jsonData['status']) && isset($jsonData['total_time'])
            && isset($jsonData['start_date_time']) && isset($jsonData['end_date_time']) && isset($jsonData['pass_score'])
            && isset($jsonData['description']) && isset($jsonData['is_auto'])){
            throw new ApiException(20002);
        }
        $examInfo = [];
        $examInfo['name'] = $jsonData['name'];
        $examInfo['status'] = $jsonData['status'];
        $examInfo['total_time'] = $jsonData['total_time'];
        $examInfo['start_date_time'] = $jsonData['start_date_time'];
        $examInfo['end_date_time'] = $jsonData['end_date_time'];
        $examInfo['description'] = $jsonData['description'];
        $examInfo['is_auto'] = $jsonData['is_auto'];
        $validator = Validator::make($examInfo, [
            'name' => 'required',
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])],
            'total_time' => 'required|integer|min:0',
            'start_date_time' => 'required|date',
            "end_date_time" => 'required|date|after:start_date_time',
            'description' => 'required',
            'is_auto' => 'required|boolean'
        ]);
        if ($validator->fails()){
            throw new ApiException(20008);
        }
    }
    //修改考试
    public function updateExam()
    {

    }
    //删除考试
    public function deleteExam()
    {

    }
}
