<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->adminUser = User::factory()->create([
        'email' => 'admin@ya-academico.com',
        'is_active' => true,
    ]);
    $this->adminUser->assignRole('administrator');
    $this->token = $this->adminUser->createToken('test-token')->plainTextToken;
});

describe('POST /api/v1/auth/login', function () {
    it('logs in with valid credentials', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ya-academico.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user' => ['id', 'name', 'email'], 'token'],
            ])
            ->assertJsonPath('success', true);
    });

    it('rejects login with invalid password', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ya-academico.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    });

    it('rejects login with non-existent email', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    });

    it('rejects login with deactivated user', function () {
        $this->adminUser->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ya-academico.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Account is deactivated.');
    });

    it('rejects login with missing email', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    });

    it('rejects login with missing password', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ya-academico.com',
        ]);

        $response->assertStatus(422);
    });

    it('rejects login with invalid email format', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    });
});

describe('POST /api/v1/auth/logout', function () {
    it('logs out successfully', function () {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    });

    it('rejects logout without token', function () {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    });
});

describe('GET /api/v1/auth/me', function () {
    it('returns current user', function () {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'admin@ya-academico.com');
    });

    it('rejects without token', function () {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    });

    it('rejects with invalid token', function () {
        $response = $this->withToken('invalid-token')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    });
});
