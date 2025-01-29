<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'email',
        'username',
        'first_name',
        'last_name',
        'password',
        'google_id',
        'profile_picture',
        'address',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value) {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function getPasswordAttribute($value) {
        return $value;
    }

    public function history() 
    {
        return $this->hasMany(ActivityHistory::class);
    }

    public function bookmarks() 
    {
        return $this->hasMany(BookmarkModule::class);
    }

    public function answers() 
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function lessons() 
    {
        return $this->hasMany(UserLesson::class);
    }

    // Required for JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
