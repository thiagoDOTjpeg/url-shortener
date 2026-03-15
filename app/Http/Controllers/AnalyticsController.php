<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService) {
    }

    public function show(Request $request, string $slug) {
        $link = auth()->user()->urls()->where('id', $slug)->firstOrFail();
        $days = $request->query('days', 7);

        [$clicksOverTime, $clicksByHour, $topCountries, $recentClicks, $totalClicksInPeriod] = $this->analyticsService->getAnalytics($link, $days);

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
