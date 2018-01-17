<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 18-1-10
 * Time: 上午5:53
 */

namespace App\Service;


class Auth
{
    /**
     *
     * 此函数用来登录电子科大统一认证
     * @param $id
     * @param $password
     * @return array
     */
    public static function uestclogin($id, $password)
    {
        //一次访问取cookie模块
        //var_dump($id);
        //var_dump($password);
        $url = 'http://portal.uestc.edu.cn/';
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
        $info = curl_getinfo($ch);
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
        $url = 'http://idas.uestc.edu.cn/authserver/login?service=http%3A%2F%2Fportal.uestc.edu.cn%2F';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_REFERER, 'http://idas.uestc.edu.cn/authserver/login');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        //进教务系统课程管理
        $ch = curl_init();
        $url = 'http://eams.uestc.edu.cn/eams/home!childmenus.action?menu.id=844';
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
        curl_close($ch);
        //var_dump($cookie_file);
        //getUserInfo
        $ch = curl_init();
        $url = "http://eams.uestc.edu.cn/eams/stdDetail.action";
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
        curl_close($ch);
        //logout
        $ch = curl_init();
        $url = 'http://idas.uestc.edu.cn/authserver/logout?service=http%3A%2F%2Fportal.uestc.edu.cn%2Findex.portal';
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
        if(!strpos($output, '学籍信息')){//没有找到信息则直接返回
            return array(
                'status' => 'failure',
                'code' => 404
            );
        }
        //userId
        $info_loc_start = strpos($output, '<td colspan="5" style="font-weight:bold;text-align:center" class="darkColumn">学籍信息</td>');
        $info_loc_start = strpos($output, '学号', $info_loc_start + 1);
        $info_loc_end = strpos($output, '</td>', $info_loc_start + 1);
        $info_loc_end = strpos($output, '</td>', $info_loc_end + 1);
        //寻找下下个</td>
        $userId = mb_strcut($output, $info_loc_start + 39, $info_loc_end - $info_loc_start - 39);
        //userName
        $info_loc_start = strpos($output, '姓名', $info_loc_end);
        $info_loc_end = strpos($output, '</td>', $info_loc_start + 1);
        $info_loc_end = strpos($output, '</td>', $info_loc_end + 1);
        $userName = mb_strcut($output, $info_loc_start + 27, $info_loc_end - $info_loc_start - 27);
        //userSex
        $info_loc_start = strpos($output, '性别', $info_loc_end);
        $info_loc_end = strpos($output, '</td>', $info_loc_start + 1);
        $info_loc_end = strpos($output, '</td>', $info_loc_end + 1);
        $userSex = mb_strcut($output, $info_loc_start + 27, $info_loc_end - $info_loc_start - 27);
        //userCollege
        $info_loc_start = strpos($output, '院系', $info_loc_end);
        $info_loc_end = strpos($output, '</td>', $info_loc_start + 1);
        $info_loc_end = strpos($output, '</td>', $info_loc_end + 1);
        $userCollege = mb_strcut($output, $info_loc_start + 27, $info_loc_end - $info_loc_start - 27);
        //userSpecialty
        $info_loc_start = strpos($output, '专业', $info_loc_end);
        $info_loc_end = strpos($output, '</td>', $info_loc_start + 1);
        $info_loc_end = strpos($output, '</td>', $info_loc_end + 1);
        $userSpecialty = mb_strcut($output, $info_loc_start + 27, $info_loc_end - $info_loc_start - 27);
        return array(
            'status' => 'success',
            'code' => 200,
            'info' => array(
                'userId' => $userId,
                'userName' => $userName,
                'userSex' => $userSex,
                'userCollege' => $userCollege,
                'userSpecialty' => $userSpecialty
            )
        );
    }

    /**
     * 检查ip
     * @param $uid
     * @param $ip
     * @return bool
     */
    public static function check_ip($uid, $ip)
    {
        return true;
    }

    /**
     * 检查用户对应api的权限
     * @param $uid
     * @param $api_id
     * @return bool
     */
    public static function check_api($uid, $api_id)
    {
        return true;
    }

    /**
     * 检查用户对用户操作权限
     * 例如：发送消息
     * @param $from_uid
     * @param $to_uid
     * @param $api_id
     * @return bool
     */
    public static function check_user_to_user($from_uid, $to_uid, $api_id)
    {
        return true;
    }
}