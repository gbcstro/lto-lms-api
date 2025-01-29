<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'activity_id', 'score', 'duration', 'is_completed'
    ];

    public function getCreatedAtAttribute($value)  
    {
        return Carbon::parse($value)
        ->timezone('Asia/Manila') // Replace with your desired timezone, e.g., 'America/New_York'
        ->format('M-d-Y h:i A');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
