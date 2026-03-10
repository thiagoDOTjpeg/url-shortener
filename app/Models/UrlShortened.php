<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlShortened extends Model
{
    protected $fillable = ["original_url", "shortened_url", "qr_code"];
}
