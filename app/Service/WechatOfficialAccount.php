<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 18-1-15
 * Time: 下午12:45
 */

namespace App\Service;


class WechatOfficialAccount
{
    /**
     * 发送模板消息
     * @param string $openid
     * @param string $template_id
     * @param string $url
     * @param array $data
     * @return mixed
     */
    public static function template_message($openid, $template_id, $url = null, $data)
    {
        $app = app('wechat.official_account');
        $result = $app->template_message->send([
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'data' => $data
        ]);
        return $result;
    }

    /**
     * 添加模板消息
     * @param string $shortId
     * @return mixed
     */
    public static function add_template($shortId)
    {
        $app = app('wechat.official_account');
        $result = $app->template_message->addTemplate($shortId);
        return $result;
    }

    /**
     * 获取菜单列表
     * @return \Illuminate\Http\JsonResponse
     */
    public static function get_menu_list()
    {
        $app = app('wechat.official_account');
        $list = $app->menu->list();
        return $list;
    }

    /**
     * 获取当前菜单列表
     * @return \Illuminate\Http\JsonResponse
     */
    public static function get_menu_current()
    {
        $app = app('wechat.official_account');
        $current = $app->menu->current();
        return $current;
    }

    /**
     * 创建菜单
     * @param array $buttons
     * @return mixed
     */
    public static function create_menu($buttons)
    {
        $app = app('wechat.official_account');
        $result = $app->menu->create($buttons);
        return $result;
    }

    /**
     * 删除菜单
     * @return mixed
     */
    public static function delete_menu()
    {
        $app = app('wechat.official_account');
        $result = $app->menu->delete();
        return $result;
    }

    /**
     * 获取公众号菜单列表
     * @return mixed
     */
    public static function private_template()
    {
        $app = app('wechat.official_account');
        $result = $app->template_message->getPrivateTemplates();
        return $result;
    }
}