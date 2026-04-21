<?php

namespace App\Http\Controllers;

use App\Jobs\TrackClick;
use App\Models\Url;
use Illuminate\Http\Request;

class RedirectController
{
    public function __invoke(string $slug, Request $request) {
        $url = Url::findOrFail($slug);

        TrackClick::dispatch(
            $slug,
            $request->ip(),
            $request->userAgent(),
            $request->header('referer'),
            $request->query('from', 'link')
        );

        return view('r.redirect', [
            'destination' => $url->original_url,
        ]);
    }
}
