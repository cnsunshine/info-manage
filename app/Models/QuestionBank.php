<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    //
    protected $table = 'question_bank';

    public $timestamps = false;

    protected $primaryKey = 'qb_id';
}
