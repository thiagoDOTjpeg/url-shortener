<?php

/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Feature\Controllers;

use App\Jobs\GenerateQrCode;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class UrlControllerTest extends TestCase {

    use RefreshDatabase;

    public function test_should_destroy_the_url_successfully() {
        $user = $this->authenticate([
            'name' => 'thiago',
            'email' => 'test_shoulde@gmail.com',
            'password' => Hash::make('secret'),
        ]);

        $url = Url::factory()->create([
            'id' => 'xpto123',
            'original_url' => 'https://www.github.com',
            'user_id' => $user->id,
        ]);

        $response = $this->delete("/urls/{$url->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'URL deleted successfully']);
        $this->assertDatabaseMissing('urls', [
            'id' => $url->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_should_not_destroy_url_from_another_user() {
        $this->authenticate([
            'name' => 'thiago',
            'password' => Hash::make('secret'),
            'email' => 'user1@test.com'
        ]);
        $user2 = User::factory()->create([
            'name' => 'user2',
            'password' => Hash::make('secret'),
            'email' => 'user2@test.com'
        ]);

        $url = Url::factory()->create([
            'id' => 'xpto456',
            'original_url' => 'https://www.github.com',
            'user_id' => $user2->id,
        ]);

        $response = $this->delete("/urls/{$url->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('urls', ['id' => $url->id]);
    }

    public function test_should_not_destroy_url_when_not_authenticated() {
        $user = User::factory()->create([
            'name' => 'thiago',
            'email' => 'teste@gmail.com',
            'password' => Hash::make('secret'),
        ]);
        $url = Url::factory()->create([
            'id' => 'xpto456',
            'original_url' => 'https://www.github.com',
            'user_id' => $user->id
        ]);

        $this->logout();

        $response = $this->delete("/urls/{$url->id}");

        $response->assertStatus(302);
    }

    public function test_should_store_url_link_successfully() {
        Queue::fake();

        $user = $this->authenticate([
            'name' => 'thiago',
            'password' => Hash::make('secret'),
            'email' => 'teste@gmail.com',
        ]);

        $idempotencyKey = (string) Str::uuid();

        $response = $this->withHeaders([
            'Idempotency-Key' => $idempotencyKey,
        ])->post('/urls/shorten', [
            'original_url' => 'https://www.github.com',
        ]);


        $response->assertStatus(201);
        Queue::assertPushed(GenerateQrCode::class);

        $this->assertDatabaseHas('urls', [
            'id' => $response->json('id'),
            'user_id' => $user->id,
            'original_url' => 'https://www.github.com',
        ]);

    }

}
