<?php

use App\Models\Course;
use App\Models\RequestEntry;
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

describe('CRUD /api/v1/requests', function () {
    it('lists paginated', function () {
        RequestEntry::factory()->count(3)->create();
        $this->withToken($this->token)->getJson('/api/v1/requests')->assertStatus(200);
    });

    it('creates a request', function () {
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $response = $this->withToken($this->token)->postJson('/api/v1/requests', [
            'student_id' => $student->id,
            'type' => 'certificate',
            'status' => 'pending',
            'submission_date' => now()->toDateTimeString(),
            'details' => [
                'course_id' => $course->id,
                'purpose' => 'Job application',
                'urgent' => false,
            ],
        ]);
        $response->assertStatus(201)->assertJsonPath('data.type', 'certificate');
    });

    it('approves a pending request', function () {
        $request = RequestEntry::factory()->create(['status' => 'pending']);
        $this->withToken($this->token)
            ->postJson("/api/v1/requests/{$request->id}/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');
    });

    it('denies a pending request', function () {
        $request = RequestEntry::factory()->create(['status' => 'pending']);
        $this->withToken($this->token)
            ->postJson("/api/v1/requests/{$request->id}/deny", ['reason' => 'Insufficient documentation'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'denied');
    });

    it('shows, updates, soft-deletes, restores, force-deletes', function () {
        $req = RequestEntry::factory()->create();
        $this->withToken($this->token)->getJson("/api/v1/requests/{$req->id}")->assertStatus(200);
        $this->withToken($this->token)->putJson("/api/v1/requests/{$req->id}", ['status' => 'approved'])->assertStatus(200);

        $this->withToken($this->token)->deleteJson("/api/v1/requests/{$req->id}");
        $this->assertNotNull(DB::table('requests')->where('id', $req->id)->value('deleted_at'));

        $id = $req->id;
        $this->withToken($this->token)->patchJson("/api/v1/requests/{$id}/restore");
        $this->assertNull(DB::table('requests')->where('id', $req->id)->value('deleted_at'));

        $this->withToken($this->token)->deleteJson("/api/v1/requests/{$id}");
        $this->withToken($this->token)->deleteJson("/api/v1/requests/{$id}/force");
        $this->assertModelMissing($req);
    });
});
