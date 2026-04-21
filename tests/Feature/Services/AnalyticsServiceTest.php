<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Services;

use App\Models\Url;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase {

    private readonly User $user;

    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        Carbon::setTestNow(2026, 01, 01, 01, 0, 0);
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'email@gmail.com',
            'password' => \Hash::make('password'),
        ]);

    }

    public function test_should_generate_analytics_and_return_it() {

        $url = Url::factory()->create([
            'id' =>  'xpto123',
            'user_id' => $this->user->id,
            'original_url' => 'https://www.google.com/'
        ]);

        $url->clicks()->createMany([
            ['clicked_at' => Carbon::now()->subHours(1), 'country' => 'US'],
            ['clicked_at' => Carbon::now()->subHours(2), 'country' => 'US'],
            ['clicked_at' => Carbon::now()->subHours(3), 'country' => 'BR'],
            ['clicked_at' => Carbon::now()->subHours(4), 'country' => null],
        ]);

        $analyticsService = new AnalyticsService();
        $result = $analyticsService->getAnalytics($url, 7);

        $this->assertEquals(4, $result['totalClicks']);
        $this->assertArrayHasKey('BR', $result['heatmap']->toArray());
    }

    public function test_should_generate_analytics_and_return_it_with_empty_top_country() {

        $url = Url::factory()->create([
            'id' =>  'xpto123',
            'user_id' => $this->user->id,
            'original_url' => 'https://www.google.com/'
        ]);

        $analyticsService = new AnalyticsService();
        $result = $analyticsService->getAnalytics($url, 7);

        $this->assertEmpty($result['totalClicks']);
        $this->assertEmpty($result['heatmap']);
        $this->assertEmpty(0, $result['topCountries']);
    }
}
