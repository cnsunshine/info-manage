<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-28
 * Time: 下午5:18
 */

namespace App\Http\Controllers;


use App\Exceptions\ApiException;
use App\Models\Student;
use App\Models\User;
use App\Service\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->select(['u.uid', 'u.real_name', 'si.college'])
            ->get();
        return Helper::responseSuccess($info);

    }

    public function updateUserInfo(Request $request, $username = null)
    {

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
            'username' => 'required|string|min:5|max:10|unique:users,username',
            'password' => 'required|min:5|max:10',
            'real_name' => 'required|max:20',
            'email' => 'required|email',
            'tel' => 'required|regex:[[0-9]{11}]'
        ]);
        if ($validator->fails()) {
            throw new ApiException(20003);
        }
        //增加用户
        DB::transaction(function () use($user) {
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
}