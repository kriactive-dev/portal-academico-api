<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('CRUD /api/v1/users', function () {
    it('lists paginated', function () {
        User::factory()->count(3)->create(['is_active' => true]);
        $this->withToken($this->token)->getJson('/api/v1/users')->assertStatus(200);
    });

    it('creates a user', function () {
        $role = \Spatie\Permission\Models\Role::first();
        $response = $this->withToken($this->token)->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $response->assertStatus(201)->assertJsonPath('data.name', 'New User');
    });

    it('validates unique email on create', function () {
        User::factory()->create(['email' => 'dup@test.com', 'is_active' => true]);
        $role = \Spatie\Permission\Models\Role::first();
        $this->withToken($this->token)->postJson('/api/v1/users', [
            'name' => 'Dup',
            'email' => 'dup@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ])->assertStatus(422);
    });

    it('shows a user', function () {
        $target = User::factory()->create(['is_active' => true]);
        $this->withToken($this->token)->getJson("/api/v1/users/{$target->id}")->assertStatus(200);
    });

    it('updates a user', function () {
        $target = User::factory()->create(['is_active' => true]);
        $this->withToken($this->token)->putJson("/api/v1/users/{$target->id}", ['name' => 'Updated Name'])
            ->assertJsonPath('data.name', 'Updated Name');
    });

    it('toggles user status', function () {
        $target = User::factory()->create(['is_active' => true]);
        $this->withToken($this->token)
            ->patchJson("/api/v1/users/{$target->id}/toggle-status")
            ->assertJsonPath('data.isActive', false);

        $this->withToken($this->token)
            ->patchJson("/api/v1/users/{$target->id}/toggle-status")
            ->assertJsonPath('data.isActive', true);
    });

    it('soft-deletes, restores, force-deletes', function () {
        $target = User::factory()->create(['is_active' => true]);
        $this->withToken($this->token)->deleteJson("/api/v1/users/{$target->id}");
        $this->assertNotNull(User::withTrashed()->find($target->id)->deleted_at);

        $id = $target->id;
        $this->withToken($this->token)->patchJson("/api/v1/users/{$id}/restore");
        $this->assertDatabaseHas('users', ['id' => $target->id, 'deleted_at' => null]);

        $this->withToken($this->token)->deleteJson("/api/v1/users/{$id}");
        $this->withToken($this->token)->deleteJson("/api/v1/users/{$id}/force");
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    });
});
