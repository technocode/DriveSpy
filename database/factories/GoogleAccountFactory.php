<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoogleAccount>
 */
class GoogleAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'google_user_id' => fake()->uuid(),
            'email' => fake()->safeEmail(),
            'display_name' => fake()->name(),
            'avatar_url' => fake()->imageUrl(),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addHour(),
            'scopes' => 'https://www.googleapis.com/auth/drive.readonly',
            'drive_start_page_token' => fake()->uuid(),
            'last_synced_at' => now(),
            'status' => 'active',
        ];
    }
}
