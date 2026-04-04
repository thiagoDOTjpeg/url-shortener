<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Controllers;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase {

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_should_render_analytics_dashboard_successfully() {
            $user = $this->authenticate();

            $link = Url::factory()->create([
                'id' => 'xpto123',
                'click_count' => 20,
                'created_at' => now()->subDays(2),
                'user_id' => $user->id,
                'original_url' => 'https://www.example.com',
                'expires_at' => now()->addDays(5),
            ]);

            $response = $this->get("/dashboard/analytics/{$link->id}");

            $response->assertStatus(200);
            $response->assertViewIs('dashboard.analytics');
    }

    public function test_should_throws_an_not_found_error_if_url_does_not_exist() {
        $this->authenticate();

        $response = $this->get("/dashboard/analytics/xpto123");

        $response->assertStatus(404);
    }
}
