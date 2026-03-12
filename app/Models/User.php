<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Url newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Url newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Url query()
 * @mixin Eloquent
 */
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

    public function urls(): HasMany
    {
        return $this->hasMany(Url::class);
    }
}
