<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'reference_month' => fake()->date('Y-m'),
            'amount' => fake()->randomFloat(2, 50, 2000),
            'status' => fake()->randomElement(['pending', 'paid', 'overdue']),
            'method' => fake()->randomElement(['cash', 'transfer', 'mpesa', 'emola', 'deposit']),
            'payment_date' => fake()->optional()->date('Y-m-d'),
            'due_date' => fake()->date('Y-m-d'),
        ];
    }
}
