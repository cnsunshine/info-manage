<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 18-1-18
 * Time: 上午6:32
 */

namespace App\Models;



use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
    protected $table = 'auth';

    public $timestamps = false;

    protected $primaryKey = 'id';
}