<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => fake()->name()],
            'designation' => ['en' => fake()->jobTitle()],
            'sort' => 0,
            'is_active' => true,
        ];
    }
}
