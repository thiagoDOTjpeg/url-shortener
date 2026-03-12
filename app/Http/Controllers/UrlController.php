<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrlRequest;
use App\Jobs\GenerateQrCode;
use App\Models\Url;
use Base62\Base62;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\RandomException;
use Stevebauman\Location\Facades\Location;

class UrlController extends Controller
{

    public function __construct(private readonly Base62 $base62) {}

    public function show($slug, Request $request) {
        $url = Url::findOrFail($slug);

        $position = Location::get($request->ip());

        $url->clicks()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer'    => $request->header('referer'),
            'from'       => $request->query('from', 'link'),
            'country'    => $position?->countryCode,
        ]);
        $url->increment('click_count');
        $url->update();

        return view('r.redirect', [
            'destination' => $url->original_url,
        ]);
    }

    /**
     * @throws RandomException
     */
    public function store(StoreUrlRequest $request): JsonResponse
    {
        $slug = $this->base62->encode(random_int(1, PHP_INT_MAX));
        $url = new Url($request->validated());
        $url->id = $slug;
        $url->user_id = auth()->id();
        $url->expires_at = now()->addDays(7);
        $url->save();

        GenerateQrCode::dispatch($url);

        return response()->json($url, 201);
    }

    public function index()
    {
        $links = Url::where('user_id', auth()->id())->latest()->get();
        return view('dashboard.home', compact('links'));
    }
}
