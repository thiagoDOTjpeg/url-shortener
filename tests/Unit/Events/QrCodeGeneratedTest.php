<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Unit\Events;

use App\Events\QrCodeGenerated;
use App\Models\Url;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QrCodeGeneratedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_broadcasts_to_the_authenticated_user_private_channel_with_a_compact_payload(): void
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test-user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $url = Url::factory()->create([
            'user_id' => $user->id,
            'qr_code' => '<svg>QR CODE</svg>',
        ]);

        $event = new QrCodeGenerated($url);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-App.Models.User.' . $user->id, $channels[0]->name);
        $this->assertSame('qr-code-event', $event->broadcastAs());
        $this->assertSame([
            'url' => [
                'id' => $url->id,
                'qr_code' => '<svg>QR CODE</svg>',
                'user_id' => $user->id,
            ],
        ], $event->broadcastWith());
    }
}


