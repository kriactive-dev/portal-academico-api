<?php

use App\Models\Course;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('GET /api/v1/dashboard/metrics', function () {
    it('returns aggregate counts', function () {
        Course::factory()->count(2)->create();
        Student::factory()->count(5)->create();
        Trainer::factory()->count(3)->create();
        SchoolClass::factory()->count(4)->create();
        Fee::factory()->count(6)->create();
        Payment::factory()->count(10)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'totalCourses', 'totalStudents', 'totalTrainers',
                    'totalClasses', 'totalFees', 'totalPayments',
                    'totalRevenue', 'totalPendingPayments',
                ],
            ])
            ->assertJsonPath('data.totalCourses', 22)
            ->assertJsonPath('data.totalStudents', 15)
            ->assertJsonPath('data.totalTrainers', 3)
            ->assertJsonPath('data.totalClasses', 4)
            ->assertJsonPath('data.totalFees', 6)
            ->assertJsonPath('data.totalPayments', 10);
    });

    it('returns zeros when no data', function () {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJsonPath('data.totalCourses', 0)
            ->assertJsonPath('data.totalRevenue', 0)
            ->assertJsonPath('data.totalPendingPayments', 0);
    });

    it('requires authentication', function () {
        $this->getJson('/api/v1/dashboard/metrics')->assertStatus(401);
    });
});
