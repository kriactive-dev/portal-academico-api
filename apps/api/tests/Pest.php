<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you define here defines which test case Pest uses for
| tests in each test group. You can change this per-group.
|
*/

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

uses(
    PHPUnit\Framework\TestCase::class,
)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're creating tests, you can chain expectations with the
| `expect()` function to make assertions. You may also extend
| Pest's expectation layer with custom functions.
|
*/

expect()->extend('toBeSuccess', function () {
    return $this->toHaveKey('success', true);
});

expect()->extend('toBeFailure', function () {
    return $this->toHaveKey('success', false);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Helper functions for tests.
|
*/

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Create an admin user and return it with a Sanctum token.
 */
function createAdmin(): array
{
    (new RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create([
        'email' => 'admin@ya-academico.com',
        'is_active' => true,
    ]);
    $user->assignRole('administrator');

    $token = $user->createToken('test-token')->plainTextToken;

    return ['user' => $user, 'token' => $token];
}

/**
 * Create a user with a specific role.
 */
function createUserWithRole(string $role): array
{
    (new RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    $token = $user->createToken('test-token')->plainTextToken;

    return ['user' => $user, 'token' => $token];
}

/**
 * Create a secretary user.
 */
function createSecretary(): array
{
    return createUserWithRole('secretary');
}

/**
 * Act as a specific user in tests.
 */
function actingAsUser(string $role = 'administrator'): Tests\TestCase
{
    $data = createUserWithRole($role);
    return test()->actingAs($data['user']);
}
