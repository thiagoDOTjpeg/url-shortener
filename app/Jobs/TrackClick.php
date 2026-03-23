<?php

namespace App\Jobs;

use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Stevebauman\Location\Facades\Location;

class TrackClick implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $slug,
        private readonly string $ip,
        private readonly string $userAgent,
        private readonly ?string $referer,
        private readonly string $from
        )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = Url::findOrFail($this->slug);

        $position = Location::get($this->ip);

        $url->clicks()->create([
            'ip_address' => $this->ip,
            'user_agent' => $this->userAgent,
            'referer' => $this->referer,
            'from' => $this->from,
            'country' => $position?->countryCode,
            'longitude' => $position?->longitude,
            'latitude' => $position?->latitude,
        ]);
        $url->increment('click_count');
        $url->update();
    }
}
