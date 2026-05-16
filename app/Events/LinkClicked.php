<?php

namespace App\Events;

use App\Models\UrlClick;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LinkClicked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly UrlClick $click;

    public function __construct(UrlClick $click)
    {
        $this->click = $click;
    }

    public function broadcastAs(): string
    {
        return 'link-clicked';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.UrlClick.'.$this->click->url_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'linkClicked' => [
                'id' => $this->click->id,
                'url_id' => $this->click->url_id,
                'ip_address' => $this->click->ip_address,
                'user_agent' => $this->click->user_agent,
                'referer' => $this->click->referer,
                'from' => $this->click->from,
                'country' => $this->click->country,
                'clicked_at' => $this->click->clicked_at?->toIso8601String(),
                'longitude' => $this->click->longitude,
                'latitude' => $this->click->latitude,
                'browser' => $this->click->browser,
                'is_bot' => $this->click->is_bot,
                'os' => $this->click->os,
                'device_type' => $this->click->device_type,
            ],
        ];
    }
}


