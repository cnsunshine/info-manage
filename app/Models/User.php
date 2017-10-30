<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class User extends Model
{
    //
    protected $table = 'users';

    public $timestamps = false;
    //用户名
    private $username = '';
    //uid
    private $uid = '';
    //pwd
    private $password = '';
    //email
    private $email = '';
    //tel
    private $tel = '';
    //last_login
    private $last_login = '';
    //create_time
    private $create_time = '';

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function createUid()
    {
        $this->uid = Uuid::uuid1();
    }

    public function setPassword($password)
    {
        $this->password = Hash::make($password);
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setTel($tel)
    {
        $this->tel = $tel;
    }

    public function setLastLogin($last_login)
    {
        $this->last_login = $last_login;
    }

    public function createCreateTime()
    {
        $this->create_time = time();
    }

    /**
     * 增加用户
     * @return bool
     */
    public function addUser()
    {
        if (empty($this->username)
            || empty($this->password)
            || empty($this->create_time
            || empty($this->uid))){
            return false;
        }

        $user = [
            'username' => $this->username,
            'uid' => $this->uid,
            'password' => $this->password,
            'create_time' => $this->create_time
        ];
        //验证参数
        $validator = Validator::make($user, ['username' => 'alpha']);
        if ($validator->fails()){
            return false;
        }
        //存储数据
        $result = $this->save($user);
        return $result;
    }

    /**
     * 更新用户信息
     * @return bool
     */
    public function updateUserInfo()
    {
        if (empty($this->uid)){
            return false;
        }
        $user = [
            'email' => $this->email,
            'tel' => $this->tel
        ];
        //验证参数
        $validator = Validator::make($user, [
            'email' => 'nullable|email',
            'tel' => 'nullable|regex:[[0-9]{11}]'
        ]);
        if ($validator->fails()){
            return false;
        }
        //存储数据
        $result = $this
            ->where('uid', $this->uid)
            ->update($user);
        return $result;

    }
}
