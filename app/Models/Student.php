<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $table = 'student_info';

    protected $fillable = [
        'uid',
        'student_id',
        'college',
        'specialty'
    ];

    public $timestamps = false;
}
