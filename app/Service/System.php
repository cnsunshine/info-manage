<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 18-1-26
 * Time: 下午8:04
 */

namespace App\Service;


use Illuminate\Support\Facades\DB;

class System
{
    public static function initialiseUser($uid)
    {
        //配置用户设置
        DB::transaction(function () use ($uid){
            $result = DB::table('users_settings')
                ->insert([
                    'uid' => $uid,
                    'school_card_rest_balance_status' => true,
                    'school_card_rest_balance_threshold' => '20',
                    'lost_notice_status' => true,
                    'express_wall_status' => true,
                    'reply_status' => true
                ]);
            if (!$result){
                return false;
            }
        });
        return true;
    }

    /**
     * @return bool
     */
    public static function isEmergency(){
        $result = DB::table('system_settings')
            ->where('name', '=','emergency')
            ->value('value');
        if ($result){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public static function getSystemSetting($name)
    {
        $result = DB::table('system_settings')
            ->where('name', '=',$name)
            ->value('value');
        if ($result){
            return $result;
        }else{
            return false;
        }
    }
}