<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'choice_id',
    ];
}
