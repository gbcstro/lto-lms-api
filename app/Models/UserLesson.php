<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLesson extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'module_id'
    ];
}
