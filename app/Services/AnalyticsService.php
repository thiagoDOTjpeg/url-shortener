<?php

namespace App\Services;

use App\Models\Url;

class AnalyticsService {

    public function getAnalytics(Url $link, ?int $days = 7, bool $includeBots = false): array
    {
        $clicksQuery = $link->clicks();

        if (!is_null($days)) {
            $startDate = $days === 1
                ? now()->startOfDay()
                : now()->subDays($days - 1)->startOfDay();

            $clicksQuery->where('clicked_at', '>=', $startDate);
        }

        if (!$includeBots) {
            $clicksQuery->where('is_bot', false);
        }

        $allClicks = (clone $clicksQuery)->get();

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

        $topCountries = (clone $clicksQuery)
            ->selectRaw("country, COUNT(*) as clicks")
            ->groupBy('country')
            ->orderByDesc('clicks')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($totalClicks) {
                $item->percentage = round(($item->clicks / $totalClicks) * 100, 1);
                return $item;
            });

        $recentClicks = (clone $clicksQuery)
            ->latest('clicked_at')
            ->take(5)
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
