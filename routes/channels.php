<?php

use Illuminate\Support\Facades\Broadcast;
use \App\Models\UrlClick;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.UrlClick.{urlId}', function ($user, $urlId) {
    return UrlClick::first()->where('url_id', '=', $urlId)->join('urls', 'url_clicks.url_id', '=', 'urls.id');
});
