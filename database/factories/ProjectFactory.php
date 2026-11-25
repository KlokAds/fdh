<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
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
            'category' => fake()->randomElement(Project::CATEGORIES),
            'location' => ['en' => fake()->city()],
            'year' => (string) fake()->year(),
            'description' => ['en' => fake()->paragraph()],
            'is_featured' => false,
            'is_published' => true,
            'sort' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }
}
