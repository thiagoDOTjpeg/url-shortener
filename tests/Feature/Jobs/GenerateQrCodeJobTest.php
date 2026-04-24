<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Jobs;

use App\Events\QrCodeGenerated;
use App\Jobs\GenerateQrCode;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tests\TestCase;

class GenerateQrCodeJobTest extends TestCase {

    use RefreshDatabase;

    private readonly User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
    }

    public function test_should_successfully_generate_qr_code() {
        $url = Url::factory()->create([
            'id' => '123',
            'user_id' => $this->user->id,
        ]);

        Event::fake([QrCodeGenerated::class]);

        QrCode::shouldReceive('generate')
            ->once()
            ->with(\Mockery::on(function ($argument) {
                return str_contains($argument, '/r/123');
            }))
            ->andReturn('<svg>QR CODE MOCK</svg>');


        $job = new GenerateQrCode($url);

        $job->handle();

        Event::assertDispatched(QrCodeGenerated::class, function (QrCodeGenerated $event) use ($url) {
            return $event->url->is($url->fresh()) && $event->url->qr_code === '<svg>QR CODE MOCK</svg>';
        });

        $this->assertNotNull($url->fresh()->qr_code);
        $this->assertEquals('<svg>QR CODE MOCK</svg>', $url->fresh()->qr_code);
    }

}
