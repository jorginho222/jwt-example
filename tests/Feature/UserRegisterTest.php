<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_can_register(): void
    {
        $data = [
            'email' => 'example@example.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);
        $response->assertJsonFragment([
            'data' => [
                'user' => [
                    'id' => 1,
                    'email' => 'example@example.com',
                    'name' => 'example',
                    'last_name' => 'example',
                ]
            ]
        ]);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'example@example.com',
            'name' => 'example',
            'last_name' => 'example',
        ]);
    }

    #[Test]
    public function a_registered_user_can_login(): void
    {
        $data = [
            'email' => 'example@example.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $this->postJson("{$this->apiBase}/user", $data);
        $response = $this->postJson("{$this->apiBase}/login", [
            'email' => 'example@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['token']]);
    }

    #[Test]
    public function email_must_be_required()
    {
        $data = [
            'email' => '',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function email_must_be_valid()
    {
        $data = [
            'email' => 'sdffff',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function email_must_be_unique()
    {
        User::factory()->create([
            'email' => 'example@example.com',
        ]);
        $data = [
            'email' => 'example@example.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function password_must_be_required()
    {
        $data = [
            'email' => 'example@example.com',
            'password' => '',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['password'], 'status', 'message']);
    }

    #[Test]
    public function password_must_be_at_least_8_characters()
    {
        $data = [
            'email' => 'example@example.com',
            'password' => 'pass',
            'name' => 'example',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['password'], 'status', 'message']);
    }

    #[Test]
    public function name_must_be_required()
    {
        $data = [
            'email' => 'example@example.com',
            'password' => 'password',
            'name' => '',
            'last_name' => 'example',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['name'], 'status', 'message']);
    }

    #[Test]
    public function last_name_must_be_required()
    {
        $data = [
            'email' => 'example@example.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => '',
        ];

        $response = $this->postJson("{$this->apiBase}/user", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['last_name'], 'status', 'message']);
    }
}
