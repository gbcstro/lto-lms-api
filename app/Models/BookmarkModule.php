<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookmarkModule extends Model
{
    protected $fillable = [
        'user_id',
        'module_id'
    ];

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Module model
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
