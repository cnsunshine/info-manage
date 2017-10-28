<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 17-10-28
 * Time: 下午5:18
 */

namespace App\Http\Controllers;


class UserController extends Controller
{
    public function info($id = null){
        return response([
            'code' => 200,
            'body' => [
                'id' => $id,
            ]
        ]);
    }
}