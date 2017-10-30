<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-28
 * Time: 下午5:18
 */

namespace App\Http\Controllers;


use App\Models\User;

class UserController extends Controller
{
    public function info($id = null){
        $info = User::where('username', 'sunshine')
            ->get();
        return response([
            'code' => 200,
            'body' => [
                'id' => $id,
                'info' => $info
            ]
        ]);
    }
}