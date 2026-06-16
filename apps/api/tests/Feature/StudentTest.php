<?php

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('CRUD /api/v1/students', function () {
    it('lists paginated', function () {
        Student::factory()->count(5)->create();
        $response = $this->withToken($this->token)->getJson('/api/v1/students');
        $response->assertStatus(200)->assertJsonPath('success', true);
    });

    it('creates a student', function () {
        $payload = [
            'student_number' => 'STU-001',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '912345678',
            'birth_date' => '2000-01-15',
            'status' => 'active',
            'enrollment_date' => '2025-09-01',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/students', $payload);

        $response->assertStatus(201)->assertJsonPath('data.email', 'john@example.com');
    });

    it('validates unique email', function () {
        Student::factory()->create(['email' => 'existing@example.com']);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/students', [
                'student_number' => 'STU-002',
                'name' => 'Jane',
                'email' => 'existing@example.com',
                'phone' => '912345679',
                'birth_date' => '2000-01-15',
                'enrollment_date' => '2025-09-01',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    });

    it('shows a student', function () {
        $student = Student::factory()->create();
        $response = $this->withToken($this->token)
            ->getJson("/api/v1/students/{$student->id}");
        $response->assertStatus(200)->assertJsonPath('data.id', $student->id);
    });

    it('updates a student', function () {
        $student = Student::factory()->create();
        $response = $this->withToken($this->token)
            ->putJson("/api/v1/students/{$student->id}", ['name' => 'Updated']);
        $response->assertStatus(200)->assertJsonPath('data.name', 'Updated');
    });

    it('soft-deletes', function () {
        $student = Student::factory()->create();
        $this->withToken($this->token)->deleteJson("/api/v1/students/{$student->id}");
        $this->assertNotNull(Student::withTrashed()->find($student->id)->deleted_at);
    });

    it('restores', function () {
        $student = Student::factory()->create();
        $student->delete();
        $this->withToken($this->token)->patchJson("/api/v1/students/{$student->id}/restore");
        $this->assertNull(Student::withTrashed()->find($student->id)->deleted_at);
    });

    it('force deletes', function () {
        $student = Student::factory()->create();
        $student->delete();
        $this->withToken($this->token)->deleteJson("/api/v1/students/{$student->id}/force");
        $this->assertModelMissing($student);
    });

    it('toggles status', function () {
        $student = Student::factory()->create(['status' => 'active']);
        $this->withToken($this->token)
            ->patchJson("/api/v1/students/{$student->id}/toggle-status")
            ->assertStatus(200);
    });

    it('returns 404 on non-existent', function () {
        $this->withToken($this->token)->getJson('/api/v1/students/99999')->assertStatus(404);
    });
});
