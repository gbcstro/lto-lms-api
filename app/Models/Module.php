<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'description'
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
