<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonitoredFolder>
 */
class MonitoredFolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'root_drive_file_id' => fake()->uuid(),
            'root_name' => fake()->words(3, true),
            'include_subfolders' => true,
            'status' => 'active',
            'last_indexed_at' => now(),
        ];
    }
}
