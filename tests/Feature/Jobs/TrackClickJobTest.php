<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Jobs\TrackClickJob;

use App\Jobs\TrackClick;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Tests\TestCase;

class TrackClickJobTest extends TestCase {

    use RefreshDatabase;

    private readonly User $user;
    private readonly Url $url;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'email@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->url = Url::factory()->create([
            'id' => 'xpto123',
            'original_url' => 'www.github.com',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_should_successfully_track_link_click() {
        $rawUserAgent = 'Mozilla/5.0 (Linux; Android 9; P80X_ROW Build/PPR1.180610.011; en-us) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36 Puffin/8.3.1.41624AT';
        $agent = new Agent();
        $agent->setUserAgent($rawUserAgent);
        $browser = $agent->browser();
        $os = $agent->platform();
        $device = $agent->deviceType();

        $job = new TrackClick(
            $this->url->id,
            '192.168.3.1',
            $rawUserAgent,
            'www.teste.com.br',
            'qrcode'
        );
        $job->handle();

        $this->assertEquals(1, $this->url->fresh()->click_count);
        $this->assertDatabaseHas('url_clicks', [
            'url_id' => $this->url->id,
            'from' => 'qrcode',
            'ip_address' => '192.168.3.1',
            'referer' => 'www.teste.com.br',
            'user_agent' => $rawUserAgent,
            'browser' => $browser,
            'os' => $os,
            'device_type' => $device,
        ]);
        $click = $this->url->fresh()->clicks()->first();
        $this->assertEquals($click->url_id, $this->url->id);
        $this->assertEquals($click->url->id, $this->url->id);
    }

}
