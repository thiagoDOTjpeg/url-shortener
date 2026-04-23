<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController
{
    public function __construct(private readonly AnalyticsService $analyticsService) {
    }

    public function show(Request $request, string $slug) {

        $link = auth()->user()->urls()->where('id', $slug)->firstOrFail();

        $periodOptions = [
            'today' => 1,
            '7d' => 7,
            '30d' => 30,
            'total' => null,
        ];

        $selectedPeriod = (string) $request->query('period', '7d');

        if (!array_key_exists($selectedPeriod, $periodOptions)) {
            $selectedPeriod = '7d';
        }

        $days = $periodOptions[$selectedPeriod];

        $includeBots = $request->boolean(
            'include_bots',
            $request->boolean('bot_enabled', false)
        );

        $analytics = $this->analyticsService->getAnalytics($link, $days, $includeBots);

        return view('dashboard.analytics', array_merge([
            'link' => $link,
            'days' => $days,
            'selectedPeriod' => $selectedPeriod,
            'includeBots' => $includeBots,
        ], $analytics));
    }
}
