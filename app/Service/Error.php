<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-30
 * Time: 下午11:44
 */

namespace App\Service;


use function Faker\Provider\pt_BR\check_digit;

class Error
{
    private static $errorMsgList = [
        20001 => '请重新登录',

    ];
    //获取异常信息
    public static function getErrorMessage($code){
        if (!isset(Error::$errorMsgList[$code])){
            return '请查阅错误码文档';
        }else{
            return Error::$errorMsgList[$code];
        }
    }
}