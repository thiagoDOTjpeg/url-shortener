<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrlRequest;
use App\Jobs\GenerateQrCode;
use App\Models\UrlShortened;
use Base62\Base62;
use Illuminate\Http\JsonResponse;
use Random\RandomException;

class UrlController extends Controller
{

    public function __construct(private readonly Base62 $base62) {}

    public function show($id) {
        $url = UrlShortened::findOrFail($id);
        return redirect($url->original_url);
    }

    /**
     * @throws RandomException
     */
    public function store(StoreUrlRequest $request): JsonResponse
    {
        $slug = $this->base62->encode(random_int(1, PHP_INT_MAX));
        $url = new UrlShortened($request->validated());
        $url->id = $slug;
        $url->expires_at = now()->addDays(7);
        $url->save();

        GenerateQrCode::dispatch($url);

        return response()->json($url, 201);
    }
}
