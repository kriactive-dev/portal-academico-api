<?php

use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('CRUD /api/v1/trainers', function () {
    it('lists paginated', function () {
        Trainer::factory()->count(3)->create();
        $this->withToken($this->token)->getJson('/api/v1/trainers')->assertStatus(200);
    });

    it('creates a trainer', function () {
        $response = $this->withToken($this->token)->postJson('/api/v1/trainers', [
            'name' => 'Dr. Smith',
            'email' => 'smith@example.com',
            'phone' => '911111111',
            'specialty' => 'Mathematics',
            'status' => 'active',
        ]);
        $response->assertStatus(201)->assertJsonPath('data.name', 'Dr. Smith');
    });

    it('validates unique email', function () {
        Trainer::factory()->create(['email' => 'dup@example.com']);
        $this->withToken($this->token)->postJson('/api/v1/trainers', [
            'name' => 'Dup',
            'email' => 'dup@example.com',
            'phone' => '911111112',
            'status' => 'active',
        ])->assertStatus(422);
    });

    it('shows a trainer', function () {
        $trainer = Trainer::factory()->create();
        $this->withToken($this->token)->getJson("/api/v1/trainers/{$trainer->id}")->assertStatus(200);
    });

    it('updates a trainer', function () {
        $trainer = Trainer::factory()->create();
        $this->withToken($this->token)->putJson("/api/v1/trainers/{$trainer->id}", ['name' => 'Updated'])
            ->assertJsonPath('data.name', 'Updated');
    });

    it('soft-deletes, restores, force-deletes', function () {
        $t = Trainer::factory()->create();
        $this->withToken($this->token)->deleteJson("/api/v1/trainers/{$t->id}");
        $this->assertNotNull(Trainer::withTrashed()->find($t->id)->deleted_at);

        $this->withToken($this->token)->patchJson("/api/v1/trainers/{$t->id}/restore");
        $this->assertNull(Trainer::withTrashed()->find($t->id)->deleted_at);

        $this->withToken($this->token)->deleteJson("/api/v1/trainers/{$t->id}");
        $this->withToken($this->token)->deleteJson("/api/v1/trainers/{$t->id}/force");
        $this->assertModelMissing($t);
    });
});
