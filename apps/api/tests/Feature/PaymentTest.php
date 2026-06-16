<?php

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('CRUD /api/v1/payments', function () {
    it('lists paginated', function () {
        Payment::factory()->count(3)->create();
        $this->withToken($this->token)->getJson('/api/v1/payments')->assertStatus(200);
    });

    it('creates a payment', function () {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $response = $this->withToken($this->token)->postJson('/api/v1/payments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'reference_month' => '2025-01',
            'amount' => 299.99,
            'status' => 'paid',
            'method' => 'cash',
            'due_date' => '2025-01-15',
        ]);
        $response->assertStatus(201)->assertJsonPath('data.amount', 299.99);
    });

    it('validates status enum', function () {
        $this->withToken($this->token)->postJson('/api/v1/payments', [
            'student_id' => 1,
            'course_id' => 1,
            'reference_month' => '2025-01',
            'amount' => 100,
            'status' => 'invalid_status',
            'method' => 'invalid_method',
        ])->assertStatus(422);
    });

    it('gets payments by student', function () {
        $student = Student::factory()->create();
        Payment::factory()->count(3)->create(['student_id' => $student->id]);
        $this->withToken($this->token)
            ->getJson("/api/v1/payments/student/{$student->id}/records")
            ->assertStatus(200);
    });

    it('gets student payment summary', function () {
        $student = Student::factory()->create();
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 100, 'status' => 'paid']);
        Payment::factory()->create(['student_id' => $student->id, 'amount' => 50, 'status' => 'pending']);
        $this->withToken($this->token)
            ->getJson("/api/v1/payments/student/{$student->id}/summary")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['totalPaid', 'totalPending', 'totalOverdue', 'paymentsCount']]);
    });

    it('shows, updates, soft-deletes, restores, force-deletes', function () {
        $payment = Payment::factory()->create();
        $this->withToken($this->token)->getJson("/api/v1/payments/{$payment->id}")->assertStatus(200);
        $this->withToken($this->token)->putJson("/api/v1/payments/{$payment->id}", ['status' => 'paid']);
        $this->withToken($this->token)->deleteJson("/api/v1/payments/{$payment->id}");
        $this->assertNotNull(DB::table('payments')->where('id', $payment->id)->value('deleted_at'));

        $id = $payment->id;
        $this->withToken($this->token)->patchJson("/api/v1/payments/{$id}/restore");
        $this->assertNull(DB::table('payments')->where('id', $payment->id)->value('deleted_at'));

        $this->withToken($this->token)->deleteJson("/api/v1/payments/{$id}");
        $this->withToken($this->token)->deleteJson("/api/v1/payments/{$id}/force");
        $this->assertModelMissing($payment);
    });
});
