<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SyncRun>
 */
class SyncRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $runTypes = ['initial_index', 'changes_sync', 'reindex'];
        $statuses = ['success', 'failed', 'partial'];
        $startedAt = now()->subHours(fake()->numberBetween(1, 24));

        return [
            'run_type' => fake()->randomElement($runTypes),
            'status' => fake()->randomElement($statuses),
            'started_at' => $startedAt,
            'finished_at' => $startedAt->copy()->addMinutes(fake()->numberBetween(1, 60)),
            'items_scanned' => fake()->numberBetween(0, 1000),
            'changes_fetched' => fake()->numberBetween(0, 100),
            'events_created' => fake()->numberBetween(0, 50),
        ];
    }
}
