<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BookmarkModule extends Model
{
    protected $fillable = [
        'user_id',
        'module_id'
    ];

    public function getCreatedAtAttribute($value)  
    {
        return Carbon::parse($value)
        ->timezone('Asia/Manila') // Replace with your desired timezone, e.g., 'America/New_York'
        ->format('M-d-Y h:i A');
    }

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
