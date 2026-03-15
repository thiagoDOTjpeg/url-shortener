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

    public function show($slug, Request $request)
    {
        $url = Url::findOrFail($slug);

        $position = Location::get($request->ip());

        $url->clicks()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'from' => $request->query('from', 'link'),
            'country' => $position?->countryCode,
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

    public function analytics(Request $request, $slug)
    {
        $link = auth()->user()
            ->urls()
            ->latest()
            ->get();
        $days = $request->query('days', 7);
        $startDate = now()->subDays($days);

        $totalClicksInPeriod = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->count();

        $clicksByHour = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->selectRaw("TO_CHAR(clicked_at, 'HH24') || 'h' as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $clicksOverTime = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->selectRaw("TO_CHAR(clicked_at, 'DD Mon') as date_label, COUNT(*) as clicks")
            ->groupBy('date_label')
            ->orderByRaw('MIN(clicked_at)')
            ->get();

        $topCountries = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->selectRaw("country, COUNT(*) as clicks")
            ->groupBy('country')
            ->orderByDesc('clicks')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($totalClicksInPeriod) {
                $item->percentage = $totalClicksInPeriod > 0
                    ? round(($item->clicks / $totalClicksInPeriod) * 100, 1)
                    : 0;
                return $item;
            });

        $recentClicks = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->latest('clicked_at')
            ->paginate(10);

        return view('dashboard.analytics', compact(
            'link',
            'clicksOverTime',
            'clicksByHour',
            'topCountries',
            'recentClicks',
            'days',
            'totalClicksInPeriod'
        ));
    }
}
