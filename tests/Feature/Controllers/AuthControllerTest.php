<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthControllerTest extends TestCase {

    use RefreshDatabase;

    private readonly User $user;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'thiago',
            'email' => 'test_shoulde@gmail.com',
            'password' => Hash::make('secret'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function test_should_send_forgot_password_email() {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => $this->user->email,
        ]
        );

        Notification::assertSentTo($this->user, ResetPassword::class);

        $response->assertSessionHas('status', 'We have emailed your password reset link.');
    }

    /**
     * @throws \Exception
     */
    public function test_should_log_warning_when_tried_to_reset_password_with_non_existent_email()
    {
        $logSpy = Log::spy();
        Notification::fake();
        $nonExistentEmail = 'non_existent@example.com';

        $response = $this->post('/forgot-password', [
            'email' => $nonExistentEmail,
        ]);
        $logSpy->shouldHaveReceived('warning')
            ->once()
            ->with(\Mockery::on(function($argument) {
                return str_contains($argument, 'Someone tried to reset password');
            }));

        $response->assertSessionHas('status', 'We have emailed your password reset link.');
        Notification::assertNothingSent();
    }

    public function test_should_reset_password_successfully_and_redirect_to_login() {
        $token = Str::random(60);

        $this->app['db']->table('password_reset_tokens')->insert([
            'email' => $this->user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'email' => $this->user->email,
            'password' => 'newsecret',
            'password_confirmation' => 'newsecret',
            'token' => $token,
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'Your password has been reset.');

        $this->assertTrue(Hash::check('newsecret', $this->user->fresh()->password));
    }

    public function test_should_successfully_makes_login() {
        $oldSessionId = session()->getId();
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'secret',
        ]);

        $newSessionId = session()->getId();

        $response->assertRedirect('/dashboard/home');
        $this->assertAuthenticatedAs($this->user);
        $this->assertNotEquals($oldSessionId, $newSessionId);
        $this->assertTrue(Auth::check());
    }

    public function test_should_fail_login_with_wrong_credentials()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email', 'The provided credentials do not match our records.');
        $this->assertGuest();
    }

    public function test_should_successfully_register_and_redirect_to_verification_email() {
        Event::fake();
        $request = [
            'name' => 'thiago',
            'email' => 'new_email@gmail.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->post('/register', $request);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', [
            'email' => $request['email'],
            'name' => $request['name'],
            'email_verified_at' => null,
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_should_successfully_logout() {
        $oldSessionId = session()->getId();
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_should_redirect_to_login_if_user_not_connected() {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_should_returns_successfully_user() {
        $this->actingAs($this->user);
        $response = $this->get('/me');

        $response->assertOk();
        $this->assertAuthenticatedAs($this->user);
        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
            'name' => $this->user->name,
        ]);
        $response->assertJsonMissing([
            'password',
            'remember_token',
        ]);
    }

    public function test_should_returns_302_and_redirect_to_login() {
        $response = $this->get('/me');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_should_display_updated_username() {
        $oldName = $this->user->name;
        $this->user->update([
            'name' => 'thiago123',
        ]);

        $this->actingAs($this->user);
        $response = $this->get('/me');

        $response->assertOk();
        $this->assertAuthenticatedAs($this->user);
        $response->assertJson([
            'name' => 'thiago123',
            'email' => $this->user->email,
        ]);
        $this->assertnotEquals($oldName, $this->user->name);
    }

}
