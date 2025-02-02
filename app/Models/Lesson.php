<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'content',
        'module_id',
        'duration'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function userLessons()
    {
        return $this->hasMany(UserLesson::class);
    }   

}
