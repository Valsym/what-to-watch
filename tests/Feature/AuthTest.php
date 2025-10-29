<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistration(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'file' => UploadedFile::fake()->image('avatar.jpg')
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['name', 'email', 'avatar', 'role'],
                    'token' => ['token']//, 'type']
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    public function testUserLogin(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['token']
            ]);
    }

    public function testGetCurrentUser(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user' => ['name', 'email', 'avatar', 'role']]
            ]);
    }

    public function testUpdateUserProfile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson('/api/user', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user' => ['name', 'email', 'avatar', 'role']]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function testUserLogout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/logout');

        $response->assertNoContent();
    }
}
