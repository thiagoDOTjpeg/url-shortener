<?php

namespace Database\Factories;

use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Url>
 */
class UrlFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Url $url): void {
            $url->id ??= (string) Str::ulid();
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'original_url' => fake()->url(),
            'qr_code' => null,
            'click_count' => 0,
            'expires_at' => now()->addDays(7),
        ];
    }

}
