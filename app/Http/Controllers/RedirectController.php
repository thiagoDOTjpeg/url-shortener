<?php

namespace App\Http\Controllers;

use App\Jobs\TrackClick;
use App\Models\Url;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RedirectController
{
    public function __invoke(string $slug, Request $request) {
        $url = Url::findOrFail($slug);

        if($url->is_expired()) {
            return view('r.expired-link');
        }

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
