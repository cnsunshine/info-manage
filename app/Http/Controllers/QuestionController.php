<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Service\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    //创建题库
    public function createQuestionBank(Request $request)
    {
        $postData = $request->all();
        if (!(isset($postData['name']) && isset($postData['status']))) {
            throw new ApiException(20002);
        }
        $uid = Helper::getUid($request);
        $questionBankInfo = [];
        $questionBankInfo['name'] = $postData['name'];
        $questionBankInfo['status'] = $postData['status'];
        $validator = Validator::make($questionBankInfo, [
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])]
        ]);
        if ($validator->fails()) {
            throw new ApiException(20008);
        }
        $questionBankModel = new QuestionBank();
        $questionBankModel->name = $questionBankInfo['name'];
        $questionBankModel->status = $postData['status'];
        $questionBankModel->create_uid = $uid;
        $questionBankModel->create_time = date('Y-m-d H:i:s', time());
        $questionBankModel->update_uid = $uid;
        $questionBankModel->update_time = date('Y-m-d H:i:s', time());
        isset($postData['description']) ? $questionBankModel->description : null;
        $result = $questionBankModel->save();
        if (!$result) {
            throw new ApiException(20007);
        }
        return Helper::responseSuccess([
            'info' => '添加成功'
        ]);
    }

    //获取题库列表
    public function getQuestionBankList(Request $request)
    {
        $postData = $request->all();
        $questionBankList = QuestionBank::where('status', '<>', 'DELETE')
            ->paginate($postData['num']);
        return Helper::responseSuccess($questionBankList);
    }
    //获取题库
    public function getQuestionBank($qb_id)
    {
        $questionList = Question::from('questions as q')
            ->join('question_bank_contain as qbc', 'q.qid', '=', 'qbc.qid')
            ->where('qbc.qb_id', $qb_id)
            ->paginate(10);
        return Helper::responseSuccess($questionList);
    }
    //修改题库
    public function updateQuestionBank(Request $request, $qb_id)
    {
        $postData = $request->all();
        if (!(isset($qb_id) && isset($postData['name']) && isset($postData['status']))) {
            throw new ApiException(20002);
        }
        $uid = Helper::getUid($request);
        $questionBankInfo = [];
        $questionBankInfo['name'] = $postData['name'];
        $questionBankInfo['status'] = $postData['status'];
        $validator = Validator::make($questionBankInfo, [
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])]
        ]);
        if ($validator->fails()) {
            throw new ApiException(20008);
        }
        $questionBankModel = QuestionBank::where('qb_id', $qb_id)->first();
        $questionBankModel->name = $postData['name'];
        $questionBankModel->status = $postData['status'];
        isset($postData['description']) ? $questionBankModel->description = $postData['description'] : null;
        $questionBankModel->update_uid = $uid;
        $questionBankModel->update_time = date('Y-m-d H:i:s', time());
        $result = $questionBankModel->save();
        if (!$result) {
            throw new ApiException(20007);
        }
        return Helper::responseSuccess(['info' => '修改成功']);
    }

    //删除题库
    public function deleteQuestionBank(Request $request, $qb_id)
    {
        if (!isset($qb_id)) {
            throw new ApiException(20002);
        }
        $questionBankModel = QuestionBank::where('qb_id', $qb_id)->first();
        $questionBankModel->status = 'DELETE';
        $result = $questionBankModel->save();
        if (!$result) {
            throw new ApiException(20007);
        }
        return Helper::responseSuccess(['info' => '删除成功']);
    }
    //获取题目列表
    public function getQuestionList(Request $request, $qb_id)
    {
        $postData = $request->all();
        $questionList = DB::table('question_bank_contain as qc')
            ->join('questions as q', 'qc.qid', '=', 'q.qid')
            ->where('qb_id', $qb_id)
            ->paginate(15);
        return Helper::responseSuccess($questionList);
    }
    //获取题目
    public function getQuestion($qid)
    {
        $questionModel = Question::where('qid', $qid)->first();
        if (!$questionModel){
            throw new ApiException(20009);
        }
        $questionInfo = [
            'qid' => $questionModel['qid'],
            'detail' => $questionModel['detail'],
            'description' => $questionModel['description'],
            'status' => $questionModel['status'],
            'classification' => $questionModel['classification'],
            'type' => $questionModel['type'],
            'option' => json_decode($questionModel['option']),
            'answer' => json_decode($questionModel['answer']),
            'create_uid' => $questionModel['create_uid'],
            'create_time' => $questionModel['create_time'],
            'update_uid' => $questionModel['update_uid'],
            'update_time' => $questionModel['update_time']
        ];
        return Helper::responseSuccess($questionInfo);
    }
    //增加题目
    public function createQuestion(Request $request)
    {
        $postData = $request->all();
        if (!(isset($postData['status']) && isset($postData['type'])
            && isset($postData['classification']) && isset($postData['detail'])
            && isset($postData['option']) && isset($postData['answer'])
            && isset($postData['qb_id']))) {
            throw new ApiException(20002);
        }
        $uid = Helper::getUid($request);
        $questionInfo = [];
        $questionInfo['qb_id'] = $postData['qb_id'];
        $questionInfo['status'] = $postData['status'];
        $questionInfo['type'] = $postData['type'];
        $questionInfo['classification'] = $postData['classification'];
        $questionInfo['detail'] = $postData['detail'];
        $questionInfo['option'] = $postData['option'];
        $questionInfo['answer'] = $postData['answer'];
        isset($postData['description']) ? $questionInfo['description'] = $postData['description'] : null;
        $validator = Validator::make($questionInfo, [
            'qb_id' => 'required|exists:question_bank,qb_id',
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])],
            'type' => ['required', Rule::in(['SINGLE', 'MULTI'])],
            'classification' => 'required',
            'detail' => 'required',
            'option' => 'required',
            'answer' => 'required',
            'description' => 'nullable'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20008);
        }
        DB::transaction(function () use ($questionInfo, $uid){
            $questionModel = new Question();
            $questionModel->status = $questionInfo['status'];
            $questionModel->type = $questionInfo['type'];
            $questionModel->classification = $questionInfo['classification'];
            $questionModel->detail = $questionInfo['detail'];
            $questionModel->option = json_encode($questionInfo['option']);
            $questionModel->answer = json_encode($questionInfo['answer']);
            isset($questionInfo['description']) ? $questionModel->description = $questionInfo['description'] : null;
            $questionModel->create_uid = $uid;
            $questionModel->create_time = date('Y-m-d H:m:s', time());
            $questionModel->update_uid = $uid;
            $questionModel->update_time = date('Y-m-d H:m:s', time());
            $result = $questionModel->save();
            if (!$result) {
                throw new ApiException(20007);
            }
            DB::table('question_bank_contain')
                ->insert(['qb_id' => $questionInfo['qb_id'], 'qid' => $questionModel->qid]);
        });
        return Helper::responseSuccess([
            'info' => '添加成功'
        ]);
    }
    //修改题目
    public function updateQuestion(Request $request, $qid){
        $postData = $request->all();
        if (!(isset($postData['status']) && isset($postData['type'])
            && isset($postData['classification']) && isset($postData['detail'])
            && isset($postData['option']) && isset($postData['answer']))) {
            throw new ApiException(20002);
        }
        $uid = Helper::getUid($request);
        $questionInfo = [];
        $questionInfo['status'] = $postData['status'];
        $questionInfo['type'] = $postData['type'];
        $questionInfo['classification'] = $postData['classification'];
        $questionInfo['detail'] = $postData['detail'];
        $questionInfo['option'] = $postData['option'];
        $questionInfo['answer'] = $postData['answer'];
        isset($postData['description']) ? $questionInfo['description'] = $postData['description'] : null;
        $validator = Validator::make($questionInfo, [
            'status' => ['required', Rule::in(['NORMAL', 'LOCK'])],
            'type' => ['required', Rule::in(['SINGLE', 'MULTI'])],
            'classification' => 'required',
            'detail' => 'required',
            'option' => 'required',
            'answer' => 'required',
            'description' => 'nullable'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20008);
        }
        $questionModel = Question::where('qid', $qid)->first();
        $questionModel->status = $questionInfo['status'];
        $questionModel->type = $questionInfo['type'];
        $questionModel->classification = $questionInfo['classification'];
        $questionModel->detail = $questionInfo['detail'];
        $questionModel->option = json_encode($questionInfo['option']);
        $questionModel->answer = json_encode($questionInfo['answer']);
        isset($questionInfo['description']) ? $questionModel->description = $questionInfo['description'] : null;
        $questionModel->update_uid = $uid;
        $questionModel->update_time = date('Y-m-d H:m:s', time());
        $result = $questionModel->save();
        if (!$result) {
            throw new ApiException(20007);
        }
        return Helper::responseSuccess([
            'info' => '更新成功'
        ]);
    }
    //删除题目
    public function deleteQuestion($qid){
        $questionModel = Question::where('qid', $qid)->first();
        $questionModel->status = 'DELETE';
        $result = $questionModel->save();
        if (!$result){
            throw new ApiException(20007);
        }
        return Helper::responseSuccess(['info' => '删除成功']);
    }

}
