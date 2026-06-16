<?php

use App\Models\Course;
use App\Models\Fee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('CRUD /api/v1/fees', function () {
    it('lists paginated', function () {
        Fee::factory()->count(3)->create();
        $this->withToken($this->token)->getJson('/api/v1/fees')->assertStatus(200);
    });

    it('creates a fee', function () {
        $course = Course::factory()->create();
        $response = $this->withToken($this->token)->postJson('/api/v1/fees', [
            'name' => 'Monthly Tuition',
            'type' => 'monthly',
            'amount' => 299.99,
            'course_id' => $course->id,
            'is_active' => true,
        ]);
        $response->assertStatus(201)->assertJsonPath('data.name', 'Monthly Tuition');
    });

    it('validates type enum', function () {
        $course = Course::factory()->create();
        $this->withToken($this->token)->postJson('/api/v1/fees', [
            'name' => 'Bad',
            'type' => 'invalid',
            'amount' => 100,
            'course_id' => $course->id,
        ])->assertStatus(422);
    });

    it('shows, updates, soft-deletes, restores, force-deletes', function () {
        $fee = Fee::factory()->create();
        $this->withToken($this->token)->getJson("/api/v1/fees/{$fee->id}")->assertStatus(200);
        $this->withToken($this->token)->putJson("/api/v1/fees/{$fee->id}", ['name' => 'Updated Fee'])
            ->assertJsonPath('data.name', 'Updated Fee');

        $this->withToken($this->token)->deleteJson("/api/v1/fees/{$fee->id}");
        $this->assertNotNull(DB::table('fees')->where('id', $fee->id)->value('deleted_at'));

        $id = $fee->id;
        $this->withToken($this->token)->patchJson("/api/v1/fees/{$id}/restore");
        $this->assertNull(DB::table('fees')->where('id', $fee->id)->value('deleted_at'));

        $this->withToken($this->token)->deleteJson("/api/v1/fees/{$id}");
        $this->withToken($this->token)->deleteJson("/api/v1/fees/{$id}/force");
        $this->assertModelMissing($fee);
    });
});
