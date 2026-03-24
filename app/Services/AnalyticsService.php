<?php

namespace App\Services;

use App\Models\Url;

class AnalyticsService {

    public function getAnalytics(Url $link, $days): array
    {
        $startDate = now()->subDays($days);

        $allClicks = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->get();

        $totalClicks = $allClicks->count();

        $clicksByHour = $allClicks->groupBy(fn($c) => $c->clicked_at->format('H') . 'h')
            ->map(fn($g) => $g->count());

        $clicksOverTime = $allClicks->groupBy(fn($c) => $c->clicked_at->format('d M'))
            ->map(fn($g) => $g->count());

        $countryStats = $allClicks->whereNotNull('country')
            ->groupBy('country')
            ->map(function ($group) use ($totalClicks) {
                $count = $group->count();
                return [
                    'clicks' => $count,
                    'percentage' => $totalClicks > 0 ? round(($count / $totalClicks) * 100, 1) : 0,
                ];
            })->sortByDesc('clicks');

        $topCountries = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->selectRaw("country, COUNT(*) as clicks")
            ->groupBy('country')
            ->orderByDesc('clicks')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($totalClicks) {
                $item->percentage = $totalClicks > 0
                    ? round(($item->clicks / $totalClicks) * 100, 1)
                    : 0;
                return $item;
            });

        $recentClicks = $link->clicks()
            ->where('clicked_at', '>=', $startDate)
            ->latest('clicked_at')
            ->take(10)
            ->get();

        $maxClicks = $countryStats->max('clicks') ?: 1;

        $heatmapData = $countryStats->mapWithKeys(function ($item, $key) use ($maxClicks) {
            return [strtoupper($key) => $item['clicks'] / $maxClicks];
        });

        return [
            'clicksOverTime' => $clicksOverTime,
            'clicksByHour' => $clicksByHour,
            'topCountries' => $topCountries,
            'recentClicks' => $recentClicks,
            'totalClicks' => $totalClicks,
            'heatmap' => $heatmapData,
        ];
    }
}
