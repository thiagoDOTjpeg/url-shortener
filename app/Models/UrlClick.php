<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'url_id',
        'ip_address',
        'user_agent',
        'referer',
        'from',
        'country',
        'clicked_at',
        'longitude',
        'latitude',
        'browser',
        'os',
        'device_type',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'referer' => 'string',
        'from' => 'string',
        'country' => 'string',
        'longitude' => 'double',
        'latitude' => 'double',
        'browser' => 'string',
        'os' => 'string',
        'device_type' => 'string',
    ];

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }
}
