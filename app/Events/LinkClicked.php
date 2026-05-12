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
            new PrivateChannel('App.Models.UrlClick.' . $this->click->url_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'linkClicked' => $this->click,
        ];
    }
}
