<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',   // JSON / array
        'role',      // user or admin
    ];

    /**
     * The attributes that should be hidden for arrays / JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'array', // JSON automatically cast to array
    ];

    /**
     * User has many Orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Helper: Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}