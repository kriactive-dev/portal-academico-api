<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'duration_months' => fake()->randomElement([6, 12, 18, 24, 36]),
            'tuition' => fake()->randomFloat(2, 500, 5000),
            'is_active' => true,
        ];
    }
}
