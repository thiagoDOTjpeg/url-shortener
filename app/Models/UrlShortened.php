<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UrlShortened newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UrlShortened newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UrlShortened query()
 * @mixin Eloquent
 */
class UrlShortened extends Model
{

    protected $primaryKey = "id";
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ["id", "original_url", "shortened_url", "qr_code"];
}
