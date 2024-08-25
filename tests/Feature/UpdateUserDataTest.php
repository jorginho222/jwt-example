<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateUserDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    #[Test]
    public function an_authenticated_user_can_modify_his_data(): void
    {
        $data = [
            'name' => 'newName',
            'last_name' => 'newLastName',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/profile", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);
        $response->assertJsonFragment([
            'data' => [
                'user' => [
                    'id' => 1,
                    'email' => 'example@example.com',
                    'name' => 'newName',
                    'last_name' => 'newLastName',
                ]
            ],
            'status' => 200,
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'example@example.com',
            'name' => 'User',
            'last_name' => 'Test',
        ]);
    }

    #[Test]
    public function an_authenticated_user_cannot_modify_his_email(): void
    {
        $data = [
            'email' => 'newEmail@example.com',
            'name' => 'newName',
            'last_name' => 'newLastName',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/profile", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);
        $response->assertJsonFragment([
            'data' => [
                'user' => [
                    'id' => 1,
                    'email' => 'example@example.com',
                    'name' => 'newName',
                    'last_name' => 'newLastName',
                ]
            ],
            'status' => 200,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'example@example.com',
            'name' => 'newName',
            'last_name' => 'newLastName',
        ]);
    }

    #[Test]
    public function an_authenticated_user_cannot_modify_his_password(): void
    {
        $data = [
            'password' => 'newPassword',
            'name' => 'newName',
            'last_name' => 'newLastName',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/profile", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);

        $user = User::find(1);
        $this->assertFalse(Hash::check('newPassword', $user->password));
    }

    #[Test]
    public function name_must_be_required()
    {
        $data = [
            'name' => '',
            'last_name' => 'newLastName',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/profile", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['name'], 'status', 'message']);
    }

    #[Test]
    public function last_name_must_be_required()
    {
        $data = [
            'name' => 'newName',
            'last_name' => '',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/profile", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['last_name'], 'status', 'message']);
    }
}
