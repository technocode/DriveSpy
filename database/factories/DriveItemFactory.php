<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DriveItem>
 */
class DriveItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isFolder = fake()->boolean(30);

        return [
            'drive_file_id' => fake()->uuid(),
            'parent_drive_file_id' => null,
            'name' => $isFolder ? fake()->words(2, true) : fake()->word() . '.' . fake()->fileExtension(),
            'mime_type' => $isFolder ? 'application/vnd.google-apps.folder' : fake()->mimeType(),
            'is_folder' => $isFolder,
            'size_bytes' => $isFolder ? null : fake()->numberBetween(1000, 10000000),
            'md5_checksum' => $isFolder ? null : fake()->md5(),
            'modified_time' => now()->subDays(fake()->numberBetween(1, 30)),
            'created_time' => now()->subDays(fake()->numberBetween(31, 365)),
            'trashed' => false,
            'starred' => fake()->boolean(10),
            'owned_by_me' => true,
            'last_seen_at' => now(),
        ];
    }
}
