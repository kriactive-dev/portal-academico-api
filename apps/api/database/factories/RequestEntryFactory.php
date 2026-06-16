<?php

namespace Database\Factories;

use App\Models\RequestEntry;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestEntryFactory extends Factory
{
    protected $model = RequestEntry::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'type' => fake()->randomElement(['certificate', 'internship_approval']),
            'status' => fake()->randomElement(['pending', 'approved', 'denied']),
            'submission_date' => fake()->dateTimeThisYear(),
            'response_date' => null,
            'denial_reason' => null,
            'details' => ['reason' => fake()->sentence()],
        ];
    }
}
