<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait WithAuthentication
{
    public function authenticate(array $attributes = []): User
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];

        $user = User::factory()->create(array_merge($defaults, $attributes));
        $this->actingAs($user);

        return $user;
    }

    public function authenticateMultiple(int $count = 2, array $attributes = []): User
    {
        $users = User::factory($count)->create($attributes);
        $this->actingAs($users->first());

        return $users->first();
    }

    public function logout(): void
    {
        $this->actingAsGuest();
    }

    public function assertUserIsAuthenticated(User $user): void
    {
        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    public function assertUserIsNotAuthenticated(): void
    {
        $this->assertFalse(auth()->check());
        $this->assertNull(auth()->user());
    }

    public function authenticateWithToken(User $user, string $tokenName = 'api-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    public function withAuthToken(User $user)
    {
        $token = $this->authenticateWithToken($user);

        return $this->withHeader('Authorization', "Bearer {$token}");
    }
}

