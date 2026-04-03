<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Controllers;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase {

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_should_it_successfully_render_dashboard_home_view() {
        $user = $this->authenticate();

        Url::factory()->count(3)->create([
            'original_url' => 'https://www.google.com',
            'user_id' => $user->id,
        ]);

        $response = $this->get('/dashboard/home');

        $response->assertStatus(200);
        $response->assertOk();
        $response->assertViewIs('dashboard.home');

        self::assertNotNull($response->viewData('links'));
        self::assertCount(3, $response->viewData('links'));
    }

    public function test_should_it_render_dashboard_home_view_with_no_links() {
        $this->authenticate();

        $response = $this->get('/dashboard/home');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.home');

        self::assertTrue($response->viewData('links')->isEmpty());
    }

}
