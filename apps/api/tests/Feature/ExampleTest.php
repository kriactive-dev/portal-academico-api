<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_health_endpoint_returns_success(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    public function test_login_requires_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }
}
