<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Fee;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(['monthly', 'enrollment', 'registration', 'exam', 'certificate', 'other']),
            'amount' => fake()->randomFloat(2, 50, 2000),
            'course_id' => Course::factory(),
            'is_active' => true,
        ];
    }
}
