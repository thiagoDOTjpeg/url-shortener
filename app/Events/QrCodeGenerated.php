<?php

namespace App\Events;

use App\Models\Url;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QrCodeGenerated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Url $url;

    /**
     * Create a new event instance.
     */
    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->url->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'qr-code-event';
    }

    public function broadcastWith(): array
    {
        return [
            'url' => [
                'id' => $this->url->id,
                'qr_code' => $this->url->qr_code,
                'user_id' => $this->url->user_id,
            ],
        ];
    }
}
