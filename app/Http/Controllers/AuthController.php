<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Service\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        //参数完整性
        $postData = $request->all();
        if (!(isset($postData['username']) && isset($postData['password']))) {
            return Helper::responseError(20002);
        }
        //校验密码
        $userModel = new User();
        $hashPwd = $userModel
            ->where('username', $postData['username'])
            ->value('password');
        $result = Hash::check($postData['password'], $hashPwd);
        if (!$result){
            return Helper::responseError(20005);
        }
        //生成redis token ,并返回
        $token = Uuid::uuid1();
        $result1 = Redis::set($token, $postData['username']);
        $result2 = Redis::expire($token, env('REDIS_EXPIRE'));
        if (!((bool)$result1 && (bool)$result2)){
            return Helper::responseError(20006);
        }
        return Helper::responseSuccess([
            'username' => $postData['username'],
            'token' => $token
        ]);
    }

}
