<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Service\Auth;
use App\Service\Helper;
use App\Service\RabbitMQ;
use App\Service\SchoolService;
use App\Service\WechatOfficialAccount;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;

class WechatController extends Controller
{
    //
    public function serve()
    {
        $app = app('wechat.official_account');
        $app->server->push(function ($message) use ($app) {
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    if ($message['Content'] == 'template') {
                        $app->template_message->send([
                            'touser' => $message['FromUserName'],
                            'template_id' => 'xrHyEQlZ6KsGVIUm5KPOaGPiEPX-RGBiEqWAhwmKyKg',
                            'data' => [
                                'first' => '测试模板',
                                'keynote1' => '1',
                                'keynote2' => '2',
                                'keynote3' => '3',
                                'remark' => '感谢使用',
                            ],
                        ]);
                    }
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });
        return $app->server->serve();
    }

    public function template_message($openid, $template_id, $url, $data)
    {
        return Helper::responseSuccess(WechatOfficialAccount::template_message($openid, $template_id, $url, $data));
    }

    public function add_template($shortId)
    {
        return Helper::responseSuccess(WechatOfficialAccount::add_template($shortId));
    }
    /**
     * 用户认证
     * @param Request $request
     * @param Request ['redirect_url']
     * @return mixed
     */
    public function oauth(Request $request)
    {
        $redirect_url = $request->input('redirect_url', url('/wechat'));
        session(['target_url' => $redirect_url]);
        $app = app('wechat.official_account');
        if (session('wechat_user')) {
            return $app->oauth->user()->getId();
        } else {
            $response = $app->oauth->scopes(['snsapi_userinfo'])
                ->redirect(url('/wechat/oauth/callback'));
            return $response;
        }
    }


    /**
     * 用户认证信息记录
     * @param Request $request
     * @return $this|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function oauth_callback(Request $request)
    {
        $app = app('wechat.official_account');
        $oauth = $app->oauth;
        $user = $oauth->user();

        //session(['x-token' => $user->toArray()]);
        //判断是否绑定到用户
        $uid = DB::table('users_weixin')
            ->where('openid', '=', $user->toArray()['id'])
            ->value('uid');

        if (!$uid){
            //如无信息，跳转绑定
            //如无记录写入微信
            $result = DB::table('users_weixin')
                ->where('openid', '=', $user->toArray()['id'])
                ->first();
            $userInfo = $user->toArray()['original'];
            if (!$result) {
                DB::transaction(function () use ($userInfo) {
                    $result = DB::table('users_weixin')
                        ->insert([
                            'subscribe' => isset($userInfo['subscribe'])?$userInfo['subscribe']:null,
                            'openid' => isset($userInfo['openid'])?$userInfo['openid']:null,
                            'nickname' => isset($userInfo['nickname'])?$userInfo['nickname']:null,
                            'sex' => isset($userInfo['sex'])?$userInfo['sex']:null,
                            'city' => isset($userInfo['city'])?$userInfo['city']:null,
                            'country' => isset($userInfo['country'])?$userInfo['country']:null,
                            'province' => isset($userInfo['province'])?$userInfo['province']:null,
                            'language' => isset($userInfo['language'])?$userInfo['language']:null,
                            'headimgurl' => isset($userInfo['headimgurl'])?$userInfo['headimgurl']:null,
                            'subscribe_time' => isset($userInfo['subscribe_time'])?$userInfo['subscribe_time']:null,
                            'unionid' => isset($userInfo['unionid'])?$userInfo['unionid']:null,
                            'remark' => isset($userInfo['remark'])?$userInfo['remark']:null,
                            'groupid' => isset($userInfo['groupid'])?$userInfo['groupid']:null,
                            'tagid_list' => isset($userInfo['tagid_list'])?$userInfo['tagid_list']:null,
                        ]);
                    if (!$result){
                        throw new ApiException(20007);
                    }
                });
            }
            return redirect('/user/weixin/bind.html')->cookie('w-token', $userInfo['openid']);
        }
        //生成redis token
        $token = Uuid::uuid1();
        $result1 = Redis::set($token, $uid);
        $result2 = Redis::expire($token, env('REDIS_EXPIRE'));
        if (!((bool)$result1 && (bool)$result2)) {
            return Helper::responseError(20006);
        }
        return redirect(session('target_url'))->cookie('x-token', $token);

    }

    public function rabbitmq($queue = 'test',$exchange = 'test', $messageBody = 'ok')
    {
        RabbitMQ::push();
    }

    public function get_rabbitmq()
    {
        return Helper::responseSuccess(RabbitMQ::read());
    }

    public function consumer_rabbitmq()
    {
        RabbitMQ::consumer();
    }



    public function get_menu_list()
    {
        return Helper::responseSuccess(WechatOfficialAccount::get_menu_list());
    }

    public function get_menu_current()
    {
        return Helper::responseSuccess(WechatOfficialAccount::get_menu_current());
    }

    public function create_menu()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.soso.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url"  => "http://v.qq.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        return Helper::responseSuccess(WechatOfficialAccount::create_menu($buttons));
    }

    public function delete_menu()
    {
        return Helper::responseSuccess(WechatOfficialAccount::delete_menu());
    }

    public function private_template()
    {
        return Helper::responseSuccess(WechatOfficialAccount::private_template());
    }

    /**
     * 绑定用户信息
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function bind_user(Request $request)
    {
        $postData = $request->input();
        $weixinToken = $request->cookie('w-token');
        if (!$weixinToken){
            return Helper::responseError(21001);
        }
        if (!($postData['username'] && $postData['password'])){
            return Helper::responseError(20002);
        }
        $studentInfo = Auth::uestclogin($postData['username'], $postData['password']);
        if ($studentInfo['status'] != 'success'){
            return Helper::responseError(22001);
        }
        $result = DB::table('student_info')
            ->where('student_id', '=', $postData['username'])
            ->first();
        if (!$result){
            //生成uid
            $uid = Uuid::uuid1();
            DB::transaction(function () use ($uid, $studentInfo, $postData, $weixinToken){
                DB::table('student_info')
                    ->insert([
                        'uid' => $uid,
                        'student_id' => $postData['username'],
                        'password' => $postData['password'],
                        'college' => $studentInfo['info']['userCollege'],
                        'specialty' => $studentInfo['info']['userSpecialty']
                    ]);
                DB::table('users')
                    ->insert([
                        'uid' => $uid,
                        'real_name' => $studentInfo['info']['userName'],
                        'create_time' => date('Y-m-d H:i:s', time())
                    ]);
                DB::table('users_weixin')
                    ->where('openid', '=', $weixinToken)
                    ->update([
                        'uid' => $uid
                    ]);
            });
        }
        DB::transaction(function () use ($uid, $weixinToken){
            DB::table('users_weixin')
                ->where('openid','=', $weixinToken)
                ->update([
                    'uid' => $uid
                ]);
        });
        return Helper::responseSuccess('ok');
    }

    public function customer_service()
    {
        $app = app('wechat.official_account');
        $message = new Text('测试客服消息');
        $result = $app->customer_service->message($message)->to('o6CILwb4CdW3hYUPhwQuE0jxwNts')->send();
        return Helper::responseSuccess($result);
    }

    public function test()
    {
        SchoolService::getEcardBalance('2015060107012', '123456');
    }
}
