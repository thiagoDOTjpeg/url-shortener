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

        $days = (int) $request->query('days', 7);

        $analytics = $this->analyticsService->getAnalytics($link, $days);
        return view('dashboard.analytics', array_merge([
            'link' => $link,
            'days' => $days
        ], $analytics));
    }
}
