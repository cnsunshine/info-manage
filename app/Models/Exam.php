<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    //
    protected $table = 'exams';

    public $timestamps = false;

    protected $primaryKey = 'eid';
}
