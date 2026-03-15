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

    public function __construct(private readonly Base62 $base62)
    {
    }

    public function destroy($slug)
    {
        $url = Url::where('id', $slug)->where('user_id', auth()->id())->firstOrFail();
        $url->delete();

        return response()->json(['message' => 'URL deleted successfully']);
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
}
