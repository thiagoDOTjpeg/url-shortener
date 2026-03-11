<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids;

    protected $fillable = [
        "name",
        "email",
        "password",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected $casts = [
        "password" => "hashed",
    ];
}
