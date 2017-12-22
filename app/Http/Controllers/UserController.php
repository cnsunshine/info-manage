<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-28
 * Time: 下午5:18
 */

namespace App\Http\Controllers;


use App\Exceptions\ApiException;
use App\Models\Exam;
use App\Models\Student;
use App\Models\User;
use App\Service\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUserInfo(Request $request, $username = null)
    {
        $uid = Helper::getUid($request);
        $userModel = new User();
        $info = $userModel
            ->from('users as u')
            ->where('u.uid', $uid)
            ->join('student_info as si', 'u.uid', '=', 'si.uid')
            ->select(['u.username', 'u.real_name', 'si.student_id',
                'si.college', 'si.specialty', 'u.create_time',
                'u.tel', 'u.email'])
            ->first();
        $totalTime = DB::table('user_exam_log')
            ->where('create_uid', $uid)
            ->count();
        $passTime = DB::table('exams as e')
            ->join('user_exam_log as uel', 'e.eid', '=', 'uel.eid')
            ->where('uel.create_uid', $uid)
            ->where('uel.score', '>', 'e.pass_score')
            ->count();
        $info['status'] = '正常';
        $info['exam_total_time'] = $totalTime;
        $info['exam_pass_time'] = $passTime;
        return Helper::responseSuccess($info);

    }

    public function updateUserInfo(Request $request, $username = null)
    {
        $postData = $request->all();
        $userInfo = [];
        isset($postData['real_name']) ? $userInfo['real_name'] = $postData['real_name'] : null;
        isset($postData['student_id']) ? $userInfo['student_id'] = $postData['student_id'] : null;
        isset($postData['college']) ? $userInfo['college'] = $postData['college'] : null;
        isset($postData['specialty']) ? $userInfo['specialty'] = $postData['specialty'] : null;
        isset($postData['tel']) ? $userInfo['tel'] = $postData['tel'] : null;
        isset($postData['email']) ? $userInfo['email'] = $postData['email'] : null;
        //校验数据
        $validator = Validator::make($userInfo, [
            'real_name' => 'nullable|max:20',
            'student_id' => 'nullable|min:12|max:13',
            'college' => 'nullable|min:3|max:10',
            'specialty' => 'nullable|min:3|max:10',
            'email' => 'nullable|email',
            'tel' => 'nullable|regex:[[0-9]{11}]'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20003);
        }
        //获取uid
        $uid = Helper::getUid($request);
        //修改数据
        DB::transaction(function () use ($userInfo, $uid) {
            $userModel = User::where('uid', $uid)->first();
            isset($userInfo['real_name']) ? $userModel->real_name = $userInfo['real_name'] : null;
            isset($userInfo['email']) ? $userModel->email = $userInfo['email'] : null;
            isset($userInfo['tel']) ? $userModel->tel = $userInfo['tel'] : null;
            $result = $userModel->save();
            if (!$result) {
                throw new ApiException(20007);
            }
            $studentModel = Student::where('uid', $uid)->first();
            isset($userInfo['student_id']) ? $studentModel->student_id = $userInfo['student_id'] : null;
            isset($userInfo['college']) ? $studentModel->college = $userInfo['college'] : null;
            isset($userInfo['specialty']) ? $studentModel->specialty = $userInfo['specialty'] : null;
            $result = $studentModel->save();
            if (!$result) {
                throw new ApiException(20007);
            }
        });
        return Helper::responseSuccess([
            'info' => '修改成功'
        ]);

    }

    /**
     * 用户注册
     * @param Request $request
     * @throws ApiException
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        //参数完整性
        $postData = $request->all();
        if (!(isset($postData['username'])
            && isset($postData['password'])
            && isset($postData['real_name'])
            && isset($postData['email'])
            && isset($postData['tel']))) {
            throw new ApiException(20002);
        }
        //参数合法性
        $user = [
            'username' => $postData['username'],
            'password' => $postData['password'],
            'real_name' => $postData['real_name'],
            'email' => $postData['email'],
            'tel' => $postData['tel']
        ];
        $validator = Validator::make($user, [
            'username' => 'required|alpha|min:5|max:10|unique:users,username',
            'password' => 'required|min:5|max:10',
            'real_name' => 'required|max:8',
            'email' => 'required|email',
            'tel' => 'required|regex:[[0-9]{11}]'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20003);
        }
        //增加用户
        DB::transaction(function () use ($user) {
            $userModel = new User();
            $userModel->setUsername($user['username']);
            $userModel->setPassword($user['password']);
            $userModel->setRealName($user['real_name']);
            $userModel->setEmail($user['email']);
            $userModel->setTel($user['tel']);
            $result = $userModel->addUser();
            if (!$result) {
                throw new ApiException(20004);
            }
            //增加学生
            $studentModel = new Student();
            $studentModel->uid = $userModel->getUid();
            $result = $studentModel->save();
            if (!$result) {
                throw new ApiException(20004);
            }
        });
        return Helper::responseSuccess([
            'info' => '注册成功'
        ]);
    }

    public function changePassword(Request $request)
    {
        $postData = $request->all();
        if (!(isset($postData['old_password']) && isset($postData['new_password']))){
            throw new ApiException(20002);
        }
        $validator = Validator::make(['password' => $postData['new_password']], [
            'password' => 'required|min:5|max:10'
        ]);
        if ($validator->fails()){
            throw new ApiException(20008);
        }
        //检查旧密码
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $userModel = User::where('uid', $uid)
            ->first();
        $hashPwd = $userModel->password;
        $result = Hash::check($postData['old_password'], $hashPwd);
        if (!$result) {
            return Helper::responseError(20005);
        }
        //修改密码
        $userModel->password = Hash::make($postData['new_password']);
        $result = $userModel->save();
        if (!$result){
            throw new ApiException(20007);
        }
        $info = '修改成功';
        return Helper::responseSuccess($info);
    }

    public function ifHasUser($name)
    {
        $result = User::where('username', $name)->first();
        $info = (boolean)$result;
        return Helper::responseSuccess($info);
    }
}