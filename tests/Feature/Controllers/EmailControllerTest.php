<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailControllerTest extends TestCase {

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_should_prevent_user_access_dashboard_without_verifying_email() {
        $this->authenticate([
            'name' => 'thiago',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
        ]);

        $response = $this->get('/dashboard/home');

        $response->assertRedirect(route('verification.notice'));

        $verifyNoticeResponse = $this->get(route('verification.notice'));

        $verifyNoticeResponse->assertOk();
        $verifyNoticeResponse->assertViewIs('auth.verify-email');
    }

    public function test_should_allow_user_with_verified_email_access_dashboard() {
        $this->authenticate([
            'name' => 'thiago',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/dashboard/home');

        $response->assertOk();
        $response->assertViewIs('dashboard.home');
    }

    /**
     * @throws \Exception
     */
    public function test_should_send_email_verification_notification()
    {
        Notification::fake();

        $user = $this->authenticate([
            'name' => 'thiago',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null
        ]);

        $response = $this->from("/dashboard/home")->post('/email/verification-notification');
        $response->assertRedirect('/dashboard/home');
        $response->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_should_verify_email_with_valid_signed_url()
    {
        $user = User::factory()->create([
            'name' => 'thiago',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard/home');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_should_not_verify_email_with_invalid_signature()
    {
        $user = User::factory()->create([
            'name' => 'thiago',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $tamperedUrl = $url . 'manipulacao';

        $response = $this->actingAs($user)->get($tamperedUrl);

        $response->assertStatus(403);
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
