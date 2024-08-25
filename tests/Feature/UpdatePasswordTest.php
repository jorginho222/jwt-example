<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    #[Test]
    public function an_authenticated_user_can_update_his_password(): void
    {
        $data = [
            'old_password' => 'password',
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/password", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors']);
        $user = User::find(1);
        $this->assertTrue(Hash::check('newPassword', $user->password));
    }

    #[Test]
    public function old_password_must_be_validated(): void
    {
        $data = [
            'old_password' => 'wrongPassword',
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/password", $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['old_password']]);
    }

    #[Test]
    public function old_password_must_be_required(): void
    {
        $data = [
            'old_password' => '',
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/password", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['old_password'], 'status', 'message']);
    }

    #[Test]
    public function password_must_be_required(): void
    {
        $data = [
            'old_password' => 'password',
            'password' => '',
            'password_confirmation' => 'newPassword',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/password", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['password'], 'status', 'message']);
    }

    #[Test]
    public function password_must_be_confirmed(): void
    {
        $data = [
            'old_password' => 'password',
            'password' => 'newPassword',
            'password_confirmation' => '',
        ];

        $response = $this->apiAs(User::find(1), 'PUT', "{$this->apiBase}/password", $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['password'], 'status', 'message']);
    }

}
