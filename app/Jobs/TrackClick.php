<?php

namespace App\Jobs;

use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class TrackClick implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $ip,
        public readonly string $userAgent,
        public readonly ?string $referer,
        public readonly string $from
        )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = Url::findOrFail($this->slug);

        $agent = new Agent();
        $agent->setUserAgent($this->userAgent);
        $browser = $agent->browser();
        $os = $agent->platform();
        $device = $agent->deviceType() ?: 'Bot/unknown';

        $position = Location::get($this->ip) ?: null;

        $url->clicks()->create([
            'ip_address' => $this->ip,
            'user_agent' => $this->userAgent,
            'referer' => $this->referer,
            'from' => $this->from,
            'country' => $position?->countryCode,
            'longitude' => $position?->longitude,
            'latitude' => $position?->latitude,
            'browser' => $browser,
            'os' => $os,
            'device_type' => $device,
        ]);

        $url->increment('click_count');
        $url->update();
    }
}
