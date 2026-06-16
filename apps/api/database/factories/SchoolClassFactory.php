<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'course_id' => Course::factory(),
            'shift' => fake()->randomElement(['morning', 'afternoon', 'evening']),
            'status' => fake()->randomElement(['planned', 'in_progress', 'completed']),
            'start_date' => fake()->date('Y-m-d', 'now'),
            'end_date' => fake()->date('Y-m-d', '+2 years'),
        ];
    }
}
