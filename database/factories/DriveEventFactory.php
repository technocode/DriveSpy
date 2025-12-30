<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DriveEvent>
 */
class DriveEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = ['created', 'updated', 'moved', 'renamed', 'trashed', 'restored', 'permission_changed'];

        return [
            'drive_file_id' => fake()->uuid(),
            'event_type' => fake()->randomElement($eventTypes),
            'change_source' => 'changes_api',
            'occurred_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'actor_email' => fake()->safeEmail(),
            'actor_name' => fake()->name(),
            'summary' => fake()->sentence(),
        ];
    }
}
