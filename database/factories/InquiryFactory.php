<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '017'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'service' => 'Building Design',
            'location' => fake()->city(),
            'message' => fake()->sentence(),
            'status' => 'new',
        ];
    }
}
