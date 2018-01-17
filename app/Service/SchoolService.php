<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 18-1-17
 * Time: 上午11:10
 */

namespace App\Service;


class SchoolService
{

    /**
     * @param $id
     * @param $password
     * @return bool|string
     */
    public static function getEcardBalance($id, $password)
    {
        //一次访问取cookie模块
        //var_dump($id);
        //var_dump($password);
        $url = 'http://idas.uestc.edu.cn/authserver/login?service=http%3A%2F%2Fecard.uestc.edu.cn%2Fcaslogin.jsp';
        $cookie_file = tempnam(env('tmp'), 'cookie');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        $ret = curl_exec($ch);
        curl_close($ch);

        //抓取数据构成表单模块
        $lt_start = strpos($ret, '<input type="hidden" name="lt" value=');
        $lt_end = strpos($ret, 'cas"/>');
        $JSESSIONID_start = strpos($ret, 'JSESSIONID_ids1');
        $JSESSIONID_ids1 = substr($ret, $JSESSIONID_start, 20);
        $lt = substr($ret, $lt_start + 38, $lt_end - ($lt_start + 35));
        $execution_start = strpos($ret, '<input type="hidden" name="execution" value=');
        $execution = substr($ret, $execution_start + 45, 4);
        $post_data = "username=$id&password=$password&lt=$lt&dllt=userNamePasswordLogin&execution=$execution&_eventId=submit&rmShown=1";
        //尝试登陆模块
        $ch = curl_init();
        $url = 'http://idas.uestc.edu.cn/authserver/login?service=http%3A%2F%2Fecard.uestc.edu.cn%2Fcaslogin.jsp';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, 'http://idas.uestc.edu.cn/authserver/login?service=http%3A%2F%2Fecard.uestc.edu.cn%2Fcaslogin.jsp');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $ch = curl_init();
        $url = 'http://ecard.uestc.edu.cn/caslogin.jsp';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_POST, false);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $ch = curl_init();
        $url = 'http://ecard.uestc.edu.cn/c/portal/login';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_POST, false);
        $ret = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $money_start = strpos($ret, '<td>卡余额：<span style="font-size:20px; color:#548efc;">');
        //判断出错
        if($money_start === false){
            return false;
        }
        $money_end = strpos($ret, '</span>元</td>', $money_start);
        $money = substr($ret, $money_start + 61, $money_end - ($money_start + 61));
        //print_r($money);
        //登出用户
        $ch = curl_init();
        $url = 'http://idas.uestc.edu.cn/authserver/login?service=http%3A%2F%2Fecard.uestc.edu.cn%2Fcaslogin.jsp';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_exec($ch);
        curl_close($ch);
        unlink($cookie_file);

        return $money;
    }

}