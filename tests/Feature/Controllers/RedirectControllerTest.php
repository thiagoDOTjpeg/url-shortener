<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Controllers;

use App\Jobs\TrackClick;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RedirectControllerTest extends TestCase {

    use RefreshDatabase;

    private readonly User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'thiago',
            'email' => 'test_shoulde@gmail.com',
            'password' => Hash::make('secret'),
        ]);
    }

    public function test_should_it_returns_redirect_view_and_dispatches_tracking_job() {
        Queue::fake();

        Url::factory()->create([
            'id' => 'xpto123',
            'original_url' =>  'https://www.github.com',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/r/xpto123');

        $response->assertStatus(200);

        $response->assertViewIs('r.redirect');

        $response->assertViewHas('destination', 'https://www.github.com');

        Queue::assertPushed(TrackClick::class, function (TrackClick $job) {
            return $job->slug === 'xpto123';
        });
    }

    public function test_should_it_returns_an_error_if_the_url_does_not_exist() {
        Queue::fake();

        $response = $this->get('/r/xpto123');

        $response->assertNotFound();

        Queue::assertNotPushed(TrackClick::class);
    }

    public function test_should_it_click_count_increment_when_redirected() {
        $url = Url::factory()->create([
            'id' => 'xpto123',
            'original_url' =>  'https://www.github.com',
            'user_id' => $this->user->id,
            'click_count' => 0,
        ]);

        $response = $this->get('r/xpto123');

        $response->assertStatus(200);

        $response->assertViewIs('r.redirect');

        $this->assertDatabaseHas('urls', [
            'id' => $url->id,
            'original_url' =>  $url->original_url,
            'click_count' => 1,
        ]);
    }

    public function test_should_it_throws_an_exception_if_the_url_is_invalid() {
        Queue::fake();

        $response = $this->get('r/invalidslug');

        Queue::assertNotPushed(TrackClick::class);

        $response->assertNotFound();
    }

    public function test_should_it_throws_an_exceptions_if_the_url_is_expired() {
        Queue::fake();
        Url::factory()->create([
            'id' => 'xpto123',
            'original_url' =>  'https://www.github.com',
            'user_id' => $this->user->id,
            'click_count' => 0,
            'expires_at' => now()->subDays(30),
        ]);

        $response = $this->get('r/xpto123');

        Queue::assertNotPushed(TrackClick::class);
        $response->assertViewIs('r.expired-link');
    }

}
