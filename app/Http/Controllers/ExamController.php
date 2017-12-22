<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Exam;
use App\Models\Question;
use App\Service\Helper;
use ClassesWithParents\D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    //获取考试
    public function getExam(Request $request, $eid)
    {
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $examInfo = DB::table('exams as e')
            ->where('e.eid', $eid)
            ->select([
                'e.eid', 'e.name', 'e.total_time', 'e.description',
                'e.pass_score', 'e.total_score'
            ])
            ->first();
        $questionList = DB::table('questions as q')
            ->join('exam_contain as ec', 'ec.qid', '=', 'q.qid')
            ->where('ec.eid', $examInfo->eid)
            ->get()->toArray();
        foreach ($questionList as &$item){
            $item->option = json_decode($item->option);
            $item->answer = json_decode($item->answer);
        }
        unset($item);
        $info = [
            'examInfo' => $examInfo,
            'questionList' => $questionList
        ];
        return Helper::responseSuccess($info);
    }

    //新增考试
    public function createExam(Request $request)
    {
        $postData = $request->all();
        if (!(isset($postData['name']) && isset($postData['status']) && isset($postData['total_time'])
            && isset($postData['start_date_time']) && isset($postData['end_date_time']) && isset($postData['pass_score'])
            && isset($postData['description']) && isset($postData['is_auto']))) {
            throw new ApiException(20002);
        }
        $examInfo = [];
        $examInfo['name'] = $postData['name'];
        $examInfo['status'] = $postData['status'];
        $examInfo['total_time'] = $postData['total_time'];
        $examInfo['start_date_time'] = $postData['start_date_time'];
        $examInfo['end_date_time'] = $postData['end_date_time'];
        $examInfo['pass_score'] = $postData['pass_score'];
        $examInfo['description'] = $postData['description'];
        $examInfo['is_auto'] = $postData['is_auto'];
        $validator = Validator::make($examInfo, [
            'name' => 'required',
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])],
            'total_time' => 'required|integer|min:0',
            'start_date_time' => 'required|date',
            "end_date_time" => 'required|date|after:start_date_time',
            'pass_score' => 'required|integer',
            'description' => 'required',
            'is_auto' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20008);
        }
        $uid = Helper::getUid($request);
        switch ($postData['is_auto']) {
            case true://自动设置题目

                break;
            case false://手动设置每一道题目
                if (!isset($postData['question'])) {
                    throw new ApiException(20008);
                }
                $examInfo['question'] = $postData['question'];
                DB::transaction(function () use ($examInfo, $uid) {
                    $examModel = new Exam();
                    $examModel->name = $examInfo['name'];
                    $examModel->status = $examInfo['status'];
                    $examModel->total_time = $examInfo['total_time'];
                    $examModel->start_date_time = $examInfo['start_date_time'];
                    $examModel->end_date_time = $examInfo['end_date_time'];
                    $examModel->pass_score = $examInfo['pass_score'];
                    $examModel->description = $examInfo['description'];
                    $examModel->is_auto = $examInfo['is_auto'];
                    $examModel->create_uid = $uid;
                    $examModel->create_time = date('Y-m-d H:m:s', time());
                    $examModel->update_uid = $uid;
                    $examModel->update_time = date('Y-m-d H:m:s', time());
                    $result = $examModel->save();
                    if (!$result) {
                        throw new ApiException(20010);
                    }
                    $data = [];
                    $totalScore = 0;
                    foreach ($examInfo['question'] as $item) {
                        array_push($data, [
                            'eid' => $examModel->eid,
                            'qid' => $item['qid'],
                            'score' => $item['score']
                        ]);
                        $totalScore += $item['score'];
                    }
                    $examModel->total_score = $totalScore;
                    $result = $examModel->save();
                    if (!$result) {
                        throw new ApiException(20010);
                    }
                    $result = DB::table('exam_contain')
                        ->insert($data);
                    if (!$result) {
                        throw new ApiException(20010);
                    }
                });
                $info = '添加成功';
                return Helper::responseSuccess($info);
                break;
        }

    }

    //修改考试
    public function updateExam(Request $request, $eid)
    {
        $postData = $request->all();
        if (!(isset($postData['name']) && isset($postData['status']) && isset($postData['total_time'])
            && isset($postData['start_date_time']) && isset($postData['end_date_time']) && isset($postData['pass_score'])
            && isset($postData['description']))) {
            throw new ApiException(20002);
        }
        $examInfo = [];
        $examInfo['name'] = $postData['name'];
        $examInfo['status'] = $postData['status'];
        $examInfo['total_time'] = $postData['total_time'];
        $examInfo['start_date_time'] = $postData['start_date_time'];
        $examInfo['end_date_time'] = $postData['end_date_time'];
        $examInfo['pass_score'] = $postData['pass_score'];
        $examInfo['description'] = $postData['description'];
        $validator = Validator::make($examInfo, [
            'name' => 'required',
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])],
            'total_time' => 'required|integer|min:0',
            'start_date_time' => 'required|date',
            "end_date_time" => 'required|date|after:start_date_time',
            'pass_score' => 'required|integer',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20008);
        }
        $uid = Helper::getUid($request);
        if (!isset($postData['question'])) {
            throw new ApiException(20008);
        }
        $examInfo['question'] = $postData['question'];
        DB::transaction(function () use ($examInfo, $uid, $eid) {
            $examModel = Exam::where('eid', $eid)->first();
            $examModel->name = $examInfo['name'];
            $examModel->status = $examInfo['status'];
            $examModel->total_time = $examInfo['total_time'];
            $examModel->start_date_time = $examInfo['start_date_time'];
            $examModel->end_date_time = $examInfo['end_date_time'];
            $examModel->pass_score = $examInfo['pass_score'];
            $examModel->description = $examInfo['description'];
            $examModel->update_uid = $uid;
            $examModel->update_time = date('Y-m-d H:m:s', time());
            $data = [];
            $totalScore = 0;
            foreach ($examInfo['question'] as $item) {
                array_push($data, [
                    'eid' => $examModel->eid,
                    'qid' => $item['qid'],
                    'score' => $item['score']
                ]);
                $totalScore += $item['score'];
            }
            $examModel->total_score = $totalScore;
            $result = $examModel->save();
            if (!$result) {
                throw new ApiException(20010);
            }
            $result = DB::table('exam_contain')
                ->where('eid', $eid)
                ->delete();
            if (!$result) {
                throw new ApiException(20010);
            }
            $result = DB::table('exam_contain')
                ->insert($data);
            if (!$result) {
                throw new ApiException(20010);
            }
        });
        $info = '修改成功';
        return Helper::responseSuccess($info);

    }

    //删除考试
    public function deleteExam(Request $request, $eid)
    {
        $uid = Helper::getUid($request);
        if (!$uid) {
            throw new ApiException(20001);
        }
        $examModel = Exam::where('eid', $eid)->first();
        $examModel->status = 'DELETE';
        $examModel->update_uid = $uid;
        $examModel->update_time = date('Y-m-d H:m:s', time());
        $result = $examModel->save();
        if (!$result) {
            throw new ApiException(20007);
        }
        $info = '删除成功';
        return Helper::responseSuccess($info);
    }

    //管理员获取考试列表
    public function getAdminExamList(Request $request)
    {
        $postData = $request->all();
        if (!(isset($postData['page']) && isset($postData['num']))) {
            throw new ApiException(20002);
        }
        if (isset($postData['text'])){
            $name = '%'.$postData['text'].'%';
        }else{
            $name = '%';
        }
        $examList = Exam::where('status', '<>', 'DELETE')
            ->where('name', 'like', $name)
            ->paginate($postData['num']);
        return Helper::responseSuccess($examList);
    }

    //用户获取考试列表
    public function getUserExamList(Request $request)
    {
        $postData = $request->all();
        if (!(isset($postData['page']) && isset($postData['num']))) {
            throw new ApiException(20002);
        }
        $examList = Exam::where('status', '=', 'NORMAL')
            ->paginate($postData['num']);
        return Helper::responseSuccess($examList);
    }

    //用户获取试卷
    public function getUserExam($eid)
    {
        $examModel = Exam::where('eid', $eid)
            ->first();
        $examInfo['name'] = $examModel->name;
        $examInfo['description'] = $examModel->description;
        $examInfo['total_time'] = $examModel->total_time;
        $examInfo['pass_score'] = $examModel->pass_score;

        $questionList = DB::table('exam_contain as ec')
            ->join('questions as q', 'q.qid', '=', 'ec.qid')
            ->where('eid', $eid)
            ->select(['q.qid', 'q.type', 'q.detail', 'q.option', 'ec.score'])
            ->get()->toArray();
        $totalScore = 0;
        foreach ($questionList as &$item) {
            $totalScore += $item->score;
            $item->option = json_decode($item->option);
        }
        unset($item);
        $examInfo['total_score'] = $totalScore;
        $examInfo['questionList'] = $questionList;
        return Helper::responseSuccess($examInfo);
    }

    //用户提交试卷
    public function submitUserExam(Request $request, $eid)
    {
        $postData = $request->all();
        if (!isset($postData['answer'])) {
            throw new ApiException(20002);
        }
        $uid = Helper::getUid($request);

        $answer = $postData['answer'];
        $questionCount = DB::table('exam_contain')
            ->where('eid', $eid)
            ->get()
            ->count();
        $questionList = DB::table('exam_contain as ec')
            ->join('questions as q', 'ec.qid', '=', 'q.qid')
            ->where('eid', $eid)
            ->get()->toArray();
//        //计算answer数量
//        if ($questionCount != count($answer)) {
//            throw new ApiException(20012);
//        }

        $totalScore = 0;
        foreach ($questionList as $item) {
            if (json_decode($item->answer) == $answer[$item->qid]) {
                $totalScore += $item->score;
            }
        }
        DB::transaction(function () use ($uid, $eid, $answer, $totalScore) {
            $result = DB::table('user_exam_log')
                ->insert([
                    'eid' => $eid,
                    'answer' => json_encode($answer),
                    'score' => $totalScore,
                    'create_uid' => $uid,
                    'create_time' => date('Y-m-d H:m:s', time())
                ]);
            if (!$result) {
                throw new ApiException(20012);
            }
        });


        $info = [
            'info' => '提交成功',
            'score' => $totalScore,
        ];
        return Helper::responseSuccess($info);
    }

    public function getUserAttendExamList(Request $request)
    {
        $uid = Helper::getUid($request);
        if (!$uid) {
            throw new ApiException(20001);
        }
        $userExamList = DB::table('user_exam_log as uel')
            ->join('exams as e', 'uel.eid', '=', 'e.eid')
            ->where('uel.create_uid', $uid)
            ->select(['uel.id', 'e.name', 'uel.create_time', 'e.total_time',
                'e.pass_score', 'uel.score', 'e.status'])
            ->paginate(15);
        return Helper::responseSuccess($userExamList);
    }
    //用户考试统计
    public function getUserExamCount(Request $request){
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $totalTime = DB::table('user_exam_log')
            ->where('create_uid', $uid)
            ->count();
        $passTime = DB::table('exams as e')
            ->join('user_exam_log as uel', 'e.eid', '=', 'uel.eid')
            ->where('uel.create_uid', $uid)
            ->where('uel.score', '>', 'e.pass_score')
            ->count();
        $info = [
            'total_time' => $totalTime,
            'pass_time' => $passTime
        ];
        return Helper::responseSuccess($info);

    }

    //用户获取考试记录
    public function getUserExamLog(Request $request, $id){
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $userExamInfo = DB::table('user_exam_log as uel')
            ->join('exams as e', 'e.eid', '=', 'uel.eid')
            ->where('uel.id', $id)
            ->where('uel.create_uid', $uid)
            ->select(['uel.id', 'e.name', 'e.start_date_time', 'e.end_date_time',
                'e.total_time', 'e.total_score', 'uel.score'])
            ->first();
        return Helper::responseSuccess($userExamInfo);
    }

    //管理员获取考试记录
    public function getAdminExamLog(Request $request, $eid)
    {
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $examInfo = DB::table('exams as e')
            ->where('eid', $eid)
            ->select('e.name', 'e.start_date_time', 'e.end_date_time',
                'e.total_time', 'e.total_score')
            ->first();
        $totalNum = DB::table('user_exam_log')
            ->where('eid', $eid)
            ->count();
        $passNum = DB::table('user_exam_log as uel')
            ->join('exams as e', 'uel.eid', '=','e.eid')
            ->where('uel.eid', $eid)
            ->where('uel.score', '>', 'e.pass_score')
            ->count();
        $userLogList = DB::table('user_exam_log as uel')
            ->join('users as u', 'uel.create_uid', '=', 'u.uid')
            ->join('exams as e', 'uel.eid', '=', 'e.eid')
            ->where('uel.eid', $eid)
            ->select('u.real_name', 'uel.create_time', 'uel.score', 'e.total_score', 'e.pass_score')
            ->paginate(15);

        return Helper::responseSuccess([
            'exam_info' => $examInfo,
            'total_num' => $totalNum,
            'pass_num' => $passNum,
            'user_log_list' => $userLogList
        ]);
    }

    //获取用户考试记录详细信息
    public function getUserExamLogDetail(Request $request, $id){
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $examInfo = DB::table('user_exam_log as uel')
            ->join('exams as e', 'uel.eid', '=', 'e.eid')
            ->where('uel.id', $id)
            ->where('uel.create_uid', $uid)
            ->select([
                'e.eid', 'e.name', 'e.total_time', 'e.description',
                'e.pass_score', 'e.total_score', 'uel.answer as user_answer', 'uel.score as user_score'
            ])
            ->first();
        $examInfo->user_answer = json_decode($examInfo->user_answer);
        $questionList = DB::table('questions as q')
            ->join('exam_contain as ec', 'ec.qid', '=', 'q.qid')
            ->where('ec.eid', $examInfo->eid)
            ->get()->toArray();
        foreach ($questionList as &$item){
            $item->option = json_decode($item->option);
            $item->answer = json_decode($item->answer);
        }
        unset($item);
        $info = [
            'examInfo' => $examInfo,
            'questionList' => $questionList
        ];
        return Helper::responseSuccess($info);
    }

    //管理员获取考试信息
    public function getAdminExamInfo(Request $request, $eid)
    {
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $examInfo = DB::table('exams')
            ->where('eid', $eid)
            ->first();
        $examInfo->question = DB::table('exam_contain as ec')
            ->join('questions as q', 'ec.qid', '=', 'q.qid')
            ->where('ec.eid', $eid)
            ->select(['ec.qid', 'ec.score', 'q.detail'])
            ->get();

        return Helper::responseSuccess($examInfo);
    }

}
