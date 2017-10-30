<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-30
 * Time: 下午9:11
 */

namespace App\Service;


use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class Helper
{
    //获取uid
    public static function getUsername(Request $request)
    {
        $token = $request->header('x-token');
        $username = Redis::get($token);
        return $username;
    }
    //返回正确数据
    public static function responseSuccess($body, $code = 200){
        return response([
            'code' => $code,
            'body' => $body
        ]);
    }
    //返回错误数据
    public static function responseError($code){
        return response([
            'code' => $code,
            'errmsg' => Error::getErrorMessage($code)
        ]);
    }
}