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

    protected $fillable = [
        'username',
        'password',
        'uid',
        'real_name',
        'create_time',
        'email',
        'tel'
    ];

    public $timestamps = false;
    //用户名
    private $username = '';
    //uid
    private $uid = '';
    //pwd
    private $password = '';
    //real_name
    private $real_name = '';
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

    private function createUid()
    {
        $this->uid = Uuid::uuid1();
    }

    public function setRealName($real_name)
    {
        $this->real_name = $real_name;
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

    private function createCreateTime()
    {
        $this->create_time = date('Y-m-d H:i:s', time());
    }

    /**
     * 增加用户
     * @return bool
     */
    public function addUser()
    {
        //创建uid
        $this->createUid();
        //创建时间
        $this->createCreateTime();

        if (empty($this->username)
            || empty($this->password)
            || empty($this->create_time)
            || empty($this->uid)
            || empty($this->real_name)
            || empty($this->email)
            || empty($this->tel)) {
            return false;
        }

        $user = [
            'username' => $this->username,
            'uid' => $this->uid,
            'password' => $this->password,
            'real_name' => $this->real_name,
            'create_time' => $this->create_time,
            'email' => $this->email,
            'tel' => $this->tel
        ];
        //验证参数
        $validator = Validator::make($user,
            [
                'username' => 'required|alpha',
                'real_name' => 'required|max:20',
                'email' => 'required|email',
                'tel' => 'required|regex:[[0-9]{11}]'
            ]);
        if ($validator->fails()) {
            return false;
        }
        //存储数据
        $model = $this->create($user);
        $result = $model->save();
        return $result;
    }

    /**
     * 更新用户信息
     * @return bool
     */
    public function updateUserInfo()
    {
        if (empty($this->uid)) {
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
        if ($validator->fails()) {
            return false;
        }
        //存储数据
        $result = $this
            ->where('uid', $this->uid)
            ->update($user);
        return $result;

    }
}
