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
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

}
