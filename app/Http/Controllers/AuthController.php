<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Service\Auth;
use App\Service\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (!isset($postData['type'])){
            $postData['type'] = 'local';
        }
        switch ($postData['type']){
            case 'uestc':
                $result = Auth::uestclogin($postData['username'], $postData['password']);
                if ($result['status'] != 'success'){
                    return Helper::responseError(20005);
                }
                //生成redis token ,并返回
                $token = Uuid::uuid1();
                $uid = DB::table('student_info')
                    ->where('student_id', '=', $postData['username'])
                    ->value('uid');
                $result1 = Redis::set($token, $uid);
                $result2 = Redis::expire($token, env('REDIS_EXPIRE'));
                if (!((bool)$result1 && (bool)$result2)) {
                    return Helper::responseError(20006);
                }
                return Helper::responseSuccess([
                    'username' => $postData['username'],
                    'token' => $token,
                    'expire' => env('REDIS_EXPIRE')
                ]);
                break;
            case 'local':
                //校验密码
                $userModel = new User();
                $hashPwd = $userModel
                    ->where('username', $postData['username'])
                    ->value('password');
                $result = Hash::check($postData['password'], $hashPwd);
                if (!$result) {
                    return Helper::responseError(20005);
                }
                //生成redis token ,并返回
                $token = Uuid::uuid1();
                $uid = DB::table('users')
                    ->where('username', '=', $postData['username'])
                    ->value('uid');
                $result1 = Redis::set($token, $uid);
                $result2 = Redis::expire($token, env('REDIS_EXPIRE'));
                if (!((bool)$result1 && (bool)$result2)) {
                    return Helper::responseError(20006);
                }
                return Helper::responseSuccess([
                    'username' => $postData['username'],
                    'token' => $token,
                    'expire' => env('REDIS_EXPIRE')
                ]);
                break;
        }
    }
    //权限表######################################################################
    /**
     * 添加权限
     * @param Request $request
     * @return bool
     * @throws ApiException
     */
    public function add_auth(Request $request)
    {
        $jsonData = $request->input();
        if (!(isset($jsonData['aid']) && isset($jsonData['description']) && isset($jsonData['type']))){
            throw new ApiException(20002);
        }
        DB::transaction(function () use ($jsonData){
           $result = DB::table('auth')
               ->insert([
                   'aid' => $jsonData['aid'],
                   'type' => $jsonData['type'],
                   'description' => $jsonData['description']
               ]);
           if (!$result){
               throw new ApiException(20007);
           }
        });
        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function get_auth(Request $request){
        $jsonData = $request->input();
        isset($jsonData['aid'])?null:$jsonData['aid'] = '%';
        isset($jsonData['type'])?null:$jsonData['type'] = '%';
        isset($jsonData['description'])?null:$jsonData['description'] = '%';
        $result = DB::table('auth')
            ->where('aid', 'like', '%'.$jsonData['aid'].'%')
            ->where('type', 'like', '%'.$jsonData['type'].'%')
            ->where('description', 'like', '%'.$jsonData['description'].'%')
            ->get();
        return $result;
    }

    /**
     * @param Request $request
     * @return int
     * @throws ApiException
     */
    public function delete_auth(Request $request){
        $jsonData = $request->input();
        if (!isset($jsonData['aid'])){
            throw new ApiException(20002);
        }
        $result = DB::table('auth')
            ->where('aid', '=', $jsonData['aid'])
            ->delete();
        return $result;
    }
    //路由表#####################################################################
    /**
     * 添加路由信息
     * @param Request $request
     * @return bool
     * @throws ApiException
     */
    public function add_route(Request $request)
    {
        $jsonData = $request->input();
        if (!(isset($jsonData['uri']) && isset($jsonData['description']))){
            throw new ApiException(20002);
        }
        DB::transaction(function () use ($jsonData){
            $result = DB::table('routes')
                ->insert([
                    'uri' => $jsonData['uri'],
                    'description' => $jsonData['description']
                ]);
            if (!$result){
                throw new ApiException(20007);
            }
        });
        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function get_route(Request $request)
    {
        $jsonData = $request->input();
        isset($jsonData['uri'])?null:$jsonData['uri'] = '%';
        isset($jsonData['description'])?null:$jsonData['description'] = '%';
        $result = DB::table('routes')
            ->where('uri', 'like', '%'.$jsonData['uri'].'%')
            ->where('description', 'like', '%'.$jsonData['description'].'%')
            ->get();
        return $result;
    }

    /**
     * @param Request $request
     * @return int
     * @throws ApiException
     */
    public function delete_route(Request $request)
    {
        $jsonData = $request->input();
        if (!isset($jsonData['id'])){
            throw new ApiException(20002);
        }
        $result = DB::table('routes')
            ->where('id', '=', $jsonData['id'])
            ->delete();
        return $result;
    }

    //角色操作################################################################
    /**
     * 增加角色信息
     * @param Request $request
     * @return bool
     * @throws ApiException
     */
    public function add_role(Request $request)
    {
        $jsonData = $request->input();
        if (!(isset($jsonData['role']) && isset($jsonData['description']))){
            throw new ApiException(20002);
        }
        DB::transaction(function () use ($jsonData){
            $result = DB::table('roles')
                ->insert([
                    'role' => $jsonData['role'],
                    'description' => $jsonData['description']
                ]);
            if (!$result){
                throw new ApiException(20007);
            }
        });
        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function get_role(Request $request)
    {
        $jsonData = $request->input();
        isset($jsonData['role'])?null:$jsonData['role'] = '%';
        $result = DB::table('roles')
            ->where('role', 'like', '%'.$jsonData['role'].'%')
            ->get();
        return $result;
    }

    /**
     * @param Request $request
     * @return int
     * @throws ApiException
     */
    public function delete_role(Request $request)
    {
        $jsonData = $request->input();
        if (!isset($jsonData['id'])){
            throw new ApiException(20002);
        }
        $result = DB::table('roles')
            ->where('id', '=', $jsonData['id'])
            ->delete();
        return $result;
    }
    //ip名单操作###############################################################
    /**
     * 增加ip
     * @param Request $request
     * @return bool
     * @throws ApiException
     */
    public function add_ip(Request $request)
    {
        $jsonData = $request->input();
        if (!(isset($jsonData['ip_type']) && isset($jsonData['ip']))){
            throw new ApiException(20002);
        }
        DB::transaction(function () use ($jsonData){
            $result = DB::table('ips')
                ->insert([
                    'ip_type' => $jsonData['ip_type'],
                    'ip' => $jsonData['ip']
                ]);
            if (!$result){
                throw new ApiException(20007);
            }
        });
        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function get_ip(Request $request)
    {
        $jsonData = $request->input();
        isset($jsonData['ip_type'])?null:$jsonData['ip_type'] = '%';
        isset($jsonData['ip'])?null:$jsonData['ip'] = '%';
        $result = DB::table('ips')
            ->where('ip_type', 'like', '%'.$jsonData['ip_type'].'%')
            ->where('ip', 'like', '%'.$jsonData['ip'].'%')
            ->get();
        return $result;
    }

    /**
     * @param Request $request
     * @return int
     * @throws ApiException
     */
    public function delete_ip(Request $request)
    {
        $jsonData = $request->input();
        if (!isset($jsonData['id'])){
            throw new ApiException(20002);
        }
        $result = DB::table('ips')
            ->where('id', '=', $jsonData['id'])
            ->delete();
        return $result;
    }

    //用户权限表#############################################################

    /**
     * @param Request $request
     * @return bool
     * @throws ApiException
     */
    public function add_auth_user(Request $request)
    {
        $jsonData = $request->input();
        if (!($jsonData['from'] && $jsonData['to'] && $jsonData['role']
            && $jsonData['uri'] && $jsonData['ip_type'])){
            throw new ApiException(20002);
        }
        DB::transaction(function () use ($jsonData){
            $result = DB::table('auth_user')
                ->insert([
                    'from' => $jsonData['from'],
                    'to' => $jsonData['to'],
                    'aid' => Uuid::uuid1(),
                    'role' => $jsonData['role'],
                    'uri' => $jsonData['uri'],
                    'ip_type' => $jsonData['ip_type'],
                ]);
            if (!$result){
                throw new ApiException(20007);
            }
        });
        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function get_auth_user(Request $request)
    {
        $jsonData = $request->input();
        isset($jsonData['from'])?null:$jsonData['from'] = '%';
        isset($jsonData['to'])?null:$jsonData['to'] = '%';
        isset($jsonData['aid'])?null:$jsonData['aid'] = '%';
        isset($jsonData['role'])?null:$jsonData['role'] = '%';
        isset($jsonData['uri'])?null:$jsonData['uri'] = '%';
        isset($jsonData['ip_type'])?null:$jsonData['ip_type'] = '%';
        $result = DB::table('auth_user')
            ->where('from', 'like', '%'.$jsonData['from'].'%')
            ->where('to', 'like', '%'.$jsonData['to'].'%')
            ->where('aid', 'like', '%'.$jsonData['aid'].'%')
            ->where('role', 'like', '%'.$jsonData['role'].'%')
            ->where('uri', 'like', '%'.$jsonData['uri'].'%')
            ->where('ip_type', 'like', '%'.$jsonData['ip_type'].'%')
            ->get();
        return $result;
    }

    /**
     * @param Request $request
     * @return int
     * @throws ApiException
     */
    public function delete_auth_user(Request $request)
    {
        $jsonData = $request->input();
        if (!isset($jsonData['id'])){
            throw new ApiException(20002);
        }
        $result = DB::table('auth_user')
            ->where('id', '=', $jsonData['id'])
            ->delete();
        return $result;
    }
}
