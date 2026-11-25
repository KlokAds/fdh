<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true));

        return [
            'slug' => Str::slug($title),
            'title' => ['en' => $title],
            'summary' => ['en' => fake()->sentence()],
            'description' => ['en' => fake()->paragraph()],
            'features' => ['en' => fake()->word().', '.fake()->word()],
            'icon' => 'building',
            'sort' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
