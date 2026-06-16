<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    (new RolesAndPermissionsSeeder)->run();
    $this->user = User::factory()->create(['is_active' => true]);
    $this->user->assignRole('administrator');
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('POST /api/v1/import/*', function () {
    it('requires authentication', function () {
        $this->postJson('/api/v1/import/students')->assertStatus(401);
        $this->postJson('/api/v1/import/trainers')->assertStatus(401);
        $this->postJson('/api/v1/import/courses')->assertStatus(401);
        $this->postJson('/api/v1/import/school-classes')->assertStatus(401);
        $this->postJson('/api/v1/import/fees')->assertStatus(401);
        $this->postJson('/api/v1/import/payments')->assertStatus(401);
    });

    it('validates file is required', function () {
        $this->withToken($this->token)
            ->postJson('/api/v1/import/students', [])
            ->assertStatus(422);
    });

    it('validates file type', function () {
        $txtFile = UploadedFile::fake()->create('data.txt', 100);

        $this->withToken($this->token)
            ->postJson('/api/v1/import/students', ['file' => $txtFile])
            ->assertStatus(422);
    });

    it('queues job for valid file', function () {
        Bus::fake();

        $xlsxFile = UploadedFile::fake()->create('students.xlsx', 100);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/import/students', ['file' => $xlsxFile]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['file', 'size']]);
    });
});
