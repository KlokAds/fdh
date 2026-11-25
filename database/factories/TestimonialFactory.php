<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'subtitle' => ['en' => fake()->city()],
            'quote' => ['en' => fake()->sentence(12)],
            'rating' => 5,
            'sort' => 0,
            'is_active' => true,
        ];
    }
}
