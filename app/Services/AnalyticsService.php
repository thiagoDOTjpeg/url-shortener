<?php

namespace App\Services;

use App\Models\Url;

class AnalyticsService {

    public function getAnalytics(Url $link, $days): array
    {
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

        return [
            $clicksOverTime,
            $clicksByHour,
            $topCountries,
            $recentClicks,
            $days,
            $totalClicksInPeriod
        ];
    }

}
