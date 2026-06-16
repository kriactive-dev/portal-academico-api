<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'student_number' => fake()->unique()->numerify('STU-####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'birth_date' => fake()->date('Y-m-d', '2005-01-01'),
            'enrollment_date' => fake()->date('Y-m-d'),
            'status' => 'active',
        ];
    }
}
