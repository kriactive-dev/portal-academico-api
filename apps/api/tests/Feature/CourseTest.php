<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('GET /api/v1/courses', function () {
    it('lists courses paginated', function () {
        Course::factory()->count(15)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'last_page', 'total', 'per_page'],
            ])
            ->assertJsonPath('meta.total', 15);
    });

    it('returns empty list when no courses', function () {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/courses');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 0);
    });

    it('requires authentication', function () {
        $this->getJson('/api/v1/courses')->assertStatus(401);
    });
});

describe('POST /api/v1/courses', function () {
    it('creates a course', function () {
        $payload = [
            'name' => 'Computer Science',
            'description' => 'A comprehensive CS degree',
            'duration_months' => 36,
            'tuition' => 1500.00,
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/courses', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Computer Science');
    });

    it('validates required fields', function () {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/courses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'duration_months', 'tuition']);
    });

    it('validates tuition is numeric', function () {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/courses', [
                'name' => 'Test',
                'duration_months' => 12,
                'tuition' => 'not-a-number',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tuition']);
    });

    it('validates duration_months is integer minimum 1', function () {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/courses', [
                'name' => 'Test',
                'duration_months' => 0,
                'tuition' => 100,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['duration_months']);
    });
});

describe('GET /api/v1/courses/{course}', function () {
    it('shows a course', function () {
        $course = Course::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $course->id);
    });

    it('returns 404 for non-existent course', function () {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/courses/99999');

        $response->assertStatus(404);
    });
});

describe('PUT /api/v1/courses/{course}', function () {
    it('updates a course', function () {
        $course = Course::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/courses/{$course->id}", [
                'name' => 'Updated Name',
                'duration_months' => 24,
                'tuition' => 2000,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    });

    it('returns 404 updating non-existent course', function () {
        $response = $this->withToken($this->token)
            ->putJson('/api/v1/courses/99999', [
                'name' => 'Test',
                'duration_months' => 12,
                'tuition' => 100,
            ]);

        $response->assertStatus(404);
    });
});

describe('DELETE /api/v1/courses/{course}', function () {
    it('soft-deletes a course', function () {
        $course = Course::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/courses/{$course->id}");

        $response->assertStatus(200);
        $this->assertNotNull(Course::withTrashed()->find($course->id)->deleted_at);
    });
});

describe('PATCH /api/v1/courses/{id}/restore', function () {
    it('restores a soft-deleted course', function () {
        $course = Course::factory()->create();
        $course->delete();

        $response = $this->withToken($this->token)
            ->patchJson("/api/v1/courses/{$course->id}/restore");

        $response->assertStatus(200);
        $this->assertNull(Course::withTrashed()->find($course->id)->deleted_at);
    });
});

describe('DELETE /api/v1/courses/{id}/force', function () {
    it('force deletes a course', function () {
        $course = Course::factory()->create();
        $course->delete();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/courses/{$course->id}/force");

        $response->assertStatus(200);
        $this->assertModelMissing($course);
    });
});

describe('GET /api/v1/courses/all/active', function () {
    it('lists only active courses', function () {
        Course::factory()->create(['is_active' => true]);
        Course::factory()->create(['is_active' => false]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/courses/all/active');

        $response->assertStatus(200);
        expect(collect($response->json('data')))->each->toHaveKey('is_active', true);
    });
});

describe('POST /api/v1/courses/{course}/duplicate', function () {
    it('duplicates a course with its school classes', function () {
        $course = Course::factory()->create(['name' => 'Original']);
        $class = \App\Models\SchoolClass::factory()->create(['course_id' => $course->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/courses/{$course->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Original (copy)');
    });
});
