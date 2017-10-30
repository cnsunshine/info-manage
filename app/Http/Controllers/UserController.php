<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-28
 * Time: 下午5:18
 */

namespace App\Http\Controllers;


use App\Models\User;
use App\Service\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function info(Request $request, $id = null){
        $info = User::where('username', 'sunshine')
            ->get();
        return response([
            'code' => 200,
            'body' => [
                'id' => $id,
                'info' => $info,
                'username' => Helper::getUsername($request)
            ]
        ]);
    }

    /**
     * 用户注册
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        //参数完整性
        $postData = $request->all();
        if (!(isset($postData['username']) && isset($postData['password']))){
            return Helper::responseError(20002);
        }
        //参数合法性
        $user = [
            'username' => $postData['username'],
            'password' => $postData['password']
        ];
        $validator = Validator::make($user, [
            'username' => 'required|string|min:5|max:10|unique:users,username',
            'password' => 'required|min:5|max:10'
        ]);
        if ($validator->fails()){
            return Helper::responseError(20003);
        }
        //增加用户
        $userModel = new User();
        $userModel->setUsername($user['username']);
        $userModel->setPassword($user['password']);
        $result = $userModel->addUser();
        if (!$result){
            return Helper::responseError(20004);
        }
        return Helper::responseSuccess([
            'info' => '注册成功'
        ]);
    }
}