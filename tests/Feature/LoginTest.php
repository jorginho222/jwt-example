<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    #[Test]
    public function it_can_login_an_existing_user(): void
    {
        $this->withoutDeprecationHandling();
        $credentials = ['email' => 'example@example.com', 'password' => 'password'];

        $response = $this->postJson("{$this->apiBase}/login", $credentials);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['token']]);
    }

    #[Test]
    public function it_cannot_login_a_not_existing_user(): void
    {
        $credentials = ['email' => 'example@notexisting.com', 'password' => 'eight_chars'];

        $response = $this->postJson("{$this->apiBase}/login", $credentials);

        $response->assertStatus(401);
    }

    #[Test]
    public function email_must_be_required()
    {
        $credentials = ['password' => 'password'];

        $response = $this->postJson("{$this->apiBase}/login", $credentials);
        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }

    #[Test]
    public function email_must_be_valid()
    {
        $credentials = ['email' => 'sdffff', 'password' => 'password'];

        $response = $this->postJson("{$this->apiBase}/login", $credentials);
        $response->assertStatus(422);
        $response->assertJsonStructure(['data', 'errors' => ['email'], 'status', 'message']);
    }
}
