<?php

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('CRUD /api/v1/school-classes', function () {
    it('lists paginated', function () {
        SchoolClass::factory()->count(3)->create();
        $this->withToken($this->token)->getJson('/api/v1/school-classes')->assertStatus(200);
    });

    it('creates a school class', function () {
        $course = Course::factory()->create();
        $trainer = \App\Models\Trainer::factory()->create();
        $student = \App\Models\Student::factory()->create();
        $response = $this->withToken($this->token)->postJson('/api/v1/school-classes', [
            'name' => 'Class A',
            'course_id' => $course->id,
            'shift' => 'morning',
            'status' => 'planned',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-01',
            'trainer_ids' => [$trainer->id],
            'student_ids' => [$student->id],
        ]);
        $response->assertStatus(201)->assertJsonPath('data.name', 'Class A');
    });

    it('validates course_id exists', function () {
        $this->withToken($this->token)->postJson('/api/v1/school-classes', [
            'name' => 'Invalid',
            'course_id' => 99999,
            'shift' => 'morning',
            'status' => 'planned',
            'start_date' => '2025-01-01',
        ])->assertStatus(422);
    });

    it('validates shift enum', function () {
        $course = Course::factory()->create();
        $this->withToken($this->token)->postJson('/api/v1/school-classes', [
            'name' => 'Bad',
            'course_id' => $course->id,
            'shift' => 'midnight',
            'status' => 'planned',
            'start_date' => '2025-01-01',
        ])->assertStatus(422);
    });

    it('shows, updates, soft-deletes, restores, force-deletes', function () {
        $class = SchoolClass::factory()->create();

        $this->withToken($this->token)->getJson("/api/v1/school-classes/{$class->id}")->assertStatus(200);
        $this->withToken($this->token)->putJson("/api/v1/school-classes/{$class->id}", ['name' => 'Changed'])
            ->assertJsonPath('data.name', 'Changed');

        $this->withToken($this->token)->deleteJson("/api/v1/school-classes/{$class->id}")->assertStatus(200);
        $this->assertNotNull(DB::table('school_classes')->where('id', $class->id)->value('deleted_at'));

        $classId = $class->id;
        $this->withToken($this->token)->patchJson("/api/v1/school-classes/{$classId}/restore");
        $this->assertNull(DB::table('school_classes')->where('id', $class->id)->value('deleted_at'));

        $this->withToken($this->token)->deleteJson("/api/v1/school-classes/{$classId}");
        $this->withToken($this->token)->deleteJson("/api/v1/school-classes/{$classId}/force");
        $this->assertModelMissing($class);
    });
});
